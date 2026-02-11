<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStatusLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status_type_id',
        'started_at',
        'ended_at',
        'duration_seconds',
    ];

    public function statusType()
    {
        return $this->belongsTo(AgentStatusType::class);
    }
}
