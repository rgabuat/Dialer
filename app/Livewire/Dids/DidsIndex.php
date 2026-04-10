<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Did;

class DidsIndex extends Component
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
        $dids = Did::with(['cidNumber', 'inGroup', 'ivrMenu'])
            ->when(
                $this->search,
                fn($q) => $q
                    ->where('phone_number', 'like', "%{$this->search}%")
                    ->orWhereHas('cidNumber', fn($cq) => $cq->where('friendly_name', 'like', "%{$this->search}%"))
            )
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.dids.dids-index', compact('dids'))->layout(
            'components.layouts.app'
        );
    }
}
