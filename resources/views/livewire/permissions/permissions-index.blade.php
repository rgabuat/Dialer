<div class="p-6 max-w-3xl">
    <h1 class="text-lg font-semibold mb-4">Create Role</h1>

    <input wire:model="name" class="input" placeholder="Role name">

    <div class="mt-6 space-y-4">
        @foreach($allPermissions as $group => $perms)
            <div>
                <h3 class="text-sm font-semibold text-zinc-400 mb-2">
                    {{ strtoupper($group) }}
                </h3>

                @foreach($perms as $perm)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="permissions" value="{{ $perm->name }}">
                        {{ $perm->name }}
                    </label>
                @endforeach
            </div>
        @endforeach
    </div>

    <button wire:click="save" class="btn-primary mt-6">Save</button>
</div>
