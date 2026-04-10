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
  ];

  protected $casts = [
    "is_active"    => "boolean",
    "cid_rotation" => "boolean",
    "dial_level"   => "decimal:2",
    "acw_seconds"  => "integer",
    "hopper_level" => "integer",
    "max_calls"    => "integer",
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
   * Pick the next CID for this campaign using round-robin from the global CID rotation pool.
   * The pool is all CidNumbers where in_rotation=true and is_active=true.
   * Returns a phone_number string, or the fixed caller_id if rotation is disabled / pool is empty.
   */
  public function nextCid(): ?string
  {
    if (!$this->cid_rotation) {
        return $this->caller_id ?: null;
    }

    $pool = \App\Models\CidNumber::where('is_active', true)
        ->where('in_rotation', true)
        ->orderBy('phone_number')
        ->pluck('phone_number')
        ->toArray();

    if (empty($pool)) {
        return $this->caller_id ?: null;
    }

    $cacheKey = "cid_rotation:{$this->id}";
    $index    = (int) \Illuminate\Support\Facades\Cache::get($cacheKey, 0);
    $number   = $pool[$index % count($pool)];

    \Illuminate\Support\Facades\Cache::put($cacheKey, ($index + 1) % count($pool), now()->addDay());

    return $number;
  }
}
