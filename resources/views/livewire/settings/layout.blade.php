<x-layouts.app>
    <div class="flex gap-6 min-h-full">

        {{-- LEFT SETTINGS NAV --}}
        <aside class="w-52 shrink-0">
            <div class="top-0 sticky bg-zinc-900 p-3 border border-zinc-800 rounded-xl">
                @php
                    $groups = config('settingsubitems', []);
                    $currentRoute = request()->route()?->getName();
                @endphp

                @foreach ($groups as $group)
                    <div class="mb-5 last:mb-0">
                        <p class="mb-1.5 px-2 font-semibold text-[10px] text-zinc-500 uppercase tracking-widest">
                            {{ $group['group'] }}
                        </p>

                        <div class="space-y-0.5">
                            @foreach ($group['items'] as $item)
                                @php
                                    $routeName = 'settings.' . $item['key'];
                                    $isActive = $currentRoute === $routeName;
                                @endphp

                                <a href="{{ route($routeName) }}" wire:navigate
                                    class="flex items-center gap-2.5 px-2 py-2 rounded-lg text-sm transition-all
                                      {{ $isActive
                                          ? 'bg-zinc-800 text-zinc-100 font-medium'
                                          : 'text-zinc-400 hover:bg-zinc-800/60 hover:text-zinc-200' }}">
                                    @if (!empty($item['icon']))
                                        <x-dynamic-component :component="$item['icon']" class="w-4 h-4 shrink-0" />
                                    @endif
                                    <span>{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </aside>

        {{-- RIGHT CONTENT --}}
        <div class="flex-1 min-w-0">
            {{ $slot }}
        </div>

    </div>
</x-layouts.app>
