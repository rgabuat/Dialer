<div class="space-y-4 p-6 stagger-children">

    {{-- Page title --}}
    <div>
        <h1 class="font-bold text-fg text-xl">Leads</h1>
        <p class="mt-0.5 text-zinc-500 text-sm">Browse and manage your leads across all stores.</p>
    </div>

    {{-- LEADS TABLE --}}
    <div class="bg-surface border border-surface rounded-xl [overflow:clip]">

        {{-- Header --}}
        <div class="flex sm:flex-row flex-col justify-between sm:items-center gap-3 px-5 py-4 border-surface border-b">
            <h2 class="font-bold text-fg text-base">Leads</h2>
            <a href="{{ route('lead.create') }}"
                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-500 px-4 py-2 rounded-md font-medium text-white text-sm transition">
                <x-heroicon-o-plus class="w-4 h-4" />
                New Lead
            </a>
        </div>

        {{-- Table --}}
        <div class="overflow-auto" x-data="{
            _fn: null,
            init() {
                this._fn = () => {
                    const pg = this.$el.nextElementSibling;
                    this.$el.style.maxHeight = (window.innerHeight - this.$el.getBoundingClientRect().top - (pg ? pg.offsetHeight : 57) - 8) + 'px';
                };
                this._fn();
                window.addEventListener('resize', this._fn);
            },
            destroy() { window.removeEventListener('resize', this._fn); }
        }">
            <table class="min-w-full text-fg text-sm stagger-rows">
                <thead class="top-0 z-10 sticky bg-surface">
                    <tr class="border-surface border-b font-semibold text-zinc-500 text-xs uppercase tracking-wider">
                        <th class="px-5 py-3 text-left">Name</th>
                        <th class="px-5 py-3 text-left">Phone</th>
                        <th class="px-5 py-3 text-left">Email</th>
                        <th class="px-5 py-3 text-left">Store</th>
                        <th class="px-5 py-3 text-left">Created By</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($leads as $lead)
                        <tr onclick="window.location='{{ route('lead.edit', $lead->id) }}'"
                            class="hover:bg-hover border-surface border-b transition cursor-pointer">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-fg">{{ $lead->first_name }} {{ $lead->last_name }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $lead->phone ?? '—' }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $lead->email ?? '—' }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $lead->store->name }}</td>
                            <td class="px-5 py-4 text-fg-muted text-sm">{{ $lead->creator->name }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-16 text-zinc-500 text-center italic">No leads found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <x-table-pagination :paginator="$leads" label="leads" />

    </div>

</div>
