<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    public function purchases(): HasMany{
        return $this->hasMany(Purchase::class,'suppliers_id');
    }

    public function debtHistories(): HasMany {
        return $this->hasMany(DebtHistory::class, 'supplier_id');
    }


}
