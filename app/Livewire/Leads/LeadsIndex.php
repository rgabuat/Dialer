<?php

namespace App\Livewire\Leads;

use livewire;
use App\Models\Lead;
use Livewire\Component;
use Livewire\WithPagination;

class LeadsIndex extends Component
{
  use WithPagination;

  protected $paginationTheme = "tailwind";

  public int $perPage = 10;

  public function updatedPerPage(): void
  {
    $this->resetPage();
  }

  public function render()
  {
    return view("livewire.leads.leads-index", [
      "leads" => Lead::with(["store", "creator"])
        ->latest()
        ->paginate($this->perPage),
    ])->layout("components.layouts.app");
  }
}
