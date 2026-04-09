<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Campaign extends Model
{
  use HasFactory;

  protected $fillable = [
    "name", "description", "phone_number", "is_active",
    "type", "dial_mode", "dial_level", "caller_id",
    "script", "acw_seconds", "hopper_level", "max_calls",
  ];

  protected $casts = [
    "is_active"   => "boolean",
    "dial_level"  => "decimal:2",
    "acw_seconds" => "integer",
    "hopper_level"=> "integer",
    "max_calls"   => "integer",
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
}
