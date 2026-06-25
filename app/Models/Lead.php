<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'store_id',
        'conversation_id',
        'call_list_id',
        'status',
        'last_called_at',
        'call_count',
        'timezone',
        'address',
        'city',
        'state',
        'zip',
        'country',
        'created_by',
        'last_actioned_by',
        'lead_type',
        'move_in_date',
        'reason_for_storage',
        'types_of_items',
        'duration',
        'property_protection',
        'promo',
        'admin_fee_credit',
        'unit_size',
        'notify_sms',
        'notify_email',
        'notify_email_address',
        'selected_units',
        'dynamic_data',
        'pipeline_stage',
        'source',
        'expires_at',
    ];

    protected $casts = [
        'last_called_at'   => 'datetime',
        'move_in_date'     => 'date',
        'expires_at'       => 'date',
        'admin_fee_credit' => 'boolean',
        'notify_sms'       => 'boolean',
        'notify_email'     => 'boolean',
        'selected_units'   => 'array',
        'dynamic_data'     => 'array',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function callList()
    {
        return $this->belongsTo(CallList::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastActionedBy()
    {
        return $this->belongsTo(User::class, 'last_actioned_by');
    }

}
