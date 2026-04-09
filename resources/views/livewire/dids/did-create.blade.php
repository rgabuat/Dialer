<form wire:submit.prevent="save">
    <div class="bg-surface-4 p-6 min-h-screen text-fg">

        <div class="mb-8">
            <h1 class="font-semibold text-2xl tracking-tight">New DID</h1>
            <p class="text-fg-muted text-sm">Register a phone number and route it to an in-group or IVR menu.</p>
        </div>

        <div class="space-y-10 max-w-4xl">

            <div class="gap-6 grid grid-cols-1 md:grid-cols-4 pb-10 border-surface border-b">
                <div>
                    <h2 class="font-medium">Number</h2>
                    <p class="text-fg-muted text-sm">The inbound phone number (E.164 format).</p>
                </div>
                <div class="space-y-5 md:col-span-3">
                    <div>
                        <label class="text-fg-muted text-sm">Phone Number</label>
                        <input wire:model.defer="phone_number" type="text" placeholder="+15551234567"
                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full font-mono text-sm" />
                        @error('phone_number')
                            <p class="mt-1 text-xs text-accent-red">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="text-fg-muted text-sm">Description</label>
                        <input wire:model.defer="description" type="text" placeholder="e.g. Sales line"
                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm" />
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
                    <h2 class="font-medium">Destination</h2>
                    <p class="text-fg-muted text-sm">Where to route calls to this number.</p>
                </div>
                <div class="space-y-5 md:col-span-3">
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="destination" type="radio" value="in_group"
                                class="focus:ring-blue-500 text-blue-500" />
                            <span class="text-fg text-sm">In-Group</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="destination" type="radio" value="ivr_menu"
                                class="focus:ring-blue-500 text-blue-500" />
                            <span class="text-fg text-sm">IVR Menu</span>
                        </label>
                    </div>

                    @if ($destination === 'in_group')
                        <div>
                            <label class="text-fg-muted text-sm">In-Group</label>
                            <select wire:model.defer="in_group_id"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                <option value="">Select in-group…</option>
                                @foreach ($inGroups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            @error('in_group_id')
                                <p class="mt-1 text-xs text-accent-red">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div>
                            <label class="text-fg-muted text-sm">IVR Menu</label>
                            <select wire:model.defer="ivr_menu_id"
                                class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                                <option value="">Select IVR menu…</option>
                                @foreach ($ivrMenus as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                @endforeach
                            </select>
                            @error('ivr_menu_id')
                                <p class="mt-1 text-xs text-accent-red">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif

                    <div>
                        <label class="text-fg-muted text-sm">Campaign (optional — for reporting)</label>
                        <select wire:model.defer="campaign_id"
                            class="bg-surface mt-1 px-3 py-2 border border-surface rounded-md focus:ring-1 focus:ring-zinc-600 w-full text-sm">
                            <option value="">— None —</option>
                            @foreach ($campaigns as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                    Create DID
                </button>
                <a href="{{ route('dids.index') }}" wire:navigate
                    class="bg-surface-2 hover:bg-surface px-4 py-2 rounded-md font-medium text-fg-muted text-sm transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
