<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'types',
        'status', 
        'value', 
        'configurations_id',
        'code'
    ];
    
    public function configuration(): BelongsTo
    {
        return $this->belongsTo(Configuration::class, 'configurations_id');
    }
}
