<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use App\Models\CidNumber;
use App\Services\TwilioService;

class CidNumbersIndex extends Component
{
    /** @var array<int, array> Numbers fetched from Twilio */
    public array $numbers = [];

    /** @var array<string, int> phone_number => cid_numbers.id */
    public array $importedMap = [];

    /** @var array<string, string> phone_number => twilio_sid */
    public array $importedSidMap = [];

    public ?string $errorMessage   = null;
    public ?string $successMessage = null;
    public array   $pendingRows    = [];

    public function mount(): void
    {
        $this->refresh();
    }

    public function refresh(): void
    {
        $this->errorMessage   = null;
        $this->successMessage = null;
        $this->loadNumbers();
    }

    private function loadNumbers(): void
    {
        $this->importedMap    = [];
        $this->importedSidMap = [];

        foreach (CidNumber::all(['id', 'phone_number', 'twilio_sid']) as $cid) {
            $this->importedMap[$cid->phone_number]    = $cid->id;
            $this->importedSidMap[$cid->phone_number] = $cid->twilio_sid ?? '';
        }

        try {
            $this->numbers = app(TwilioService::class)->getPhoneNumbers();
        } catch (\Throwable $e) {
            $this->numbers      = [];
            $this->errorMessage = 'Could not reach Twilio API: ' . $e->getMessage();
        }
    }

    /**
     * Import a Twilio number as a CID Number and sync its inbound webhook.
     */
    public function import(string $sid, string $phoneNumber, string $friendlyName): void
    {
        $this->successMessage = null;
        $this->errorMessage   = null;
        $this->pendingRows[$phoneNumber] = true;

        if (isset($this->importedMap[$phoneNumber])) {
            $this->pendingRows[$phoneNumber] = false;
            $this->errorMessage = "{$phoneNumber} is already imported.";
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
            $this->successMessage = "{$phoneNumber} added to CID pool and webhook synced.";
        } catch (\Throwable $e) {
            $this->errorMessage = 'Import failed: ' . $e->getMessage();
        }

        $this->pendingRows[$phoneNumber] = false;
    }

    /**
     * Sync the Twilio voice webhook for an already-imported CID.
     */
    public function syncWebhook(string $sid, string $phoneNumber): void
    {
        $this->successMessage = null;
        $this->errorMessage   = null;
        $this->pendingRows[$phoneNumber] = true;

        try {
            app(TwilioService::class)->syncVoiceWebhook($sid);

            if (isset($this->importedMap[$phoneNumber])) {
                CidNumber::where('id', $this->importedMap[$phoneNumber])
                    ->update(['twilio_sid' => $sid]);
            }

            $this->successMessage = "Webhook synced for {$phoneNumber}.";
        } catch (\Throwable $e) {
            $this->errorMessage = 'Sync failed: ' . $e->getMessage();
        }

        $this->pendingRows[$phoneNumber] = false;
    }

    /**
     * Toggle the is_active flag for an imported CID.
     */
    public function toggleActive(int $id): void
    {
        $cid = CidNumber::find($id);
        if (!$cid) return;
        $cid->update(['is_active' => !$cid->is_active]);
        $this->successMessage = "{$cid->phone_number} marked " . ($cid->is_active ? 'active' : 'inactive') . '.';
    }

    /**
     * Toggle the in_rotation flag for an imported CID.
     */
    public function toggleRotation(int $id): void
    {
        $cid = CidNumber::find($id);
        if (!$cid) return;
        $cid->update(['in_rotation' => !$cid->in_rotation]);
        $this->successMessage = "{$cid->phone_number} " . ($cid->in_rotation ? 'added to' : 'removed from') . ' rotation pool.';
    }

    public function render()
    {
        return view('livewire.dids.cid-numbers-index', [
            'ourWebhookBase' => rtrim(config('app.url'), '/') . '/api/call-routing',
            'importedCids'   => CidNumber::with('cidGroup')->orderBy('phone_number')->get(),
        ])->layout('components.layouts.app');
    }
}
