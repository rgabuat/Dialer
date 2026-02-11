<x-layouts.guest title="Sign in — csrpro">
    <main class="container main-content w-full max-w-screen-lg mx-auto px-4 sm:px-6 lg:px-8">
        <form method="POST"action="{{ route('login') }}">
            @csrf
            <x-card
                class="grid grid-cols-1 md:grid-cols-2 transition-all duration-300 ease-in-out gap-0 bg-gradient-to-b from-[#151a20] to-[#0f1115]
                   border border-white/5">
                <div
                    class="relative flex flex-col items-center justify-center shadow-lg rounded-l-xl overflow-hidden auth-bg">
                    <div class="absolute inset-0 bg-black/40"></div>
                </div>
                <div class="w-full p-8 md:p-10 lg:p-12">
                    <div class="text-center ">
                        <x-brand-logo />
                        <p class="text-white semi-bold">Log in to your account</p>
                    </div>
                    <div class="flex flex-col gap-6 mt-4">
                        <div class="flex flex-col gap-2">
                            <label for="email" class="text-neutral-400">Email</label>
                            <x-input type="email" id="email" class="email" name="email" placeholder="Email"
                                required value="{{ old('email') }}" :error="$errors->has('email')" />
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="password" class="text-neutral-400">Password</label>
                            <x-password-input wire:model="password" id="password" name="password" required />
                        </div>
                        <x-toggle-button name="remember" class="text-neutral-400" label="Remember Me" />
                        <x-button text="Login" variant="primary" type="submit" />
                    </div>
                </div>
            </x-card>
        </form>
    </main>
</x-layouts.guest>
