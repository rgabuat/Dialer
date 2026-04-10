<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
  use HasFactory;

  protected $fillable = [
    "name", "description", "is_active",
    "type", "dial_mode", "dial_level", "caller_id", "cid_rotation",
    "script", "acw_seconds", "hopper_level", "max_calls",
    // Voice & recording
    "tts_voice", "tts_language",
    "tts_completed", "tts_busy", "tts_no_answer", "tts_failed", "tts_canceled",
    "greeting_message", "hold_music_url",
    "recording_enabled", "recording_channels",
  ];

  protected $casts = [
    "is_active"          => "boolean",
    "cid_rotation"       => "boolean",
    "dial_level"         => "decimal:2",
    "acw_seconds"        => "integer",
    "hopper_level"       => "integer",
    "max_calls"          => "integer",
    "recording_enabled"  => "boolean",
  ];

  public function userGroups()
  {
    return $this->belongsToMany(UserGroup::class);
  }

  public function users()
  {
    return $this->belongsToMany(User::class);
  }

  public function callLists()
  {
    return $this->hasMany(\App\Models\CallList::class);
  }

  public function dispositions()
  {
    return $this->hasMany(\App\Models\Disposition::class);
  }

  public function callbackSchedules()
  {
    return $this->hasMany(\App\Models\CallbackSchedule::class);
  }

  public function inGroups()
  {
    return $this->hasMany(\App\Models\InGroup::class);
  }

  /**
   * Pick the next CID model for this campaign using round-robin from the global CID rotation pool.
   * Returns a CidNumber model, or null if rotation is disabled / pool is empty.
   */
  public function nextCidModel(): ?\App\Models\CidNumber
  {
    if (!$this->cid_rotation) {
        return null;
    }

    $pool = \App\Models\CidNumber::where('is_active', true)
        ->where('in_rotation', true)
        ->orderBy('phone_number')
        ->get();

    if ($pool->isEmpty()) {
        return null;
    }

    $cacheKey = "cid_rotation:{$this->id}";
    $count    = $pool->count();
    $index    = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
    $model    = $pool[$index % $count];

    \Illuminate\Support\Facades\Cache::put($cacheKey, ($index + 1) % $count, now()->addDay());

    return $model;
  }

  /**
   * Pick the next CID for this campaign using round-robin from the global CID rotation pool.
   * The pool is all CidNumbers where in_rotation=true and is_active=true.
   * Returns a phone_number string, or the fixed caller_id if rotation is disabled / pool is empty.
   */
  public function nextCid(): ?string
  {
    return $this->nextCidModel()?->phone_number ?? ($this->caller_id ?: null);
  }
}
