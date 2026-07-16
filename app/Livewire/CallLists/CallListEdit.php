<?php

namespace App\Livewire\CallLists;

use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use App\Models\CallList;
use App\Models\Lead;
use Illuminate\Support\Facades\Auth;
use League\Csv\Reader;

class CallListEdit extends Component
{
    use WithFileUploads;
    use WithPagination;

    public CallList $callList;

    public string $name = '';
    public string $description = '';
    public bool $is_active = true;
    public int $sort_order = 0;
    public string $timezone = 'UTC';

    // CSV import
    public $csvFile = null;
    public bool $importing = false;
    public string $importResult = '';

    public bool $confirmingDelete = false;

    public function mount(CallList $callList): void
    {
        $this->callList    = $callList;
        $this->name        = $callList->name;
        $this->description = $callList->description ?? '';
        $this->is_active   = $callList->is_active;
        $this->sort_order  = $callList->sort_order;
        $this->timezone    = $callList->timezone;
    }

    public function save(): void
    {
        $this->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
            'sort_order'  => ['integer', 'min:0'],
            'timezone'    => ['required', 'string', 'max:100'],
        ]);

        $this->callList->update([
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'is_active'   => $this->is_active,
            'sort_order'  => $this->sort_order,
            'timezone'    => $this->timezone,
        ]);

        session()->flash('success', 'Call list updated.');
    }

    public function importCsv(): void
    {
        $this->validate([
            'csvFile' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $path = $this->csvFile->getRealPath();

        $csv = Reader::createFromPath($path, 'r');
        $csv->setHeaderOffset(0);

        $imported = 0;
        $userId   = Auth::id();

        foreach ($csv->getRecords() as $row) {
            // Accept flexible column names
            $phone = trim($row['phone'] ?? $row['Phone'] ?? $row['phone_number'] ?? '');
            $firstName = trim($row['first_name'] ?? $row['FirstName'] ?? $row['first'] ?? '');
            $lastName  = trim($row['last_name'] ?? $row['LastName'] ?? $row['last'] ?? '');

            if (empty($phone) && empty($firstName)) {
                continue;
            }

            Lead::create([
                'call_list_id'    => $this->callList->id,
                'campaign_id'     => $this->callList->campaign_id,
                'store_id'        => $this->callList->campaign->userGroups()->first()?->id ?? 1,
                'first_name'      => $firstName ?: 'Unknown',
                'last_name'       => $lastName ?: '',
                'phone'           => $phone,
                'email'           => trim($row['email'] ?? $row['Email'] ?? ''),
                'address'         => trim($row['address'] ?? ''),
                'city'            => trim($row['city'] ?? ''),
                'state'           => trim($row['state'] ?? ''),
                'zip'             => trim($row['zip'] ?? $row['postal_code'] ?? ''),
                'country'         => trim($row['country'] ?? ''),
                'timezone'        => trim($row['timezone'] ?? '') ?: $this->callList->timezone,
                'status'          => 'NEW',
                'created_by'      => $userId,
                'last_actioned_by'=> $userId,
            ]);

            $imported++;
        }

        $this->csvFile      = null;
        $this->importResult = "Imported {$imported} leads.";
        $this->resetPage();
    }

    public function confirmDelete(): void
    {
        $this->confirmingDelete = true;
    }

    public function delete(): void
    {
        $campaign = $this->callList->campaign;
        $this->callList->delete();
        $this->redirect(route('campaign.lists', $campaign), navigate: true);
    }

    public function render()
    {
        $leads = $this->callList->leads()
            ->latest()
            ->paginate(20);

        return view('livewire.call-lists.call-list-edit', compact('leads'))
            ->layout('components.layouts.app');
    }
}
