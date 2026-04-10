<div class="min-h-screen bg-surface-4 text-fg p-6">

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight font-mono">{{ $did->phone_number }}</h1>
            <p class="text-sm text-fg-muted">Edit DID routing configuration.</p>
        </div>
        <a href="{{ route('dids.index') }}" wire:navigate class="text-fg-muted hover:text-fg text-sm transition">←
            Back</a>
    </div>

    @if (session('success'))
        <div class="mb-6 bg-green-500/10 border border-green-500/20 text-green-400 text-sm px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="save">
        <div class="max-w-4xl space-y-10">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">CID Number</h2>
                    <p class="text-sm text-fg-muted">The imported CID number linked to this DID.</p>
                </div>
                <div class="md:col-span-3 space-y-5">
                    <div>
                        <label class="text-sm text-fg-muted">Phone Number</label>
                        <select wire:model.defer="cid_number_id"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600 font-mono">
                            <option value="">— Select a CID number —</option>
                            @foreach ($availableCids as $cid)
                                <option value="{{ $cid->id }}" @selected($cid->id == $cid_number_id)>
                                    {{ $cid->phone_number }}{{ $cid->friendly_name ? ' — ' . $cid->friendly_name : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('cid_number_id')
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

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Destination</h2>
                    <p class="text-sm text-fg-muted">Where to route calls.</p>
                </div>
                <div class="md:col-span-3 space-y-5">
                    <div class="flex gap-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="destination" type="radio" value="in_group"
                                class="text-blue-500 focus:ring-blue-500" />
                            <span class="text-sm text-fg">In-Group</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input wire:model.live="destination" type="radio" value="ivr_menu"
                                class="text-blue-500 focus:ring-blue-500" />
                            <span class="text-sm text-fg">IVR Menu</span>
                        </label>
                    </div>

                    @if ($destination === 'in_group')
                        <div>
                            <label class="text-sm text-fg-muted">In-Group</label>
                            <select wire:model.defer="in_group_id"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="">Select in-group…</option>
                                @foreach ($inGroups as $g)
                                    <option value="{{ $g->id }}" @selected($g->id == $in_group_id)>{{ $g->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('in_group_id')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @else
                        <div>
                            <label class="text-sm text-fg-muted">IVR Menu</label>
                            <select wire:model.defer="ivr_menu_id"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                                <option value="">Select IVR menu…</option>
                                @foreach ($ivrMenus as $m)
                                    <option value="{{ $m->id }}" @selected($m->id == $ivr_menu_id)>
                                        {{ $m->name }}</option>
                                @endforeach
                            </select>
                            @error('ivr_menu_id')
                                <p class="text-xs text-accent-red mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                        Save Changes
                    </button>
                </div>
                @if (!$confirmingDelete)
                    <button type="button" wire:click="confirmDelete"
                        class="px-4 py-2 rounded-md bg-red-600/10 hover:bg-red-600/20 text-red-400 text-sm font-medium transition">
                        Delete DID
                    </button>
                @else
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-fg-muted">Are you sure?</span>
                        <button type="button" wire:click="delete"
                            class="px-4 py-2 rounded-md bg-red-600 hover:bg-red-500 text-white text-sm font-medium transition">
                            Yes, Delete
                        </button>
                        <button type="button" wire:click="$set('confirmingDelete', false)"
                            class="px-4 py-2 rounded-md bg-surface-2 text-fg-muted text-sm font-medium transition">
                            Cancel
                        </button>
                    </div>
                @endif
            </div>

        </div>
    </form>
</div>
