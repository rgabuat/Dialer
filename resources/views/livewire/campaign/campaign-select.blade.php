<x-layouts.guest title="Select Campaign — csrpro">
    <main class="mx-auto px-4 sm:px-6 lg:px-8 w-full max-w-screen-lg container main-content">
        <x-card class="bg-gradient-to-b from-[#151a20] to-[#0f1115] mx-auto border border-white/5 max-w-md">
            <div class="p-8 md:p-10">
                <div class="mb-6 text-center">
                    <x-brand-logo />
                    <p class="mt-2 font-semibold text-fg">Select your campaign</p>
                    <p class="mt-1 text-fg-muted text-sm">Choose the campaign you'll be working on this session.</p>
                </div>

                @if ($campaigns->isEmpty())
                    <p class="py-4 text-zinc-500 text-sm text-center">
                        No campaigns have been assigned to your account. Please contact your administrator.
                    </p>
                @else
                    <div class="flex flex-col gap-4">
                        <div class="flex flex-col gap-2">
                            <label class="text-fg-muted text-sm">Campaign</label>
                            <select wire:model="campaignId"
                                class="bg-surface px-3 py-2 border border-surface rounded focus:outline-none focus:ring focus:ring-blue-500/20 w-full text-fg text-sm">
                                <option value="">— select a campaign —</option>
                                @foreach ($campaigns as $campaign)
                                    <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if ($campaignId)
                            @php $selected = $campaigns->find($campaignId) @endphp
                            @if ($selected)
                                <div
                                    class="space-y-1 bg-surface px-4 py-3 border border-surface rounded-lg text-zinc-300 text-sm">
                                    <div class="font-medium text-fg">{{ $selected->name }}</div>
                                    @if ($selected->description)
                                        <div class="text-fg-muted text-xs">{{ $selected->description }}</div>
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
