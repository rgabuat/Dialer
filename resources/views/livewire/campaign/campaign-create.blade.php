<form wire:submit.prevent="save">
    <div class="min-h-screen bg-zinc-950 text-zinc-100 p-6">

        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-semibold tracking-tight">New Campaign</h1>
            <p class="text-sm text-zinc-400">Create a new campaign and assign agents to it.</p>
        </div>

        <div class="max-w-4xl space-y-10">

            {{-- Details --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-zinc-800 pb-10">
                <div>
                    <h2 class="font-medium">Details</h2>
                    <p class="text-sm text-zinc-400">Campaign information.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-zinc-400">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-zinc-400">Phone Number</label>
                        <input wire:model.defer="phone_number" type="text" placeholder="+1234567890"
                            class="mt-1 w-full rounded-md bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('phone_number') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="text-sm text-zinc-400">Description</label>
                        <textarea wire:model.defer="description" rows="3"
                            class="mt-1 w-full rounded-md bg-zinc-900 border border-zinc-800 px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                        @error('description') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-zinc-900 border-zinc-700 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-zinc-400">Active</label>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-4">
                <button type="submit"
                    class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                    Create Campaign
                </button>
                <a href="{{ route('campaigns.index') }}" wire:navigate
                    class="px-4 py-2 rounded-md bg-zinc-800 hover:bg-zinc-700 text-zinc-300 text-sm font-medium transition">
                    Cancel
                </a>
            </div>

        </div>
    </div>
</form>
