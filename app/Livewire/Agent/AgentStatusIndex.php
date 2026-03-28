<?php

namespace App\Livewire\Agent;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AgentStatus;
use App\Models\AgentStatusType;
use App\Models\UserGroup;

class AgentStatusIndex extends Component
{
  use WithPagination;

  public string $search = "";
  public string $filterStatus = "";
  public string $filterGroup = "";
  public int $perPage = 25;

  protected $queryString = [
    "search" => ["except" => ""],
    "filterStatus" => ["except" => ""],
    "filterGroup" => ["except" => ""],
  ];

  public function updatingSearch(): void
  {
    $this->resetPage();
  }
  public function updatingFilterStatus(): void
  {
    $this->resetPage();
  }
  public function updatingFilterGroup(): void
  {
    $this->resetPage();
  }

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    $baseQuery = AgentStatus::with(["user.userGroup", "statusType"])
      ->when(
        $this->search,
        fn($q) => $q->whereHas(
          "user",
          fn($u) => $u
            ->where("first_name", "like", "%{$this->search}%")
            ->orWhere("last_name", "like", "%{$this->search}%")
            ->orWhere("email", "like", "%{$this->search}%")
        )
      )
      ->when(
        $this->filterStatus,
        fn($q) => $q->whereHas(
          "statusType",
          fn($s) => $s->where("name", $this->filterStatus)
        )
      )
      ->when(
        $this->filterGroup,
        fn($q) => $q->whereHas(
          "user.userGroup",
          fn($g) => $g->where("name", $this->filterGroup)
        )
      );

    $statuses = $baseQuery->paginate($this->perPage);
    $totalCount = AgentStatus::count();
    $availCount = AgentStatus::whereHas(
      "statusType",
      fn($q) => $q->where("is_available", true)
    )->count();
    $unavailCnt = $totalCount - $availCount;

    // Average seconds in current status for unavailable agents
    $avgSeconds = AgentStatus::whereHas(
      "statusType",
      fn($q) => $q->where("is_available", false)
    )
      ->selectRaw("AVG(TIMESTAMPDIFF(SECOND, started_at, NOW())) as avg_sec")
      ->value("avg_sec");
    $avgMinutes = $avgSeconds ? round($avgSeconds / 60) : 0;
    $avgLabel =
      $avgMinutes >= 60
        ? floor($avgMinutes / 60) . "h " . $avgMinutes % 60 . "m"
        : $avgMinutes . "m";

    return view("livewire.agent.agent-status-index", [
      "statuses" => $statuses,
      "statusTypes" => AgentStatusType::orderBy("name")->get(),
      "userGroups" => UserGroup::orderBy("name")->get(),
      "totalCount" => $totalCount,
      "availCount" => $availCount,
      "unavailCnt" => $unavailCnt,
      "avgLabel" => $avgLabel,
    ])->layout("components.layouts.app");
  }
}
