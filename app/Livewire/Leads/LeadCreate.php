<?php

namespace App\Livewire\Leads;

use App\Models\ActivityLog;
use App\Models\Lead;
use App\Models\Store;
use Livewire\Component;

class LeadCreate extends Component
{
    public $first_name, $last_name, $phone, $email, $store_id;

    protected $rules = [
        'first_name' => 'required',
        'last_name' => 'required',
        'store_id' => 'required|exists:stores,id',
    ];

    public function save()
    {
        $this->validate();

        $lead = Lead::create([
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'store_id' => $this->store_id,
            'created_by' => auth()->id(),
            'last_actioned_by' => auth()->id(),
        ]);

        $initialStage = $lead->pipeline_stage ?? 'interested';
        $stageLabels = [
            'interested'           => 'Interested',
            'converted'            => 'Converted',
            'expired'              => 'Expired',
            'no_longer_interested' => 'No Longer Interested',
        ];

        ActivityLog::create([
            'actor_type'   => 'App\\Models\\User',
            'actor_id'     => auth()->id(),
            'subject_type' => 'App\\Models\\Lead',
            'subject_id'   => $lead->id,
            'type'         => 'activity',
            'severity'     => 'info',
            'event'        => 'pipeline_stage_updated',
            'action'       => 'Set pipeline stage to ' . ($stageLabels[$initialStage] ?? ucfirst($initialStage)),
            'properties'   => ['from' => null, 'to' => $initialStage],
            'performed_at' => now(),
        ]);

        return redirect()->route('leads.index');
    }

    public function render()
    {
        return view('livewire.leads.lead-create', [
            'stores' => Store::all(),
        ])->layout('components.layouts.app');
    }
}
