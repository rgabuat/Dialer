<div class="bg-base p-6 w-full min-h-screen">
    <div class="flex gap-6 h-full">

        {{-- LEFT SIDEBAR --}}
        <aside
            class="bg-gradient-to-b from-[#151a20] to-[#0f1115] p-5 border border-white/5 rounded-2xl w-72 shrink-0">
            @php
                $groups = config('settingsubitems', []);
                $currentRoute = request()->route()?->getName();
            @endphp

            @foreach ($groups as $group)
                <div class="mb-8">
                    <h4 class="mb-3 font-semibold text-gray-400 text-xs uppercase">
                        {{ $group['group'] }}
                    </h4>

                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php
                                // expects keys like: profile, password, preferences
                                $routeName = 'settings.' . $item['key'];
                                $isActive = $currentRoute === $routeName;
                            @endphp

                            <a href="{{ route($routeName) }}" wire:navigate
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg
                                       text-sm transition
                                       {{ $isActive ? 'bg-white/10 text-white' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                                @if (!empty($item['icon']))
                                    <x-dynamic-component :component="$item['icon']" class="w-5 h-5" />
                                @endif

                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </aside>

        {{-- RIGHT CONTENT --}}
        <main
            class="flex-1 bg-gradient-to-b from-[#151a20] to-[#0f1115] p-8 border border-white/5 rounded-2xl">
            {{ $slot }}
        </main>

    </div>
</div>
