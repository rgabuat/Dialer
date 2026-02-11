<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\Timezone;
use Illuminate\Support\Str;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;

class Preferences extends Component
{
    // UI Options
    public $dark_mode = false;
    public $mini_sidebar = false;
    public $support_widget = false;

    // Notifications
    public $flash_notifications = false;
    public $audio_notifications = false;
    public $desktop_notifications = false;
    public $offline_notifications = false;

    // Localization
    public $timezone = 'Asia/Manila';
    public $time_format = 'standard';
    public $week_start = 'monday';

    public $timezones = [];
    

    public function mount()
    {
        $user = Auth::user();

        $this->dark_mode = (bool) $user->getMeta('dark_mode', false);
        $this->mini_sidebar = (bool) $user->getMeta('mini_sidebar', false);
        $this->support_widget = (bool) $user->getMeta('support_widget', false);

        $this->flash_notifications = (bool) $user->getMeta('flash_notifications', true);
        $this->audio_notifications = (bool) $user->getMeta('audio_notifications', true);
        $this->desktop_notifications = (bool) $user->getMeta('desktop_notifications', true);
        $this->offline_notifications = (bool) $user->getMeta('offline_notifications', false);

        $this->timezone = $user->getMeta('timezone', 'Asia/Manila');
        $this->time_format = $user->getMeta('time_format', 'standard');
        $this->week_start = $user->getMeta('week_start', 'monday');

        $this->timezones = Timezone::where('active', true)
                ->orderBy('utc_offset')
                ->get();
    }

    public function save()
    {

        request()->attributes->set(
            'activity_batch',
            request()->attributes->get('activity_batch') ?? (string) Str::uuid()
        );

        $user = Auth::user();

        /**
         * Preference keys we manage here
         */
        $keys = [
            'dark_mode',
            'mini_sidebar',
            'support_widget',
            'flash_notifications',
            'audio_notifications',
            'desktop_notifications',
            'offline_notifications',
            'timezone',
            'time_format',
            'week_start',
        ];

        /**
         * META BEFORE
         */
        $metaBefore = $user->meta()
            ->whereIn('key', $keys)
            ->pluck('value', 'key')
            ->toArray();

        /**
         * NEW VALUES (from UI)
         */
        $metaAfter = [
            'dark_mode'             => $this->dark_mode,
            'mini_sidebar'          => $this->mini_sidebar,
            'support_widget'        => $this->support_widget,
            'flash_notifications'   => $this->flash_notifications,
            'audio_notifications'   => $this->audio_notifications,
            'desktop_notifications' => $this->desktop_notifications,
            'offline_notifications' => $this->offline_notifications,
            'timezone'              => $this->timezone,
            'time_format'           => $this->time_format,
            'week_start'            => $this->week_start,
        ];

        /**
         * APPLY CHANGES
         */
        foreach ($metaAfter as $key => $value) {
            $user->setMeta($key, $value);
        }

        /**
         * COMPUTE DIFF (ONLY WHAT CHANGED)
         */
        $metaChanges = [];

        foreach ($metaAfter as $key => $after) {
            $before = $metaBefore[$key] ?? null;

            if ((string) $before !== (string) $after) {
                $metaChanges[$key] = [
                    'before' => $before,
                    'after'  => $after,
                ];
            }
        }

        /**
         * SINGLE AUDIT ENTRY
         */
        if (! empty($metaChanges)) {
            ActivityLogger::info(
                type: 'activity',
                event: 'user_preferences_updated',
                action: 'User updated preferences',
                actor: $user,
                subject: $user,
                properties: [
                    'meta' => $metaChanges,
                ]
            );
        }


        $this->dispatch(
            'toast',
            message: 'Preferences updated successfully',
            type: 'success'
        );
    }

    public function render()
    {
        return view('livewire.settings.preferences')->layout('livewire.settings.layout');
    }
}
