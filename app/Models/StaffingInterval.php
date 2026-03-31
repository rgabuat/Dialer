<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffingInterval extends Model
{
  use HasFactory;

  protected $fillable = [
    "roster_id",
    "day_of_week",
    "interval_start",
    "agents_required",
    "calls_forecast",
  ];

  protected $casts = [
    "agents_required" => "integer",
    "calls_forecast" => "integer",
  ];

  // ── Relationships ─────────────────────────────────────────────────

  public function roster()
  {
    return $this->belongsTo(Roster::class);
  }
}
