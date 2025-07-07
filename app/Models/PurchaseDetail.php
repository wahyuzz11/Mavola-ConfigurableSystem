<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseDetail extends Model
{
    use HasFactory;


    protected $fillable = [
        'id',
        'subtotal',
        'quantity',
        'created_at',
        'recalculate_date',
        'purchases_id',
        'products_id',
        'updated_at',
        'deleted_at',
    ];


    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class, 'purchases_id');
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'suppliers_id');
    }

    public function productBatch()
    {
        return $this->hasOne(ProductBatch::class, 'purchase_details_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'products_id');
    }
}
