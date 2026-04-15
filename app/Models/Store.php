<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'address',
        'brand',
        'type',
        'occupancy',
        'city',
        'state',
        'zip',
        'country',
        'featured',
        'pricing',
    ];

    protected $casts = [
        'occupancy' => 'float',
        'featured'  => 'boolean',
        'pricing'   => 'array',
    ];
    
    public function units()
    {
        return $this->hasMany(StoreUnit::class);
    }
}
