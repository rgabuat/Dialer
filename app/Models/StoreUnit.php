<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreUnit extends Model
{
    use HasFactory;

    protected $table = 'store_units';

    protected $fillable = [
        'store_id',
        'size',
        'category',
        'available',
        'street_rate',
        'push_rate',
        'features',
        'promos',
    ];

    protected $casts = [
        'features'    => 'array',
        'promos'      => 'array',
        'street_rate' => 'float',
        'push_rate'   => 'float',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }
}
