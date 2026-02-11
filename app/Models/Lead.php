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
        'created_by',
        'last_actioned_by',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
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
