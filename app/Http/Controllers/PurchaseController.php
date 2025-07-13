<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\SubConfiguration;
use App\Http\Controllers\DebtHistoryController;
use App\Models\DebtHistory;
use Carbon\Carbon;
use Carbon\Exceptions\Exception;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\TryCatch;

class PurchaseController extends Controller
{


    public function index()
    {

        $inDeliveryPurchases = Purchase::where('status', 'In delivery')
            ->orderBy('purchase_date', 'DESC')
            ->simplePaginate(5, ['*'], 'delivery_page');

        $completedPurchases = Purchase::where('status', 'completed')
            ->orderBy('purchase_date', 'DESC')
            ->simplePaginate(5, ['*'], 'completed_page');


        $inDeliveryCount = Purchase::where('status', 'In delivery')->count();
        $completedCount = Purchase::where('status', 'completed')->count();

        $inDeliveryTotalAmount = Purchase::where('status', 'In delivery')->sum('total_price');
        $completedTotalAmount = Purchase::where('status', 'completed')->sum('total_price');

        return view('purchases.index', compact(

            'inDeliveryPurchases',
            'completedPurchases',
            'inDeliveryCount',
            'completedCount',
            'inDeliveryTotalAmount',
            'completedTotalAmount'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {


        
        $purchaseMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'purchase_payment');
            })->where('status', 1)->get();

        $receiveMethods = SubConfiguration::with('configuration')
            ->whereHas('configuration', function ($query) {
                $query->where('name', 'receiving_purchase_method');
            })
            ->get();
        $activeMethods = $receiveMethods->where('status', 1);
        $expiredSettings =  SubConfiguration::where('code',"EXP-01")->first();
        $invoiceNumber = $this->generateInvoiceNumber();

        return view('purchases.create', compact('invoiceNumber', 'receiveMethods', 'purchaseMethods', 'activeMethods','expiredSettings'));
    }


    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $formData = json_decode($request->form_data, true);

            $request->validate([
                'order_date' => 'required|date',
                'invoice_number' => 'required|string',
                'suppliers' => 'required|exists:suppliers,id',
                'grand_total' => 'required|numeric',
                'form_data' => 'required|json'
            ]);

            // Conditional validation
            if ($formData['receive_method'] === 'RE-02' && empty($formData['delivery_cost'])) {
                throw new \Exception('Delivery cost required for RE-02');
            }
            if ($formData['payment_method'] === 'P-PAY-03' && empty($formData['due_date'])) {
                throw new \Exception('Due date required for P-PAY-03');
            }

            // Create purchase
            $purchase = Purchase::create([
                'invoice_number' => $formData['invoice_number'],
                'purchase_date' => $formData['order_date'],
                'suppliers_id' => $formData['suppliers'],
                'total_price' => $formData['grand_total'],
                'status' => $formData['receive_method'] === 'RE-02' ? 'In delivery' : 'completed',
                'payment_method' => $formData['payment_method'],
                'receive_method' => $formData['receive_method'],
                'delivery_cost' => $formData['receive_method'] === 'RE-02' ? $formData['delivery_cost'] : null,
                'users_id' => auth()->id()
            ]);

            // Create debt if needed
            if ($formData['payment_method'] === 'P-PAY-03') {
                DebtHistory::create([
                    'debt_nominal' => $formData['grand_total'],
                    'bill_date' => $formData['order_date'],
                    'due_date' => $formData['due_date'],
                    'status' => 'pending',
                    'supplier_id' => $formData['suppliers'],
                    'purchases_id' => $purchase->id,
                ]);
            }

            // Process products
            foreach ($formData['purchased_products'] as $product) {
                $this->processPurchaseProduct($purchase, $product);
            }

            DB::commit();
            return redirect()->route('purchases.index')->with('success', 'Purchase created');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function confirmReceipt(Request $request, Purchase $purchase)
    {
        // Validate purchase is in correct status
        if ($purchase->status !== 'In delivery') {
            return back()->withErrors(['error' => 'Only purchases In delivery can be received']);
        }

        // Validate request data
        $validated = $request->validate([
            'receive_date' => 'required|date'
        ]);

        DB::beginTransaction();

        try {
            // Update purchase status and receive date
            $purchase->update([
                'receive_date' => $validated['receive_date'],
                'status' => 'completed'
            ]);

            // Only process batches if receive method is RE-02
            if ($purchase->receive_method == 'RE-02') {
                foreach ($purchase->purchaseDetails as $detail) {
                    $this->processPurchaseReceipt($detail);
                }
            }

            DB::commit();
            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', 'Purchase received successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to confirm receipt: ' . $e->getMessage()]);
        }
    }

    protected function processPurchaseReceipt(PurchaseDetail $detail)
    {
        $configCtrl = new ConfigurationController();
        $inventoryMethod = $configCtrl->getOneConfigMethod('inventory_tracking_method');
        $cogsMethod = $configCtrl->getOneConfigMethod('cogs_method');

        $product = $detail->product;

        // Calculate dates
        $purchaseDate = Carbon::parse($detail->purchase->purchase_date);
        $expiredDate = $purchaseDate->copy()->addDays($product->expired_date_settings);
        $bestBeforeDate = $purchaseDate->copy()->addDays($product->best_before_date_settings);

        // Create product batch
        $batchCtrl = new ProductBatchController();
        $productBatch = new ProductBatch();
        $productBatch->serial_code = $batchCtrl->generateSerialCode($product->id);
        $productBatch->stock = $detail->quantity;
        $productBatch->purchase_date = $purchaseDate;
        $productBatch->expired_date = $expiredDate;
        $productBatch->best_before_date = $bestBeforeDate;
        $productBatch->empty_status = 0;
        $productBatch->cost_per_batch = $detail->subtotal / $detail->quantity;
        $productBatch->products_id = $product->id;
        $productBatch->purchase_details_id = $detail->id;
        $productBatch->save();

        $this->updateProductInventory($product, $detail, $inventoryMethod, $cogsMethod);
    }



