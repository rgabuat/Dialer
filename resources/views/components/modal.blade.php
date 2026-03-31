{{--
    Reusable modal shell.

    Props:
      - show      : boolean — pass the Livewire property value e.g. :show="$showModal"
      - title     : heading text
      - maxWidth  : Tailwind max-w class (default "max-w-lg")
      - wireClose : name of the Livewire boolean property to set false on close (e.g. "showCreateModal")

    Slots:
      - default   : modal body content
      - footer    : (optional) rendered in a padded footer bar with justify-end
--}}

@props([
    'show' => false,
    'title' => '',
    'maxWidth' => 'max-w-lg',
    'wireClose' => null,
])

@if ($show)
    {{-- x-teleport moves this out of any CSS-transformed ancestor (which would trap position:fixed) --}}
    <template x-teleport="body">
        <div x-data x-on:keydown.escape.window="{{ $wireClose ? "\$wire.set('{$wireClose}', false)" : '' }}"
            class="z-50 fixed inset-0 flex justify-center items-center bg-black/70 px-4">

            {{-- Backdrop click closes --}}
            <div class="absolute inset-0"
                @if ($wireClose) wire:click="{{ '$' }}set('{{ $wireClose }}', false)" @endif>
            </div>

            {{-- Panel --}}
            <div class="relative z-10 w-full {{ $maxWidth }} rounded-xl bg-surface border border-surface shadow-2xl animate-modal-in"
                @click.stop>

                {{-- Header --}}
                <div class="flex justify-between items-center px-6 py-4 border-surface border-b">
                    <h2 class="font-semibold text-fg text-base">{{ $title }}</h2>
                    @if ($wireClose)
                        <button type="button" wire:click="{{ '$' }}set('{{ $wireClose }}', false)"
                            class="flex justify-center items-center hover:bg-hover rounded-md w-7 h-7 text-fg-muted hover:text-fg transition">
                            <x-heroicon-o-x-mark class="w-4 h-4" />
                        </button>
                    @endif
                </div>

                {{-- Body --}}
                <div class="px-6 py-5">
                    {{ $slot }}
                </div>

                {{-- Footer --}}
                @isset($footer)
                    <div class="flex justify-end gap-3 px-6 py-4 border-surface border-t">
                        {{ $footer }}
                    </div>
                @endisset

            </div>
        </div>
    </template>
@endif
