<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use App\Models\Did;
use App\Models\CidNumber;
use App\Models\InGroup;
use App\Models\IvrMenu;

class DidEdit extends Component
{
    public Did $did;

    public ?int $cid_number_id = null;
    public string $destination = 'in_group';
    public ?int $in_group_id = null;
    public ?int $ivr_menu_id = null;
    public bool $is_active = true;

    public bool $confirmingDelete = false;

    public function mount(Did $did): void
    {
        $this->did         = $did;
        $this->cid_number_id = $did->cid_number_id;
        $this->in_group_id = $did->in_group_id;
        $this->ivr_menu_id = $did->ivr_menu_id;
        $this->is_active   = $did->is_active;
        $this->destination = $did->ivr_menu_id ? 'ivr_menu' : 'in_group';
    }

    public function save(): void
    {
        $this->validate([
            'cid_number_id' => [
                'required', 'integer', 'exists:cid_numbers,id',
                'unique:dids,cid_number_id,' . $this->did->id,
            ],
            'destination'   => ['required', 'in:in_group,ivr_menu'],
            'in_group_id'   => ['required_if:destination,in_group', 'nullable', 'integer', 'exists:in_groups,id'],
            'ivr_menu_id'   => ['required_if:destination,ivr_menu', 'nullable', 'integer', 'exists:ivr_menus,id'],
            'is_active'     => ['boolean'],
        ]);

        $cid = CidNumber::findOrFail($this->cid_number_id);

        $this->did->update([
            'phone_number'  => $cid->phone_number,
            'cid_number_id' => $cid->id,
            'in_group_id'   => $this->destination === 'in_group' ? $this->in_group_id : null,
            'ivr_menu_id'   => $this->destination === 'ivr_menu' ? $this->ivr_menu_id : null,
            'is_active'     => $this->is_active,
        ]);

        session()->flash('success', 'DID updated.');
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        $this->did->delete();
        $this->redirect(route('dids.index'), navigate: true);
    }

    public function render()
    {
        // Available CIDs: currently assigned to this DID + unassigned ones
        $assignedCidIds = Did::whereNotNull('cid_number_id')
            ->where('id', '!=', $this->did->id)
            ->pluck('cid_number_id');

        $availableCids = CidNumber::where('is_active', true)
            ->whereNotIn('id', $assignedCidIds)
            ->orderBy('phone_number')
            ->get();

        $inGroups = InGroup::where('is_active', true)->orderBy('name')->get();
        $ivrMenus = IvrMenu::where('is_active', true)->orderBy('name')->get();

        return view('livewire.dids.did-edit', compact('availableCids', 'inGroups', 'ivrMenus'))
            ->layout('components.layouts.app');
    }
}

