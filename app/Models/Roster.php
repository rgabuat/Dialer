<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
  use HasFactory;

  protected $fillable = [
    "campaign_id",
    "name",
    "week_start",
    "timezone",
    "status",
    "created_by",
  ];

  protected $casts = [
    "week_start" => "date",
  ];

  // ── Relationships ─────────────────────────────────────────────────

  public function campaign()
  {
    return $this->belongsTo(Campaign::class);
  }

  public function shifts()
  {
    return $this->hasMany(RosterShift::class);
  }

  public function intervals()
  {
    return $this->hasMany(StaffingInterval::class);
  }

  public function creator()
  {
    return $this->belongsTo(User::class, "created_by");
  }

  // ── Accessors ────────────────────────────────────────────────────

  /** Last day of the roster week (week_start + 6 days). */
  public function getWeekEndAttribute(): Carbon
  {
    return $this->week_start->copy()->addDays(6);
  }

  /** Human-readable label; falls back to "Undefined" when name is null. */
  public function getLabelAttribute(): string
  {
    return $this->name ?? "Undefined";
  }

  // ── Scopes ───────────────────────────────────────────────────────

  public function scopePublished(Builder $query): Builder
  {
    return $query->where("status", "published");
  }

  public function scopeForWeek(Builder $query, string $date): Builder
  {
    return $query->where("week_start", $date);
  }
}
