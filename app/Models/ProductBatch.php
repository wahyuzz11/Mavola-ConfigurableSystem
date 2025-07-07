<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBatch extends Model
{
    use HasFactory;


    protected $table = 'product_batchs';

    protected $fillable = [
        'id',
        'serial_code',
        'stock',
        'cost_per_batch',
        'purchase_date',
        'expired_date',
        'empty_status',
        'products_id',
        'purchase_details_id',

    ];



    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'products_id');
    }

    public function purchaseDetail(): BelongsTo
    {
        return $this->belongsTo(PurchaseDetail::class, 'purchase_details_id');
    }
}
