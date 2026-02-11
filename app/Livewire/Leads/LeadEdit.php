<?php

namespace App\Livewire\Leads;

use App\Models\Lead;
use App\Models\Store;
use Livewire\Component;

class LeadEdit extends Component
{
    public ?Lead $lead = null;
    public bool $confirmingDelete = false;
    public $first_name;
    public $last_name;
    public $phone;
    public $email;
    public $store_id;
    
    protected $rules = [
        'first_name' => 'required',
        'last_name'  => 'required',
        'store_id'   => 'required|exists:stores,id',
    ];

    public function mount(Lead $lead)
    {
        $this->lead = $lead;

        // ✅ MANUAL HYDRATION (THIS IS THE KEY)
        $this->first_name = $lead->first_name;
        $this->last_name  = $lead->last_name;
        $this->phone      = $lead->phone;
        $this->email      = $lead->email;
        $this->store_id   = $lead->store_id;
    }

    public function save()
    {
        $this->validate();

        $this->lead->update([
            'first_name'        => $this->first_name,
            'last_name'         => $this->last_name,
            'phone'             => $this->phone,
            'email'             => $this->email,
            'store_id'          => $this->store_id,
            'last_actioned_by'  => auth()->id(),
        ]);

        session()->flash('success', 'Lead updated.');
    }


    public function confirmDelete()
    {
        $this->confirmingDelete = true;
    }


    public function delete()
    {
        $this->lead->delete();
        return redirect()->route('leads.index');
    }

    public function render()
    {
        return view('livewire.leads.lead-edit', [
            'stores' => Store::all(),
        ])->layout('components.layouts.app');
    }
}
