<?php

namespace App\Livewire\Conversations;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Conversation;
use App\Models\Campaign;

class ConversationsIndex extends Component
{
  use WithPagination;

  public string $search = "";
  public string $tab = "all"; // assigned | all
  public string $filterStatus = "";
  public string $filterChannel = "";
  public string $filterQueue = "";
  public string $filterDate = "";
  public int $perPage = 50;

  protected $queryString = [
    "search" => ["except" => ""],
    "tab" => ["except" => "all"],
    "filterStatus" => ["except" => ""],
    "filterChannel" => ["except" => ""],
    "filterQueue" => ["except" => ""],
    "filterDate" => ["except" => ""],
  ];

  public function mount(): void
  {
    if (request()->routeIs("conversations.assigned")) {
      $this->tab = "assigned";
    }
  }

  public function updatingSearch(): void
  {
    $this->resetPage();
  }

  public function updatingFilterStatus(): void
  {
    $this->resetPage();
  }
  public function updatingFilterChannel(): void
  {
    $this->resetPage();
  }
  public function updatingFilterQueue(): void
  {
    $this->resetPage();
  }
  public function updatingFilterDate(): void
  {
    $this->resetPage();
  }

  public function setTab(string $tab): void
  {
    $this->tab = $tab;
    $this->resetPage();
  }

  public function render()
  {
    $campaignId = session('active_campaign_id');

    $query = Conversation::with([
      "assignedAgent",
      "completedByAgent",
      "campaign",
    ])
      ->select('conversations.*')
      ->distinct()
      ->when(
        $campaignId,
        fn($q) => $q->where('campaign_id', $campaignId)
      )
      ->when(
        $this->tab === "assigned",
        fn($q) => $q->where("assigned_to", auth()->id())
      )
      ->when($this->search, function ($q) {
        $q->where(function ($inner) {
          $inner
            ->where("contact_name", "like", "%{$this->search}%")
            ->orWhere("contact_phone", "like", "%{$this->search}%");
        });
      })
      ->when(
        $this->filterStatus,
        fn($q) => $q->where("status", $this->filterStatus)
      )
      ->when(
        $this->filterChannel,
        fn($q) => $q->where("channel", $this->filterChannel)
      )
      ->when(
        $this->filterQueue,
        fn($q) => $q->where("queue", $this->filterQueue)
      )
      ->when(
        $this->filterDate,
        fn($q) => $q->whereDate("started_at", $this->filterDate)
      )
      ->latest("started_at");

    return view("livewire.conversations.conversations-index", [
      "conversations" => $query->paginate($this->perPage),
    ])->layout("components.layouts.app");
  }
}
