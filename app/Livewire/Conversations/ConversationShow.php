<?php

namespace App\Livewire\Conversations;

use Livewire\Component;
use App\Models\ActivityLog;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Store;
use App\Models\StoreUnit;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Throwable;

class ConversationShow extends Component
{
  public Conversation $conversation;
  public string $activeTab = "timeline";
  public string $sidebarTab = "details";
  public string $accountView = "overview";
  public ?int $selectedLeadId = null;
  public array $leadCreateErrors = [];

  public function mount(Conversation $conversation): void
  {
    $this->conversation = $conversation;
  }

  public function addNote(string $rawNote): void
  {
    $content = trim($rawNote);

    if (!$content || strlen($content) > 50000) {
      return;
    }

    $this->conversation->notes()->create([
      "user_id" => auth()->id(),
      "content" => $content,
      "type" => "note",
    ]);

    $this->dispatch('note-added');
  }

  public function selectLead(int $id): void
  {
    $this->selectedLeadId = $id;
    $this->accountView = 'lead_detail';
  }

  public function createLead(array $data): void
  {
    $this->leadCreateErrors = [];

    $process = $this->resolveLeadProcess($this->conversation->campaign?->lead_process);
    $fieldMap = [];
    foreach (($process['steps'] ?? []) as $step) {
      foreach (($step['fields'] ?? []) as $field) {
        $key = $field['key'] ?? '';
        if ($key === '') {
          continue;
        }
        $fieldMap[$key] = $field;
      }
    }

    if (count($fieldMap) === 0) {
      $this->leadCreateErrors = ['No lead process configured for this campaign. Go to Campaign Edit → Lead Process tab to add fields.'];
      return;
    }

    $rules = [];
    foreach ($fieldMap as $key => $field) {
      $rules[$key] = $this->fieldRules($field);
    }

    $validator = Validator::make($data, $rules);
    if ($validator->fails()) {
      $this->leadCreateErrors = $validator->errors()->all();
      return;
    }
    $validated = $validator->validated();

    $fillable = collect((new Lead())->getFillable())
      ->reject(fn (string $column) => in_array($column, ['created_by', 'last_actioned_by', 'dynamic_data'], true))
      ->values()
      ->all();

    $booleanColumns = ['admin_fee_credit', 'notify_sms', 'notify_email'];
    $arrayColumns = ['selected_units'];

    $leadPayload = [
      'conversation_id' => $this->conversation->id,
      'created_by' => auth()->id(),
      'last_actioned_by' => auth()->id(),
      'status' => 'NEW',
      'dynamic_data' => $this->normalizedDynamicData($validated),
    ];

    foreach ($validated as $key => $value) {
      if (!in_array($key, $fillable, true)) {
        continue;
      }

      if (in_array($key, $booleanColumns, true)) {
        $leadPayload[$key] = (bool) $value;
        continue;
      }

      if (in_array($key, $arrayColumns, true)) {
        $leadPayload[$key] = is_array($value) ? $value : [];
        continue;
      }

      if (is_string($value)) {
        $value = trim($value);
      }

      $leadPayload[$key] = $value === '' ? null : $value;
    }

    $resolvedNames = $this->resolveLeadNames($validated);
    $leadPayload['first_name'] = $resolvedNames['first_name'];
    $leadPayload['last_name'] = $resolvedNames['last_name'];

    if (empty($leadPayload['phone']) && $this->conversation->contact_phone) {
      $leadPayload['phone'] = $this->conversation->contact_phone;
    }

    try {
      $lead = Lead::create($leadPayload);
    } catch (Throwable $e) {
      Log::error('Conversation lead create failed.', [
        'conversation_id' => $this->conversation->id,
        'campaign_id' => $this->conversation->campaign_id,
        'validated_keys' => array_keys($validated),
        'payload_keys' => array_keys($leadPayload),
        'error' => $e->getMessage(),
      ]);

      $this->leadCreateErrors = ['Unable to create lead right now. Please verify lead process fields (first and last name) and try again.'];
      return;
    }

    $fullName = trim(((string) ($leadPayload['first_name'] ?? '')) . ' ' . ((string) ($leadPayload['last_name'] ?? '')));
    if ($fullName !== '' && strcasecmp($fullName, 'Unknown Lead') !== 0) {
      $this->conversation->update(['contact_name' => $fullName]);
      $this->conversation->refresh();
    }

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

    ActivityLog::create([
      'actor_type'   => 'App\\Models\\User',
      'actor_id'     => auth()->id(),
      'subject_type' => 'App\\Models\\Lead',
      'subject_id'   => $lead->id,
      'type'         => 'activity',
      'severity'     => 'info',
      'event'        => 'conversation_linked',
      'action'       => 'Linked to conversation #' . $this->conversation->id,
      'properties'   => ['conversation_id' => $this->conversation->id],
      'performed_at' => now(),
    ]);

    $this->dispatch('lead-created', id: $lead->id);
  }

  public function closeConversation(): void
  {
    if ($this->conversation->status === "completed") {
      return;
    }

    $this->conversation->update([
      "status" => "completed",
      "completed_by" => auth()->id(),
      "ended_at" => now(),
    ]);

    $this->conversation->notes()->create([
      "user_id" => auth()->id(),
      "content" => "Conversation marked as completed.",
      "type" => "event",
    ]);

    $this->conversation->refresh();
  }

