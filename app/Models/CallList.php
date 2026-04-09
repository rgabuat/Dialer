<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallList extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'description',
        'is_active',
        'sort_order',
        'status_filter',
        'timezone',
    ];

    protected $casts = [
        'is_active'     => 'boolean',
        'status_filter' => 'array',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function leads()
    {
        return $this->hasMany(Lead::class);
    }
}
