<?php

namespace App\Livewire\Workforce;

use App\Models\Roster;
use Livewire\Component;
use Livewire\WithPagination;

class RosterIndex extends Component
{
  use WithPagination;

  // ── List state ───────────────────────────────────────────────────
  public string $search = "";
  public int $perPage = 25;

  protected $queryString = [
    "search" => ["except" => ""],
  ];

  // ── Create modal state ───────────────────────────────────────────
  public bool $showCreateModal = false;
  public string $createName = "";
  public string $createWeekStart = "";
  public string $createTimezone = "America/Denver";

  protected function rules(): array
  {
    return [
      "createWeekStart" => ["required", "date"],
      "createTimezone" => ["required", "string", "max:100"],
      "createName" => ["nullable", "string", "max:255"],
    ];
  }

  public function openCreateModal(): void
  {
    $this->reset(["createName", "createWeekStart"]);
    $this->createTimezone = "America/Denver";
    $this->resetValidation();
    $this->showCreateModal = true;
  }

  public function createRoster(): void
  {
    $this->validate();

    $roster = Roster::create([
      "campaign_id" => session("active_campaign_id"),
      "name" => $this->createName ?: null,
      "week_start" => $this->createWeekStart,
      "timezone" => $this->createTimezone,
      "status" => "draft",
      "created_by" => auth()->id(),
    ]);

    $this->showCreateModal = false;
    $this->redirect(route("workforce.roster.show", $roster), navigate: true);
  }

  // ── List ─────────────────────────────────────────────────────────
  public function updatingSearch(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    $rosters = Roster::query()
      ->with("campaign")
      ->when(
        $this->search,
        fn($q) => $q->where("name", "like", "%{$this->search}%")
      )
      ->orderByDesc("week_start")
      ->paginate($this->perPage);

    return view("livewire.workforce.roster-index", compact("rosters"))->layout(
      "components.layouts.app"
    );
  }
}
