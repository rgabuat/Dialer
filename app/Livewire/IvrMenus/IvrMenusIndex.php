<?php

namespace App\Livewire\IvrMenus;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\IvrMenu;

class IvrMenusIndex extends Component
{
  use WithPagination;

  public string $search = "";
  public int $perPage = 20;

  public function updatingSearch(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    $ivrMenus = IvrMenu::withCount("options")
      ->when(
        $this->search,
        fn($q) => $q->where("name", "like", "%{$this->search}%")
      )
      ->latest()
      ->paginate($this->perPage);

    return view(
      "livewire.ivr-menus.ivr-menus-index",
      compact("ivrMenus")
    )->layout("components.layouts.app");
  }
}
