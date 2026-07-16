<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\Store;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class LeadsIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public int $perPage = 20;

    public string $search = '';

    public array $filterStatus = [];

    public string $filterType = '';

    public string $filterStore = '';

    public string $filterUser = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterStore' => ['except' => ''],
        'filterUser' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStore(): void
    {
        $this->resetPage();
    }

    public function updatingFilterUser(): void
    {
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'filterType', 'filterStore', 'filterUser']);
        $this->filterStatus = [];
        $this->resetPage();
    }

    public function removeStatusFilter(string $status): void
    {
        $this->filterStatus = array_values(array_filter($this->filterStatus, fn ($s) => $s !== $status));
        $this->resetPage();
    }

    public function render()
    {
        $campaignId = session('active_campaign_id');

        $query = Lead::with(['store', 'creator', 'lastActionedBy'])
            ->when($campaignId, fn ($q) => $q->where('campaign_id', $campaignId))
            ->latest();

        if ($this->search) {
            $q = '%'.$this->search.'%';
            $query->where(fn ($b) => $b->where('first_name', 'like', $q)
                ->orWhere('last_name', 'like', $q)
                ->orWhere('phone', 'like', $q)
                ->orWhere('email', 'like', $q)
            );
        }

        if (! empty($this->filterStatus)) {
            $statuses = array_map('strtoupper', $this->filterStatus);
            $query->whereIn('status', $statuses);
        }

        if ($this->filterType) {
            $query->where('lead_type', $this->filterType);
        }

        if ($this->filterStore) {
            $query->where('store_id', $this->filterStore);
        }

        if ($this->filterUser) {
            $query->where('created_by', $this->filterUser);
        }

        $leads = $query->paginate($this->perPage);
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $users = User::orderBy('name')->get(['id', 'name']);

        // Load the template field definitions for the active campaign
        $templateFields = [];
        if ($campaignId) {
            $campaign = \App\Models\Campaign::with('leadTemplate')->find($campaignId);
            $process  = $campaign?->leadTemplate?->lead_process ?? $campaign?->lead_process;
            foreach (($process['steps'] ?? []) as $step) {
                foreach (($step['fields'] ?? []) as $field) {
                    if (!empty($field['key'])) {
                        $templateFields[$field['key']] = [
                            'label' => $field['label'] ?? ucfirst(str_replace('_', ' ', $field['key'])),
                            'type'  => $field['type'] ?? 'text',
                        ];
                    }
                }
            }
        }

        return view('livewire.leads.leads-index', [
            'leads'          => $leads,
            'stores'         => $stores,
            'users'          => $users,
            'templateFields' => $templateFields,
        ])->layout('components.layouts.app');
    }
}
