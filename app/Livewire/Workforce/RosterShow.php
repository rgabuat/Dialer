<?php

namespace App\Livewire\Workforce;

use App\Models\AgentStatusType;
use App\Models\Campaign;
use App\Models\Roster;
use App\Models\RosterShift;
use App\Models\ShiftActivity;
use Carbon\Carbon;
use Livewire\Component;

class RosterShow extends Component
{
  public Roster $roster;
  public string $activeTab = "summary"; // summary | details | day
  public int $activeDay = 0; // 0 = Sunday … 6 = Saturday

  // Resolved once in mount() from session('active_campaign_id')
  public ?int $activeCampaignId = null;

  // ── Day view filters ─────────────────────────────────────────────
  public string $filterSearch = "";
  public string $filterGroup = "";
  public string $filterLocation = "";

  // ── Activity modal state ─────────────────────────────────────────
  public bool $showActivityModal = false;
  public ?int $editingActivityId = null; // null = adding new
  public int $activityUserId = 0;
  public int $activityDay = 0;
  public string $activityType = "phones";
  public string $activityStart = "";
  public string $activityEnd = "";

  public function mount(): void
  {
    $this->activeCampaignId =
      session("active_campaign_id") ?? $this->roster->campaign_id;
  }

  const DAYS = [
    "Sunday",
    "Monday",
    "Tuesday",
    "Wednesday",
    "Thursday",
    "Friday",
    "Saturday",
  ];
  const DAY_ABBR = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
  const PX_PER_HOUR = 160;

  // ── Actions ──────────────────────────────────────────────────────

  public function setTab(string $tab): void
  {
    $this->activeTab = $tab;
  }

  public function setDay(int $day): void
  {
    $this->activeTab = "day";
    $this->activeDay = $day;
  }

  public function publish(): void
  {
    $this->roster->update(["status" => "published"]);
    $this->roster->refresh();
  }

  public function unpublish(): void
  {
    $this->roster->update(["status" => "draft"]);
    $this->roster->refresh();
  }

  // ── Activity CRUD ─────────────────────────────────────────────────

  /** Open the modal to add a new activity block for a specific user+day. */
  public function openAddActivity(
    int $userId,
    int $day,
    string $startTime = ""
  ): void {
    $this->reset(["editingActivityId", "activityStart", "activityEnd"]);
    $this->activityUserId = $userId;
    $this->activityDay = $day;
    $this->activityType = "phones";
    if ($startTime) {
      $this->activityStart = $startTime;
      $this->activityEnd = Carbon::createFromFormat("H:i", $startTime)
        ->addHour()
        ->format("H:i");
    }
    $this->showActivityModal = true;
  }

  /** Open the modal to edit an existing activity block. */
  public function openEditActivity(int $activityId): void
  {
    $activity = ShiftActivity::with("shift")->find($activityId);
    if (!$activity || $activity->shift->roster_id !== $this->roster->id) {
      return;
    }
    $this->editingActivityId = $activityId;
    $this->activityUserId = $activity->shift->user_id;
    $this->activityDay = $activity->shift->day_of_week;
    $this->activityType = $activity->activity_type;
    $this->activityStart = substr($activity->start_time, 0, 5);
    $this->activityEnd = substr($activity->end_time, 0, 5);
    $this->showActivityModal = true;
  }

  /** Create or update an activity block; auto-creates the RosterShift if needed. */
  public function saveActivity(): void
  {
    $this->validate([
      "activityUserId" => "required|exists:users,id",
      "activityDay" => "required|integer|between:0,6",
      "activityType" => "required|exists:agent_status_types,slug",
      "activityStart" => "required|date_format:H:i",
      "activityEnd" => "required|date_format:H:i|after:activityStart",
    ]);

    // Auto-create the RosterShift for this user+day if it doesn't exist yet
    $shift = RosterShift::firstOrCreate(
      [
        "roster_id" => $this->roster->id,
        "user_id" => $this->activityUserId,
        "day_of_week" => $this->activityDay,
      ],
      [
        "start_time" => $this->activityStart . ":00",
        "end_time" => $this->activityEnd . ":00",
        "total_hours" => 0,
      ]
    );

    if ($this->editingActivityId) {
      ShiftActivity::where("id", $this->editingActivityId)->update([
        "activity_type" => $this->activityType,
        "start_time" => $this->activityStart . ":00",
        "end_time" => $this->activityEnd . ":00",
      ]);
    } else {
      ShiftActivity::create([
        "roster_shift_id" => $shift->id,
        "activity_type" => $this->activityType,
        "start_time" => $this->activityStart . ":00",
        "end_time" => $this->activityEnd . ":00",
        "sort_order" => 0,
      ]);
    }

    $this->syncShiftTimes($shift->fresh());
    $this->showActivityModal = false;
    $this->roster->load(
      "shifts.user.userGroup",
      "shifts.activities",
      "intervals"
    );
  }

