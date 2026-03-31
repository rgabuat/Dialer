<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShiftActivity extends Model
{
  use HasFactory;

  /** Allowed activity type slugs. */
  const TYPES = [
    "phones",
    "break",
    "lunch",
    "quality",
    "outbound",
    "support",
    "escalations",
  ];

  /** Tailwind colour map per activity type. */
  const COLORS = [
    "phones" => [
      "bg" => "bg-teal-500",
      "text" => "text-white",
      "hex" => "#14b8a6",
    ],
    "break" => [
      "bg" => "bg-pink-500",
      "text" => "text-white",
      "hex" => "#ec4899",
    ],
    "lunch" => [
      "bg" => "bg-yellow-500",
      "text" => "text-black",
      "hex" => "#eab308",
    ],
    "quality" => [
      "bg" => "bg-fuchsia-600",
      "text" => "text-white",
      "hex" => "#c026d3",
    ],
    "outbound" => [
      "bg" => "bg-sky-500",
      "text" => "text-white",
      "hex" => "#0ea5e9",
    ],
    "support" => [
      "bg" => "bg-violet-600",
      "text" => "text-white",
      "hex" => "#7c3aed",
    ],
    "escalations" => [
      "bg" => "bg-red-500",
      "text" => "text-white",
      "hex" => "#ef4444",
    ],
  ];

  protected $fillable = [
    "roster_shift_id",
    "activity_type",
    "start_time",
    "end_time",
    "sort_order",
  ];

  // ── Relationships ─────────────────────────────────────────────────

  public function shift()
  {
    return $this->belongsTo(RosterShift::class, "roster_shift_id");
  }

  // ── Accessors ────────────────────────────────────────────────────

  /** Returns the COLORS array entry for this activity type. */
  public function getColorAttribute(): array
  {
    return self::COLORS[$this->activity_type] ?? [
      "bg" => "bg-zinc-500",
      "text" => "text-white",
      "hex" => "#71717a",
    ];
  }

  /** Human-readable label e.g. "Phones". */
  public function getLabelAttribute(): string
  {
    return ucfirst($this->activity_type);
  }
}
