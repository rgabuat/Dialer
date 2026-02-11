<div class="p-6">
    <div class="flex justify-between mb-4">
        <h1 class="text-lg text-white font-semibold">Roles</h1>
        <a href="{{ route('roles.create') }}" class="px-3 py-1.5 text-sm text-white rounded-md bg-zinc-900 border border-zinc-800 hover:bg-zinc-800 ">New Role</a>
    </div>

    <table class="w-full text-sm border border-zinc-800 rounded-lg overflow-hidden">
        <thead class="bg-zinc-900 text-zinc-400 border-b border-zinc-800">
            <tr class="text-left text-xs uppercase text-white">
                <th class="px-4 py-3 font-medium">Role</th>
                <th class="px-4 py-3 font-medium">Permissions</th>
                <th class="px-4 py-3 font-medium text-right">Actions</th>
            </tr>
        </thead>

        <tbody class="divide-y divide-zinc-800 bg-zinc-900 text-white">

        @forelse($roles as $role)
            <tr class="hover:bg-zinc-800/40 transition">
                <td class="px-4 py-3 font-medium">
                    {{ $role->name }}
                </td>

                <td class="px-4 py-3">
                    @if($role->permissions_count > 0)
                        {{ $role->permissions_count }} permissions
                    @else
                        <span class="italic text-zinc-400">
                            No permissions yet
                        </span>
                    @endif
                </td>

                <td class="px-4 py-3 text-right">
                    <a
                        href="{{ route('roles.edit', $role) }}"
                        class="text-blue-400 hover:text-blue-300 transition"
                    >
                        Edit
                    </a>
                </td>
            </tr>

        @empty
            <tr>
                <td colspan="3" class="px-4 py-6 text-center text-zinc-400">
                    No roles created yet
                </td>
            </tr>
        @endforelse

    </tbody>

    </table>
</div>
