<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Roles &amp; Permissions</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Define roles and control which pages and actions each role can access.
        </p>
    </div>

    {{-- ROLES TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex justify-between items-center px-5 py-4 border-surface border-b">
            <div>
                <h2 class="font-bold text-fg text-base">Roles</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">{{ $roles->total() }}
                    {{ Str::plural('role', $roles->total()) }} defined</p>
            </div>
            <button wire:click="openCreate"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New Role
            </button>
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
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Permissions</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr class="hover:bg-hover border-surface border-b transition">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="inline-flex items-center justify-center bg-indigo-500/15 text-indigo-400 rounded-md w-8 h-8 text-xs font-bold shrink-0 select-none">
                                        {{ strtoupper(substr($role->name, 0, 2)) }}
                                    </span>
                                    <span class="font-semibold text-fg">{{ $role->name }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                @if ($role->permissions_count > 0)
                                    <span
                                        class="inline-flex items-center gap-1.5 bg-zinc-700/50 px-2.5 py-1 rounded-md text-zinc-300 text-xs">
                                        <span class="bg-indigo-400/60 rounded-full w-1.5 h-1.5 shrink-0"></span>
                                        {{ $role->permissions_count }}
                                        {{ Str::plural('permission', $role->permissions_count) }}
                                    </span>
                                @else
                                    <span class="text-zinc-600 text-xs italic">No permissions</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-4">
                                    <button wire:click="openEdit({{ $role->id }})"
                                        class="inline-flex items-center gap-1.5 text-fg-muted hover:text-fg text-xs transition">
                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                        Edit
                                    </button>
                                    <button wire:click="delete({{ $role->id }})"
                                        wire:confirm="Delete the '{{ $role->name }}' role? This cannot be undone."
                                        class="inline-flex items-center gap-1.5 text-zinc-600 hover:text-accent-red text-xs transition">
                                        <x-heroicon-o-trash class="w-3.5 h-3.5" />
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-16 text-zinc-500 text-center italic">No roles created
                                yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$roles" label="roles" />

    </div>

    {{-- --------------------------------------------------------------
         FULL-SCREEN SLIDE-OVER MODAL (create / edit role)
    -------------------------------------------------------------- --}}
    @if ($showModal)
        @php
            $sectionIcons = [
                'Dashboard' => 'heroicon-o-home',
                'Campaign' => 'heroicon-o-megaphone',
                'People' => 'heroicon-o-users',
                'Activity' => 'heroicon-o-signal',
                'Operations' => 'heroicon-o-briefcase',
                'Reports' => 'heroicon-o-chart-bar',
                'Conversations' => 'heroicon-o-chat-bubble-left-right',
                'Workforce' => 'heroicon-o-calendar-days',
                'Inbound' => 'heroicon-o-phone-arrow-down-left',
                'Settings' => 'heroicon-o-cog-6-tooth',
            ];
            $allPagePerms = collect($pageGroups)->flatMap(fn($p) => array_keys($p))->toArray();
            $allCrudPerms = $groupedPermissions->flatten()->pluck('name')->toArray();
            $allPerms = array_merge($allPagePerms, $allCrudPerms);
            $grantAll = count($allPerms) > 0 && count(array_diff($allPerms, $permissions)) === 0;
        @endphp

        <div class="fixed inset-0 z-50 flex" x-data x-on:keydown.escape.window="$wire.closeModal()">

            {{-- Backdrop --}}
            <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeModal"></div>

            {{-- Panel (wider for permissions) --}}
            <div class="relative ml-auto h-full w-full max-w-3xl bg-surface border-l border-surface flex flex-col shadow-2xl"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">

                {{-- Header --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-surface shrink-0">
                    <div>
                        <h2 class="font-semibold text-fg text-base">
                            {{ $modalMode === 'create' ? 'New Role' : 'Edit Role' }}
                        </h2>
                        <p class="text-xs text-fg-muted mt-0.5">Define role name and assign page access.</p>
                    </div>
                    <button wire:click="closeModal"
                        class="text-fg-muted hover:text-fg transition p-1 rounded-md hover:bg-hover">
                        <x-heroicon-o-x-mark class="w-5 h-5" />
                    </button>
                </div>

                {{-- Scrollable body --}}
                <div class="flex-1 overflow-y-auto px-6 py-6 space-y-4">

                    {{-- Role name --}}
                    <div class="bg-surface-2 border border-surface rounded-xl px-5 py-4">
                        <label class="block text-xs font-medium text-fg-muted mb-1">Role Name</label>
                        <input type="text" wire:model.defer="name" placeholder="e.g. Admin, Supervisor"
                            class="w-full max-w-sm rounded-lg bg-surface border border-surface px-3 py-2 text-sm text-fg focus:border-indigo-500 focus:outline-none placeholder-fg-muted" />
                        @error('name')
                            <p class="text-xs text-red-400 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Campaigns --}}
                    <div class="bg-surface border border-surface rounded-xl overflow-hidden" x-data="{ open: true }"
                        wire:ignore.self>
                        <div class="flex items-center justify-between px-5 py-3.5 cursor-pointer select-none hover:bg-surface-2/5 transition-colors"
                            :class="open ? 'border-b border-surface' : ''" @click="open = !open">
                            <div>
                                <h3 class="font-semibold text-fg text-sm">Campaigns</h3>
                                <p class="mt-0.5 text-zinc-500 text-xs">Restrict this role to specific campaigns.</p>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <button type="button" @click.stop
                                    x-on:click="
                                        const ids = {{ json_encode(collect($allCampaigns)->pluck('id')->map(fn($id) => (string) $id)->values()->toArray()) }};
                                        const allSelected = $wire.selectedCampaigns.length === ids.length;
                                        $wire.selectedCampaigns = allSelected ? [] : ids;
                                    "
                                    class="text-xs px-2.5 py-1 rounded-full border transition"
                                    :class="({{ json_encode(count($allCampaigns)) }} > 0 && $wire.selectedCampaigns.length ===
                                        {{ json_encode(count($allCampaigns)) }}) ?
                                    'bg-indigo-600 border-indigo-500 text-white' :
                                    'bg-surface-2 border-surface text-zinc-400 hover:text-fg hover:border-zinc-500'">
                                    All
                                </button>
                                <x-heroicon-o-chevron-down
                                    class="w-4 h-4 text-zinc-600 shrink-0 transition-transform duration-200"
                                    ::class="open ? 'rotate-180' : ''" />
                            </div>
                        </div>

                        <div x-show="open" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0">
                            @if (count($allCampaigns) > 0)
                                <div class="px-5 py-4 flex flex-wrap gap-2">
                                    @foreach ($allCampaigns as $campaign)
                                        @php $cid = (string) $campaign['id']; @endphp
                                        <button type="button" wire:key="campaign-pill-{{ $cid }}"
                                            x-on:click="
                                                const id = '{{ $cid }}';
                                                const idx = $wire.selectedCampaigns.indexOf(id);
                                                if (idx === -1) {
                                                    $wire.selectedCampaigns = [...$wire.selectedCampaigns, id];
                                                } else {
                                                    $wire.selectedCampaigns = $wire.selectedCampaigns.filter(c => c !== id);
                                                }
                                            "
                                            :class="{{ json_encode($selectedCampaigns) }}.includes('{{ $cid }}') ?
                                                'bg-indigo-600 border-indigo-500 text-white' :
                                                'bg-surface-2 border-surface text-zinc-400 hover:text-fg hover:border-zinc-500'"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full border text-xs font-medium transition cursor-pointer select-none">
                                            <span
                                                :class="{{ json_encode($selectedCampaigns) }}.includes(
                                                    '{{ $cid }}') ? 'opacity-100' : 'opacity-0'"
                                                class="w-1.5 h-1.5 rounded-full bg-white transition-opacity shrink-0"></span>
                                            {{ $campaign['name'] }}
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <p class="px-5 py-4 text-xs text-zinc-600 italic">No campaigns available.</p>
                            @endif
                        </div>
                    </div>

                    {{-- Global grant-all bar --}}
                    <div
                        class="flex items-center justify-between px-5 py-3.5 bg-surface border border-surface rounded-xl">
                        <div>
                            <h3 class="font-semibold text-fg text-sm">Permissions</h3>
                            <p class="mt-0.5 text-zinc-500 text-xs">Click a section to expand or collapse it.</p>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer select-none shrink-0">
                            <span class="text-xs text-zinc-500">Grant all</span>
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" wire:click="toggleAll({{ json_encode($allPerms) }})"
                                    @checked($grantAll) class="sr-only peer">
                                <div
                                    class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                </div>
                            </div>
                        </label>
                    </div>

                    {{-- Section accordion cards --}}
                    @foreach ($pageGroups as $section => $pages)
                        @php
                            $icon = $sectionIcons[$section] ?? 'heroicon-o-squares-2x2';
                            $sectionPagePerms = array_keys($pages);
                            $sectionModules = $crudGroups[$section] ?? [];
                            $sectionCrudPerms = $groupedPermissions
                                ->only($sectionModules)
                                ->flatten()
                                ->pluck('name')
                                ->toArray();
                            $sectionAllPerms = array_merge($sectionPagePerms, $sectionCrudPerms);
                            if (empty($sectionAllPerms)) {
                                continue;
                            }
                            $activeCount = count(array_intersect($sectionAllPerms, $permissions));
                            $totalCount = count($sectionAllPerms);
                            $sectionAll = $activeCount === $totalCount && $totalCount > 0;
                            $hasCrud = !empty($sectionModules) && $groupedPermissions->hasAny($sectionModules);
                        @endphp

                        <div x-data="{ open: false }"
                            class="bg-surface border border-surface rounded-xl overflow-hidden">
                            <div class="flex items-center gap-3 px-4 py-3 cursor-pointer select-none hover:bg-surface-2/5 transition-colors"
                                :class="open ? 'border-b border-surface' : ''" @click="open = !open">
                                <div
                                    class="flex items-center justify-center w-7 h-7 rounded-md bg-zinc-800/80 shrink-0">
                                    <x-dynamic-component :component="$icon" class="w-3.5 h-3.5 text-zinc-400" />
                                </div>
                                <span class="font-semibold text-fg text-sm flex-1 min-w-0">{{ $section }}</span>
                                <span
                                    class="text-[11px] tabular-nums shrink-0 {{ $activeCount > 0 ? 'text-indigo-400' : 'text-zinc-600' }}">{{ $activeCount }}/{{ $totalCount }}</span>
                                <label class="flex items-center gap-1.5 cursor-pointer select-none shrink-0"
                                    @click.stop>
                                    <span class="text-[11px] text-zinc-600">All</span>
                                    <div class="relative inline-flex items-center">
                                        <input type="checkbox"
                                            wire:click="toggleModule({{ json_encode($sectionAllPerms) }})"
                                            @checked($sectionAll) class="sr-only peer">
                                        <div
                                            class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                        </div>
                                    </div>
                                </label>
                                <x-heroicon-o-chevron-down
                                    class="w-4 h-4 text-zinc-600 shrink-0 transition-transform duration-200"
                                    ::class="open ? 'rotate-180' : ''" />
                            </div>

                            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                                x-transition:leave="transition ease-in duration-100"
                                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                                @if (!empty($sectionPagePerms))
                                    <div class="{{ $hasCrud ? 'border-b border-zinc-800' : '' }} pb-1.5">
                                        <p
                                            class="px-5 pt-3 pb-1 text-[10px] font-semibold text-zinc-600 uppercase tracking-widest">
                                            Pages</p>
                                        @foreach ($pages as $perm => $label)
                                            <label
                                                class="flex items-center justify-between gap-3 mx-3 px-3 py-2 rounded-lg hover:bg-surface-2/20 cursor-pointer group/pg transition">
                                                <div class="flex items-center gap-2.5 min-w-0">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full bg-zinc-700 group-hover/pg:bg-zinc-500 shrink-0 transition-colors"></span>
                                                    <span
                                                        class="text-sm text-zinc-400 group-hover/pg:text-zinc-200 transition-colors truncate">{{ $label }}</span>
                                                </div>
                                                <div class="relative inline-flex items-center shrink-0" @click.stop>
                                                    <input type="checkbox" wire:model.defer="permissions"
                                                        value="{{ $perm }}" class="sr-only peer">
                                                    <div
                                                        class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                                    </div>
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($hasCrud)
                                    <div class="pb-1.5">
                                        <p
                                            class="px-5 pt-3 pb-1 text-[10px] font-semibold text-zinc-600 uppercase tracking-widest">
                                            Data Access</p>
                                        @foreach ($sectionModules as $module)
                                            @if ($groupedPermissions->has($module))
                                                @php
                                                    $modPerms = $groupedPermissions[$module];
                                                    $modPermNames = $modPerms->pluck('name')->toArray();
                                                    $modAll =
                                                        count($modPermNames) > 0 &&
                                                        count(array_diff($modPermNames, $permissions)) === 0;
                                                @endphp
                                                <div class="mx-3 mb-1">
                                                    <div class="flex items-center justify-between px-3 py-1.5">
                                                        <span
                                                            class="text-xs text-zinc-500 font-medium capitalize">{{ $module }}</span>
                                                        <label
                                                            class="flex items-center gap-1.5 cursor-pointer select-none"
                                                            @click.stop>
                                                            <span class="text-[11px] text-zinc-600">All</span>
                                                            <div class="relative inline-flex items-center">
                                                                <input type="checkbox"
                                                                    wire:click="toggleModule({{ json_encode($modPermNames) }})"
                                                                    @checked($modAll) class="sr-only peer">
                                                                <div
                                                                    class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                                                </div>
                                                            </div>
                                                        </label>
                                                    </div>
                                                    @foreach ($modPerms as $perm)
                                                        <label
                                                            class="flex items-center justify-between gap-3 px-3 py-1.5 rounded-lg hover:bg-surface-2/20 cursor-pointer group/cp transition">
                                                            <div class="flex items-center gap-2.5 min-w-0">
                                                                <span
                                                                    class="w-1.5 h-1.5 rounded-full bg-zinc-700 group-hover/cp:bg-zinc-500 shrink-0 transition-colors"></span>
                                                                <span
                                                                    class="text-sm text-zinc-400 group-hover/cp:text-zinc-200 transition-colors font-mono truncate">{{ $perm->name }}</span>
                                                            </div>
                                                            <div class="relative inline-flex items-center shrink-0"
                                                                @click.stop>
                                                                <input type="checkbox" wire:model.defer="permissions"
                                                                    value="{{ $perm->name }}" class="sr-only peer">
                                                                <div
                                                                    class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                                                </div>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach

                </div>

                {{-- Footer --}}
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-surface shrink-0">
                    <button type="button" wire:click="closeModal"
                        class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg hover:bg-hover transition">
                        Cancel
                    </button>
                    <button type="button" wire:click="save"
                        class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium transition">
                        {{ $modalMode === 'create' ? 'Create Role' : 'Save Changes' }}
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
