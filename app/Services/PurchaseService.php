<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\DebtHistory;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\ProductBatchController;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class PurchaseService
{


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

    private function createPurchaseDetail(): PurchaseDetail
    {
        return PurchaseDetail::create([
            
        ]);
    }
 
}
