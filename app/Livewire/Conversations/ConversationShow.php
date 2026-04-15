<?php

namespace App\Livewire\Conversations;

use Livewire\Component;
use App\Models\ActivityLog;
use App\Models\Conversation;
use App\Models\Lead;
use App\Models\Store;
use App\Models\StoreUnit;

class ConversationShow extends Component
{
  public Conversation $conversation;
  public string $activeTab = "timeline";
  public string $sidebarTab = "details";
  public string $accountView = "overview";
  public ?int $selectedLeadId = null;

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
  }

  public function selectLead(int $id): void
  {
    $this->selectedLeadId = $id;
    $this->accountView = 'lead_detail';
  }

  public function createLead(array $data): void
  {
    $lead = Lead::create([
      'first_name'           => $data['first_name'] ?? '',
      'last_name'            => $data['last_name'] ?? '',
      'email'                => $data['email'] ?: null,
      'phone'                => $this->conversation->contact_phone,
      'conversation_id'      => $this->conversation->id,
      'store_id'             => $data['store_id'] ?: null,
      'lead_type'            => $data['lead_type'] ?? null,
      'move_in_date'         => $data['move_in_date'] ?: null,
      'reason_for_storage'   => $data['reason_for_storage'] ?: null,
      'types_of_items'       => $data['types_of_items'] ?: null,
      'duration'             => $data['duration'] ?: null,
      'property_protection'  => $data['property_protection'] ?: null,
      'promo'                => ($data['promo'] !== '-') ? ($data['promo'] ?: null) : null,
      'admin_fee_credit'     => (bool) ($data['admin_fee_credit'] ?? false),
      'unit_size'            => $data['unit_size'] ?: null,
      'notify_sms'           => (bool) ($data['notify_sms'] ?? false),
      'notify_email'         => (bool) ($data['notify_email'] ?? false),
      'notify_email_address' => $data['notify_email_address'] ?: null,
      'selected_units'       => $data['selected_units'] ?? [],
      'status'               => 'NEW',
      'created_by'           => auth()->id(),
    ]);

    $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
    if ($fullName !== '') {
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

    $stores = Store::with('units')->orderByDesc('featured')->orderBy('name')->get()
      ->map(fn(Store $s) => [
        'id'        => $s->id,
        'featured'  => (bool) $s->featured,
        'name'      => $s->name,
        'type'      => $s->type,
        'occupancy' => (float) $s->occupancy,
        'address'   => $s->address,
        'location'  => implode(', ', array_filter([$s->city, $s->state, $s->zip, $s->country])),
        'distance'  => null,
        'pricing'   => $s->pricing,
        'units'     => $s->units->map(fn(StoreUnit $u) => [
          'id'          => $u->id,
          'size'        => $u->size,
          'category'    => $u->category,
          'available'   => $u->available,
          'street_rate' => $u->street_rate,
          'push_rate'   => $u->push_rate,
          'features'    => $u->features ?? [],
          'promos'      => $u->promos ?? [],
        ])->values()->all(),
      ])
      ->values()
      ->all();

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
      "stores"       => $stores,
      "leads"        => $leads,
      "accountStore" => $accountStore,
      "selectedLead" => $selectedLead,
    ])->layout("components.layouts.app");
  }
}
