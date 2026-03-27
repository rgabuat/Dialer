<div class="space-y-4">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Profile</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Manage your personal information and how you appear to others.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-4">

        {{-- Basics --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-zinc-800 border-b">
                <h2 class="font-semibold text-zinc-100 text-sm">Basics</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Your basic account information.</p>
            </div>
            <div class="space-y-4 px-6 py-5">
                <div class="gap-4 grid grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">First Name</label>
                        <input wire:model.defer="first_name"
                            class="bg-zinc-800/50 px-3 py-2.5 border border-zinc-700/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-zinc-100 text-sm transition placeholder-zinc-600">
                        @error('first_name')
                            <p class="mt-1.5 text-red-400 text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Last Name</label>
                        <input wire:model.defer="last_name"
                            class="bg-zinc-800/50 px-3 py-2.5 border border-zinc-700/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-zinc-100 text-sm transition placeholder-zinc-600">
                        @error('last_name')
                            <p class="mt-1.5 text-red-400 text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="gap-4 grid grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Nickname</label>
                        <input wire:model.defer="nickname" placeholder="Optional"
                            class="bg-zinc-800/50 px-3 py-2.5 border border-zinc-700/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-zinc-100 text-sm transition placeholder-zinc-600">
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Job Title</label>
                        <input wire:model.defer="job_title" placeholder="Optional"
                            class="bg-zinc-800/50 px-3 py-2.5 border border-zinc-700/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-zinc-100 text-sm transition placeholder-zinc-600">
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-zinc-800 border-b">
                <h2 class="font-semibold text-zinc-100 text-sm">Contact</h2>
                <p class="mt-0.5 text-zinc-500 text-xs">Manage your contact details.</p>
            </div>
            <div class="px-6 py-5">
                <div class="gap-4 grid grid-cols-1 md:grid-cols-2">
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Email</label>
                        <input wire:model.defer="email" type="email"
                            class="bg-zinc-800/50 px-3 py-2.5 border border-zinc-700/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-zinc-100 text-sm transition placeholder-zinc-600">
                        @error('email')
                            <p class="mt-1.5 text-red-400 text-xs">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block mb-1.5 font-medium text-zinc-400 text-xs">Mobile Number</label>
                        <input wire:model.defer="mobile" type="tel"
                            class="bg-zinc-800/50 px-3 py-2.5 border border-zinc-700/60 focus:border-zinc-500 rounded-lg focus:outline-none w-full text-zinc-100 text-sm transition placeholder-zinc-600">
                    </div>
                </div>
            </div>
        </div>

        {{-- Save bar --}}
        <div class="flex justify-end items-center gap-3 py-1">
            @if ($saved)
                <span class="flex items-center gap-1.5 text-green-400 text-sm">
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