  public function render()
  {
    $this->conversation->loadMissing([
      "campaign",
      "assignedAgent",
      "completedByAgent",
      "disposition",
      "lead",
    ]);

    $notes = $this->conversation->notes()->with("author")->get();

    $leadCreateStores = Store::orderBy('name')->get(['id', 'name']);
    $leadCreateProcess = $this->resolveLeadProcess($this->conversation->campaign?->lead_process);

    $leads = Lead::where('conversation_id', $this->conversation->id)
      ->with('creator', 'store')
      ->orderByDesc('created_at')
      ->get();

    $accountStore = $leads->first()?->store;

    $selectedLead = $this->selectedLeadId
      ? Lead::with('store', 'creator')->find($this->selectedLeadId)
      : null;

    return view("livewire.conversations.conversation-show", [
      "notes"        => $notes,
      "stores"       => $leadCreateStores->toArray(),
      "leadCreateStores" => $leadCreateStores,
      "leadCreateProcess" => $leadCreateProcess,
      "leads"        => $leads,
      "accountStore" => $accountStore,
      "selectedLead" => $selectedLead,
    ])->layout("components.layouts.app");
  }

  private function resolveLeadProcess(mixed $process): array
  {
    $mode = 'single';
    if (is_array($process) && in_array(($process['mode'] ?? null), ['single', 'stepper'], true)) {
      $mode = $process['mode'];
    }

    $steps = [];
    if (is_array($process)) {
      foreach (($process['steps'] ?? []) as $rawStep) {
        if (!is_array($rawStep)) {
          continue;
        }

        $fields = [];
        foreach (($rawStep['fields'] ?? []) as $rawField) {
          if (!is_array($rawField)) {
            continue;
          }

          $key = strtolower(trim((string) ($rawField['key'] ?? '')));
          if ($key === '') {
            continue;
          }

          $fields[] = [
            'key' => $key,
            'label' => trim((string) ($rawField['label'] ?? $key)),
            'type' => (string) ($rawField['type'] ?? 'text'),
            'required' => (bool) ($rawField['required'] ?? false),
            'placeholder' => (string) ($rawField['placeholder'] ?? ''),
            'help_text' => (string) ($rawField['help_text'] ?? ''),
            'options' => collect($rawField['options'] ?? [])->map(fn ($v) => trim((string) $v))->filter()->values()->all(),
          ];
        }

        if (count($fields) === 0) {
          continue;
        }

        $steps[] = [
          'title' => trim((string) ($rawStep['title'] ?? 'Step')) ?: 'Step',
          'fields' => $fields,
        ];
      }
    }

    if ($mode === 'single' && count($steps) > 1) {
      $steps = [array_values($steps)[0]];
    }

    return [
      'mode' => $mode,
      'steps' => array_values($steps),
    ];
  }

  private function fieldRules(array $field): array
  {
    $rules = [];
    $type = $field['type'] ?? 'text';
    $required = (bool) ($field['required'] ?? false);

    $rules[] = $required ? 'required' : 'nullable';

    if (($field['key'] ?? '') === 'store_id') {
      $rules[] = 'integer';
      $rules[] = 'exists:stores,id';
      return $rules;
    }

    if ($type === 'email') {
      $rules[] = 'email';
      $rules[] = 'max:255';
    } elseif ($type === 'number') {
      $rules[] = 'numeric';
    } elseif ($type === 'date') {
      $rules[] = 'date';
    } elseif ($type === 'checkbox') {
      $rules[] = 'boolean';
    } elseif ($type === 'select') {
      $options = collect($field['options'] ?? [])->filter()->values()->all();
      if (count($options) > 0) {
        $rules[] = 'in:' . implode(',', array_map(fn ($v) => (string) $v, $options));
      }
    } else {
      $rules[] = 'string';
      $rules[] = 'max:1000';
    }

    return $rules;
  }

  private function normalizedDynamicData(array $data): array
  {
    $output = [];
    foreach ($data as $key => $value) {
      if (is_string($value)) {
        $value = trim($value);
      }

      $output[$key] = $value === '' ? null : $value;
    }

    return $output;
  }

  private function resolveLeadNames(array $validated): array
  {
    $firstName = $this->firstNonEmpty($validated, ['first_name', 'firstname', 'first', 'fname', 'given_name']);
    $lastName = $this->firstNonEmpty($validated, ['last_name', 'lastname', 'last', 'lname', 'family_name', 'surname']);

    $contactName = trim((string) ($this->conversation->contact_name ?? ''));
    if ($contactName !== '') {
      $parts = preg_split('/\s+/', $contactName) ?: [];
      if ($firstName === null && count($parts) > 0) {
        $firstName = trim((string) ($parts[0] ?? '')) ?: null;
      }
      if ($lastName === null && count($parts) > 1) {
        $lastName = trim((string) implode(' ', array_slice($parts, 1))) ?: null;
      }
    }

    return [
      'first_name' => $firstName ?? 'Unknown',
      'last_name' => $lastName ?? 'Lead',
    ];
  }

  private function firstNonEmpty(array $data, array $keys): ?string
  {
    foreach ($keys as $key) {
      if (!array_key_exists($key, $data)) {
        continue;
      }

      $value = trim((string) $data[$key]);
      if ($value !== '') {
        return $value;
      }
    }

    return null;
  }
}
