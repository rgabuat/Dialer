<div class="max-w-5xl">

    <form wire:submit.prevent="save">

        <h1 class="text-2xl font-semibold text-white mb-1">
            Preferences
        </h1>

        <p class="text-sm text-neutral-400 mb-10">
            Set up CSRScape to work just how you like it with preferences that affect your user account only.
        </p>

        {{-- UI OPTIONS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
            <div>
                <h3 class="text-lg font-semibold text-white mb-1">UI Options</h3>
                <p class="text-sm text-neutral-400">
                    Set your preferences for how the CSRScape user interface looks.
                </p>
            </div>

            <div class="space-y-6">
                @foreach ([
                    ['dark_mode', 'Dark Mode', 'Use CSRScape in a dark color scheme.'],
                    ['mini_sidebar', 'Mini Sidebar', 'Use a smaller version of the sidebar.'],
                    ['support_widget', 'Support Widget', 'Show the CSRScape support widget.'],
                ] as [$model, $title, $desc])
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $title }}</p>
                            <p class="text-xs text-neutral-400">{{ $desc }}</p>
                        </div>
                        <input type="checkbox" wire:model.defer="{{ $model }}"
                               class="w-10 h-5 rounded-full bg-neutral-700 checked:bg-indigo-600">
                    </div>
                @endforeach
            </div>
        </div>

        <hr class="border-neutral-800 mb-12">

        {{-- NOTIFICATIONS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
            <div>
                <h3 class="text-lg font-semibold text-white mb-1">Notifications</h3>
                <p class="text-sm text-neutral-400">
                    Configure your notifications for new events.
                </p>
            </div>

            <div class="space-y-6">
                @foreach ([
                    ['flash_notifications', 'Flash Notifications', 'Briefly display a pop-up message when you receive a new notification.'],
                    ['audio_notifications', 'Audio Notifications', 'Play a sound when flash notifications are displayed.'],
                    ['desktop_notifications', 'Desktop Notifications', 'Show a desktop notification if the window is not in focus.'],
                    ['offline_notifications', 'Offline Notifications', 'Receive email notifications when offline.'],
                ] as [$model, $title, $desc])
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-white">{{ $title }}</p>
                            <p class="text-xs text-neutral-400">{{ $desc }}</p>
                        </div>
                        <input type="checkbox" wire:model.defer="{{ $model }}"
                               class="w-10 h-5 rounded-full bg-neutral-700 checked:bg-indigo-600">
                    </div>
                @endforeach
            </div>
        </div>

        <hr class="border-neutral-800 mb-12">

        {{-- LOCALIZATION --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
            <div>
                <h3 class="text-lg font-semibold text-white mb-1">Localization</h3>
                <p class="text-sm text-neutral-400">
                    Choose your time and date preferences.
                </p>
            </div>

            <div class="space-y-5">
                <select wire:model.defer="timezone"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                            px-3 py-2 text-white">

                    @foreach ($timezones as $tz)
                        <option value="{{ $tz->identifier }}">
                            {{ $tz->label }}
                        </option>
                    @endforeach
                </select>

                <select wire:model.defer="time_format"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white">
                    <option value="standard">Standard</option>
                    <option value="24h">24 Hour</option>
                </select>

                <select wire:model.defer="week_start"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white">
                    <option value="monday">Monday</option>
                    <option value="sunday">Sunday</option>
                </select>
            </div>
        </div>

        <div class="flex justify-end">
            <button
                type="submit"
                class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500
                       text-white font-medium transition">
                Update
            </button>
        </div>

    </form>
</div>
