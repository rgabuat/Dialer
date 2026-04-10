<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use App\Models\Did;
use App\Models\CidNumber;
use App\Models\InGroup;
use App\Models\IvrMenu;

class DidCreate extends Component
{
    public ?int $cid_number_id = null;
    public string $destination = 'in_group'; // in_group | ivr_menu
    public ?int $in_group_id = null;
    public ?int $ivr_menu_id = null;
    public bool $is_active = true;

    public function save(): void
    {
        $this->validate([
            'cid_number_id' => ['required', 'integer', 'exists:cid_numbers,id', 'unique:dids,cid_number_id'],
            'destination'   => ['required', 'in:in_group,ivr_menu'],
            'in_group_id'   => ['required_if:destination,in_group', 'nullable', 'integer', 'exists:in_groups,id'],
            'ivr_menu_id'   => ['required_if:destination,ivr_menu', 'nullable', 'integer', 'exists:ivr_menus,id'],
            'is_active'     => ['boolean'],
        ]);

        $cid = CidNumber::findOrFail($this->cid_number_id);

        Did::create([
            'phone_number'  => $cid->phone_number,
            'cid_number_id' => $cid->id,
            'in_group_id'   => $this->destination === 'in_group' ? $this->in_group_id : null,
            'ivr_menu_id'   => $this->destination === 'ivr_menu' ? $this->ivr_menu_id : null,
            'is_active'     => $this->is_active,
        ]);

        session()->flash('success', 'DID created successfully.');
        $this->redirect(route('dids.index'), navigate: true);
    }

    public function render()
    {
        // Only CID numbers not already assigned as a DID
        $assignedCidIds = Did::whereNotNull('cid_number_id')->pluck('cid_number_id');
        $availableCids  = CidNumber::where('is_active', true)
            ->whereNotIn('id', $assignedCidIds)
            ->orderBy('phone_number')
            ->get();

        $inGroups = InGroup::where('is_active', true)->orderBy('name')->get();
        $ivrMenus = IvrMenu::where('is_active', true)->orderBy('name')->get();

        return view('livewire.dids.did-create', compact('availableCids', 'inGroups', 'ivrMenus'))
            ->layout('components.layouts.app');
    }
}
