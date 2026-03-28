<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Roles &amp; Permissions</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Define roles and control what each role can access.</p>
    </div>

    {{-- ROLES TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex justify-between items-center px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">Roles</h2>
            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New Role
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-auto" x-data x-init="const update = () => {
            const pg = $el.nextElementSibling;
            $el.style.maxHeight = (window.innerHeight - $el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
        };
        update();
        window.addEventListener('resize', update);
        $cleanup(() => window.removeEventListener('resize', update));">
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
                            <td class="px-5 py-4 font-semibold text-fg">{{ $role->name }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">
                                @if ($role->permissions_count > 0)
                                    {{ $role->permissions_count }} permissions
                                @else
                                    <span class="italic">No permissions yet</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('roles.edit', $role) }}"
                                    class="text-fg-muted hover:text-fg text-xs transition">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-5 py-16 text-zinc-500 text-center italic">No roles created yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$roles" label="roles" />

    </div>

</div>
