<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CidNumber extends Model
{
    use HasFactory;

    protected $table = 'cid_numbers';

    protected $fillable = [
        'phone_number',
        'twilio_sid',
        'friendly_name',
        'is_active',
        'in_rotation',
        'cid_group_id',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'in_rotation' => 'boolean',
    ];

    public function cidGroup()
    {
        return $this->belongsTo(CidGroup::class);
    }
}
