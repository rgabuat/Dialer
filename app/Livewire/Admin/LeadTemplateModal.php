<?php

namespace App\Livewire\Admin;

use App\Models\LeadTemplate;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadTemplateModal extends Component
{
    public bool   $open = false;
    public string $mode = 'create';
    public ?int   $templateId = null;
    public int    $step = 1;

    public string $name        = '';
    public string $description = '';
    public bool   $is_active   = true;

    public string $lead_process_mode  = 'single';
    public array  $lead_process_steps = [];

    #[On('open-template-create')]
    public function openCreate(): void
    {
        $this->reset(['templateId', 'name', 'description', 'lead_process_mode', 'lead_process_steps']);
        $this->resetErrorBag();
        $this->is_active         = true;
        $this->lead_process_mode = 'single';
        $this->mode = 'create';
        $this->step = 1;
        $this->open = true;
    }

    #[On('open-template-edit')]
    public function openEdit(int $id): void
    {
        $t = LeadTemplate::findOrFail($id);

        $this->templateId   = $t->id;
        $this->name         = $t->name;
        $this->description  = $t->description ?? '';
        $this->is_active    = (bool) $t->is_active;

        $normalized = $this->normalizeLeadProcess($t->lead_process);
        $this->lead_process_mode  = $normalized['mode'];
        $this->lead_process_steps = $normalized['steps'];

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
            'name'                                       => 'required|string|max:255',
            'description'                                => 'nullable|string',
            'is_active'                                  => 'boolean',
            'lead_process_mode'                          => 'required|in:single,stepper',
            'lead_process_steps'                         => 'nullable|array',
            'lead_process_steps.*.title'                 => 'nullable|string|max:80',
            'lead_process_steps.*.fields'                => 'nullable|array',
            'lead_process_steps.*.fields.*.key'          => 'nullable|alpha_dash|max:50',
            'lead_process_steps.*.fields.*.label'        => 'nullable|string|max:80',
            'lead_process_steps.*.fields.*.type'         => 'nullable|in:text,textarea,email,phone,number,date,select,checkbox',
            'lead_process_steps.*.fields.*.required'     => 'boolean',
            'lead_process_steps.*.fields.*.placeholder'  => 'nullable|string|max:150',
            'lead_process_steps.*.fields.*.help_text'    => 'nullable|string|max:250',
            'lead_process_steps.*.fields.*.options_text' => 'nullable|string|max:2000',
        ]);

        $leadProcess = $this->buildLeadProcess();
        if ($this->getErrorBag()->isNotEmpty()) return;

        $payload = [
            'name'         => $this->name,
            'description'  => $this->description ?: null,
            'is_active'    => $this->is_active,
            'lead_process' => $leadProcess,
        ];

        if ($this->mode === 'create') {
            LeadTemplate::create($payload);
        } else {
            LeadTemplate::findOrFail($this->templateId)->update($payload);
        }

        $this->open = false;
        session()->flash('success', $this->mode === 'create' ? 'Template created.' : 'Template updated.');
        $this->dispatch('template-saved');
    }

    // ── Lead process builder ──────────────────────────────────────────────────

    public function addLeadStep(): void
    {
        $this->lead_process_steps[] = [
            'title'  => 'Step ' . (count($this->lead_process_steps) + 1),
            'fields' => [$this->newLeadField()],
        ];
    }

    public function removeLeadStep(int $si): void
    {
        unset($this->lead_process_steps[$si]);
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function addLeadFieldOfType(int $si, string $type): void
    {
        if (!isset($this->lead_process_steps[$si])) return;
        $this->lead_process_steps[$si]['fields'][] = $this->newLeadField($type);
    }

    public function insertLeadFieldAt(int $si, int $fi, string $type): void
    {
        if (!isset($this->lead_process_steps[$si])) return;
        $fields = $this->lead_process_steps[$si]['fields'] ?? [];
        $fi     = max(0, min($fi, count($fields)));
        array_splice($fields, $fi, 0, [$this->newLeadField($type)]);
        $this->lead_process_steps[$si]['fields'] = array_values($fields);
    }

    public function removeLeadField(int $si, int $fi): void
    {
        if (!isset($this->lead_process_steps[$si]['fields'][$fi])) return;
        unset($this->lead_process_steps[$si]['fields'][$fi]);
        $this->lead_process_steps[$si]['fields'] = array_values($this->lead_process_steps[$si]['fields']);
    }

    public function moveLeadStepTo(int $from, int $to): void
    {
        if (!isset($this->lead_process_steps[$from])) return;
        $to    = max(0, min($to, count($this->lead_process_steps) - 1));
        $moved = $this->lead_process_steps[$from];
        array_splice($this->lead_process_steps, $from, 1);
        array_splice($this->lead_process_steps, $to, 0, [$moved]);
        $this->lead_process_steps = array_values($this->lead_process_steps);
    }

    public function moveLeadFieldTo(int $si, int $from, int $to): void
    {
        if (!isset($this->lead_process_steps[$si]['fields'][$from])) return;
        $fields = $this->lead_process_steps[$si]['fields'];
        $to     = max(0, min($to, count($fields) - 1));
        if ($from === $to) return;
        $moved  = $fields[$from];
        array_splice($fields, $from, 1);
        array_splice($fields, $to, 0, [$moved]);
        $this->lead_process_steps[$si]['fields'] = array_values($fields);
    }

    public function moveLeadFieldAcrossSteps(int $fromSi, int $fromFi, int $toSi, int $toFi): void
    {
        if (!isset($this->lead_process_steps[$fromSi]['fields'][$fromFi])) return;
        if (!isset($this->lead_process_steps[$toSi]['fields'])) return;
        $fromFields = $this->lead_process_steps[$fromSi]['fields'];
        $toFields   = $this->lead_process_steps[$toSi]['fields'];
        $moved      = $fromFields[$fromFi];
        array_splice($fromFields, $fromFi, 1);
        if (count($fromFields) === 0) $fromFields[] = $this->newLeadField();
        $toFi = max(0, min($toFi, count($toFields)));
        array_splice($toFields, $toFi, 0, [$moved]);
        $this->lead_process_steps[$fromSi]['fields'] = array_values($fromFields);
        $this->lead_process_steps[$toSi]['fields']   = array_values($toFields);
    }

    private function newLeadField(string $type = 'text'): array
    {
        $allowed = ['text', 'textarea', 'email', 'phone', 'number', 'date', 'select', 'checkbox'];
        return [
            'uid'          => (string) Str::uuid(),
            'key'          => '',
            'label'        => '',
            'type'         => in_array($type, $allowed, true) ? $type : 'text',
            'required'     => false,
            'placeholder'  => '',
            'help_text'    => '',
            'options_text' => '',
        ];
    }

    private function buildLeadProcess(): ?array
    {
        $seenKeys = [];
        $rawSteps = $this->lead_process_mode === 'single'
            ? array_slice($this->lead_process_steps, 0, 1)
            : $this->lead_process_steps;

        $steps = [];
        foreach ($rawSteps as $si => $step) {
            $stepFields = [];
            foreach (($step['fields'] ?? []) as $fi => $field) {
                $key   = strtolower(trim((string) ($field['key'] ?? '')));
                $label = trim((string) ($field['label'] ?? ''));
                $type  = (string) ($field['type'] ?? 'text');
                if ($key === '' && $label === '') continue;
                if ($key === '' || $label === '') {
                    $this->addError("lead_process_steps.{$si}.fields.{$fi}.key", 'Both key and label are required.');
                    continue;
                }
                if (isset($seenKeys[$key])) {
                    $this->addError("lead_process_steps.{$si}.fields.{$fi}.key", 'Field keys must be unique.');
                    continue;
                }
                $seenKeys[$key] = true;
                $options = [];
                if ($type === 'select') {
                    $options = collect(explode("\n", (string) ($field['options_text'] ?? '')))
                        ->map(fn ($i) => trim($i))->filter()->unique()->values()->all();
                }
                $stepFields[] = [
                    'uid'         => (string) ($field['uid'] ?? Str::uuid()),
                    'key'         => $key,
                    'label'       => $label,
                    'type'        => $type,
                    'required'    => (bool) ($field['required'] ?? false),
                    'placeholder' => trim((string) ($field['placeholder'] ?? '')) ?: null,
                    'help_text'   => trim((string) ($field['help_text'] ?? '')) ?: null,
                    'options'     => $options,
                ];
            }
            if (count($stepFields) > 0) {
                $title   = trim((string) ($step['title'] ?? ''));
                $steps[] = ['title' => $title ?: ('Step ' . (count($steps) + 1)), 'fields' => $stepFields];
            }
        }

        if ($this->getErrorBag()->isNotEmpty()) return null;
        return count($steps) > 0 ? ['mode' => $this->lead_process_mode, 'steps' => $steps] : null;
    }

    private function normalizeLeadProcess(mixed $process): array
    {
        if (!is_array($process)) return ['mode' => 'single', 'steps' => []];
        $mode  = in_array($process['mode'] ?? null, ['single', 'stepper'], true) ? $process['mode'] : 'single';
        $steps = [];
        foreach (($process['steps'] ?? []) as $rawStep) {
            if (!is_array($rawStep)) continue;
            $fields = [];
            foreach (($rawStep['fields'] ?? []) as $rawField) {
                if (!is_array($rawField)) continue;
                $optText = collect($rawField['options'] ?? [])
                    ->map(fn ($i) => trim((string) $i))->filter()->implode("\n");
                $fields[] = [
                    'uid'          => (string) ($rawField['uid'] ?? Str::uuid()),
                    'key'          => strtolower(trim((string) ($rawField['key'] ?? ''))),
                    'label'        => trim((string) ($rawField['label'] ?? '')),
                    'type'         => (string) ($rawField['type'] ?? 'text'),
                    'required'     => (bool) ($rawField['required'] ?? false),
                    'placeholder'  => (string) ($rawField['placeholder'] ?? ''),
                    'help_text'    => (string) ($rawField['help_text'] ?? ''),
                    'options_text' => $optText,
                ];
            }
            if (count($fields) === 0) $fields[] = $this->newLeadField();
            $steps[] = ['title' => trim((string) ($rawStep['title'] ?? 'Step')) ?: 'Step', 'fields' => $fields];
        }
        return ['mode' => $mode, 'steps' => $steps];
    }

    public function render()
    {
        return view('livewire.admin.lead-template-modal');
    }
}
