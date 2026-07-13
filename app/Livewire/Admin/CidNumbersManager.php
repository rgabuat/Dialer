<?php

namespace App\Livewire\Admin;

use App\Models\CidGroup;
use App\Models\CidNumber;
use App\Services\TwilioService;
use Livewire\Component;

class CidNumbersManager extends Component
{
    public array   $twilioNumbers = [];
    public array   $importedMap   = [];   // phone_number => cid_numbers.id
    public array   $importedSidMap = [];  // phone_number => twilio_sid
    public array   $pendingRows   = [];

    public ?string $error   = null;
    public ?string $success = null;

    // Filter
    public string $tab = 'all'; // 'all' | 'imported' | 'twilio'

    public function mount(): void
    {
        $this->loadAll();
    }

    public function loadAll(): void
    {
        $this->error   = null;
        $this->success = null;
        $this->loadImported();
        $this->loadTwilio();
    }

    private function loadImported(): void
    {
        $this->importedMap    = [];
        $this->importedSidMap = [];
        foreach (CidNumber::all(['id', 'phone_number', 'twilio_sid']) as $cid) {
            $this->importedMap[$cid->phone_number]    = $cid->id;
            $this->importedSidMap[$cid->phone_number] = $cid->twilio_sid ?? '';
        }
    }

    private function loadTwilio(): void
    {
        try {
            $this->twilioNumbers = app(TwilioService::class)->getPhoneNumbers();
        } catch (\Throwable $e) {
            $this->twilioNumbers = [];
            $this->error = 'Could not reach Twilio API: ' . $e->getMessage();
        }
    }

    public function refresh(): void
    {
        $this->loadAll();
        $this->success = 'Refreshed from Twilio.';
    }

    // ── Import a Twilio number ─────────────────────────────────────────────

    public function import(string $sid, string $phoneNumber, string $friendlyName): void
    {
        $this->error   = null;
        $this->success = null;
        $this->pendingRows[$phoneNumber] = true;

        if (isset($this->importedMap[$phoneNumber])) {
            $this->pendingRows[$phoneNumber] = false;
            $this->error = "{$phoneNumber} is already imported.";
            return;
        }

        try {
            $cid = CidNumber::create([
                'phone_number'  => $phoneNumber,
                'twilio_sid'    => $sid,
                'friendly_name' => $friendlyName,
                'is_active'     => true,
            ]);

            app(TwilioService::class)->syncVoiceWebhook($sid);

            $this->importedMap[$phoneNumber]    = $cid->id;
            $this->importedSidMap[$phoneNumber] = $sid;
            $this->success = "{$phoneNumber} imported and webhook synced.";
        } catch (\Throwable $e) {
            $this->error = 'Import failed: ' . $e->getMessage();
        }

        $this->pendingRows[$phoneNumber] = false;
    }

    // ── Sync webhook ──────────────────────────────────────────────────────

    public function syncWebhook(string $sid, string $phoneNumber): void
    {
        $this->error   = null;
        $this->success = null;
        $this->pendingRows[$phoneNumber] = true;

        try {
            app(TwilioService::class)->syncVoiceWebhook($sid);

            if (isset($this->importedMap[$phoneNumber])) {
                CidNumber::where('id', $this->importedMap[$phoneNumber])
                    ->update(['twilio_sid' => $sid]);
            }

            $this->success = "Webhook synced for {$phoneNumber}.";
        } catch (\Throwable $e) {
            $this->error = 'Sync failed: ' . $e->getMessage();
        }

        $this->pendingRows[$phoneNumber] = false;
    }

    // ── Local CID number toggles ──────────────────────────────────────────

    public function toggleActive(int $id): void
    {
        $cid = CidNumber::find($id);
        if (!$cid) return;
        $cid->update(['is_active' => !$cid->is_active]);
        $this->success = "{$cid->phone_number} marked " . ($cid->is_active ? 'active' : 'inactive') . '.';
    }

    public function toggleRotation(int $id): void
    {
        $cid = CidNumber::find($id);
        if (!$cid) return;
        $cid->update(['in_rotation' => !$cid->in_rotation]);
        $this->success = "{$cid->phone_number} " . ($cid->in_rotation ? 'added to' : 'removed from') . ' rotation pool.';
    }

    public function remove(int $id): void
    {
        $cid = CidNumber::find($id);
        if (!$cid) return;
        $num = $cid->phone_number;
        $cid->delete();
        unset($this->importedMap[$num], $this->importedSidMap[$num]);
        $this->success = "{$num} removed from local CID pool.";
    }

    public function render()
    {
        return view('livewire.admin.cid-numbers-manager', [
            'importedCids' => CidNumber::with('cidGroup:id,name')
                ->orderBy('phone_number')
                ->get(),
            'allGroups'    => CidGroup::orderBy('name')->get(['id', 'name']),
            'webhookBase'  => rtrim(config('app.url'), '/') . '/api/call-routing',
        ])->layout('components.layouts.admin', ['heading' => 'CID Numbers']);
    }
}
