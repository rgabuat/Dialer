<x-layouts.app>
<div class="h-screen w-full overflow-hidden">
    <div class="flex flex-col md:flex-row gap-6 h-full">

        {{-- LEFT SIDEBAR --}}
        <aside
            class="w-full md:w-72 shrink-0 rounded-2xl
                   bg-gradient-to-b from-[#151a20] to-[#0f1115]
                   border border-white/5 p-5
                   sticky top-0 h-screen
                   "
        >
            @php
                $groups = config('settingsubitems', []);
                $currentRoute = request()->route()?->getName();
            @endphp

            @foreach ($groups as $group)
                <div class="mb-8">
                    <h4 class="text-xs font-semibold uppercase text-gray-400 mb-3">
                        {{ $group['group'] }}
                    </h4>

                    <div class="space-y-1">
                        @foreach ($group['items'] as $item)
                            @php
                                // expects keys like: profile, password, preferences
                                $routeName = 'settings.' . $item['key'];
                                $isActive = $currentRoute === $routeName;
                            @endphp

                            <a
                                href="{{ route($routeName) }}"
                                wire:navigate
                                class="w-full flex items-center gap-3 px-3 py-2 rounded-lg
                                       text-sm transition
                                       {{ $isActive
                                            ? 'bg-white/10 text-white'
                                            : 'text-gray-400 hover:bg-white/5 hover:text-white'
                                       }}"
                            >
                                @if (!empty($item['icon']))
                                    <x-dynamic-component
                                        :component="$item['icon']"
                                        class="w-5 h-5"
                                    />
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
            class="flex-1 rounded-2xl
                   bg-gradient-to-b from-[#151a20] to-[#0f1115]
                   border border-white/5 p-8"
        >
            {{ $slot }}
        </main>

    </div>
</div>

</x-layouts.app>