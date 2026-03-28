<div class="space-y-4">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Profile</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Manage your personal information and how you appear to others.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-4">

        {{-- Basics --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Basics</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Your basic account information.</p>
            </div>
            <div class="space-y-4 px-6 py-5">
                <div class="gap-4 grid grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 font-medium text-fg-muted text-xs">First Name</label>
                        <input wire:model.defer="first_name"
                            class="bg-surface-2/70 px-3 py-2.5 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition placeholder-fg-muted">
                        @error('first_name')
                            <p class="mt-1.5 text-accent-red text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium text-fg-muted text-xs">Last Name</label>
                        <input wire:model.defer="last_name"
                            class="bg-surface-2/70 px-3 py-2.5 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition placeholder-fg-muted">
                        @error('last_name')
                            <p class="mt-1.5 text-accent-red text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="gap-4 grid grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 font-medium text-fg-muted text-xs">Nickname</label>
                        <input wire:model.defer="nickname" placeholder="Optional"
                            class="bg-surface-2/70 px-3 py-2.5 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition placeholder-fg-muted">
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium text-fg-muted text-xs">Job Title</label>
                        <input wire:model.defer="job_title" placeholder="Optional"
                            class="bg-surface-2/70 px-3 py-2.5 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition placeholder-fg-muted">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="bg-surface border border-surface rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-surface border-b">
                <h2 class="font-semibold text-fg text-sm">Contact</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Manage your contact details.</p>
            </div>
            <div class="px-6 py-5">
                <div class="gap-4 grid grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 font-medium text-fg-muted text-xs">Email</label>
                        <input wire:model.defer="email" type="email"
                            class="bg-surface-2/70 px-3 py-2.5 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition placeholder-fg-muted">
                        @error('email')
                            <p class="mt-1.5 text-accent-red text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium text-fg-muted text-xs">Mobile Number</label>
                        <input wire:model.defer="mobile" type="tel"
                            class="bg-surface-2/70 px-3 py-2.5 border border-surface-2/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-fg text-sm transition placeholder-fg-muted">
                    </div>
                </div>
            </div>
        </div>

        {{-- Save bar --}}
        <div class="flex justify-end items-center gap-3 py-1">
            @if ($saved)
                <span class="flex items-center gap-1.5 text-accent-green text-sm">
                    <x-heroicon-o-check-circle class="w-4 h-4" />
                    Saved
                </span>
            @endif
            <button type="submit"
                class="bg-indigo-600 hover:bg-indigo-500 px-5 py-2 rounded-lg font-medium text-white text-sm transition">
                Save changes
            </button>
        </div>

    </form>

</div>
