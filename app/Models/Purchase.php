<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'purchase_date',
        'suppliers_id',
        'total_price',
        'status',
        'users_id',
        'payment_method',
        'receive_method',
        'receive_date',
        'delivery_cost',
    ];

    public function supplier():BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'suppliers_id');
    }

    public function purchaseDetails(): HasMany
    {
        return $this->hasMany(PurchaseDetail::class, 'purchases_id');
    }

    public function debtHistory(){
        return $this->hasOne(DebtHistory::class, 'purchases_id');
    }
}
