<?php

namespace App\Livewire\Leads;

use livewire;
use App\Models\Lead;
use Livewire\Component;
use Livewire\WithPagination;

class LeadsIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public function render()
    {
        return view('livewire.leads.leads-index', [
            'leads' => Lead::with(['store', 'creator'])
                ->latest()
                ->paginate(10),
        ])->layout('components.layouts.app');
    }
}