  /** Delete an activity block; removes the RosterShift too if it becomes empty. */
  public function deleteActivity(int $activityId): void
  {
    $activity = ShiftActivity::with("shift")->find($activityId);
    if (!$activity || $activity->shift->roster_id !== $this->roster->id) {
      return;
    }
    $shift = $activity->shift;
    $activity->delete();
    $shift->refresh();
    if ($shift->activities()->count() === 0) {
      $shift->delete();
    } else {
      $this->syncShiftTimes($shift);
    }
    $this->showActivityModal = false;
    $this->roster->load(
      "shifts.user.userGroup",
      "shifts.activities",
      "intervals"
    );
  }

  /** Move or resize an activity block by updating its start/end times. */
  public function moveActivity(
    int $activityId,
    string $start,
    string $end
  ): void {
    $activity = ShiftActivity::with("shift")->find($activityId);
    if (!$activity || $activity->shift->roster_id !== $this->roster->id) {
      return;
    }
    if (
      !preg_match('/^\d{2}:\d{2}$/', $start) ||
      !preg_match('/^\d{2}:\d{2}$/', $end) ||
      $start >= $end
    ) {
      return;
    }
    $activity->update([
      "start_time" => $start . ":00",
      "end_time" => $end . ":00",
    ]);
    $this->syncShiftTimes($activity->shift->fresh());
    $this->roster->load(
      "shifts.user.userGroup",
      "shifts.activities",
      "intervals"
    );
  }

  /** Sync RosterShift start/end/hours to span of its activities. */
  private function syncShiftTimes(RosterShift $shift): void
  {
    $activities = $shift->activities()->get();
    if ($activities->isEmpty()) {
      return;
    }
    $shiftStart = $activities->pluck("start_time")->sort()->first();
    $shiftEnd = $activities->pluck("end_time")->sortDesc()->first();
    $shift->update([
      "start_time" => $shiftStart,
      "end_time" => $shiftEnd,
      "total_hours" => round(
        Carbon::parse($shiftStart)->floatDiffInHours(Carbon::parse($shiftEnd)),
        2
      ),
    ]);
  }

  // ── Render ───────────────────────────────────────────────────────

  public function render()
  {
    // Eager-load everything once per render
    $this->roster->load(
      "shifts.user.userGroup",
      "shifts.activities",
      "intervals"
    );

    // Load campaign users from the session-selected campaign (via user groups)
    $campaign = $this->activeCampaignId
      ? Campaign::with("userGroups.users")->find($this->activeCampaignId)
      : null;

    $summaryData = $this->computeSummary();
    $locationData = $this->computeLocationStaffing();
    $detailsByLocation = $this->computeDetails();
    $dayViewData =
      $this->activeTab === "day"
        ? $this->computeDayView($this->activeDay, $campaign)
        : null;

    $statusTypes = AgentStatusType::orderBy("name")->get();

    $activityTypeOptions = $statusTypes
      ->map(
        fn($t) => [
          "value" => $t->slug,
          "label" => $t->name,
          "color" => $t->color,
        ]
      )
      ->values()
      ->toArray();

    // Build filter option lists from all campaign users (unfiltered so dropdowns stay full)
    $allCampaignUsers = $campaign
      ? $campaign->userGroups->flatMap(fn($g) => $g->users)->unique("id")
      : collect();
    $groupOptions = $allCampaignUsers
      ->pluck("userGroup.name")
      ->filter()
      ->unique()
      ->sort()
      ->values()
      ->map(fn($n) => ["value" => $n, "label" => $n])
      ->toArray();
    $locationOptions = $this->roster->shifts
      ->pluck("location")
      ->map(fn($l) => $l ?? "Unassigned")
      ->unique()
      ->sort()
      ->values()
      ->map(fn($l) => ["value" => $l, "label" => $l])
      ->toArray();

    return view("livewire.workforce.roster-show", [
      "days" => self::DAYS,
      "dayAbbr" => self::DAY_ABBR,
      "summaryData" => $summaryData,
      "locationData" => $locationData,
      "detailsByLocation" => $detailsByLocation,
      "dayViewData" => $dayViewData,
      "statusTypes" => $statusTypes,
      "activityTypeOptions" => $activityTypeOptions,
      "groupOptions" => $groupOptions,
      "locationOptions" => $locationOptions,
      "campaign" => $campaign,
    ])->layout("components.layouts.app");
  }

