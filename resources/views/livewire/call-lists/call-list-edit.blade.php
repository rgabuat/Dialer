<div class="min-h-screen bg-surface-4 text-fg p-6">

    <div class="mb-8 flex items-center justify-between">
        <div>
            <div class="flex items-center gap-2 text-sm text-fg-muted mb-1">
                <a href="{{ route('campaign.edit', $callList->campaign) }}" wire:navigate class="hover:text-fg transition">{{ $callList->campaign->name }}</a>
                <span>/</span>
                <a href="{{ route('campaign.lists', $callList->campaign) }}" wire:navigate class="hover:text-fg transition">Call Lists</a>
                <span>/</span>
                <span>{{ $callList->name }}</span>
            </div>
            <h1 class="text-2xl font-semibold tracking-tight">{{ $callList->name }}</h1>
            <p class="text-sm text-fg-muted">{{ $callList->leads()->count() }} leads in this list</p>
        </div>
        @can('call-list.delete')
            <button wire:click="confirmDelete"
                class="px-4 py-2 rounded-md bg-red-600/20 hover:bg-red-600/40 text-accent-red text-sm font-medium transition">
                Delete List
            </button>
        @endcan
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-md bg-green-500/10 border border-green-500/20 px-4 py-3 text-sm text-accent-green">
            {{ session('success') }}
        </div>
    @endif

    @if ($importResult)
        <div class="mb-6 rounded-md bg-blue-500/10 border border-blue-500/20 px-4 py-3 text-sm text-blue-400">
            {{ $importResult }}
        </div>
    @endif

    @if ($confirmingDelete)
        <div class="mb-6 rounded-md bg-red-500/10 border border-red-500/20 px-4 py-4 flex items-center justify-between gap-4">
            <p class="text-sm text-accent-red">Delete <strong>{{ $callList->name }}</strong> and all its leads? This cannot be undone.</p>
            <div class="flex gap-3 shrink-0">
                <button wire:click="delete" class="px-3 py-1.5 rounded-md bg-red-600 hover:bg-red-500 text-white text-sm font-medium transition">Yes, delete</button>
                <button wire:click="$set('confirmingDelete', false)" class="px-3 py-1.5 rounded-md bg-zinc-700 hover:bg-zinc-600 text-fg-3 text-sm font-medium transition">Cancel</button>
            </div>
        </div>
    @endif

    <div class="max-w-4xl space-y-10">

        {{-- Settings --}}
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
                <div>
                    <h2 class="font-medium">Settings</h2>
                    <p class="text-sm text-fg-muted">List configuration.</p>
                </div>
                <div class="md:col-span-3 space-y-6">
                    <div>
                        <label class="text-sm text-fg-muted">Name</label>
                        <input wire:model.defer="name" type="text"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        @error('name') <p class="text-xs text-accent-red mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm text-fg-muted">Description</label>
                        <textarea wire:model.defer="description" rows="2"
                            class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-fg-muted">Timezone</label>
                            <input wire:model.defer="timezone" type="text"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        </div>
                        <div>
                            <label class="text-sm text-fg-muted">Sort Order</label>
                            <input wire:model.defer="sort_order" type="number" min="0"
                                class="mt-1 w-full rounded-md bg-surface border border-surface px-3 py-2 text-sm focus:ring-1 focus:ring-zinc-600" />
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <input wire:model.defer="is_active" type="checkbox" id="is_active"
                            class="rounded bg-surface border-surface-2 text-blue-500 focus:ring-blue-500" />
                        <label for="is_active" class="text-sm text-fg-muted">Active</label>
                    </div>
                    <button type="submit"
                        class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium transition">
                        Save Changes
                    </button>
                </div>
            </div>
        </form>

        {{-- CSV Import --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 border-b border-surface pb-10">
            <div>
                <h2 class="font-medium">Import Leads</h2>
                <p class="text-sm text-fg-muted">Upload a CSV file. Required columns: <code class="text-xs">phone</code>. Optional: <code class="text-xs">first_name, last_name, email, address, city, state, zip, country, timezone</code></p>
            </div>
            <div class="md:col-span-3 space-y-4">
                <div>
                    <input wire:model="csvFile" type="file" accept=".csv,.txt"
                        class="block w-full text-sm text-fg-muted file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-surface-2 file:text-fg hover:file:bg-hover" />
                    @error('csvFile') <p class="text-xs text-accent-red mt-1">{{ $message }}</p> @enderror
                </div>
                <button wire:click="importCsv" type="button"
                    class="px-4 py-2 rounded-md bg-surface-2 hover:bg-surface text-fg text-sm font-medium transition">
                    Import CSV
                </button>
            </div>
        </div>

        {{-- Leads table --}}
        <div>
            <h2 class="font-medium mb-4">Leads ({{ $leads->total() }})</h2>
            <div class="bg-surface border border-surface rounded-xl [overflow:clip]">
                <div class="overflow-auto">
                    <table class="min-w-full text-fg text-sm">
                        <thead class="bg-surface">
                            <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                                <th class="px-5 py-3 text-left">Name</th>
                                <th class="px-5 py-3 text-left">Phone</th>
                                <th class="px-5 py-3 text-left">Status</th>
                                <th class="px-5 py-3 text-right">Calls</th>
                                <th class="px-5 py-3 text-left">Last Called</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($leads as $lead)
                                <tr class="hover:bg-hover border-surface border-b transition">
                                    <td class="px-5 py-3 font-medium">{{ $lead->first_name }} {{ $lead->last_name }}</td>
                                    <td class="px-5 py-3 font-mono text-fg-muted">{{ $lead->phone ?? '—' }}</td>
                                    <td class="px-5 py-3">
                                        <span class="inline-block px-2 py-0.5 rounded text-xs font-bold uppercase
                                            {{ in_array($lead->status, ['SALE']) ? 'bg-green-500/10 text-accent-green' :
                                               ($lead->status === 'DNC' ? 'bg-red-500/10 text-accent-red' :
                                               ($lead->status === 'NEW' ? 'bg-blue-500/10 text-blue-400' : 'bg-surface-2 text-fg-muted')) }}">
                                            {{ $lead->status }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-right font-mono text-fg-muted">{{ $lead->call_count }}</td>
                                    <td class="px-5 py-3 text-fg-muted text-xs">
                                        {{ $lead->last_called_at ? $lead->last_called_at->diffForHumans() : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-12 text-center text-zinc-500 italic">No leads yet. Import a CSV file to get started.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-table-pagination :paginator="$leads" label="leads" />
            </div>
        </div>

    </div>
</div>
