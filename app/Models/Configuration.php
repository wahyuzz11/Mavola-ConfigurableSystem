<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Configuration extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'types',
        'status',
        'codes',
    ];

    public function subConfigurations(): HasMany
    {
        return $this->hasMany(SubConfiguration::class, 'configurations_id');
    }
}
