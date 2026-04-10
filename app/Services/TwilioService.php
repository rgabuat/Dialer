<?php

namespace App\Services;

use Twilio\Rest\Client as TwilioClient;

class TwilioService
{
    private function client(): TwilioClient
    {
        return new TwilioClient(
            config('services.twilio.key'),    // API Key SID  (SK…)
            config('services.twilio.secret'), // API Secret
            config('services.twilio.sid')     // Account SID  (AC…)
        );
    }

    /**
     * Fetch all incoming phone numbers provisioned on the Twilio account.
     * Returns an array of plain objects with normalised fields.
     */
    public function getPhoneNumbers(): array
    {
        $numbers = $this->client()->incomingPhoneNumbers->read([], 1000);

        return array_map(function ($n) {
            return [
                'sid'           => $n->sid,
                'phone_number'  => $n->phoneNumber,
                'friendly_name' => $n->friendlyName,
                'voice_url'     => $n->voiceUrl ?? '',
                'voice_method'  => $n->voiceMethod ?? 'POST',
                'capabilities'  => (array) $n->capabilities,
            ];
        }, $numbers);
    }

    /**
     * Point a Twilio phone number's inbound voice webhook at our call-routing endpoint.
     */
    public function syncVoiceWebhook(string $twilioSid): void
    {
        $voiceUrl = rtrim(config('app.url'), '/') . '/api/call-routing';

        $this->client()
            ->incomingPhoneNumbers($twilioSid)
            ->update([
                'voiceUrl'    => $voiceUrl,
                'voiceMethod' => 'POST',
            ]);
    }
}
