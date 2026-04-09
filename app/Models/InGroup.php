<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InGroup extends Model
{
  use HasFactory;

  protected $fillable = [
    "name",
    "description",
    "is_active",
    "queue_priority",
    "agent_routing",
    "max_wait_seconds",
    "drop_action",
    "drop_destination",
    "web_form_url",
    "hold_music_url",
    "after_hours_action",
    "after_hours_destination",
    "hours_json",
    "timezone",
    "campaign_id",
  ];

  protected $casts = [
    "is_active" => "boolean",
    "hours_json" => "array",
  ];

  public function users()
  {
    return $this->belongsToMany(User::class, "in_group_user")
      ->withPivot(["priority", "last_call_at", "is_active"])
      ->withTimestamps();
  }

  public function dids()
  {
    return $this->hasMany(Did::class);
  }

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }

  public function conversations()
  {
    return $this->hasMany(Conversation::class);
  }
}
