<?php

namespace App\Livewire\Activitylogs;

use Livewire\Component;
use App\Models\ActivityLog;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class ActivitylogsIndex extends Component
{
  use WithPagination;

  protected $paginationTheme = "tailwind";
  protected $queryString = ["filters"];

  public int $perPage = 20;

  public ?ActivityLog $selectedLog = null;
  public Collection $batchLogs;

  public array $filters = [
    "type" => null,
    "severity" => null,
    "source" => null,
  ];

  public function mount(): void
  {
    $this->batchLogs = collect();
  }

  public function show(int $id): void
  {
    $this->selectedLog = ActivityLog::with(["actor", "subject"])->findOrFail(
      $id
    );

    if ($this->selectedLog->batch_id) {
      $this->batchLogs = ActivityLog::where(
        "batch_id",
        $this->selectedLog->batch_id
      )
        ->orderBy("performed_at")
        ->get();
    } else {
      $this->batchLogs = collect();
    }
  }

  public function closeDrawer(): void
  {
    $this->selectedLog = null;
    $this->batchLogs = collect();
  }

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    $logs = ActivityLog::query()
      ->whereIn("id", function ($query) {
        $query
          ->select(DB::raw("MIN(id)"))
          ->from("activity_logs")
          ->groupBy(DB::raw("COALESCE(batch_id, id)"));
      })
      ->with(["actor", "subject"])
      ->orderByDesc("performed_at")
      ->paginate($this->perPage);

    return view(
      "livewire.activitylogs.activitylogs-index",
      compact("logs")
    )->layout("components.layouts.app");
  }
}
