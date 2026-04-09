<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgentStatusType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'description',
        'is_available',
        'is_productive',
        'is_break',
        'handles_inbound',
        'handles_outbound',
    ];

    protected $casts = [
        'is_available'     => 'boolean',
        'is_productive'    => 'boolean',
        'is_break'         => 'boolean',
        'handles_inbound'  => 'boolean',
        'handles_outbound' => 'boolean',
    ];
    
}
