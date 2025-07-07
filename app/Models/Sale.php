<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id',
        'invoice_number',
        'total_price',
        'sale_date',
        'delivery_method',
        'payment_methods',
        'status',
        'global_discount',
        'shipped_date',
        'discount_cashback',
        'recipient_name',
        'customer_address',
        'delivery_cost',
        'users_id',
        'customers_id',
        'created_at',
        'updated_at',
        'deleted_at',
        'cogs_method',
    ];

    public function saleDetails(): HasMany
    {
        return $this->hasMany(SaleDetail::class, 'sales_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customers_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }
}
