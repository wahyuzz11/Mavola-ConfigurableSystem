<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DebtHistory extends Model
{
    use HasFactory;

    protected $table = 'debt_histories'; 


    protected $fillable =[
        'debt_nominal',
        'bill_date',
        'due_date',
        'status',
        'supplier_id',
        'purchases_id'
    ];
    

    public function supplier() :BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }


    public function purchase(){
        return $this->belongsTo(Purchase::class, 'purchases_id');
    }
}
