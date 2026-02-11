<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordValidate;

class Password extends Component
{
    public $current_password;
    public $password;
    public $password_confirmation;

    public function changePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                PasswordValidate::min(8)->mixedCase()->numbers(),
            ],
        ]);

        $user = auth()->user();

        $user->update([
            'password' => Hash::make($this->password),
        ]);

        // optional audit (NO password values)
        ActivityLogger::critical(
            type: 'security',
            event: 'password_updated',
            action: 'User updated account password',
            actor: $user,
            subject: $user
        );

        // reset inputs
        $this->reset([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $this->dispatch(
            'toast',
            message: 'Password updated successfully',
            type: 'success'
        );
    }

    public function render()
    {
        return view('livewire.settings.password')->layout('livewire.settings.layout');
    }
}
