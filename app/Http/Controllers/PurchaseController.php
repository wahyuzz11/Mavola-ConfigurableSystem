<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\SubConfiguration;
use App\Models\DebtHistory;
use App\Models\Account;
use Carbon\Carbon;
use Carbon\Exceptions\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{

    public function index()
    {
        $purchases = Purchase::orderBy('invoice_number', 'DESC')
            ->simplePaginate(10, ['*'], 'completed_page');
        $purchasesCount = Purchase::count();
        $totalPurchasesTransaction = Purchase::sum('total_price');

        return view('purchases.index', compact(
            'purchases',
            'purchasesCount',
            'totalPurchasesTransaction',
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
        $expiredSetting =  SubConfiguration::where('code', 'EXP-01')->first();
        $purchaseShippingConfig = SubConfiguration::where('code', 'SHP-P-01')->first();
        $invoiceNumber = $this->generateInvoiceNumber();
        $checkAccount = $this->checkAccount();

        // return view('purchases.create', dd(compact('invoiceNumber', 'receiveMethods', 'purchaseMethods', 'activeMethods', 'expiredSetting')));
        return view('purchases.create', compact('invoiceNumber', 'purchaseMethods', 'expiredSetting', 'checkAccount', 'purchaseShippingConfig'));
    }


    private function checkAccount(): string
    {
        $assetCodes = ['1101', '1102', '1103'];
        $checkAccount = '';
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


    public function storeAjax(Request $request)
    {
        $validated = $request->validate([
            'invoice_number'           => 'required|string',
            'order_date'               => 'required|date',
            'suppliers'                => 'required',
            'grand_total'              => 'required',
            'payment_method'           => 'required|string',
            'delivery_cost'            => 'nullable|numeric',
            'due_date'                 => 'nullable|date',
            'products'                    => 'required|array|min:1',
            'products.*.product_id'       => 'required|exists:products,id',
            'products.*.quantity'         => 'required|integer|min:1',
            'products.*.purchase_price'   => 'required|numeric|min:0',
            'products.*.expire_days'      => 'nullable|integer|min:0',
            'products.*.total_price'       => 'required|numeric|min:0',
        ]);

        $this->validateForm($validated);

        DB::beginTransaction();
        try {
            $purchase = $this->createPurchaseRecord($validated);

            if ($this->isCreditPayment($validated)) {
                $this->createDebtRecord($purchase, $validated);
            }

            $journal = JournalEntryController::storeJournalPurchase($purchase);


            foreach ($validated['products'] as $productData) {
                $this->processPurchaseProduct($purchase, $productData);
            }

            $accounts = JournalEntryController::checkPurchaseAccount($purchase);

            foreach ($accounts['base_accounts'] as $account) {
                JournalEntryController::storeJournalEntry(
                    $account['code'],
                    $account['position'],
                    $purchase->total_price,
                    $journal->id
                );
            }

            if (!is_null($accounts['delivery_accounts'])) {
                foreach ($accounts['delivery_accounts'] as $account) {
                    JournalEntryController::storeJournalEntry(
                        $account['code'],
                        $account['position'],
                        $purchase->delivery_cost,
                        $journal->id
                    );
                }
            }

            DB::commit();
            // Return JSON instead of redirect — frontend handles navigation
            return response()->json([
                'success'     => true,
                'purchase_id' => $purchase->id,
                'redirect'    => route('purchases.index')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }




    // #region PRIVATE FUNCTION

    // VALIDATE DATA 
    private function validateForm(array $formData): void
    {

        if ($formData['payment_method'] === 'P-PAY-03' && empty($formData['due_date'])) {
            throw new \InvalidArgumentException('Due date is required for credit payments');
        }
    }

    // RECORD CREATION

    private function createPurchaseRecord(array $formData): Purchase
    {
        return Purchase::create([
            'invoice_number' => $formData['invoice_number'],
            'purchase_date'  => $formData['order_date'],
            'suppliers_id'   => $formData['suppliers'],
            'total_price'    => $formData['grand_total'],
            'payment_method' => $formData['payment_method'],
            'delivery_cost'  => $formData['delivery_cost'] ?? null,
            'users_id'       => auth()->id(),
        ]);
    }

    private function createDebtRecord(Purchase $purchase, array $formData): void
    {
        DebtHistory::create([
            'debt_nominal' => $formData['grand_total'],
            'bill_date'    => $formData['order_date'],
            'due_date'     => $formData['due_date'],
            'status'       => 'pending',
            'supplier_id'  => $formData['suppliers'],
            'purchases_id' => $purchase->id,
        ]);
    }

    // PURCHASE PROCESSING

    private function processPurchaseProduct(Purchase $purchase, array $productData): void
    {
        $expireDays  = $productData['expire_days'] ?? null;
        $expiredDate = $expireDays
            ? Carbon::parse($purchase->purchase_date)->addDays($expireDays)
            : null;

        $detail = PurchaseDetail::create([
            'purchases_id'   => $purchase->id,
            'products_id'    => $productData['product_id'],
            'quantity'       => $productData['quantity'],
            'expire_days'    => $expireDays,
            'purchase_price' => $productData['purchase_price'],
            'subtotal'       => $productData['total_price'],
        ]);

        $this->createProductBatch($detail, $purchase->purchase_date, $expiredDate);
        $this->updateProductInventory(
            Product::findOrFail($detail->products_id),
            $detail,
        );
    }




    private function createProductBatch(PurchaseDetail $detail, $purchaseDate, ?Carbon $expiredDate): void
    {
        ProductBatch::create([
            'serial_code'         => ProductBatchController::generateSerialCode($detail->products_id),
            'stock'               => $detail->quantity,
            'purchase_date'       => $purchaseDate,
            'expired_date'        => $expiredDate,
            'empty_status'        => 0,
            'cost_per_batch'      => $detail->subtotal / $detail->quantity,
            'products_id'         => $detail->products_id,
            'purchase_details_id' => $detail->id,
        ]);
    }


    // INVENTORY

    private function updateProductInventory(Product $product, PurchaseDetail $detail): void
    {
        $oldStock          = $product->total_stock;
        $newStock          = $oldStock + $detail->quantity;


        $product->update([
            'total_stock' => $newStock,

        ]);
    }


    // CONDITIONALS

    private function isCreditPayment(array $formData): bool
    {
        return $formData['payment_method'] === 'P-PAY-03';
    }


    // UTILIETIES



    // #endregion



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

        $currentDate   = Carbon::now()->format('Y_m_d');
        $latestInvoice = Purchase::whereDate('purchase_date', $currentDate)->orderBy('id', 'desc')->first();

        $sequence = $latestInvoice
            ? ((int) substr($latestInvoice->invoice_number, -3)) + 1
            : 1;

        return $currentDate . '_' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }

    public function productQuery(Request $request)
    {
        $search = $request->input('search', '');

        $products = Product::query()
            ->where('product_name', 'LIKE', '%' . $search . '%')
            ->select([
                'id',
                'product_name',
                'total_stock',
                'expired_date_active as expired',
                'expired_date_setting as expired_date',
            ])
            ->selectSub(function ($query) {
                $query->from('purchase_details')
                    ->selectRaw('COALESCE(subtotal / NULLIF(quantity, 0), 0)')
                    ->whereColumn('purchase_details.products_id', 'products.id')
                    ->whereNull('purchase_details.deleted_at')
                    ->orderBy('purchase_details.created_at', 'desc')
                    ->limit(1);
            }, 'price')
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
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->withErrors(['error' => 'Failed to confirm receipt: ' . $e->getMessage()]);
        }
    }


    private function processPurchaseReceipt(PurchaseDetail $detail): void
    {
        $purchaseDate = Carbon::parse($detail->purchase->purchase_date);
        $expiredDate  = $detail->expire_days
            ? $purchaseDate->copy()->addDays($detail->expire_days)
            : null;

        $this->createProductBatch($detail, $purchaseDate, $expiredDate);
        $this->updateProductInventory(
            Product::findOrFail($detail->products_id),
            $detail,
        );
    }
}
