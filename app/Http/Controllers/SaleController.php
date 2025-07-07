<?php

namespace App\Http\Controllers;


use App\Models\Sale;
use Carbon\Carbon;
use Exception;
use App\Models\Configuration;
use App\Models\SubConfiguration;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\ProductBatch;
use App\Http\Controllers\ConfigurationController;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configCtrl = new ConfigurationController();
        $inventory_tracking = $configCtrl->getOneConfigMethod('inventory_tracking_method');

        $inDeliveryQuery = Sale::where('status', 'In delivery');
        $completedQuery = Sale::where('status', 'completed');

        // Only add withSum if inventory tracking is perpetual
        if ($inventory_tracking == 'perpetual') {
            $inDeliveryQuery->withSum('saleDetails', 'cogs_sale');
            $completedQuery->withSum('saleDetails', 'cogs_sale');
        }

        $inDeliverySales = $inDeliveryQuery
            ->orderBy('sale_date', 'DESC')
            ->paginate(5, ['*'], 'delivery_page');

        $completedSales = $completedQuery
            ->orderBy('sale_date', 'DESC')
            ->paginate(5, ['*'], 'completed_page');

        $inDeliveryCount = Sale::where('status', 'In delivery')->count();
        $completedCount = Sale::where('status', 'completed')->count();

        $inDeliveryTotalAmount = Sale::where('status', 'In delivery')->sum('total_price');
        $completedTotalAmount = Sale::where('status', 'completed')->sum('total_price');

        return view('sales.index', compact(
            'inDeliverySales',
            'completedSales',
            'inDeliveryCount',
            'completedCount',
            'inDeliveryTotalAmount',
            'completedTotalAmount',
            'inventory_tracking'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get payment methods (active only)
        $paymentMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'sale_payment');
            })
            ->where('status', 1)
            ->get();

        // Get shipping methods (active only)
        $shippingMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'shipping_sale_method');
            })
            ->where('status', 1)
            ->get();

        // Get discount status from main configuration
        $discountStatus = Configuration::where('name', 'sale_discount')->first();
        $discStatus = $discountStatus ? $discountStatus->status : 0;

        // Get all discount methods with their status
        $discountMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'sale_discount');
            })
            ->get(); // Don't filter by status here, let the view handle it

        $invoiceNumber = $this->generateInvoiceNumber();

        // Get default cashback value (only if DISC-03 is active)
        $cashbackConfig = SubConfiguration::where('code', 'DISC-03')
            ->where('status', 1)
            ->first();
        $cashbackDefault = $cashbackConfig ? $cashbackConfig->value : 0;

        return view('sales.create', compact(
            'invoiceNumber',
            'paymentMethods',
            'shippingMethods',
            'discountMethods',
            'discStatus',
            'cashbackDefault'
        ));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate form data
            $validated = $request->validate([
                'form_data' => 'required|json'
            ]);

            $formData = json_decode($request->form_data, true);

            // Additional validation for the actual data
            if (empty($formData['sale_products'])) {
                throw new \Exception('No products selected');
            }

            if (empty($formData['customers'])) {
                throw new \Exception('Customer is required');
            }

            // Validate customer exists
            $customer = \App\Models\Customer::find($formData['customers']);
            if (!$customer) {
                throw new \Exception('Selected customer not found');
            }

            // Get configuration once
            $configCtrl = new ConfigurationController();
            $cogsMethod = $configCtrl->getOneConfigMethod('cogs_method');
            $inventoryMethod = $configCtrl->getOneConfigMethod('inventory_tracking_method');

            // Bulk load products for better performance
            $productIds = collect($formData['sale_products'])->pluck('product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // Validate products and stock before processing
            foreach ($formData['sale_products'] as $productData) {
                $product = $products[$productData['product_id']] ?? null;
                if (!$product) {
                    throw new \Exception("Product with ID {$productData['product_id']} not found");
                }

                if ($product->total_stock < $productData['quantity']) {
                    throw new \Exception("Insufficient stock for product: {$product->product_name}. Available: {$product->total_stock}, Requested: {$productData['quantity']}");
                }
            }


            if ($formData['delivery_method'] == 'DEL-02') {
                $delivery_cost = $formData['delivery_cost'] ?? null;
                $recipient_name = $formData['recipient_name'] ?? null;
                $customer_address = $formData['customer_address'] ?? null;
            } else {
                $delivery_cost = null;
                $recipient_name = null;
                $customer_address = null;
            }

            // Create sale record
            $sale = Sale::create([
                'invoice_number' => $formData['invoice_number'],
                'sale_date' => $formData['order_date'],
                'customers_id' => $formData['customers'],
                'total_price' => $formData['grand_total'],
                'users_id' => auth()->id() ?? 1,
                'payment_method' => $formData['payment_method'],
                'delivery_method' => $formData['delivery_method'],
                'global_discount' => $formData['global_discount'] ?? 0,
                'discount_cashback' => $formData['cashback'] ?? 0,
                'global_discount_percentage' => $formData['global_discount_percentage'] ?? 0,
                'global_discount_amount' => $formData['global_discount_amount'] ?? 0,
                'subtotal' => $formData['subtotal'] ?? 0,
                'cogs_method' => $cogsMethod,
                'delivery_cost' => $delivery_cost,
                'recipient_name' => $recipient_name,
                'customer_address' => $customer_address

            ]);

            // Process each product
            foreach ($formData['sale_products'] as $productData) {
                $product = $products[$productData['product_id']];
                $this->processSaleProduct($sale, $productData, $product, $cogsMethod, $inventoryMethod);
            }

            DB::commit();
            return redirect()->route('sales.index')->with('success', 'Sale created successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    protected function processSaleProduct(Sale $sale, array $productData, Product $product, string $cogsMethod, string $inventoryMethod)
    {
        $subtotal = $productData['quantity'] * $productData['unit_price'];
        $discountAmount = $subtotal * (($productData['discount_percentage'] ?? 0) / 100);
        $totalAfterDiscount = $subtotal - $discountAmount;

        $saleDetail = SaleDetail::create([
            'sales_id' => $sale->id,
            'products_id' => $productData['product_id'],
            'quantity' => $productData['quantity'],
            'subtotal' => $totalAfterDiscount,
            'cogs_sale' => $this->calculateCogsSale($product, $productData['quantity'], $cogsMethod),
            'discount_value' => $discountAmount,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Only reduce inventory immediately for pickup orders (DEL-01)
        if ($sale->delivery_method === 'DEL-01') {
            $this->reduceProductInventory($product, $saleDetail, $cogsMethod, $inventoryMethod);
        }

        // For delivery orders (DEL-02), inventory will be reduced when order is delivered
        // This allows for order cancellation without inventory complications
    }

    /**
     * Reduce product inventory based on inventory tracking method
     */
    protected function reduceProductInventory(Product $product, SaleDetail $saleDetail, string $cogsMethod, string $inventoryMethod)
    {
        // Skip inventory reduction for periodic method
        if ($inventoryMethod === 'periodic') {
            return;
        }

        $remainingQty = $saleDetail->quantity;

        $batchQuery = ProductBatch::where('products_id', $product->id)
            ->where('stock', '>', 0);

        // Apply COGS method ordering
        switch ($cogsMethod) {
            case 'FIFO':
                $batchQuery->orderBy('purchase_date', 'asc');
                break;
            case 'LIFO':
                $batchQuery->orderBy('purchase_date', 'desc');
                break;
            default: // average
                $batchQuery->orderBy('expired_date', 'asc');
                break;
        }

        $batches = $batchQuery->get();

        foreach ($batches as $batch) {
            if ($remainingQty <= 0) break;

            $usedQty = min($batch->stock, $remainingQty);
            $batch->stock -= $usedQty;

            if ($batch->stock <= 0) {
                $batch->empty_status = 1;
            }

            $batch->save();
            $remainingQty -= $usedQty;
        }

        if ($remainingQty > 0) {
            throw new \Exception("Not enough stock for product {$product->product_name}. Missing: {$remainingQty} units");
        }

        // Update product total stock
        $product->total_stock -= $saleDetail->quantity;
        $product->save();
    }

    /**
     * Reduce inventory for delivery orders when they are delivered
     * Call this method when delivery status changes to 'delivered'
     */
    public function processDeliveryInventoryReduction(Sale $sale)
    {
        // Only process if this is a delivery order
        if ($sale->delivery_method !== 'DEL-02') {
            return;
        }

        $configCtrl = new ConfigurationController();
        $cogsMethod = $configCtrl->getOneConfigMethod('cogs_method');
        $inventoryMethod = $configCtrl->getOneConfigMethod('inventory_tracking_method');

        foreach ($sale->saleDetails as $saleDetail) {
            $product = $saleDetail->product;
            $this->reduceProductInventory($product, $saleDetail, $cogsMethod, $inventoryMethod);
        }
    }

    protected function calculateCogsSale(Product $product, int $quantity, string $cogsMethod): float
    {
        switch ($cogsMethod) {
            case 'FIFO':
            case 'LIFO':
                $batchQuery = ProductBatch::where('products_id', $product->id)
                    ->where('stock', '>', 0)
                    ->orderBy('purchase_date', $cogsMethod === 'FIFO' ? 'asc' : 'desc');

                $batches = $batchQuery->get();
                $remainingQty = $quantity;
                $totalCost = 0;

                foreach ($batches as $batch) {
                    if ($remainingQty <= 0) break;

                    $usedQty = min($batch->stock, $remainingQty);
                    $totalCost += $usedQty * $batch->cost_per_batch;
                    $remainingQty -= $usedQty;
                }


                if ($remainingQty > 0) {
                    throw new \Exception("Insufficient stock for COGS calculation for product: {$product->product_name}");
                }

                return $totalCost;

            case 'average':
                return $quantity * $product->cost;

            default:
                return 0; // If COGS is not active
        }
    }



    // Helper method to get configuration values
    private function getSubConfigValue($code, $default = null)
    {
        $config = SubConfiguration::where('code', $code)
            ->where('status', 1)
            ->first();

        return $config ? $config->value : $default;
    }



    public function show(Sale $sale)
    {
        // Load relationships
        $sale->load(['customer', 'saleDetails.product', 'user']);

        // Get customer information
        $customer = $sale->customer;

        // Get sale details
        $saleDetails = $sale->saleDetails;

        return view('sales.detail', compact('sale', 'customer', 'saleDetails'));
    }

    /**
     * Mark sale as shipped and reduce inventory for delivery orders
     */
    public function ship(Request $request, Sale $sale)
    {
        DB::beginTransaction();

        try {
            // Validate that this is a delivery order
            if ($sale->delivery_method !== 'DEL-02') {
                return back()->withErrors(['error' => 'This is not a delivery order']);
            }

            // Validate that it hasn't been shipped yet
            if ($sale->shipped_date) {
                return back()->withErrors(['error' => 'This order has already been shipped']);
            }

            // Validate the request
            $request->validate([
                'shipped_date' => 'required|date'
            ]);

            // Update the sale with shipping information
            $sale->update([
                'shipped_date' => $request->shipped_date
            ]);

            // Reduce inventory for all products in this sale
            $this->processDeliveryInventoryReduction($sale);

            DB::commit();

            return redirect()->route('sales.show', $sale->id)
                ->with('success', 'Order marked as shipped and inventory has been updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }


    public function generateInvoiceNumber()
    {

        $currentDate = Carbon::now()->format('Y_m_d');


        $latestInvoice = Sale::whereDate('sale_date', $currentDate)
            ->orderBy('id', 'desc')
            ->first();

        // Determine the sequence number
        $sequenceNumber = $latestInvoice ? ((int) substr($latestInvoice->invoice_number, -3)) + 1 : 1;

        // Format sequence number with leading zeros
        $formattedSequence = str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);

        // Generate the invoice number in the format `YYYYMMDD_sequenceNumber`
        $invoiceNumber = $currentDate . '_' . $formattedSequence;

        return $invoiceNumber;
    }

    public function query(Request $request)
    {
        $search = $request->get('search', '');

        $query = Product::where('total_stock', '>', 0); // Only products with stock

        // If search term provided, filter by it
        if (!empty($search)) {
            $query->where('product_name', 'LIKE', "%{$search}%")
                ->orWhere('product_code', 'LIKE', "%{$search}%");
        }

        $products = $query->limit(20)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'product_name' => $product->product_name,
                    'price' => $product->price,
                    'total_stock' => $product->total_stock
                ];
            });

        return response()->json($products);
    }
}
