@extends('admin.layouts.app', ['heading' => 'Dashboard'])

@section('content')
    @php use Illuminate\Support\Number; @endphp

    <div class="space-y-6">

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $cards = [
                    [
                        'label' => 'Total Users',
                        'value' => $stats['total_users'],
                        'icon' => 'users',
                        'color' => 'indigo',
                    ],
                    [
                        'label' => 'Total Campaigns',
                        'value' => $stats['total_campaigns'],
                        'icon' => 'campaign',
                        'color' => 'violet',
                    ],
                    [
                        'label' => 'Active Campaigns',
                        'value' => $stats['active_campaigns'],
                        'icon' => 'active',
                        'color' => 'green',
                    ],
                    ['label' => 'Total Calls', 'value' => $stats['total_calls'], 'icon' => 'phone', 'color' => 'sky'],
                ];
            @endphp

            @foreach ($cards as $card)
                <div class="bg-surface border border-surface rounded-xl px-5 py-4 flex items-center gap-4">
                    <span @class([
                        'inline-flex justify-center items-center rounded-xl w-10 h-10 shrink-0',
                        'bg-indigo-500/15' => $card['color'] === 'indigo',
                        'bg-violet-500/15' => $card['color'] === 'violet',
                        'bg-green-500/15' => $card['color'] === 'green',
                        'bg-sky-500/15' => $card['color'] === 'sky',
                    ])>
                        @if ($card['icon'] === 'users')
                            <svg class="w-5 h-5 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        @elseif ($card['icon'] === 'campaign')
                            <svg class="w-5 h-5 text-violet-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3c1.167 0 2.301.186 3.353.54m-3.353-.54c.194 3.63.126 7.26-.204 10.845" />
                            </svg>
                        @elseif ($card['icon'] === 'active')
                            <svg class="w-5 h-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 6.75Z" />
                            </svg>
                        @endif
                    </span>
                    <div>
                        <p class="text-2xl font-bold text-fg">{{ Number::format($card['value']) }}</p>
                        <p class="text-xs text-fg-muted mt-0.5">{{ $card['label'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div
            class="bg-surface border border-surface rounded-xl px-6 py-10 flex flex-col items-center justify-center text-center">
            <span class="inline-flex justify-center items-center bg-indigo-500/10 rounded-2xl w-14 h-14 mb-4">
                <svg class="w-7 h-7 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l5.654-4.654m5.647-4.647 2.497-3.031a1.125 1.125 0 0 1 1.667-.218l2.22 2.221a1.125 1.125 0 0 1-.218 1.667l-3.03 2.497" />
                </svg>
            </span>
            <h2 class="font-semibold text-fg text-sm mb-1">Admin Panel — Work in Progress</h2>
            <p class="text-fg-muted text-xs max-w-xs">Configuration modules will be moved here one by one.</p>
        </div>

    </div>
@endsection
