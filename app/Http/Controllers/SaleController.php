<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Configuration;
use App\Models\Product;
use App\Models\SubConfiguration;
use App\Models\SaleDetail;
use App\Models\ProductBatch;
use App\Models\JournalEntry;
use App\Models\Account;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;


use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{

    // #region Public Methods

    private $cogs = 0;
    private $delivery = 0;
    private $discount = 0;

    public function index()
    {


        $sales = Sale::orderBy('invoice_number', 'DESC')->simplePaginate(10, ['*'], 'completed_page');
        $salesCount = Sale::count();
        $totalSalesTransaction = Sale::sum('total_price');


        return view('sales.index', compact(
            'sales',
            'salesCount',
            'totalSalesTransaction',

        ));
    }

    public function create()
    {
        $paymentMethods  = $this->getActiveSubConfig('sale_payment');
        $shippingMethods = $this->getActiveSubConfig('shipping_sale_method');
        $discountMethods = $this->getAllSubConfig('sale_discount');
        $saleShippingConfig = SubConfiguration::where('code', 'SHP-S-01')->first();

        $discountStatus  = Configuration::where('name', 'sale_discount')->first();
        $discStatus      = $discountStatus ? $discountStatus->status : 0;


        $invoiceNumber   = $this->generateInvoiceNumber();
        $checkAccount = $this->checkAccount();


        return view('sales.create', compact(
            'invoiceNumber',
            'paymentMethods',
            'shippingMethods',
            'discountMethods',
            'discStatus',
            'checkAccount',
            'saleShippingConfig'
        ));
    }

    private function checkAccount(): string
    {
        $assetCodes = ['1101', '1102', '1103'];
        $checkAccount = '';
        // Ambil semua akun beserta total debit dan kredit dari detail jurnal
        $accounts = Account::whereIn('code', $assetCodes)
            ->withSum('journalEntriesDetails as total_debit',  'debit')
            ->withSum('journalEntriesDetails as total_credit', 'credit')
            ->get();

        // Cek apakah semua akun kosong (tidak ada transaksi sama sekali)
        $allEmpty = $accounts->every(function ($account) {
            return ($account->total_debit  ?? 0) == 0
                && ($account->total_credit ?? 0) == 0;
        });

        if ($allEmpty) {
            $checkAccount = 'kosong';
        }

        return $checkAccount;
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

    public function ship(Request $request, Sale $sale)
    {
        if ($this->isPickupOrder($sale)) {
            return back()->withErrors(['error' => 'This is not a delivery order']);
        }

        if ($sale->shipped_date) {
            return back()->withErrors(['error' => 'This order has already been shipped']);
        }

        $request->validate(['shipped_date' => 'required|date']);

        DB::beginTransaction();
        try {
            $sale->update(['shipped_date' => $request->shipped_date]);
            $this->processDeliveryInventoryReduction($sale);

            DB::commit();
            return redirect()->route('sales.show', $sale->id)->with('success', 'Order shipped and inventory updated');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function edit(Sale $sale) {}
    public function update(Request $request, Sale $sale) {}
    public function destroy(Sale $sale) {}

    // #endregion

    // #region Record Creation


    public function storeAjax(Request $request)
    {

        $validated = $request->validate([
            'invoice_number' => 'required|string',
            'total_price'    => 'required',
            'sale_date'      => 'required|date',
            'customers_id'   => 'required',
            'payment_method' => 'required',
            'status'         => 'required',
            'global_discount' => 'nullable',
            'total_discount' => 'nullable',
            'discount_cashback'       => 'nullable',
            'recipient_name' => 'nullable',
            'customer_address' => 'nullable',
            'shipped_date' => 'nullable|date',
            'delivery_cost' => 'nullable',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.subtotal' => 'required|numeric|min:0',
            'products.*.discount_value' => 'nullable|numeric|min:0',

        ]);

        $this->validateForm($validated);

        DB::beginTransaction();
        try {

            $sale = $this->createSaleRecord($validated);
            $journalEntry = JournalEntryController::storeJournalSale($sale);
            foreach ($validated['products'] as $productData) {
                $this->processSaleProduct($sale, $productData);
            }

            $accounts = JournalEntryController::checkSaleAccount($sale);

            $totalDiscountAmount = (float) ($sale->total_discount ?? 0)
                + (float) ($sale->global_discount ?? 0)
                + (float) ($sale->discount_cashback ?? 0);


            foreach ($accounts['base_accounts'] as $account) {
                $amount = $account['position'] === 'debit'
                    ? ($sale->total_price - $totalDiscountAmount)
                    : $sale->total_price;

                JournalEntryController::storeJournalEntry($account['code'], $account['position'], $amount, $journalEntry->id);
            }


            if (!empty($accounts['discount_accounts'])) {
                foreach ($accounts['discount_accounts'] as $discountAccount) {
                    JournalEntryController::storeJournalEntry(
                        $discountAccount['code'],
                        $discountAccount['position'],
                        $totalDiscountAmount,
                        $journalEntry->id
                    );
                }
            }


            if (!is_null($accounts['cogs_accounts'])) {
                foreach ($accounts['cogs_accounts'] as $account) {
                    JournalEntryController::StoreJournalEntry($account['code'], $account['position'], $this->cogs, $journalEntry->id);
                }
            }

            if (!is_null($accounts['delivery_accounts'])) {
                foreach ($accounts['delivery_accounts'] as $account) {
                    JournalEntryController::storeJournalEntry($account['code'], $account['position'], $sale->delivery_cost, $journalEntry->id);
                }
            }
            DB::commit();

            return response()->json([
                'success'     => true,
                'sale_id' => $sale->id,
                'redirect'    => route('sales.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }


    private function createSaleRecord(array $formData): Sale
    {
        return Sale::create([
            'invoice_number'            => $formData['invoice_number'],
            'sale_date'                 => $formData['sale_date'],
            'customers_id'              => $formData['customers_id'],
            'total_price'               => $formData['total_price'],
            'users_id'                  => auth()->id(),
            'payment_method'            => $formData['payment_method'],
            'global_discount'           => $formData['global_discount'] ?? 0,
            'total_discount'            => $formData['total_discount'] ?? 0,
            'discount_cashback'         => $formData['discount_cashback'] ?? 0,
            'delivery_cost'             => $formData['delivery_cost'] ?? null,
        ]);
    }

    // #endregion

    // #region Sale Processing

    private function processSaleProduct(Sale $sale, array $productData): void
    {
        $product = Product::findOrFail($productData['product_id']);

        $saleDetail = SaleDetail::create([
            'sales_id'       => $sale->id,
            'products_id'    => $productData['product_id'],
            'quantity'       => $productData['quantity'],
            'subtotal'       => $productData['subtotal'],
            'price'          => $product->price,
        ]);


        $this->reduceProductInventory($product, $saleDetail);
    }

    private function processDeliveryInventoryReduction(Sale $sale): void
    {
        if (!$this->isDeliveryOrder($sale)) return;

        foreach ($sale->saleDetails as $saleDetail) {
            $this->reduceProductInventory($saleDetail->product, $saleDetail);
        }
    }

    private function validateForm(array $formData): void
    {
        // if ($formData['receive_method'] === 'RE-02' && empty($formData['delivery_cost'])) {
        //     throw new \InvalidArgumentException('Delivery cost is required for delivery orders');
        // }

    }

    // #endregion

    // #region Inventory

    private function reduceProductInventory(Product $product, SaleDetail $saleDetail): void
    {
        $remainingQty = $saleDetail->quantity;
        $cogsMethod   = ConfigurationController::getOneConfigMethod('cogs_method');
        $expiryDate = ConfigurationController::getOneConfigMethod("expired_date_settings");
        $batches = ProductBatch::where('products_id', $product->id)
            ->where('empty_status', '=', 0)
            ->when($cogsMethod === 'FIFO',    fn($q) => $q->orderBy('purchase_date', 'asc'))
            ->when($cogsMethod === 'average', function ($q) use ($expiryDate) {
                if ($expiryDate !== null) {
                    return $q->orderByRaw('expired_date IS NULL, expired_date ASC, purchase_date ASC');
                }
                return $q->orderBy('purchase_date', 'asc');
            })
            ->get();

        $averageCostPerUnit = null;
        if ($cogsMethod === 'average') {
            $totalStock  = $batches->sum('stock');
            $totalValue  = $batches->sum(fn($b) => $b->stock * $b->cost_per_batch);
            $averageCostPerUnit = $totalStock > 0 ? $totalValue / $totalStock : 0;
        }

        foreach ($batches as $batch) {
            if ($remainingQty == 0) break;

            $usedQty     = min($batch->stock, $remainingQty);
            $costPerUnit = $cogsMethod === 'FIFO' ? $batch->cost_per_batch : $averageCostPerUnit;

            $this->cogs += $usedQty * $costPerUnit;

            $batch->stock -= $usedQty;
            $batch->empty_status = $batch->stock <= 0 ? 1 : 0;
            $batch->save();

            $remainingQty -= $usedQty;
        }

        if ($remainingQty > 0) {
            throw new \Exception("Tidak cukup stok untuk {$product->product_name}. Kekurangan: {$remainingQty} {$product->unit_name}");
        }

        $product->decrement('total_stock', $saleDetail->quantity);
    }



    // #endregion

    // #region Utilities

    public function generateInvoiceNumber(): string
    {
        $currentDate   = Carbon::now()->format('Y_m_d');
        $latestInvoice = Sale::whereDate('sale_date', $currentDate)->orderBy('id', 'desc')->first();

        $sequence = $latestInvoice
            ? ((int) substr($latestInvoice->invoice_number, -3)) + 1
            : 1;

        return $currentDate . '_' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function query(Request $request)
    {
        $search   = $request->get('search', '');

        $products = Product::where('total_stock', '>', 0)
            ->when(!empty($search), fn($q) => $q->where(function ($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('product_code', 'LIKE', "%{$search}%");
            }))
            ->limit(20)
            ->get(['id', 'product_name', 'price', 'total_stock']);

        return response()->json($products);
    }

    private function getActiveSubConfig(string $configName)
    {
        return SubConfiguration::with('configuration')
            ->whereHas('configuration', fn($q) => $q->where('name', $configName))
            ->where('status', 1)
            ->get();
    }

    private function getAllSubConfig(string $configName)
    {
        return SubConfiguration::with('configuration')
            ->whereHas('configuration', fn($q) => $q->where('name', $configName))
            ->get();
    }


    // #endregion
}
