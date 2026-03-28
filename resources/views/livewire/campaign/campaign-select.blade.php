<x-layouts.guest title="Select Campaign — csrpro">
    <main class="container main-content w-full max-w-screen-lg mx-auto px-4 sm:px-6 lg:px-8">
        <x-card class="max-w-md mx-auto bg-gradient-to-b from-[#151a20] to-[#0f1115] border border-white/5">
            <div class="p-8 md:p-10">
                <div class="text-center mb-6">
                    <x-brand-logo />
                    <p class="text-white font-semibold mt-2">Select your campaign</p>
                    <p class="text-zinc-400 text-sm mt-1">Choose the campaign you'll be working on this session.</p>
                </div>

                @if ($campaigns->isEmpty())
                    <p class="text-center text-zinc-500 text-sm py-4">
                        No campaigns have been assigned to your account. Please contact your administrator.
                    </p>
                @else
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-zinc-400 text-sm">Campaign</label>
                            <select
                                wire:model="campaignId"
                                class="w-full rounded bg-surface border border-surface px-3 py-2 text-sm text-white focus:outline-none focus:ring focus:ring-blue-500/20"
                            >
                                <option value="">— select a campaign —</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($campaignId)
                            @php $selected = $campaigns->find($campaignId) @endphp
                            @if ($selected)
                                <div class="rounded-lg bg-surface border border-surface px-4 py-3 text-sm text-zinc-300 space-y-1">
                                    <div class="font-medium text-white">{{ $selected->name }}</div>
                                    @if ($selected->description)
                                        <div class="text-zinc-400 text-xs">{{ $selected->description }}</div>
                                    @endif
                                    <div class="text-zinc-500 text-xs">{{ $selected->phone_number }}</div>
                                </div>
                            @endif
                        @endif

                        <x-button wire:click="select" text="Continue" variant="primary" type="button" />
                    </div>
                @endif
            </div>
        </x-card>
    </main>
</x-layouts.guest>
