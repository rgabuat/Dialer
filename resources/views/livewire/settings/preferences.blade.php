<div class="space-y-4">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Preferences</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Customise how the application looks and behaves for your account.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-4">

        {{-- UI Options --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">UI Options</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Customise how the interface looks for you.</p>
            </div>
            <div class="divide-y divide-zinc-800">
                @foreach ([['dark_mode', 'Dark Mode', 'Use the application in a dark colour scheme.'], ['mini_sidebar', 'Mini Sidebar', 'Use a compact version of the sidebar.'], ['support_widget', 'Support Widget', 'Show the support widget in the corner.']] as [$model, $title, $desc])
                    <div class="flex justify-between items-center px-6 py-4">
                        <div>
                            <p class="text-fg-2 text-sm">{{ $title }}</p>
                            <p class="mt-0.5 text-zinc-500 text-xs">{{ $desc }}</p>
                        </div>
                        <label class="inline-flex relative items-center ml-6 cursor-pointer shrink-0">
                            <input type="checkbox" wire:model.defer="{{ $model }}" class="sr-only peer">
                            <div
                                class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-11 after:w-5 h-6 after:h-5 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-5 duration-200 after:duration-200">
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Notifications --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Notifications</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Configure how and when you receive notifications.</p>
            </div>
            <div class="divide-y divide-zinc-800">
                @foreach ([['flash_notifications', 'Flash Notifications', 'Show a brief pop-up when you receive a notification.'], ['audio_notifications', 'Audio Notifications', 'Play a sound when flash notifications are displayed.'], ['desktop_notifications', 'Desktop Notifications', 'Show desktop notifications if the window is not in focus.'], ['offline_notifications', 'Offline Notifications', 'Receive email notifications when you are offline.']] as [$model, $title, $desc])
                    <div class="flex justify-between items-center px-6 py-4">
                        <div>
                            <p class="text-fg-2 text-sm">{{ $title }}</p>
                            <p class="mt-0.5 text-zinc-500 text-xs">{{ $desc }}</p>
                        </div>
                        <label class="inline-flex relative items-center ml-6 cursor-pointer shrink-0">
                            <input type="checkbox" wire:model.defer="{{ $model }}" class="sr-only peer">
                            <div
                                class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 after:shadow rounded-full after:rounded-full w-11 after:w-5 h-6 after:h-5 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-5 duration-200 after:duration-200">
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Localization --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Localization</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Choose your time and date preferences.</p>
            </div>
            <div class="space-y-4 px-6 py-5">

                <div>
                    <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Timezone</label>
                    <div class="relative">
                        <select wire:model.defer="timezone"
                            class="bg-surface-2/70 px-3 py-2.5 pr-9 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition appearance-none">
                            @foreach ($timezones as $tz)
                                <option value="{{ $tz->identifier }}">{{ $tz->label }}</option>
                            @endforeach
                        </select>
                        <div
                            class="right-0 absolute inset-y-0 flex items-center px-2.5 text-zinc-500 pointer-events-none">
                            <x-heroicon-o-chevron-down class="w-4 h-4" />
                        </div>
                    </div>
                </div>

                <div class="gap-4 grid grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Time Format</label>
                        <div class="relative">
                            <select wire:model.defer="time_format"
                                class="bg-surface-2/70 px-3 py-2.5 pr-9 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition appearance-none">
                                <option value="standard">Standard (12h)</option>
                                <option value="24h">24 Hour</option>
                            </select>
                            <div
                                class="right-0 absolute inset-y-0 flex items-center px-2.5 text-zinc-500 pointer-events-none">
                                <x-heroicon-o-chevron-down class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Week Starts On</label>
                        <div class="relative">
                            <select wire:model.defer="week_start"
                                class="bg-surface-2/70 px-3 py-2.5 pr-9 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition appearance-none">
                                <option value="monday">Monday</option>
                                <option value="sunday">Sunday</option>
                            </select>
                            <div
                                class="right-0 absolute inset-y-0 flex items-center px-2.5 text-zinc-500 pointer-events-none">
                                <x-heroicon-o-chevron-down class="w-4 h-4" />
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- Save bar --}}
        <div class="flex justify-end items-center py-1">
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2 rounded-lg font-medium text-white text-sm transition">
                Save preferences
            </button>
        </div>

    </form>

</div>
