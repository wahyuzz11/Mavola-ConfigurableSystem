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
use Illuminate\Http\Request;

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

    private function createPurchaseDetail(array $formData): PurchaseDetail
    {
        return purchaseDetail::create([
           'subtotal' => $formData['subtotal'],
           'quantity' => $formData['quantity'],
           'expire_days' => $formData['expire_days'],
           'purchases_id' => $formData['purchases_id'],
           'products_id' => $formData['products_id'],
        ]);
    }

    
 
}
