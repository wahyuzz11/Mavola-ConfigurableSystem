<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\Configuration;
use App\Models\SaleDetail;

class SaleService{


    private function createSaleRecord(array $formData): Sale{
        return Sale::create([
            'invoice_number' => $formData['invoice_number'],
            'total_sales' => $formData['total_sales'],
            'sale_date' => $formData['sale_date'],
            'delivery_method' => $formData['delivery_method'],
            'payment_methods' => $formData['payment_methods'],
            'cogs_method' => $formData['cogs_method'],
            'global_discount' => $formData['global_discount'],
            'total_discount' => $formData['total_discount'],
            'discount_cashback' => $formData['discount_cashback'],
            'delivery_cost' => $formData['delivery_cost'],
            'customers_id' => $formData['customers_id'],
            'users_id' => auth()->id(),
        ]);
    }

    private function createSaleDetail(array $formData): SaleDetail{
        return SaleDetail::create([
            'subtotal' => $formData['subtotal'],
            'quantity' => $formData['quantity'],
            'price' => $formData['price'],
            'sales_id' => $formData['sales_id'],
            'products_id' => $formData['products_id'],
        ]);
    }

    

}