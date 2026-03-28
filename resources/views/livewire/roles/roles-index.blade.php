<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Roles &amp; Permissions</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Define roles and control what each role can access.</p>
    </div>

    {{-- ROLES TABLE --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">

        {{-- Header --}}
        <div class="flex justify-between items-center px-5 py-4 border-zinc-800 border-b">
            <h2 class="font-bold text-white text-base">Roles</h2>
            <a href="{{ route('roles.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New Role
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full text-white text-sm stagger-rows">
                <thead>
                    <tr class="border-zinc-800 border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Permissions</th>
                        <th class="px-5 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($roles as $role)
                        <tr class="hover:bg-zinc-800/30 border-zinc-800/60 border-b transition">
                            <td class="px-5 py-4 font-semibold text-white">{{ $role->name }}</td>
                            <td class="px-5 py-4 text-zinc-400 text-sm">
                                @if ($role->permissions_count > 0)
                                    {{ $role->permissions_count }} permissions
                                @else
                                    <span class="italic">No permissions yet</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('roles.edit', $role) }}"
                                    class="text-zinc-400 hover:text-white text-xs transition">Edit</a>
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
