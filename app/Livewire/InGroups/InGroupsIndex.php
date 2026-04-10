<?php

namespace App\Livewire\InGroups;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\InGroup;

class InGroupsIndex extends Component
{
  use WithPagination;

  public string $search = "";
  public int $perPage = 20;

  public function updatingSearch(): void
  {
    $this->resetPage();
  }

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    $inGroups = InGroup::with('campaign')
      ->withCount(['dids', 'users'])
      ->when(
        $this->search,
        fn($q) => $q->where('name', 'like', "%{$this->search}%")
      )
      ->orderByDesc('queue_priority')
      ->latest()
      ->paginate($this->perPage);

    return view(
      "livewire.in-groups.in-groups-index",
      compact("inGroups")
    )->layout("components.layouts.app");
  }
}