  // ── Private helpers ──────────────────────────────────────────────

  /**
   * Per-day staffing index computed from staffing_intervals vs phones activities.
   */
  private function computeSummary(): array
  {
    $data = [];

    for ($d = 0; $d <= 6; $d++) {
      $shifts = $this->roster->shifts->where("day_of_week", $d);
      $intervals = $this->roster->intervals->where("day_of_week", $d);

      if ($intervals->isEmpty()) {
        $data[$d] = [
          "day" => self::DAYS[$d],
          "staffing_index" => null,
          "understaffed_intervals" => 0,
          "understaffed_agents" => 0.0,
        ];
        continue;
      }

      $totalIndexSum = 0.0;
      $understaffedIntervals = 0;
      $understaffedAgentsSum = 0.0;

      foreach ($intervals as $interval) {
        $iStart = $interval->interval_start;
        $iEnd = Carbon::parse($iStart)->addMinutes(15)->format("H:i:s");

        // Count agents who have a "phones" activity that overlaps this 15-min slot
        $phonesCount = 0;
        foreach ($shifts as $shift) {
          foreach (
            $shift->activities->where("activity_type", "phones")
            as $activity
          ) {
            if (
              $activity->start_time <= $iEnd &&
              $activity->end_time > $iStart
            ) {
              $phonesCount++;
              break; // one activity match per shift is enough
            }
          }
        }

        $required = max(1, (int) $interval->agents_required);
        $totalIndexSum += ($phonesCount / $required) * 100;

        if ($phonesCount < (int) $interval->agents_required) {
          $understaffedIntervals++;
          $understaffedAgentsSum +=
            (int) $interval->agents_required - $phonesCount;
        }
      }

      $count = $intervals->count();
      $data[$d] = [
        "day" => self::DAYS[$d],
        "staffing_index" => round($totalIndexSum / $count, 1),
        "understaffed_intervals" => $understaffedIntervals,
        "understaffed_agents" =>
          $understaffedIntervals > 0
            ? round($understaffedAgentsSum / $understaffedIntervals, 1)
            : 0.0,
      ];
    }

    return $data;
  }

  /**
   * Agents rostered per location per day.
   */
  private function computeLocationStaffing(): array
  {
    $locations = $this->roster->shifts
      ->pluck("location")
      ->unique()
      ->sort()
      ->values();
    $data = [];

    foreach ($locations as $location) {
      $row = ["location" => $location];
      for ($d = 0; $d <= 6; $d++) {
        $row[$d] = $this->roster->shifts
          ->where("location", $location)
          ->where("day_of_week", $d)
          ->count();
      }
      $data[] = $row;
    }

    return $data;
  }

