<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStatus extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status_type_id', 'started_at'];

    protected $casts = [
        'started_at' => 'datetime',
    ];

    public function statusType()
    {
        return $this->belongsTo(AgentStatusType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
