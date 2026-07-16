<?php

namespace App\Livewire\Users;

use Livewire\Component;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class UserDelete extends Component
{

    public function delete(User $user)
    {
        $this->authorize('delete', $user);

        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        DB::transaction(function () use ($user) {
            $user->delete(); // 🔹 Triggers UserObserver@deleted
        });

        $this->dispatch(
            'toast',
            message: 'User deleted successfully.',
            type: 'success'
        );

        return redirect()->route('users.index');
    }


    public function render()
    {
        return view('livewire.users.user-delete')->layout('components.layouts.admin', ['heading' => 'Delete User']);
    }
}
