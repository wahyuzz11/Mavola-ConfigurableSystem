<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'subtotal',
        'quantity',
        'discount_value',
        'sales_id',
        'products_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'cogs_sale',
        'price',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sales_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'products_id');
    }
}
