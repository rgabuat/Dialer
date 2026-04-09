<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DialerHopper extends Model
{
    use HasFactory;

    protected $table = 'dialer_hopper';

    protected $fillable = [
        'campaign_id',
        'lead_id',
        'phone_number',
        'status',
        'attempt',
        'scheduled_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'attempt'      => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
