<?php

namespace App\Livewire\Users;

use App\Models\User;
use App\Models\Conversation;
use Livewire\Component;
use Livewire\WithPagination;

class UserShow extends Component
{
    use WithPagination;

    public User $user;

    public function mount(User $user): void
    {
        $this->user = $user->load(['agentStatus.statusType', 'roles', 'userGroup']);
    }

    public function render()
    {
        $conversations = Conversation::where('assigned_to', $this->user->id)
            ->latest('started_at')
            ->paginate(15);

        return view('livewire.users.user-show', [
            'conversations' => $conversations,
            'jobTitle'      => $this->user->getMeta('job_title'),
            'mobile'        => $this->user->getMeta('mobile'),
        ])->layout('components.layouts.admin', ['heading' => $this->user->first_name . ' ' . $this->user->last_name]);
    }
}
