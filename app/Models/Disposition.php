<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Disposition extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'code',
        'category',
        'is_dnc',
        'requires_callback',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_dnc'             => 'boolean',
        'requires_callback'  => 'boolean',
        'is_active'          => 'boolean',
        'sort_order'         => 'integer',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Returns dispositions available for a campaign:
     * campaign-specific ones first, then global (campaign_id = null).
     */
    public static function forCampaign(?int $campaignId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)
            ->where(function ($q) use ($campaignId) {
                $q->whereNull('campaign_id');
                if ($campaignId) {
                    $q->orWhere('campaign_id', $campaignId);
                }
            })
            ->orderBy('campaign_id')
            ->orderBy('sort_order')
            ->get();
    }
}
