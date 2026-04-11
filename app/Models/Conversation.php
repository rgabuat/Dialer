<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Conversation extends Model
{
  use HasFactory;

  protected $fillable = [
    "call_sid",
    "channel",
    "direction",
    "status",
    "contact_name",
    "contact_phone",
    "caller_name",
    "caller_city",
    "caller_state",
    "caller_country",
    "caller_zip",
    "to_number",
    "forwarded_from",
    "queue",
    "detail_preview",
    "duration_seconds",
    "campaign_id",
    "cid_number_id",
    "in_group_id",
    "assigned_to",
    "completed_by",
    "ended_at",
    "started_at",
    "disposition_id",
    "disposition_notes",
    "lead_id",
    "wrapped_at",
  ];

  protected $casts = [
    "started_at"  => "datetime",
    "ended_at"    => "datetime",
    "wrapped_at"  => "datetime",
  ];

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }

  public function cidNumber()
  {
    return $this->belongsTo(\App\Models\CidNumber::class);
  }

  public function inGroup()
  {
    return $this->belongsTo(InGroup::class);
  }

  public function assignedAgent()
  {
    return $this->belongsTo(User::class, "assigned_to");
  }

  public function completedByAgent()
  {
    return $this->belongsTo(User::class, "completed_by");
  }

  public function notes()
  {
    return $this->hasMany(ConversationNote::class)->orderBy("created_at");
  }

  public function disposition()
  {
    return $this->belongsTo(Disposition::class);
  }

  public function lead()
  {
    return $this->belongsTo(Lead::class);
  }

  public function getDurationLabelAttribute(): ?string
  {
    if ($this->duration_seconds === null) {
      return null;
    }
    $m = intdiv($this->duration_seconds, 60);
    $s = $this->duration_seconds % 60;
    return sprintf("%d:%02d", $m, $s);
  }

  public function getInitialsAttribute(): string
  {
    if (!$this->contact_name) {
      return "?";
    }
    $parts = explode(" ", trim($this->contact_name));
    $first = strtoupper(substr($parts[0] ?? "", 0, 1));
    $last = strtoupper(substr($parts[1] ?? "", 0, 1));
    return $first . $last;
  }
}
