<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    /**
     * Mass assignment
     */
    protected $guarded = [];

    /**
     * Attribute casting
     */
    protected $casts = [
        'properties' => 'array',
        'performed_at' => 'datetime',
    ];

    /**
     * User who performed the action
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Subject of the activity (any model)
     * ex: User, Campaign, Lead, etc.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}