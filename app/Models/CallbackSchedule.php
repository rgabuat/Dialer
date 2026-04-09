<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CallbackSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'lead_id',
        'conversation_id',
        'assigned_to',
        'scheduled_at',
        'notes',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function assignedAgent()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
