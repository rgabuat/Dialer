<?php

namespace App\Livewire\Conversations;

use Livewire\Component;
use App\Models\Conversation;
use App\Models\Disposition;
use App\Models\Lead;

class ConversationShow extends Component
{
  public Conversation $conversation;
  public string $activeTab = "timeline";
  public string $sidebarTab = "details";

  // ACW / disposition panel
  public bool $showDispositionPanel = false;
  public ?int $selectedDispositionId = null;
  public string $dispositionNotes = '';
  public int $acwSecondsLeft = 0;

  public function mount(Conversation $conversation): void
  {
    $this->conversation = $conversation;

    // Show ACW panel if call ended but not yet wrapped
    if ($conversation->ended_at && !$conversation->wrapped_at) {
      $this->showDispositionPanel = true;
      $acw = $conversation->campaign?->acw_seconds ?? 0;
      $this->acwSecondsLeft = $acw;
    }
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

    // Show ACW panel if campaign has dispositions or ACW seconds
    $acw = $this->conversation->campaign?->acw_seconds ?? 0;
    $this->acwSecondsLeft = $acw;
    $this->showDispositionPanel = true;
  }

  public function saveDisposition(): void
  {
    $this->validate([
      'selectedDispositionId' => 'nullable|exists:dispositions,id',
      'dispositionNotes'      => 'nullable|string|max:5000',
    ]);

    $updates = [
      'wrapped_at'         => now(),
      'disposition_notes'  => $this->dispositionNotes ?: null,
    ];

    if ($this->selectedDispositionId) {
      $updates['disposition_id'] = $this->selectedDispositionId;

      $disposition = Disposition::find($this->selectedDispositionId);

      // Mark lead as DNC if disposition requires it
      if ($disposition?->is_dnc && $this->conversation->lead_id) {
        Lead::where('id', $this->conversation->lead_id)->update(['status' => 'DNC']);
      }

      // Schedule callback if disposition requires it
      if ($disposition?->requires_callback && $this->conversation->lead_id) {
        \App\Models\CallbackSchedule::create([
          'campaign_id'     => $this->conversation->campaign_id,
          'lead_id'         => $this->conversation->lead_id,
          'conversation_id' => $this->conversation->id,
          'scheduled_at'    => now()->addHour(),
          'notes'           => $this->dispositionNotes ?: null,
          'status'          => 'pending',
        ]);
      }
    }

    $this->conversation->update($updates);

    // Update lead call stats
    if ($this->conversation->lead_id) {
      Lead::where('id', $this->conversation->lead_id)->increment('call_count');
      Lead::where('id', $this->conversation->lead_id)->update(['last_called_at' => now()]);
    }

    $this->conversation->refresh();
    $this->showDispositionPanel = false;

    $this->dispatch('disposition-saved');
  }

  public function skipDisposition(): void
  {
    $this->conversation->update(['wrapped_at' => now()]);
    $this->conversation->refresh();
    $this->showDispositionPanel = false;
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

    $dispositions = Disposition::forCampaign($this->conversation->campaign_id);

    return view("livewire.conversations.conversation-show", [
      "notes"        => $notes,
      "dispositions" => $dispositions,
    ])->layout("components.layouts.app");
  }
}
