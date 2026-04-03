<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class Phone extends Component
{
    // Call forwarding
    public bool $call_forwarding_enabled = false;
    public string $call_forward_to = '';

    // Voicemail (placeholder — future feature)
    public bool $voicemail_enabled = false;
    public string $voicemail_greeting = 'default';

    // Do Not Disturb
    public bool $do_not_disturb = false;

    public function mount(): void
    {
        $user = Auth::user();

        $this->call_forwarding_enabled = (bool) $user->getMeta('call_forwarding_enabled', false);
        $this->call_forward_to         = (string) $user->getMeta('call_forward_to', '');
        $this->voicemail_enabled       = (bool) $user->getMeta('voicemail_enabled', false);
        $this->voicemail_greeting      = (string) $user->getMeta('voicemail_greeting', 'default');
        $this->do_not_disturb          = (bool) $user->getMeta('do_not_disturb', false);
    }

    public function save(): void
    {
        $this->validate([
            'call_forward_to' => [
                'nullable',
                'string',
                'max:30',
                function ($attr, $value, $fail) {
                    if ($this->call_forwarding_enabled && empty(trim($value))) {
                        $fail('A forwarding number is required when call forwarding is enabled.');
                    }
                    if (!empty($value) && !preg_match('/^[\d\+\-\(\) ]+$/', $value)) {
                        $fail('Please enter a valid phone number (digits, +, -, spaces, and parentheses only).');
                    }
                },
            ],
        ]);

        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        $user = Auth::user();

        // Only persist the implemented settings (voicemail is coming-soon)
        $keys = [
            'call_forwarding_enabled',
            'call_forward_to',
            'do_not_disturb',
        ];

        $metaBefore = $user->meta()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        $metaAfter = [
            'call_forwarding_enabled' => $this->call_forwarding_enabled,
            'call_forward_to'         => $this->call_forward_to,
            'do_not_disturb'          => $this->do_not_disturb,
        ];

        foreach ($metaAfter as $key => $value) {
            $user->setMeta($key, $value);
        }

        $metaChanges = [];
        foreach ($metaAfter as $key => $after) {
            $before = $metaBefore[$key] ?? null;
            if ((string) $before !== (string) $after) {
                $metaChanges[$key] = ['before' => $before, 'after' => $after];
            }
        }

        if (!empty($metaChanges)) {
            ActivityLogger::log(
                type:       'audit',
                event:      'settings.phone.updated',
                action:     'Updated phone settings',
                actor:      $user,
                properties: $metaChanges,
            );
        }

        $this->dispatch('toast', message: 'Phone settings saved.', type: 'success');
    }

    public function render()
    {
        return view('livewire.settings.phone')
            ->layout('livewire.app.layout', ['title' => 'Phone Settings']);
    }
}
