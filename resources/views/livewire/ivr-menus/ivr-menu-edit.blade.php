<div class="bg-surface-4 p-6 min-h-screen text-fg">

    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="font-semibold text-2xl tracking-tight">{{ $ivrMenu->name }}</h1>
            <p class="text-fg-muted text-sm">IVR Menu</p>
        </div>
        <div class="flex items-center gap-2">
            @if (!$confirmingDelete)
                <button wire:click="$set('confirmingDelete', true)"
                    class="bg-surface-2 hover:bg-surface px-3 py-1.5 rounded-md text-sm transition text-accent-red">
                    Delete
                </button>
            @else
                <span class="mr-2 text-fg-muted text-sm">Are you sure?</span>
                <button wire:click="delete"
                    class="bg-red-600 hover:bg-red-500 px-3 py-1.5 rounded-md text-white text-sm transition">
                    Yes, Delete
                </button>
                <button wire:click="$set('confirmingDelete', false)"
                    class="bg-surface-2 hover:bg-surface px-3 py-1.5 rounded-md text-fg-muted text-sm transition">
                    Cancel
                </button>
            @endif
        </div>
    </div>

    <div class="flex gap-1 mb-6 border-surface border-b">
        <button wire:click="$set('activeTab', 'settings')"
            class="px-4 py-2 text-sm font-medium transition rounded-t-md
                {{ $activeTab === 'settings' ? 'bg-surface text-fg border-b-2 border-blue-500' : 'text-fg-muted hover:text-fg' }}">
            Settings
        </button>
        <button wire:click="$set('activeTab', 'options')"
            class="px-4 py-2 text-sm font-medium transition rounded-t-md
                {{ $activeTab === 'options' ? 'bg-surface text-fg border-b-2 border-blue-500' : 'text-fg-muted hover:text-fg' }}">
            Digit Options
            <span class="bg-surface-2 ml-1 px-1.5 py-0.5 rounded-full text-xs">{{ count($options) }}</span>
        </button>
    </div>

    @if ($activeTab === 'settings')
        <form wire:submit.prevent="save">
            <div class="space-y-10 max-w-4xl">

                <div class="gap-6 grid grid-cols-1 md:grid-cols-4 pb-10 border-surface border-b">
                    <div>
                        <h2 class="font-medium">Details</h2>
                        <p class="text-fg-muted text-sm">Name and status of this IVR menu.</p>
                    </div>
                    <div class="space-y-5 md:col-span-3">
                        <div>
                            <label class="text-fg-muted text-sm">Name</label>
                            <input wire:model.defer="name" type="text"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            @error('name')
                                <p class="mt-1 text-xs text-accent-red">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-fg-muted text-sm">Description</label>
                            <textarea wire:model.defer="description" rows="2"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm"></textarea>
                        </div>
                        <div class="flex items-center gap-3">
                            <input wire:model.defer="is_active" type="checkbox" id="is_active"
                                class="bg-surface border-surface-2 rounded focus:ring-blue-500 text-blue-500" />
                            <label for="is_active" class="text-fg-muted text-sm">Active</label>
                        </div>
                    </div>
                </div>

                <div class="gap-6 grid grid-cols-1 md:grid-cols-4 pb-10 border-surface border-b">
                    <div>
                        <h2 class="font-medium">Greeting</h2>
                        <p class="text-fg-muted text-sm">What callers hear when they arrive at this menu.</p>
                    </div>
                    <div class="space-y-5 md:col-span-3">
                        <div class="flex gap-6">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model.live="greeting_type" type="radio" value="tts"
                                    class="focus:ring-blue-500 text-blue-500" />
                                <span class="text-fg text-sm">Text-to-Speech</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input wire:model.live="greeting_type" type="radio" value="audio"
                                    class="focus:ring-blue-500 text-blue-500" />
                                <span class="text-fg text-sm">Audio File URL</span>
                            </label>
                        </div>
                        <div>
                            <label class="text-fg-muted text-sm">
                                {{ $greeting_type === 'audio' ? 'Audio File URL' : 'TTS Message' }}
                            </label>
                            @if ($greeting_type === 'audio')
                                <input wire:model.defer="greeting_value" type="url" placeholder="https://..."
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            @else
                                <textarea wire:model.defer="greeting_value" rows="3"
                                    placeholder="Thank you for calling. Press 1 for Sales. Press 2 for Support."
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm"></textarea>
                            @endif
                        </div>
                        <div class="gap-4 grid grid-cols-2">
                            <div>
                                <label class="text-fg-muted text-sm">Digit Input Timeout (seconds)</label>
                                <input wire:model.defer="gather_timeout" type="number" min="1" max="30"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            </div>
                            <div>
                                <label class="text-fg-muted text-sm">Max Invalid Attempts</label>
                                <input wire:model.defer="invalid_attempts_max" type="number" min="1"
                                    max="10"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            </div>
                        </div>
                        <div class="gap-4 grid grid-cols-2">
                            <div>
                                <label class="text-fg-muted text-sm">Invalid Input Action</label>
                                <select wire:model.defer="invalid_action"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                    <option value="repeat">Repeat Menu</option>
                                    <option value="hangup">Hang Up</option>
                                    <option value="transfer">Transfer to Number</option>
                                </select>
                            </div>
                            <div>
                                <label class="text-fg-muted text-sm">Invalid Destination (if transfer)</label>
                                <input wire:model.defer="invalid_destination" type="text" placeholder="+15551234567"
                                    class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                        Save Changes
                    </button>
                    <a href="{{ route('ivr-menus.index') }}" wire:navigate
                        class="bg-surface-2 hover:bg-surface px-4 py-2 rounded-md font-medium text-fg-muted text-sm transition">
                        Cancel
                    </a>
                </div>

            </div>
        </form>
    @endif

    @if ($activeTab === 'options')
        <div class="space-y-6 max-w-4xl">

            {{-- Add new option --}}
            <div class="space-y-4 bg-surface p-5 border border-surface rounded-lg">
                <h3 class="font-medium text-sm">Add Digit Option</h3>
                <div class="gap-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
                    <div>
                        <label class="text-fg-muted text-sm">Digit</label>
                        <select wire:model.defer="newDigit"
                            class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                            <option value="">—</option>
                            @foreach (['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '*', '#'] as $d)
                                <option value="{{ $d }}">{{ $d }}</option>
                            @endforeach
                        </select>
                        @error('newDigit')
                            <p class="mt-1 text-xs text-accent-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-fg-muted text-sm">Description</label>
                        <input wire:model.defer="newDescription" type="text" placeholder="Sales"
                            class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                    </div>
                    <div>
                        <label class="text-fg-muted text-sm">Action</label>
                        <select wire:model.live="newAction"
                            class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                            <option value="">— choose —</option>
                            <option value="in_group">Route to In-Group</option>
                            <option value="ivr_menu">Go to IVR Menu</option>
                            <option value="transfer">Transfer to Number</option>
                            <option value="voicemail">Voicemail</option>
                            <option value="hangup">Hang Up</option>
                        </select>
                        @error('newAction')
                            <p class="mt-1 text-xs text-accent-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-fg-muted text-sm">Destination</label>
                        @if ($newAction === 'in_group')
                            <select wire:model.defer="newDestination"
                                class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                <option value="">— select group —</option>
                                @foreach ($inGroups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        @elseif ($newAction === 'ivr_menu')
                            <select wire:model.defer="newDestination"
                                class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                <option value="">— select menu —</option>
                                @foreach ($ivrMenus as $menu)
                                    <option value="{{ $menu->id }}">{{ $menu->name }}</option>
                                @endforeach
                            </select>
                        @elseif ($newAction === 'transfer')
                            <input wire:model.defer="newDestination" type="text" placeholder="+15551234567"
                                class="bg-surface-2 mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
                        @else
                            <input type="text" disabled placeholder="N/A"
                                class="bg-surface-3 mt-1 px-3 py-2 border border-surface rounded-md w-full text-fg-muted text-sm" />
                        @endif
                    </div>
                </div>
                <div class="flex justify-end">
                    <button wire:click="addOption"
                        class="bg-blue-600 hover:bg-blue-500 px-4 py-1.5 rounded-md font-medium text-white text-sm transition">
                        Add Option
                    </button>
                </div>
            </div>

            {{-- Options table --}}
            @if (count($options))
                <table class="w-full text-sm stagger-rows">
                    <thead>
                        <tr class="border-surface border-b text-fg-muted text-xs">
                            <th class="py-2 font-medium text-left">Digit</th>
                            <th class="py-2 font-medium text-left">Description</th>
                            <th class="py-2 font-medium text-left">Action</th>
                            <th class="py-2 font-medium text-left">Destination</th>
                            <th class="py-2"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($options as $option)
                            <tr class="hover:bg-hover border-surface border-b transition">
                                <td class="py-2.5 font-mono font-bold text-accent-green">{{ $option->digit }}</td>
                                <td class="py-2.5">{{ $option->description ?: '—' }}</td>
                                <td class="py-2.5">
                                    <span
                                        class="text-xs px-2 py-0.5 rounded-full bg-surface-2
                                        @if ($option->action === 'in_group') text-blue-400
                                        @elseif($option->action === 'ivr_menu') text-purple-400
                                        @elseif($option->action === 'transfer') text-accent-green
                                        @elseif($option->action === 'hangup') text-accent-red
                                        @else text-fg-muted @endif">
                                        {{ str_replace('_', ' ', $option->action) }}
                                    </span>
                                </td>
                                <td class="py-2.5 text-fg-muted">
                                    @if ($option->action === 'in_group')
                                        {{ $inGroups->find($option->destination)?->name ?? $option->destination }}
                                    @elseif ($option->action === 'ivr_menu')
                                        {{ $ivrMenus->find($option->destination)?->name ?? $option->destination }}
                                    @else
                                        {{ $option->destination ?: '—' }}
                                    @endif
                                </td>
                                <td class="py-2.5 text-right">
                                    <button wire:click="deleteOption({{ $option->id }})"
                                        wire:confirm="Remove digit {{ $option->digit }} option?"
                                        class="hover:text-red-400 text-xs transition text-accent-red">
                                        Remove
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="py-12 text-fg-muted text-sm text-center">
                    No digit options configured yet. Add one above.
                </div>
            @endif

        </div>
    @endif

</div>
