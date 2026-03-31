<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterShift extends Model
{
  use HasFactory;

  protected $fillable = [
    "roster_id",
    "user_id",
    "day_of_week",
    "start_time",
    "end_time",
    "location",
    "total_hours",
  ];

  // ── Relationships ─────────────────────────────────────────────────

  public function roster()
  {
    return $this->belongsTo(Roster::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function activities()
  {
    return $this->hasMany(ShiftActivity::class)
      ->orderBy("sort_order")
      ->orderBy("start_time");
  }

  // ── Accessors ────────────────────────────────────────────────────

  /** Formatted shift window e.g. "7:30am - 4:00pm", or "—" when not set. */
  public function getShiftLabelAttribute(): string
  {
    if (!$this->start_time || !$this->end_time) {
      return "—";
    }
    return Carbon::parse($this->start_time)->format("g:ia") .
      " - " .
      Carbon::parse($this->end_time)->format("g:ia");
  }
}
