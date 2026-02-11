<div class="max-w-4xl">

    <form wire:submit.prevent="save">

        <h1 class="text-2xl font-semibold text-white mb-1">
            Profile
        </h1>

        <p class="text-sm text-neutral-400 mb-10">
            Your profile information determines how you will appear to other users.
        </p>

        {{-- BASICS --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
            <div>
                <h3 class="text-lg font-semibold text-white mb-1">Basics</h3>
                <p class="text-sm text-neutral-400">
                    Enter your basic information.
                </p>
            </div>

            <div class="space-y-5">
                {{-- First Name --}}
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        First Name
                    </label>
                    <input wire:model.defer="first_name"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Last Name --}}
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Last Name
                    </label>
                    <input wire:model.defer="last_name"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Job Title --}}
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Job Title
                    </label>
                    <input wire:model.defer="job_title"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
            </div>
        </div>

        <hr class="border-neutral-800 mb-12">

        {{-- CONTACT --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 mb-12">
            <div>
                <h3 class="text-lg font-semibold text-white mb-1">Contact</h3>
                <p class="text-sm text-neutral-400">
                    Manage your contact information.
                </p>
            </div>

            <div class="space-y-5">
                {{-- Email --}}
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Email
                    </label>
                    <input wire:model.defer="email" type="email"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none">
                    @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Mobile --}}
                <div>
                    <label class="block text-sm text-neutral-400 mb-1">
                        Mobile Number
                    </label>
                    <input wire:model.defer="mobile" type="tel"
                        class="w-full rounded-lg bg-neutral-950 border border-neutral-800
                               px-3 py-2 text-white focus:border-indigo-500 focus:outline-none">
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-4">
            @if ($saved)
                <span class="text-sm text-green-500">
                    Profile updated successfully
                </span>
            @endif

            <button
                type="submit"
                class="px-6 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500
                       text-white font-medium transition"
            >
                Update
            </button>
        </div>

    </form>
</div>
