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
            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New Role
            </a>
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
                                    <a href="{{ route('roles.edit', $role) }}" wire:navigate
                                        class="inline-flex items-center gap-1.5 text-fg-muted hover:text-fg text-xs transition">
                                        <x-heroicon-o-pencil-square class="w-3.5 h-3.5" />
                                        Edit
                                    </a>
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

</div>
