<?php

namespace App\Livewire\Activity;

use App\Models\AgentStatusLog;
use App\Models\AgentStatusType;
use App\Models\Campaign;
use App\Models\Roster;
use App\Models\ShiftActivity;
use App\Models\User;
use App\Models\UserGroup;
use Carbon\Carbon;
use Livewire\Attributes\On;
use Livewire\Component;

class ShiftMonitoring extends Component
{
  public string $search = "";
  public array $filterStatus = [];
  public array $filterGroup = [];
  public string $date = "";

  #[On('refresh-timeline')]
  public function refreshTimeline(): void
  {
      // triggers full re-render
  }

  protected $queryString = [
    "search" => ["except" => ""],
    "filterStatus" => ["except" => []],
    "filterGroup" => ["except" => []],
  ];

  const PX_PER_HOUR = 160;

  public function mount(): void
  {
    $this->date = now()->toDateString();
  }

  public function render()
  {
    $pxPerHour = self::PX_PER_HOUR;
    $pxPerMin = $pxPerHour / 60;

    $date = Carbon::parse($this->date);
    $start = $date->copy()->startOfDay();
    $end = $date->copy()->endOfDay();

    // ── All logs for the day ───────────────────────────────────────────
    $allLogs = AgentStatusLog::query()
      ->whereBetween("started_at", [$start, $end])
      ->with("statusType")
      ->get();

    // ── Visible time range: full roster day (earliest shift → latest shift) ──
    // Pre-load the roster shifts for today to compute the time window
    $dayOfWeekForRange = Carbon::parse($this->date)->dayOfWeek;
    $rosterForRange = Roster::with(["shifts"])
      ->where("week_start", "<=", $date->toDateString())
      ->whereRaw("DATE_ADD(week_start, INTERVAL 6 DAY) >= ?", [
        $date->toDateString(),
      ])
      ->where("status", "published")
      ->orderByDesc("week_start")
      ->first();

    $shiftsForRange = $rosterForRange
      ? $rosterForRange->shifts->where("day_of_week", $dayOfWeekForRange)
      : collect();

    // Earliest shift start (fall back to first log, then 6am)
    $earliestShift = $shiftsForRange
      ->filter(fn($s) => $s->start_time)
      ->sortBy("start_time")
      ->first();
    $firstLog = $allLogs
      ->filter(fn($l) => $l->started_at)
      ->sortBy("started_at")
      ->first();

    $visibleStartCandidates = collect();
    if ($earliestShift) {
      $visibleStartCandidates->push(
        Carbon::parse($date->toDateString() . " " . $earliestShift->start_time)
      );
    }
    if ($firstLog) {
      $visibleStartCandidates->push($firstLog->started_at->copy());
    }
    $visibleStart = $visibleStartCandidates->isNotEmpty()
      ? $visibleStartCandidates->min()->startOfHour()
      : $date->copy()->setHour(6)->setMinute(0)->setSecond(0);

    // Latest shift end (fall back to last log, then 10pm)
    $latestShift = $shiftsForRange
      ->filter(fn($s) => $s->end_time)
      ->sortByDesc("end_time")
      ->first();
    $lastLog = $allLogs
      ->filter(fn($l) => $l->ended_at)
      ->sortByDesc("ended_at")
      ->first();

    $visibleEndCandidates = collect();
    if ($latestShift) {
      $visibleEndCandidates->push(
        Carbon::parse($date->toDateString() . " " . $latestShift->end_time)
      );
    }
    if ($lastLog) {
      $visibleEndCandidates->push($lastLog->ended_at->copy());
    }
    $visibleEnd = $visibleEndCandidates->isNotEmpty()
      ? $visibleEndCandidates->max()->addHour()->startOfHour()
      : $date->copy()->setHour(22)->setMinute(0)->setSecond(0);

    // ── Hours array for the timeline header (every 30 min) ──────────
    $hours = [];
    $cursor = $visibleStart->copy();
    while ($cursor->lte($visibleEnd)) {
      $isHalf = $cursor->minute === 30;
      $hours[] = [
        "label" => $cursor->format("G:i"),
        "left" => (int) round(
          $cursor->diffInMinutes($visibleStart) * $pxPerMin
        ),
        "isHalf" => $isHalf,
      ];
      $cursor->addMinutes(30);
    }

    $timelineWidth = (int) round(
      $visibleEnd->diffInMinutes($visibleStart) * $pxPerMin
    );

    // ── Active roster + scheduled activity map (built early so $shiftUserIds is available for the agent query) ──
    $statusBySlug = AgentStatusType::all()->keyBy("slug");

    // Use the roster's timezone for all local-time comparisons
    $rosterTz = $rosterForRange?->timezone ?? config("app.timezone", "UTC");
    $nowInRosterTz = now($rosterTz);
    $dayOfWeek = Carbon::parse($this->date, $rosterTz)->dayOfWeek; // 0 = Sunday … 6 = Saturday
    $isToday = $this->date === $nowInRosterTz->toDateString();
    $nowTime = $isToday ? $nowInRosterTz->format("H:i:s") : "23:59:59";

    $roster = Roster::with(["shifts.activities"])
      ->where("week_start", "<=", $date->toDateString())
      ->whereRaw("DATE_ADD(week_start, INTERVAL 6 DAY) >= ?", [
        $date->toDateString(),
      ])
      ->where("status", "published")
      ->orderByDesc("week_start")
      ->first();

    $scheduledByUser = collect();
    $shiftUserIds = collect();
    if ($roster) {
      foreach ($roster->shifts->where("day_of_week", $dayOfWeek) as $shift) {
        $shiftUserIds->push($shift->user_id);

        // 1st choice: activity currently in progress
        $activity = $shift->activities
          ->filter(
            fn($a) => $a->start_time <= $nowTime && $a->end_time > $nowTime
          )
          ->first();

        // 2nd choice: next upcoming activity (shift not started yet / gap)
        if (!$activity && $nowTime < $shift->end_time) {
          $activity =
            $shift->activities
              ->filter(fn($a) => $a->start_time >= $nowTime)
              ->sortBy("start_time")
              ->first() ?? $shift->activities->sortByDesc("end_time")->first();
        }

        if ($activity) {
          $st = $statusBySlug->get($activity->activity_type);
          $scheduledByUser[$shift->user_id] = [
            "slug" => $activity->activity_type,
            "label" => $st?->name ?? ucfirst($activity->activity_type),
            "color" =>
              $st?->color ??
              (ShiftActivity::COLORS[$activity->activity_type]["hex"] ??
                "#71717a"),
          ];
        }
      }
    }

    // ── All campaign users (the source of truth for who should be here) ──
    $campaignUserIds = collect();
    $campaignId = session("active_campaign_id");
    if ($campaignId) {
      $campaignUserIds =
        Campaign::find($campaignId)?->users()->pluck("users.id") ?? collect();
    }
    // Fall back: include anyone with logs or a shift today
    if ($campaignUserIds->isEmpty()) {
      $campaignUserIds = $allLogs
        ->pluck("user_id")
        ->merge($shiftUserIds)
        ->unique();
    }

    // ── Agents with eager loads ────────────────────────────────────────
    $agents = User::query()
      ->with(["userGroup", "agentStatus.statusType"])
      ->whereIn("id", $campaignUserIds->all())
      ->when(
        $this->search,
        fn($q) => $q->where(function ($q) {
          $q->where("first_name", "like", "%{$this->search}%")
            ->orWhere("last_name", "like", "%{$this->search}%")
            ->orWhere("email", "like", "%{$this->search}%");
        })
      )
      ->when(
        count($this->filterStatus),
        fn($q) => $q->whereHas(
          "statusLogs",
          fn($sq) => $sq
            ->whereBetween("started_at", [$start, $end])
            ->whereHas(
              "statusType",
              fn($t) => $t->whereIn("slug", $this->filterStatus)
            )
        )
      )
      ->when(
        count($this->filterGroup),
        fn($q) => $q->whereHas(
          "userGroup",
          fn($g) => $g->whereIn("name", $this->filterGroup)
        )
      )
      ->orderBy("first_name")
      ->get();

    // Attach timeline blocks to each agent
    $logsByUser = $allLogs->groupBy("user_id");

    $agents->each(function ($user) use (
      $logsByUser,
      $visibleStart,
      $visibleEnd,
      $pxPerMin
    ) {
      $logs = $logsByUser
        ->get($user->id, collect())
        ->filter(fn($l) => $l->started_at)
        ->sortBy("started_at");

      $user->timelineBlocks = $logs
        ->map(function ($log) use ($visibleStart, $visibleEnd, $pxPerMin) {
          $bStart =
            $log->started_at instanceof Carbon
              ? $log->started_at
              : Carbon::parse($log->started_at);
          $bEnd = $log->ended_at
            ? ($log->ended_at instanceof Carbon
              ? $log->ended_at
              : Carbon::parse($log->ended_at))
            : now();

          $cStart = $bStart->max($visibleStart);
          $cEnd = $bEnd->min($visibleEnd);

          if ($cEnd->lte($cStart)) {
            return null;
          }

          $offsetMins = max(0, (int) $cStart->diffInMinutes($visibleStart));
          $durationMins = max(1, (int) $cEnd->diffInMinutes($cStart));

          return [
            "left" => (int) round($offsetMins * $pxPerMin),
            "width" => max(2, (int) round($durationMins * $pxPerMin)),
            "label" => $bStart->format("g:ia"),
            "endLabel" => $bEnd->format("g:ia"),
            "durationLabel" =>
              $durationMins >= 60
                ? intdiv($durationMins, 60) .
                  "h " .
                  str_pad($durationMins % 60, 2, "0", STR_PAD_LEFT) .
                  "m"
                : $durationMins . "m",
            "status" => $log->statusType?->name ?? "Unknown",
            "color" => $log->statusType?->color ?? "#6366f1",
            "isOngoing" => !$log->ended_at,
          ];
        })
        ->filter()
        ->values();

      // Per-agent shift stats
      $userTotalSec = $logs->sum("duration_seconds");
      $userAvailSec = $logs
        ->filter(fn($l) => optional($l->statusType)->is_available)
        ->sum("duration_seconds");
      $firstLog = $logs->first();
      $lastLog = $logs->sortByDesc("ended_at")->first();

      $user->shiftStartLabel = $firstLog?->started_at?->format("g:ia") ?? "—";
      $user->shiftEndLabel = $lastLog?->ended_at
        ? $lastLog->ended_at->format("g:ia")
        : "ongoing";
      $user->utilPct =
        $userTotalSec > 0 ? round(($userAvailSec / $userTotalSec) * 100) : 0;
      $user->totalShiftLabel =
        $userTotalSec > 0 ? self::formatSeconds((int) $userTotalSec) : "—";
    });

    // ── Attach scheduled status (built above, before agents query) ──
    $agents->each(function ($user) use ($scheduledByUser) {
      $sched = $scheduledByUser->get($user->id);
      $actualSlug = $user->agentStatus?->statusType?->slug;
      $user->scheduledLabel = $sched["label"] ?? null;
      $user->scheduledColor = $sched["color"] ?? "#71717a";
      $user->scheduledSlug = $sched["slug"] ?? null;
      $user->isMatch = $sched && $actualSlug && $actualSlug === $sched["slug"];
      $user->hasMismatch =
        $sched && $actualSlug && $actualSlug !== $sched["slug"];
    });

    // ── Group agents by user_group ─────────────────────────────────────
    $grouped = $agents
      ->groupBy("user_group_id")
      ->map(
        fn($users) => [
          "group" => $users->first()->userGroup,
          "agents" => $users,
          "count" => $users->count(),
        ]
      )
      ->sortBy(fn($g) => optional($g["group"])->name ?? "zzzzz")
      ->values();

    // ── Summary stats ──────────────────────────────────────────────────
    $totalAgents = $agents->count();
    $totalSeconds = $allLogs->sum("duration_seconds");
    $availSeconds = $allLogs
      ->filter(fn($l) => optional($l->statusType)->is_available)
      ->sum("duration_seconds");
    $avgUtil =
      $totalSeconds > 0 ? round(($availSeconds / $totalSeconds) * 100) : 0;

    $statusTypes = AgentStatusType::orderBy("name")->get();
    $isToday = $isToday; // already computed in roster-tz above

    $availableNow = $agents
      ->filter(
        fn($u) => optional(optional($u->agentStatus)->statusType)->is_available
      )
      ->count();

    // "Now" indicator offset — calculated in roster timezone so it aligns with stored shift times
    $visibleStartTs = $visibleStart->timestamp;
    // Pass the roster-tz "now" timestamp so the JS indicator is also correct
    $nowTs = $nowInRosterTz->timestamp;

    $userGroups = UserGroup::orderBy("name")->get();

    return view(
      "livewire.activity.shift-monitoring",
      compact(
        "grouped",
        "hours",
        "timelineWidth",
        "totalAgents",
        "totalSeconds",
        "availSeconds",
        "avgUtil",
        "statusTypes",
        "date",
        "isToday",
        "availableNow",
        "pxPerMin",
        "visibleStartTs",
        "nowTs",
        "rosterTz",
        "nowInRosterTz",
        "userGroups"
      )
    )->layout("components.layouts.app");
  }

  public static function formatSeconds(int $seconds): string
  {
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    return $h > 0 ? sprintf("%dh %02dm", $h, $m) : sprintf("%dm", $m);
  }
}
