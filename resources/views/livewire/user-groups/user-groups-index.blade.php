<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">User Groups</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Organise users into groups and assign campaigns.</p>
    </div>

    {{-- USER GROUPS TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">User Groups</h2>

            <div class="flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search groups..."
                    class="bg-surface-2 px-3 py-1.5 border border-surface focus:border-zinc-600 rounded-lg focus:outline-none focus:ring-0 w-44 text-fg text-sm transition placeholder-fg-muted">
                @can('user_group.create')
                    <button wire:click="openCreate"
                        class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                        <x-heroicon-o-plus class="w-4 h-4" />
                        New Group
                    </button>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-auto" x-data="{
            _fn: null,
            init() {
                this._fn = () => {
                    const pg = this.$el.nextElementSibling;
                    this.$el.style.maxHeight = (window.innerHeight - this.$el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
                };
                this._fn();
                window.addEventListener('resize', this._fn);
            },
            destroy() { window.removeEventListener('resize', this._fn); }
        }">
            <table class="min-w-full text-fg text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Campaigns</th>
                        <th class="px-5 py-3 text-left">Users</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groups as $group)
                        <tr class="hover:bg-hover border-surface border-b transition">
                            <td class="px-5 py-4 font-semibold text-fg">{{ $group->name }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $group->campaigns_count }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $group->users_count }}</td>
                            <td class="px-5 py-4">
                                @if ($group->is_active)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-green-500/10 px-2.5 py-1 rounded-md font-bold text-xs uppercase tracking-wide text-accent-green">
                                        <span class="bg-green-400 rounded-full w-1.5 h-1.5"></span>Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-zinc-700/50 px-2.5 py-1 rounded-md font-bold text-fg-muted text-xs uppercase tracking-wide">
                                        <span class="bg-zinc-500 rounded-full w-1.5 h-1.5"></span>Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                @can('user_group.update')
                                    <button wire:click="openEdit({{ $group->id }})"
                                        class="text-fg-muted hover:text-fg text-xs transition">Edit</button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No user groups found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$groups" label="groups" />

    </div>

    {{-- ══════════════════════════════════════════════════════════════
         FULL-SCREEN SLIDE-OVER MODAL
    ══════════════════════════════════════════════════════════════ --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex" x-data x-on:keydown.escape.window="$wire.closeModal()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>

            {{-- Panel --}}
            <div class="relative ml-auto h-full w-full max-w-2xl bg-surface border-l border-surface flex flex-col shadow-2xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-surface shrink-0">
                    <div>
                        <h2 class="font-semibold text-fg text-base">
                            {{ $modalMode === 'create' ? 'New User Group' : 'Edit User Group' }}
                        </h2>
                        <p class="text-xs text-fg-muted mt-0.5">
                            {{ $modalMode === 'create' ? 'Create a user group and assign campaigns.' : 'Update group details and campaign assignments.' }}
                        </p>
                    </div>
                    <button wire:click="closeModal"
                        class="text-fg-muted hover:text-fg transition p-1 rounded-md hover:bg-hover">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Scrollable body --}}
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-6">

                    {{-- Details --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-fg-muted mb-3">Details</h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">Name</label>
                                <input wire:model.defer="name" type="text"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none" />
                                @error('name')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-fg-muted mb-1">Description</label>
                                <textarea wire:model.defer="description" rows="3"
                                    class="w-full rounded-lg bg-surface-2 border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none"></textarea>
                                @error('description')
                                    <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="flex items-center gap-3">
                                <input wire:model.defer="is_active" type="checkbox" id="ug_is_active"
                                    class="rounded bg-surface-2 border-surface text-indigo-500 focus:ring-indigo-500" />
                                <label for="ug_is_active" class="text-sm text-fg-muted">Active</label>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-surface"></div>

                    {{-- Campaigns --}}
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-fg-muted mb-3">Campaigns</h3>
                        <p class="text-xs text-fg-muted mb-3">Select which campaigns this group can access.</p>
                        <div class="space-y-2">
                            @foreach ($campaigns as $campaign)
                                <label class="flex items-center gap-3 cursor-pointer">
                                    <input type="checkbox" value="{{ $campaign['id'] }}"
                                        wire:model.defer="selectedCampaigns"
                                        class="rounded bg-surface-2 border-surface text-indigo-500 focus:ring-indigo-500" />
                                    <span class="text-sm text-fg">{{ $campaign['name'] }}</span>
                                </label>
                            @endforeach
                            @error('selectedCampaigns')
                                <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Danger zone (edit only) --}}
                    @if ($modalMode === 'edit')
                        <div class="border-t border-surface pt-6">
                            <h3 class="text-xs font-semibold uppercase tracking-wider text-red-500 mb-2">Danger Zone
                            </h3>
                            <p class="text-xs text-fg-muted mb-3">Deleting a group is permanent. Users will lose their
                                group assignment.</p>
                            <button type="button" wire:click="confirmDelete"
                                class="px-3 py-1.5 rounded-md bg-red-600/15 hover:bg-red-600/30 text-red-400 text-sm font-medium transition">
                                Delete group
                            </button>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface shrink-0">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg hover:bg-hover transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="save"
                        class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition">
                        {{ $modalMode === 'create' ? 'Create Group' : 'Save Changes' }}
                    </button>
                </div>

            </div>
        </div>

        {{-- Delete confirmation --}}
        @if ($confirmingDelete)
            <div class="fixed inset-0 z-[60] flex items-center justify-center">
                <div class="absolute inset-0 bg-black/70"></div>
                <div class="relative w-full max-w-sm mx-4 bg-surface border border-surface rounded-xl p-6 shadow-2xl">
                    <h3 class="font-semibold text-fg text-base">Delete User Group</h3>
                    <p class="mt-2 text-sm text-fg-muted">Users assigned to this group will lose their group
                        assignment. This cannot be undone.</p>
                    <div class="mt-6 flex justify-end gap-3">
                        <button type="button" wire:click="$set('confirmingDelete', false)"
                            class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg hover:bg-hover transition">
                            Cancel
                        </button>
                        <button type="button" wire:click="delete"
                            class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-500 text-white text-sm font-medium transition">
                            Delete
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif

</div>
