<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\DebtHistory;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ProductBatchController;
use App\Models\JournalEntry;
use App\Models\SubConfiguration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use DomainException;

class PurchaseService
{

    public const PAYMENT_CREDIT = 'P-PAY-03';


    public function create(array $data): Purchase {
        return DB::transaction(function () use ($data) {
            $purchase = $this->createPurchaseRecord($data);

            if($data['payment_method'] === self::PAYMENT_CREDIT){
                $this->createDebtRecord($purchase,$data);
            }

            $journal = JournalEntryController::storeJournalPurchase($purchase);

        
            foreach($data['products'] as $product){
                $this->addPurchaseDetail($purchase,$product);
            }

            $this->postJournalEntries($purchase,$journal);
            
            return $purchase;
        });

    }


    private function addPurchaseDetail(Purchase $purchase, array $detail): void
    {
        $purchaseDetail =  purchaseDetail::create([
            'subtotal' => $detail['subtotal'],
            'quantity' => $detail['quantity'],
            'expire_days' => $detail['expire_days'],
            'purchases_id' => $purchase->id,
            'products_id' => $detail['products_id'],
        ]);

        $this->stockIn($purchaseDetail, $purchase->order_date);
    }

    private function createPurchaseRecord(array $formData): Purchase
    {
        return Purchase::create([
            'invoice_number' => $formData['invoice_number'],
            'purchase_date' => $formData['purchase_date'],
            'supliers_id' => $formData['suppliers'],
            'total_price' => $formData['grand_total'],
            'payment_method' => $formData['payment_method'],
            'delivery_cost' => $formData['delivery_cost'] ?? null,
            'users_id' => auth()->id(),
        ]);
    }



    private function createDebtRecord(Purchase $purchase, array $data): void
    {
        DebtHistory::create([
            'debt_nominal' => $data['grand_total'],
            'bill_date' => $data['order_date'],
            'due_date' => $data['due_date'],
            'status' => 'pending',
            'suppliers_id' => $data['suppliers_id'],
            'purchases_id' => $purchase->id,
        ]);
    }

    private function stockIn(PurchaseDetail $purchaseDetail, $purchaseDate): void
    {
        $expiredDate = $purchaseDetail->expire_days ? Carbon::parse($purchaseDate)->addDays($purchaseDetail->expire_days) : null;


        ProductBatch::create([
            'serial_code' => ProductBatchController::generateSerialCode($purchaseDetail->products_id),
            'stock' => $purchaseDetail->quantity,
            'purchase_date' => $purchaseDate,
            'expired_date' => $expiredDate ?? null,
            'empty_status' => 0,
            'cost_per_batch' => $purchaseDetail->subtotal / $purchaseDetail->quantity,
            'products_id'   => $purchaseDetail->products_id,
            'purchase_details_id' => $purchaseDetail->id,

        ]);

        Product::findOrFail($purchaseDetail->products_id)->increment('total_stock', $purchaseDetail->quantity);
    }

    public function confirmReceipt(Purchase $purchase, string $receiveDate): void
    {
        if ($purchase->status !== 'In delivery') {
            throw new DomainException('Only purchases In delivery can be received');
        }

        DB::transaction(function () use ($purchase, $receiveDate) {
            $purchase->update(['receive_date' => $receiveDate, 'status' => 'completed']);

            if ($purchase->receive_method == 'RE-02') {
                foreach ($purchase->purchaseDetails as $detail) {
                    $this->stockIn($detail, $purchase->purchase_date);
                }
            }
        });
    }


    public function createFormData(): array
    {
        return [
            'invoice_number' => $this->generateInvoiceNumber(),
            'purchaseMethods' => SubConfiguration::with('configuration')
                ->whereHas('configuration', fn($q) => $q->where('name', 'purchase_payment'))
                ->where('status', 1)
                ->get(),
            'expiredSettings' => SubConfiguration::where('code', 'EXP-01')->first(),
            'purchaseShippingConfig' => SubConfiguration::where('code', 'SHP-P-01')->first(),
        ];
    }

    public function generateInvoiceNumber(): string
    {
        $prefix = Carbon::now()->format('Y_m_d');


        $last = Purchase::where('invoice_number', 'like', $prefix, '_%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $last ? ((int) substr($last, -3)) + 1 : 1;

        return $prefix . '_' . str_pad($sequence, 3, '0', STR_PAD_LEFT);
    }


    public function searchProducts(string $search)
    {
        return Product::query()
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
    }

    private function postJournalEntries(Purchase $purchase, JournalEntry $journal): void
    {
        $accounts = JournalEntryController::checkPurchaseAccount($purchase);

        foreach ($accounts['base_accounts'] as $account) {
            JournalEntryController::storeJournalEntry(
                $account['code'],
                $account['position'],
                $purchase->total_price,
                $journal->id
            );
        }

        foreach ($accoutn['delivery_accounts'] ?? [] as $account) {
            JournalEntryController::storeJournalEntry(
                $account['code'],
                $account['position'],
                $purchase->delivery_cost,
                $journal->id
            );
        }
    }
}
