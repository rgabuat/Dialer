<?php

namespace App\Livewire\Conversations;

use Livewire\Component;
use App\Models\Conversation;

class ConversationShow extends Component
{
  public Conversation $conversation;
  public string $activeTab = "timeline";
  public string $sidebarTab = "details";

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

  public function closeConversation(): void
  {
    if ($this->conversation->status === "completed") {
      return;
    }

    $this->conversation->update([
      "status" => "completed",
      "completed_by" => auth()->id(),
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
    ]);

    $notes = $this->conversation->notes()->with("author")->get();

    return view("livewire.conversations.conversation-show", [
      "notes" => $notes,
    ])->layout("components.layouts.app");
  }
}
