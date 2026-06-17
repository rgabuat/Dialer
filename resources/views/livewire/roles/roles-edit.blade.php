<div class="space-y-4 p-6 stagger-children" x-data="{ confirmOpen: false }">

    {{-- Confirm Save Modal --}}
    <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center"
        x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/60" @click="confirmOpen = false"></div>
        <div class="relative bg-surface border border-surface rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
            <h3 class="font-semibold text-fg text-base mb-1">Save changes?</h3>
            <p class="text-zinc-500 text-sm mb-5">This will update the role name and all assigned permissions.</p>
            <div class="flex justify-end gap-3">
                <button @click="confirmOpen = false"
                    class="px-4 py-2 rounded-lg text-sm text-fg-muted hover:text-fg transition">
                    Cancel
                </button>
                <button @click="confirmOpen = false" wire:click="save"
                    class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-lg font-medium text-white text-sm transition">
                    Yes, save
                </button>
            </div>
        </div>
    </div>

    {{-- Header + Role Name --}}
    <div class="bg-surface border border-surface rounded-xl overflow-hidden">
        <div class="flex items-center justify-between gap-4 px-5 py-3.5">
            <div class="flex items-center gap-3 min-w-0">
                <h1 class="font-bold text-fg text-base shrink-0">Edit Role</h1>
                <input type="text" wire:model="name" placeholder="Role name"
                    class="w-52 rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600">
                @error('name')
                    <p class="text-accent-red text-xs">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('roles.index') }}" wire:navigate
                    class="text-fg-muted hover:text-fg text-sm transition">
                    Cancel
                </a>
                <button @click="confirmOpen = true"
                    class="bg-indigo-600 hover:bg-indigo-500 px-4 py-1.5 rounded-lg font-medium text-white text-sm transition">
                    Save changes
                </button>
            </div>
        </div>
    </div>

    {{-- ─── Permissions accordion ─── --}}
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

    {{-- Global grant-all bar --}}
    <div class="flex items-center justify-between px-5 py-3.5 bg-surface border border-surface rounded-xl">
        <div>
            <h2 class="font-semibold text-fg text-sm">Permissions</h2>
            <p class="mt-0.5 text-zinc-500 text-xs">Sections match the sidebar. Click a section to expand or collapse
                it.</p>
        </div>
        <label class="flex items-center gap-2 cursor-pointer select-none shrink-0">
            <span class="text-xs text-zinc-500">Grant all</span>
            <div class="relative inline-flex items-center">
                <input type="checkbox" wire:click="toggleAll({{ json_encode($allPerms) }})" @checked($grantAll)
                    class="sr-only peer">
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
            $sectionCrudPerms = $groupedPermissions->only($sectionModules)->flatten()->pluck('name')->toArray();
            $sectionAllPerms = array_merge($sectionPagePerms, $sectionCrudPerms);
            if (empty($sectionAllPerms)) {
                continue;
            }
            $activeCount = count(array_intersect($sectionAllPerms, $permissions));
            $totalCount = count($sectionAllPerms);
            $sectionAll = $activeCount === $totalCount && $totalCount > 0;
            $hasCrud = !empty($sectionModules) && $groupedPermissions->hasAny($sectionModules);
        @endphp

        <div x-data="{ open: true }" class="bg-surface border border-surface rounded-xl overflow-hidden">

            {{-- Collapsible header --}}
            <div class="flex items-center gap-3 px-4 py-3 cursor-pointer select-none hover:bg-surface-2/5 transition-colors"
                :class="open ? 'border-b border-surface' : ''" @click="open = !open">
                <div class="flex items-center justify-center w-7 h-7 rounded-md bg-zinc-800/80 shrink-0">
                    <x-dynamic-component :component="$icon" class="w-3.5 h-3.5 text-zinc-400" />
                </div>
                <span class="font-semibold text-fg text-sm flex-1 min-w-0">{{ $section }}</span>
                <span
                    class="text-[11px] tabular-nums shrink-0 {{ $activeCount > 0 ? 'text-indigo-400' : 'text-zinc-600' }}">{{ $activeCount }}/{{ $totalCount }}</span>
                <label class="flex items-center gap-1.5 cursor-pointer select-none shrink-0" @click.stop>
                    <span class="text-[11px] text-zinc-600">All</span>
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" wire:click="toggleModule({{ json_encode($sectionAllPerms) }})"
                            @checked($sectionAll) class="sr-only peer">
                        <div
                            class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                        </div>
                    </div>
                </label>
                <x-heroicon-o-chevron-down class="w-4 h-4 text-zinc-600 shrink-0 transition-transform duration-200"
                    ::class="open ? 'rotate-180' : ''" />
            </div>

            {{-- Collapsible body --}}
            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0">

                {{-- Pages sub-section --}}
                @if (!empty($sectionPagePerms))
                    <div class="{{ $hasCrud ? 'border-b border-zinc-800' : '' }} pb-1.5">
                        <p class="px-5 pt-3 pb-1 text-[10px] font-semibold text-zinc-600 uppercase tracking-widest">
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
                                    <input type="checkbox" wire:model="permissions" value="{{ $perm }}"
                                        class="sr-only peer">
                                    <div
                                        class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                    </div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                @endif

                {{-- Data access sub-section --}}
                @if ($hasCrud)
                    <div class="pb-1.5">
                        <p class="px-5 pt-3 pb-1 text-[10px] font-semibold text-zinc-600 uppercase tracking-widest">Data
                            Access</p>
                        @foreach ($sectionModules as $module)
                            @if ($groupedPermissions->has($module))
                                @php
                                    $modPerms = $groupedPermissions[$module];
                                    $modPermNames = $modPerms->pluck('name')->toArray();
                                    $modAll =
                                        count($modPermNames) > 0 &&
                                        count(array_diff($modPermNames, $permissions)) === 0;
                                @endphp
                                <div
                                    class="flex items-center gap-3 px-5 py-2 border-t border-zinc-800 first:border-0 hover:bg-surface-2/10 transition">
                                    <div class="w-40 shrink-0">
                                        <span
                                            class="text-xs text-zinc-300">{{ ucwords(str_replace('_', ' ', $module)) }}</span>
                                    </div>
                                    <div class="flex items-center gap-5 flex-1">
                                        @foreach (['view', 'create', 'update', 'delete'] as $action)
                                            @php $perm = $modPerms->firstWhere('name', $module . '.' . $action); @endphp
                                            @if ($perm)
                                                <label
                                                    class="flex flex-col items-center gap-0.5 cursor-pointer group/act">
                                                    <div class="relative inline-flex items-center" @click.stop>
                                                        <input type="checkbox" wire:model="permissions"
                                                            value="{{ $perm->name }}" class="sr-only peer">
                                                        <div
                                                            class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                                        </div>
                                                    </div>
                                                    <span
                                                        class="text-[10px] text-zinc-600 group-hover/act:text-zinc-400 transition-colors">{{ ucfirst($action) }}</span>
                                                </label>
                                            @endif
                                        @endforeach
                                    </div>
                                    <label class="flex flex-col items-center gap-0.5 cursor-pointer shrink-0 group/rall"
                                        @click.stop>
                                        <div class="relative inline-flex items-center">
                                            <input type="checkbox"
                                                wire:click="toggleModule({{ json_encode($modPermNames) }})"
                                                @checked($modAll) class="sr-only peer">
                                            <div
                                                class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                            </div>
                                        </div>
                                        <span
                                            class="text-[10px] text-zinc-600 group-hover/rall:text-zinc-400 transition-colors">All</span>
                                    </label>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

            </div>
        </div>
    @endforeach

    {{-- Catch-all: modules not mapped to any section --}}
    @php
        $allGroupedMods = collect($crudGroups)->flatten()->all();
        $otherModules = $groupedPermissions->keys()->diff($allGroupedMods)->values();
    @endphp
    @if ($otherModules->isNotEmpty())
        @php
            $otherCrudPerms = $groupedPermissions->only($otherModules->all())->flatten()->pluck('name')->toArray();
            $otherActive = count(array_intersect($otherCrudPerms, $permissions));
            $otherTotal = count($otherCrudPerms);
            $otherAll = $otherActive === $otherTotal && $otherTotal > 0;
        @endphp
        <div x-data="{ open: true }" class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="flex items-center gap-3 px-4 py-3 cursor-pointer select-none hover:bg-surface-2/5 transition-colors"
                :class="open ? 'border-b border-surface' : ''" @click="open = !open">
                <div class="flex items-center justify-center w-7 h-7 rounded-md bg-zinc-800/80 shrink-0">
                    <x-heroicon-o-squares-2x2 class="w-3.5 h-3.5 text-zinc-400" />
                </div>
                <span class="font-semibold text-fg text-sm flex-1">Other</span>
                <span
                    class="text-[11px] tabular-nums shrink-0 {{ $otherActive > 0 ? 'text-indigo-400' : 'text-zinc-600' }}">{{ $otherActive }}/{{ $otherTotal }}</span>
                <label class="flex items-center gap-1.5 cursor-pointer select-none shrink-0" @click.stop>
                    <span class="text-[11px] text-zinc-600">All</span>
                    <div class="relative inline-flex items-center">
                        <input type="checkbox" wire:click="toggleModule({{ json_encode($otherCrudPerms) }})"
                            @checked($otherAll) class="sr-only peer">
                        <div
                            class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                        </div>
                    </div>
                </label>
                <x-heroicon-o-chevron-down class="w-4 h-4 text-zinc-600 shrink-0 transition-transform duration-200"
                    ::class="open ? 'rotate-180' : ''" />
            </div>
            <div x-show="open" x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0" class="pb-1.5">
                @foreach ($otherModules as $module)
                    @php
                        $modPerms = $groupedPermissions[$module];
                        $modPermNames = $modPerms->pluck('name')->toArray();
                        $modAll = count($modPermNames) > 0 && count(array_diff($modPermNames, $permissions)) === 0;
                    @endphp
                    <div
                        class="flex items-center gap-3 px-5 py-2 border-t border-surface/20 first:border-0 hover:bg-surface-2/10 transition">
                        <div class="w-40 shrink-0">
                            <span class="text-xs text-zinc-300">{{ ucwords(str_replace('_', ' ', $module)) }}</span>
                        </div>
                        <div class="flex items-center gap-5 flex-1">
                            @foreach (['view', 'create', 'update', 'delete'] as $action)
                                @php $perm = $modPerms->firstWhere('name', $module . '.' . $action); @endphp
                                @if ($perm)
                                    <label class="flex flex-col items-center gap-0.5 cursor-pointer group/act">
                                        <div class="relative inline-flex items-center">
                                            <input type="checkbox" wire:model="permissions"
                                                value="{{ $perm->name }}" class="sr-only peer">
                                            <div
                                                class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                            </div>
                                        </div>
                                        <span
                                            class="text-[10px] text-zinc-600 group-hover/act:text-zinc-400 transition-colors">{{ ucfirst($action) }}</span>
                                    </label>
                                @endif
                            @endforeach
                        </div>
                        <label class="flex flex-col items-center gap-0.5 cursor-pointer shrink-0 group/rall">
                            <div class="relative inline-flex items-center">
                                <input type="checkbox" wire:click="toggleModule({{ json_encode($modPermNames) }})"
                                    @checked($modAll) class="sr-only peer">
                                <div
                                    class="after:top-0.5 after:left-0.5 after:absolute relative bg-zinc-700 after:bg-white peer-checked:bg-indigo-500 rounded-full after:rounded-full w-9 after:w-4 h-5 after:h-4 after:content-[''] transition-colors after:transition-transform peer-checked:after:translate-x-4 duration-200 after:duration-200">
                                </div>
                            </div>
                            <span
                                class="text-[10px] text-zinc-600 group-hover/rall:text-zinc-400 transition-colors">All</span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>
    @endif



</div>