    protected function processPurchaseProduct(Purchase $purchase, array $productData)
    {
        try {
            $purchaseDetail = PurchaseDetail::create([
                'purchases_id' => $purchase->id,
                'products_id' => $productData['product_id'],
                'quantity' => $productData['quantity'],
                'purchase_price' => $productData['purchase_price'],
                'subtotal' => $productData['total_price'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Only create product batch if receive_method is RE-01
            if ($purchase->receive_method == 'RE-01') {
                // Get inventory configuration
                $configCtrl = new ConfigurationController();
                $inventoryMethod = $configCtrl->getOneConfigMethod('inventory_tracking_method');
                $cogsMethod = $configCtrl->getOneConfigMethod('cogs_method');

                // Find the product
                $product = Product::findOrFail($purchaseDetail->products_id);

                // Calculate dates
                $purchaseDate = Carbon::parse($purchase->purchase_date);
                $expiredDate = $purchaseDate->copy()->addDays($product->expired_date_settings);

                // Create product batch
                $batchCtrl = new ProductBatchController();
                $productBatch = new ProductBatch();
                $productBatch->serial_code = $batchCtrl->generateSerialCode($product->id);
                $productBatch->stock = $purchaseDetail->quantity;
                $productBatch->purchase_date = $purchase->purchase_date;
                $productBatch->expired_date = $expiredDate;
                $productBatch->cost_per_batch = $purchaseDetail->subtotal / $purchaseDetail->quantity;
                $productBatch->empty_status = 0;
                $productBatch->products_id = $product->id;
                $productBatch->purchase_details_id = $purchaseDetail->id;
                $productBatch->save();

                // Update product stock and cost based on inventory method
                $this->updateProductInventory($product, $purchaseDetail, $inventoryMethod, $cogsMethod);
            }
        } catch (\Throwable $th) {
            throw new \Exception("Error processing purchase product: " . __FUNCTION__ . " " . $th->getMessage());
        }
    }


    protected function updateProductInventory(Product $product, PurchaseDetail $purchaseDetail, string $inventoryMethod, string $cogsMethod)
    {
        $oldStock = $product->total_stock;
        $newStock = $oldStock + $purchaseDetail->quantity;

        // Hitung harga beli satuan
        $unitPurchasePrice = $purchaseDetail->subtotal / $purchaseDetail->quantity;

        // Periodik: tidak update cost/stock di sini
        if ($inventoryMethod === 'periodic') {
            return;
        }

        switch ($cogsMethod) {
            case 'FIFO':
            case 'LIFO':
                // Tidak menghitung ulang, hanya update cost sebagai info pembelian terakhir
                $newCost = $unitPurchasePrice;
                break;

            // case 'standard':
            //     // Biarkan tetap (user yang isi manual), tapi update stock tetap dilakukan
            //     $newCost = $product->cost;
            //     break;

            case 'average':
                if ($oldStock == 0) {
                    $newCost = $unitPurchasePrice;
                } else {
                    $totalValue = ($product->cost * $oldStock) + ($unitPurchasePrice * $purchaseDetail->quantity);
                    $newCost = $totalValue / $newStock;
                }
                break;

            default:
                throw new \Exception("Unknown COGS method: {$cogsMethod}");
        }

        // Update stock dan cost (cost tetap akan diisi, tapi tidak digunakan untuk laba)
        $updateData = [
            'total_stock' => $newStock,
            'cost' => $newCost
        ];

        $product->update($updateData);
    }



    /**
     * Display the specified resource.
     */
    public function show(String $id)
    {
        $purchase = Purchase::findOrFail($id);
        $purchaseDetails = $purchase->purchaseDetails;
        $supplier = $purchase->supplier;

        return view('purchases.detail', compact('purchase', 'purchaseDetails', 'supplier'));
    }

    public function receive() {}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }


    public function generateInvoiceNumber()
    {

        $currentDate = Carbon::now()->format('Y-m-d');
        $latestInvoice = Purchase::whereDate('purchase_date', $currentDate)
            ->orderBy('id', 'desc')
            ->first();
        $sequenceNumber = $latestInvoice ? ((int) substr($latestInvoice->invoice_number, -3)) + 1 : 1;
        $formattedSequence = str_pad($sequenceNumber, 3, '0', STR_PAD_LEFT);
        $invoiceNumber = $currentDate . '_' . $formattedSequence;

        return $invoiceNumber;
    }

    public function productQuery(Request $request)
    {
        $search = $request->input('search', '');

        $products = Product::query()
            ->where('product_name', 'LIKE', '%' . $search . '%')
            ->select('id', 'product_name', 'cost as price', 'total_stock')
            ->limit(10)
            ->get();

        return response()->json($products);
    }


    public function updateStatus(Request $request)
    {
        try {
            $request->validate([
                'purchaseId' => 'required',
                'status' => 'required'
            ]);

            $purchaseId = $request->purchaseId;
            $status = $request->status;

            if ($status == "In delivery") {
            } else if ($status == "completed") {
            }
        } catch (Exception $e) {
        }
    }
}
