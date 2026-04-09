<form wire:submit.prevent="save">
    <div class="min-h-screen bg-surface-4 text-fg p-6">

        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">New IVR Menu</h1>
            <p class="text-sm text-fg-muted">Create a greeting menu. You'll add digit options after saving.</p>
        </div>

        <div class="max-w-4xl space-y-10">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-fg-muted">Name and status of this IVR menu.</p>
                </div>
                <div class="md:col-span-3 space-y-5">
                    <div>
                        <label class="text-sm text-fg-muted">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name') <p class="text-xs text-accent-red mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">Description</label>
                        <textarea wire:model.defer="description" rows="2"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                    </div>
                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-fg-muted">Active</label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Greeting</h2>
                    <p class="text-sm text-fg-muted">What callers hear when they arrive at this menu.</p>
                </div>
                <div class="md:col-span-3 space-y-5">
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="greeting_type" type="radio" value="tts"
                                class="text-blue-500 focus:ring-blue-500" />
                            <span class="text-sm text-fg">Text-to-Speech</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="greeting_type" type="radio" value="audio"
                                class="text-blue-500 focus:ring-blue-500" />
                            <span class="text-sm text-fg">Audio File URL</span>
                        </label>
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">
                            {{ $greeting_type === 'audio' ? 'Audio File URL' : 'TTS Message' }}
                        </label>
                        @if ($greeting_type === 'audio')
                            <input wire:model.defer="greeting_value" type="url" placeholder="https://..."
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @else
                            <textarea wire:model.defer="greeting_value" rows="3"
                                placeholder="Thank you for calling. Press 1 for Sales. Press 2 for Support."
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                        @endif
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Digit Input Timeout (seconds)</label>
                            <input wire:model.defer="gather_timeout" type="number" min="1" max="30"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Max Invalid Attempts</label>
                            <input wire:model.defer="invalid_attempts_max" type="number" min="1" max="10"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Invalid Input Action</label>
                            <select wire:model.defer="invalid_action"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="repeat">Repeat Menu</option>
                                <option value="hangup">Hang Up</option>
                                <option value="transfer">Transfer to Number</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Invalid Destination (if transfer)</label>
                            <input wire:model.defer="invalid_destination" type="text" placeholder="+15551234567"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    Create IVR Menu
                </button>
                <a href="{{ route('ivr-menus.index') }}" wire:navigate
                    class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg-muted text-sm font-medium transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
