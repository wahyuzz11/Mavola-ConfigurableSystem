<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'description',
        'image',
        'price',
        'minimum_total_stock',
        'total_stock',
        'unit_name',
        'expired_date_settings',
        'expire_date_active',
        'cost',
        'categories_id',
        'starting_stock_periodic',
        'periodic_start_date',
        // Note: 'id', 'created_at', 'updated_at', and 'deleted_at' are typically not fillable
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'categories_id');
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class, 'products_id');
    }

    public function productBatchs(): HasMany
    {
        return $this->hasMany(ProductBatch::class, 'products_id');
    }
}
