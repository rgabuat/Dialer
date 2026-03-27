<div class="space-y-4">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-zinc-100 text-xl">Picture</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Upload a profile picture to personalise your account.</p>
    </div>

    {{-- Profile Photo Card --}}
    <div class="bg-zinc-900 border border-zinc-800 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-zinc-800 border-b">
            <h2 class="font-semibold text-zinc-100 text-sm">Profile Photo</h2>
            <p class="mt-0.5 text-zinc-500 text-xs">A photo helps people recognise you. JPG, PNG or GIF — max 2 MB.</p>
        </div>
        <div class="px-6 py-6">
            <div class="flex items-center gap-6">

                {{-- Current avatar / initials --}}
                <div
                    class="flex justify-center items-center bg-zinc-700 rounded-full w-20 h-20 overflow-hidden font-bold text-zinc-300 text-2xl shrink-0">
                    @php
                        $user = auth()->user();
                        $initials = strtoupper(
                            substr($user->first_name ?? 'U', 0, 1) . substr($user->last_name ?? '', 0, 1),
                        );
                    @endphp
                    {{ $initials }}
                </div>

                {{-- Upload area --}}
                <div class="flex-1">
                    <label for="photo-upload"
                        class="flex flex-col justify-center items-center bg-zinc-800/30 hover:bg-zinc-800/60 border-2 border-zinc-700 hover:border-zinc-600 border-dashed rounded-xl w-full h-28 transition cursor-pointer">
                        <x-heroicon-o-arrow-up-tray class="mb-2 w-6 h-6 text-zinc-500" />
                        <p class="text-zinc-400 text-sm">
                            <span class="font-medium text-zinc-300">Click to upload</span> or drag and drop
                        </p>
                        <p class="mt-1 text-zinc-600 text-xs">PNG, JPG, GIF up to 2 MB</p>
                        <input id="photo-upload" type="file" accept="image/*" class="hidden">
                    </label>
                </div>

            </div>
        </div>
    </div>

</div>