  /**
   * Details tab: shifts grouped by location then user.
   */
  private function computeDetails(): array
  {
    $locations = $this->roster->shifts
      ->pluck("location")
      ->unique()
      ->sort()
      ->values();
    $data = [];

    foreach ($locations as $location) {
      $shiftsForLoc = $this->roster->shifts->where("location", $location);
      $users = $shiftsForLoc
        ->pluck("user")
        ->filter()
        ->unique("id")
        ->sortBy("last_name");

      $rows = [];
      foreach ($users as $user) {
        $row = [
          "user" => $user,
          "total_hours" => null,
          "days" => [],
        ];
        for ($d = 0; $d <= 6; $d++) {
          $shift = $shiftsForLoc
            ->where("user_id", $user->id)
            ->where("day_of_week", $d)
            ->first();
          $row["days"][$d] = $shift?->shift_label ?? "—";
          if ($shift && $shift->total_hours) {
            $row["total_hours"] =
              ($row["total_hours"] ?? 0) + (float) $shift->total_hours;
          }
        }
        $rows[] = $row;
      }

      $data[$location] = $rows;
    }

    return $data;
  }

  /**
   * Day view: Gantt blocks + interval grid data for the selected day.
   */
  /**
   * Day view: Gantt with ALL roster users.
   * Users with no shift on this day appear as empty rows.
   */
  private function computeDayView(int $day, ?Campaign $campaign = null): array
  {
    $pxPerHour = self::PX_PER_HOUR;
    $pxPerMin = $pxPerHour / 60;

    // Index AgentStatusType by slug for O(1) color/label lookup
    $statusBySlug = AgentStatusType::all()->keyBy("slug");

    // All users from the session-selected campaign via userGroups (sorted by last name then first name)
    $campaignUsers = $campaign
      ? $campaign->userGroups
        ->flatMap(fn($g) => $g->users)
        ->unique("id")
        ->sortBy(fn($u) => strtolower($u->last_name . " " . $u->first_name))
      : collect();

    // Fall back to users from shifts if no campaign is available (legacy rosters)
    if ($campaignUsers->isEmpty()) {
      $campaignUsers = $this->roster->shifts
        ->unique("user_id")
        ->map(fn($s) => $s->user)
        ->filter()
        ->sortBy(fn($u) => strtolower($u->last_name . " " . $u->first_name));
    }

    // Shifts specifically for this day (keyed by user_id for quick lookup)
    $dayShifts = $this->roster->shifts
      ->where("day_of_week", $day)
      ->filter(fn($s) => $s->start_time && $s->end_time);

    $dayShiftsByUser = $dayShifts->keyBy("user_id");

    // Time window: always at least 6am–8pm; expand if shifts go outside that range
    $defaultStart = Carbon::today()->setTime(6, 0);
    $defaultEnd = Carbon::today()->setTime(20, 0);

    if ($dayShifts->isNotEmpty()) {
      $shiftStart = Carbon::parse($dayShifts->min("start_time"))->startOfHour();
      $shiftEnd = Carbon::parse($dayShifts->max("end_time"))
        ->addHour()
        ->startOfHour();
      $visibleStart = $shiftStart->lt($defaultStart)
        ? $shiftStart
        : $defaultStart;
      $visibleEnd = $shiftEnd->gt($defaultEnd) ? $shiftEnd : $defaultEnd;
    } else {
      $visibleStart = $defaultStart;
      $visibleEnd = $defaultEnd;
    }

    // 30-min tick marks
    $hours = [];
    $cursor = $visibleStart->copy();
    while ($cursor->lte($visibleEnd)) {
      $hours[] = [
        "label" => $cursor->format("g:ia"),
        "left" => (int) round(
          $cursor->diffInMinutes($visibleStart) * $pxPerMin
        ),
        "isHalf" => $cursor->minute === 30,
      ];
      $cursor->addMinutes(30);
    }

    $timelineWidth = (int) round(
      $visibleEnd->diffInMinutes($visibleStart) * $pxPerMin
    );

    // Build an agent row for every campaign user
    $agents = [];
    foreach ($campaignUsers as $user) {
      // Apply filters before building blocks (cheap early exit)
      $fullName = strtolower(trim($user->first_name . " " . $user->last_name));
      $group = $user->userGroup?->name ?? "Unassigned";
      if (
        $this->filterSearch &&
        !str_contains($fullName, strtolower(trim($this->filterSearch)))
      ) {
        continue;
      }
      if ($this->filterGroup && $group !== $this->filterGroup) {
        continue;
      }
      $shift = $dayShiftsByUser->get($user->id);
      $blocks = [];

      if ($shift) {
        foreach ($shift->activities as $activity) {
          $actStart = Carbon::parse($activity->start_time);
          $actEnd = Carbon::parse($activity->end_time);
          $leftPx = (int) round(
            $actStart->diffInMinutes($visibleStart) * $pxPerMin
          );
          $widthPx = max(
            8,
            (int) round($actEnd->diffInMinutes($actStart) * $pxPerMin)
          );
          $statusType = $statusBySlug->get($activity->activity_type);
          $color = $statusType?->color ?? "#6366f1";

          $blocks[] = [
            "id" => $activity->id,
            "type" => $activity->activity_type,
            "label" => $statusType?->name ?? ucfirst($activity->activity_type),
            "left" => $leftPx,
            "width" => $widthPx,
            "color" => $color,
            "startF" => $actStart->format("g:ia"),
            "endF" => $actEnd->format("g:ia"),
          ];
        }

        // Fallback: no activities yet — render the shift window as one block
        if (empty($blocks)) {
          $shiftStart = Carbon::parse($shift->start_time);
          $shiftEnd = Carbon::parse($shift->end_time);
          $blocks[] = [
            "id" => null,
            "type" => "shift",
            "label" => "Shift",
            "left" => (int) round(
              $shiftStart->diffInMinutes($visibleStart) * $pxPerMin
            ),
            "width" => max(
              8,
              (int) round($shiftEnd->diffInMinutes($shiftStart) * $pxPerMin)
            ),
            "color" => $statusBySlug->get("phones")?->color ?? "#14b8a6",
            "startF" => $shiftStart->format("g:ia"),
            "endF" => $shiftEnd->format("g:ia"),
          ];
        }
      }

      // Derive total plotted hours directly from activity blocks (excludes overlaps, most accurate)
      $plottedMinutes = 0;
      foreach ($blocks as $b) {
        if ($b["id"] ?? null) {
          $plottedMinutes += (int) round($b["width"] / $pxPerMin);
        }
      }
      $plottedHours =
        $plottedMinutes > 0 ? round($plottedMinutes / 60, 1) : null;

      $agents[] = [
        "user" => $user,
        "shift_id" => $shift?->id,
        "location" => $shift?->location ?? "Unassigned",
        "group" => $user->userGroup?->name ?? "Unassigned",
        "hours" => $plottedHours,
        "blocks" => $blocks,
      ];
    }

    // Apply location filter after building (location comes from shift, not user)
    if ($this->filterLocation) {
      $agents = array_values(
        array_filter(
          $agents,
          fn($a) => $a["location"] === $this->filterLocation
        )
      );
    }

    // Interval grid (per-15-min stats)
    $intervals = $this->roster->intervals
      ->where("day_of_week", $day)
      ->sortBy("interval_start");

    $intervalRows = [];
    foreach ($intervals as $interval) {
      $iStart = Carbon::parse($interval->interval_start);
      $iStartStr = $interval->interval_start;
      $iEndStr = $iStart->copy()->addMinutes(15)->format("H:i:s");
      $leftPx = (int) round($iStart->diffInMinutes($visibleStart) * $pxPerMin);

      $phonesCount = 0;
      foreach ($dayShifts as $shift) {
        foreach (
          $shift->activities->where("activity_type", "phones")
          as $activity
        ) {
          if (
            $activity->start_time <= $iEndStr &&
            $activity->end_time > $iStartStr
          ) {
            $phonesCount++;
            break;
          }
        }
      }

      $net = $phonesCount - (int) $interval->agents_required;

      $intervalRows[] = [
        "start" => $iStart->format("g:ia"),
        "left" => $leftPx,
        "phones_rostered" => $phonesCount,
        "phones_required" => (int) $interval->agents_required,
        "net" => $net,
        "calls" => (int) $interval->calls_forecast,
      ];
    }

    return [
      "agents" => $agents,
      "hours" => $hours,
      "timelineWidth" => $timelineWidth,
      "intervals" => $intervalRows,
      "pxPerMin" => $pxPerMin,
      "visibleStartMinutes" => $visibleStart->hour * 60 + $visibleStart->minute,
    ];
  }
}
