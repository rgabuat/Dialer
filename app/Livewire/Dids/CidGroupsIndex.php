<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use App\Models\CidGroup;

class CidGroupsIndex extends Component
{
    public bool $showCreateModal = false;
    public string $newName        = '';
    public string $newDescription = '';
    public bool $newIsActive      = true;

    public ?string $successMessage = null;

    public function openCreate(): void
    {
        $this->newName        = '';
        $this->newDescription = '';
        $this->newIsActive    = true;
        $this->showCreateModal = true;
    }

    public function create(): void
    {
        $this->validate([
            'newName'        => ['required', 'string', 'max:255'],
            'newDescription' => ['nullable', 'string', 'max:1000'],
            'newIsActive'    => ['boolean'],
        ]);

        CidGroup::create([
            'name'        => $this->newName,
            'description' => $this->newDescription ?: null,
            'is_active'   => $this->newIsActive,
        ]);

        $this->showCreateModal = false;
        $this->successMessage  = "CID group \"{$this->newName}\" created.";
        $this->newName         = '';
        $this->newDescription  = '';
    }

    public function render()
    {
        return view('livewire.dids.cid-groups-index', [
            'groups' => CidGroup::withCount('cidNumbers')
                ->with('campaign:id,name')
                ->orderBy('name')
                ->get(),
        ])->layout('components.layouts.app');
    }
}
