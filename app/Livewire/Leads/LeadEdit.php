<?php

namespace App\Livewire\Leads;

use App\Models\ActivityLog;
use App\Models\Lead;
use Livewire\Component;

class LeadEdit extends Component
{
    public Lead $lead;

    public function mount(Lead $lead): void
    {
        $this->lead = $lead;
    }

    public function setPipelineStage(string $stage): void
    {
        $allowed = ['interested', 'converted', 'expired', 'no_longer_interested'];
        if (!in_array($stage, $allowed, true)) {
            return;
        }

        $old = $this->lead->pipeline_stage;

        if ($old === $stage) {
            return;
        }

        $this->lead->update(['pipeline_stage' => $stage]);

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
            'subject_id'   => $this->lead->id,
            'type'         => 'activity',
            'severity'     => 'info',
            'event'        => 'pipeline_stage_updated',
            'action'       => 'Updated pipeline stage from ' . ($stageLabels[$old] ?? ucfirst($old ?? 'None')) . ' to ' . ($stageLabels[$stage] ?? ucfirst($stage)),
            'properties'   => ['from' => $old, 'to' => $stage],
            'performed_at' => now(),
        ]);

        $this->lead->refresh();
    }

    public function render()
    {
        $this->lead->loadMissing(['store', 'creator', 'lastActionedBy', 'conversation', 'campaign.leadTemplate']);

        // Resolve template field definitions for this lead's campaign
        $campaign       = $this->lead->campaign;
        $process        = $campaign?->leadTemplate?->lead_process ?? $campaign?->lead_process;
        $templateFields = [];
        foreach (($process['steps'] ?? []) as $step) {
            foreach (($step['fields'] ?? []) as $field) {
                if (!empty($field['key'])) {
                    $templateFields[$field['key']] = [
                        'label' => $field['label'] ?? ucfirst(str_replace('_', ' ', $field['key'])),
                        'type'  => $field['type'] ?? 'text',
                        'step'  => $step['title'] ?? null,
                    ];
                }
            }
        }

        $activityLogs = ActivityLog::where('subject_type', 'App\\Models\\Lead')
            ->where('subject_id', $this->lead->id)
            ->orderBy('performed_at')
            ->get();

        return view('livewire.leads.lead-edit', [
            'activityLogs'   => $activityLogs,
            'templateFields' => $templateFields,
        ])->layout('components.layouts.app');
    }
}
