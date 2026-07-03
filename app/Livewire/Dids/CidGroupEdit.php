<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use App\Models\CidGroup;
use App\Models\CidNumber;
use App\Models\Campaign;

class CidGroupEdit extends Component
{
    public CidGroup $cidGroup;

    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;

    /** IDs of CID numbers currently in this group */
    public array $selectedCidIds = [];

    public bool $confirmingDelete = false;

    public function mount(CidGroup $cidGroup): void
    {
        $this->cidGroup     = $cidGroup;
        $this->name         = $cidGroup->name;
        $this->description  = $cidGroup->description ?? '';
        $this->is_active    = $cidGroup->is_active;

        $this->selectedCidIds = $cidGroup->cidNumbers()
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    public function save(): void
    {
        $this->validate([
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:1000'],
            'is_active'      => ['boolean'],
            'selectedCidIds'   => ['array'],
            'selectedCidIds.*' => ['integer', 'exists:cid_numbers,id'],
        ]);

        $selectedIds = array_map('intval', $this->selectedCidIds);

        // Ensure none of the selected numbers already belong to a DIFFERENT group
        $conflict = CidNumber::whereIn('id', $selectedIds)
            ->whereNotNull('cid_group_id')
            ->where('cid_group_id', '!=', $this->cidGroup->id)
            ->count();

        if ($conflict > 0) {
            $this->addError('selectedCidIds', 'One or more selected numbers already belong to a different CID group.');
            return;
        }

        $this->cidGroup->update([
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'is_active'   => $this->is_active,
        ]);

        // Assign selected numbers to this group; release previously assigned ones
        CidNumber::whereIn('id', $selectedIds)
            ->update(['cid_group_id' => $this->cidGroup->id]);

        CidNumber::where('cid_group_id', $this->cidGroup->id)
            ->whereNotIn('id', $selectedIds)
            ->update(['cid_group_id' => null]);

        session()->flash('success', 'CID group updated successfully.');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        // Unlink any campaigns that reference this group
        \App\Models\Campaign::where('cid_group_id', $this->cidGroup->id)
            ->update(['cid_group_id' => null, 'cid_rotation' => false]);

        // Release all CID numbers from this group
        CidNumber::where('cid_group_id', $this->cidGroup->id)
            ->update(['cid_group_id' => null]);

        $this->cidGroup->delete();

        $this->redirect(route('cid-groups.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.dids.cid-group-edit', [
            'allCidNumbers' => CidNumber::orderBy('phone_number')->get(),
        ])->layout('components.layouts.app');
    }
}
