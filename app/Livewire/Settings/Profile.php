<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Profile extends Component
{
    public $first_name;
    public $last_name;
    public $nickname;
    public $email;
    public $job_title;
    public $mobile;

    public $saved = false;

    public function mount()
    {
        $user = Auth::user();

        $this->first_name = $user->first_name;
        $this->last_name  = $user->last_name;
        $this->nickname   = $user->nickname;
        $this->email      = $user->email;

        $this->job_title = $user->getMeta('job_title');
        $this->mobile    = $user->getMeta('mobile');
    }

    public function save()
    {
        // 🔹 One batch for entire profile update
        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'nickname'   => ['nullable', 'string', 'max:255'],
            'email'      => [
                'required',
                'email',
                'unique:users,email,' . Auth::id(),
            ],
        ]);

        DB::transaction(function () {
            $user = Auth::user();

            // 🔹 Update user (UserObserver will log)
            $user->update([
                'first_name' => $this->first_name,
                'last_name'  => $this->last_name,
                'nickname'   => $this->nickname,
                'email'      => $this->email,
            ]);

            // 🔹 Update meta (UsersMetaObserver will log)
            $user->setMeta('job_title', $this->job_title);
            $user->setMeta('mobile', $this->mobile);
        });

        $this->saved = true;

        $this->dispatch(
            'toast',
            message: 'Profile updated successfully',
            type: 'success'
        );
    }

    public function render()
    {
        return view('livewire.settings.profile')
            ->layout('livewire.settings.layout');
    }
}
