<?php

namespace App\Livewire\Admin;

use App\Models\CidGroup;
use App\Models\CidNumber;
use Livewire\Attributes\On;
use Livewire\Component;

class CidGroupModal extends Component
{
    public bool   $open = false;
    public string $mode = 'create';
    public ?int   $groupId = null;
    public int    $step = 1;

    // Step 1: Details
    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;

    // Step 2: Numbers
    public array $selectedCidIds = [];

    #[On('open-cid-group-create')]
    public function openCreate(): void
    {
        $this->reset(['groupId', 'name', 'description', 'selectedCidIds']);
        $this->resetErrorBag();
        $this->is_active = true;
        $this->mode = 'create';
        $this->step = 1;
        $this->open = true;
    }

    #[On('open-cid-group-edit')]
    public function openEdit(int $id): void
    {
        $group = CidGroup::findOrFail($id);

        $this->groupId     = $group->id;
        $this->name        = $group->name;
        $this->description = $group->description ?? '';
        $this->is_active   = (bool) $group->is_active;

        $this->selectedCidIds = $group->cidNumbers()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();

        $this->resetErrorBag();
        $this->mode = 'edit';
        $this->step = 1;
        $this->open = true;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function nextStep(): void
    {
        if ($this->step === 1) {
            $this->validateOnly('name', ['name' => 'required|string|max:255']);
            if ($this->getErrorBag()->has('name')) return;
        }
        if ($this->step < 2) $this->step++;
    }

    public function prevStep(): void
    {
        if ($this->step > 1) $this->step--;
    }

    public function save(): void
    {
        $this->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string|max:1000',
            'is_active'        => 'boolean',
            'selectedCidIds'   => 'array',
            'selectedCidIds.*' => 'integer|exists:cid_numbers,id',
        ]);

        $ids = array_map('intval', $this->selectedCidIds);

        if ($this->mode === 'create') {
            $group = CidGroup::create([
                'name'        => $this->name,
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
            ]);
        } else {
            $group = CidGroup::findOrFail($this->groupId);

            // Check for conflicts — numbers assigned to a different group
            $conflict = CidNumber::whereIn('id', $ids)
                ->whereNotNull('cid_group_id')
                ->where('cid_group_id', '!=', $group->id)
                ->count();

            if ($conflict > 0) {
                $this->addError('selectedCidIds', 'One or more numbers already belong to a different CID group.');
                return;
            }

            $group->update([
                'name'        => $this->name,
                'description' => $this->description ?: null,
                'is_active'   => $this->is_active,
            ]);
        }

        // Assign selected numbers; release previously assigned ones
        CidNumber::whereIn('id', $ids)->update(['cid_group_id' => $group->id]);
        CidNumber::where('cid_group_id', $group->id)
            ->whereNotIn('id', $ids)
            ->update(['cid_group_id' => null]);

        $this->open = false;
        session()->flash('success', $this->mode === 'create' ? 'CID group created.' : 'CID group updated.');
        $this->dispatch('cid-group-saved');
    }

    public function render()
    {
        return view('livewire.admin.cid-group-modal', [
            'allNumbers' => CidNumber::orderBy('friendly_name')->orderBy('phone_number')->get(),
        ]);
    }
}
