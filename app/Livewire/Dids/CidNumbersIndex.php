<?php

namespace App\Livewire\Dids;

use Livewire\Component;
use App\Models\Did;
use App\Services\TwilioService;

class CidNumbersIndex extends Component
{
    /** @var array<int, array> Numbers fetched from Twilio */
    public array $numbers = [];

    /** @var array<string, int> phone_number => did.id map for quick lookup */
    public array $importedMap = [];

    /** @var array<string, string> phone_number => twilio_sid for already-imported DIDs */
    public array $importedSidMap = [];

    public bool $loading = false;
    public ?string $errorMessage = null;
    public ?string $successMessage = null;

    /** Tracks per-row pending state (phone_number => bool) */
    public array $pendingRows = [];

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
        // Build a map of all DIDs keyed by phone_number for quick lookup
        $this->importedMap    = [];
        $this->importedSidMap = [];

        foreach (Did::all(['id', 'phone_number', 'twilio_sid']) as $did) {
            $this->importedMap[$did->phone_number]    = $did->id;
            $this->importedSidMap[$did->phone_number] = $did->twilio_sid ?? '';
        }

        try {
            $service       = app(TwilioService::class);
            $this->numbers = $service->getPhoneNumbers();
        } catch (\Throwable $e) {
            $this->numbers      = [];
            $this->errorMessage = 'Could not reach Twilio API: ' . $e->getMessage();
        }
    }

    /**
     * Import a Twilio number as a DID and immediately sync its inbound webhook.
     */
    public function import(string $sid, string $phoneNumber, string $friendlyName): void
    {
        $this->successMessage = null;
        $this->errorMessage   = null;
        $this->pendingRows[$phoneNumber] = true;

        // Guard: already imported
        if (isset($this->importedMap[$phoneNumber])) {
            $this->pendingRows[$phoneNumber] = false;
            $this->errorMessage = "{$phoneNumber} is already imported as a DID.";
            return;
        }

        try {
            $did = Did::create([
                'phone_number' => $phoneNumber,
                'twilio_sid'   => $sid,
                'description'  => $friendlyName,
                'is_active'    => true,
            ]);

            // Sync webhook so Twilio routes calls to this app
            app(TwilioService::class)->syncVoiceWebhook($sid);

            $this->importedMap[$phoneNumber]    = $did->id;
            $this->importedSidMap[$phoneNumber] = $sid;
            $this->successMessage = "{$phoneNumber} imported as DID and webhook synced.";
        } catch (\Throwable $e) {
            $this->errorMessage = 'Import failed: ' . $e->getMessage();
        }

        $this->pendingRows[$phoneNumber] = false;
    }

    /**
     * Sync the Twilio voice webhook for an already-imported DID.
     */
    public function syncWebhook(string $sid, string $phoneNumber): void
    {
        $this->successMessage = null;
        $this->errorMessage   = null;
        $this->pendingRows[$phoneNumber] = true;

        try {
            app(TwilioService::class)->syncVoiceWebhook($sid);

            // Persist the SID on the DID record in case it was missing
            if (isset($this->importedMap[$phoneNumber])) {
                Did::where('id', $this->importedMap[$phoneNumber])
                    ->update(['twilio_sid' => $sid]);
            }

            $this->successMessage = "Webhook synced for {$phoneNumber}.";
        } catch (\Throwable $e) {
            $this->errorMessage = 'Sync failed: ' . $e->getMessage();
        }

        $this->pendingRows[$phoneNumber] = false;
    }

    public function render()
    {
        $ourWebhookBase = rtrim(config('app.url'), '/') . '/api/call-routing';

        return view('livewire.dids.cid-numbers-index', [
            'ourWebhookBase' => $ourWebhookBase,
        ])->layout('components.layouts.app');
    }
}
