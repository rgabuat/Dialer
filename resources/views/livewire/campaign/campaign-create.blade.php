<form wire:submit.prevent="save">
    <div class="min-h-screen bg-surface-4 text-fg p-6">

        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">New Campaign</h1>
            <p class="text-sm text-fg-muted">Create a new campaign and configure its dialer settings.</p>
        </div>

        <div class="max-w-4xl space-y-10">

            {{-- Details --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-fg-muted">Campaign information.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">Description</label>
                        <textarea wire:model.defer="description" rows="3"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                        @error('description')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-fg-muted">Active</label>
                    </div>
                </div>
            </div>

            {{-- Dialer Settings --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Dialer Settings</h2>
                    <p class="text-sm text-fg-muted">Configure how calls are placed and handled.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Campaign Type</label>
                            <select wire:model.defer="type"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="OUTBOUND">Outbound</option>
                                <option value="INBOUND">Inbound</option>
                                <option value="BLENDED">Blended</option>
                            </select>
                            @error('type')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Dial Mode</label>
                            <select wire:model.defer="dial_mode"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="MANUAL">Manual</option>
                                <option value="PREVIEW">Preview</option>
                                <option value="PROGRESSIVE">Progressive</option>
                                <option value="PREDICTIVE">Predictive</option>
                            </select>
                            @error('dial_mode')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Dial Level <span class="text-xs">(predictive
                                    ratio)</span></label>
                            <input wire:model.defer="dial_level" type="number" step="0.1" min="0.1"
                                max="10"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('dial_level')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Max Simultaneous Calls</label>
                            <input wire:model.defer="max_calls" type="number" min="1" max="100"
                                placeholder="Unlimited"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('max_calls')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Outbound Caller ID</label>
                            <input wire:model.defer="caller_id" type="text"
                                placeholder="+1234567890 (leave blank for default)"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('caller_id')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Hopper Level <span class="text-xs">(leads to
                                    pre-queue)</span></label>
                            <input wire:model.defer="hopper_level" type="number" min="1" max="1000"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                            @error('hopper_level')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">After-Call Work (ACW) Timer <span class="text-xs">seconds,
                                0 = disabled</span></label>
                        <input wire:model.defer="acw_seconds" type="number" min="0" max="3600"
                            class="mt-1 w-32 rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('acw_seconds')
                            <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Script --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Agent Script</h2>
                    <p class="text-sm text-fg-muted">Text or HTML shown to agents during active calls.</p>
                </div>
                <div class="md:col-span-3">
                    <textarea wire:model.defer="script" rows="8" placeholder="Enter the script agents will see during calls..."
                        class="w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-zinc-600"></textarea>
                    @error('script')
                        <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    Create Campaign
                </button>
                <a href="{{ route('campaigns.index') }}" wire:navigate
                    class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg-muted text-sm font-medium transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
