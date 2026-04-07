<?php

namespace App\Http\Controllers;

use Twilio\Jwt\AccessToken;
use Illuminate\Http\Request;
use Twilio\Rest\Client as TwilioClient;
use Twilio\TwiML\VoiceResponse;
use Twilio\Jwt\Grants\VoiceGrant;
use App\Models\VoiceSetting;

class TwilioController extends Controller
{
  //

  public function getAccessToken(Request $request)
  {
    $user = auth()->user();
    $identity = $user ? "user_" . $user->id : "guest";

    $token = new AccessToken(
      config("services.twilio.sid"), // Account SID (AC…)
      config("services.twilio.key"), // API Key (SK…)
      config("services.twilio.secret"), // API Secret
      3600, // 1 hour
      $identity
    );

    $voiceGrant = new VoiceGrant();
    $voiceGrant->setOutgoingApplicationSid(
      config("services.twilio.twiml_app_sid")
    );

    // Optional: allow incoming calls
    $voiceGrant->setIncomingAllow(true);

    $token->addGrant($voiceGrant);

    return response()->json([
      "identity" => $identity,
      "token" => $token->toJWT(),
    ]);
  }

  /**
   * Handle incoming/outbound call routing from Twilio webhook
   */

  public function handleCallRouting(Request $request)
  {
    \Log::info('[Twilio] handleCallRouting', $request->all());

    $to         = $request->input('To', '');
    $from       = $request->input('From', '');
    $callSid    = $request->input('CallSid');
    $agentParam = $request->input('agent');

    $callbackUrl   = rtrim(config('app.url'), '/') . '/api/call/complete';
    $voiceResponse = new VoiceResponse();
    $vs            = VoiceSetting::instance();

    if (!empty($to) && $to === config('services.twilio.phone_number')) {
      // ── Inbound: someone rang our Twilio number ────────────────────────
      $availableAgents = \App\Models\AgentStatus::with(['statusType', 'user'])
        ->whereHas('statusType', fn($q) => $q->where('is_available', true))
        ->get();

      $campaign = \App\Models\Campaign::where('phone_number', $to)->first();

      \App\Models\Conversation::firstOrCreate(
        ['call_sid' => $callSid],
        [
          'channel'       => 'voice',
          'direction'     => 'inbound',
          'status'        => 'in_progress',
          'contact_phone' => $from,
          'campaign_id'   => $campaign?->id,
          'started_at'    => now(),
        ]
      );

      if ($availableAgents->isEmpty()) {
        $voiceResponse->say($vs->tts_no_answer, [
          'voice'    => $vs->tts_voice,
          'language' => $vs->tts_language,
        ]);
      } else {
        // Optional greeting while agents ring
        if (!empty($vs->greeting_message)) {
          $voiceResponse->say($vs->greeting_message, [
            'voice'    => $vs->tts_voice,
            'language' => $vs->tts_language,
          ]);
        }
        $dialAttrs = [
          'callerId' => config('services.twilio.caller_id'),
          'timeout'  => $vs->inbound_timeout,
          'action'   => $callbackUrl,
          'method'   => 'POST',
        ];
        if ($vs->recording_enabled) {
          $dialAttrs['record']            = 'record-from-answer';
          $dialAttrs['recordingChannels'] = $vs->recording_channels;
        }
        $dial = $voiceResponse->dial('', $dialAttrs);
        foreach ($availableAgents as $status) {
          $dial->client()->identity('user_' . $status->user_id);
        }
      }

    } elseif (!empty($to)) {
      // ── Outbound: browser client dialling a number/agent ──────────────
      $userId = null;
      if ($agentParam && preg_match('/^(?:agent|user)_(\d+)$/', $agentParam, $m)) {
        $userId = (int) $m[1];
      }

      $campaign = null;
      if ($userId) {
        $campaign = \App\Models\Campaign::whereHas('users', fn($q) => $q->where('users.id', $userId))
          ->where('is_active', true)
          ->first();
      }

      \App\Models\Conversation::firstOrCreate(
        ['call_sid' => $callSid],
        [
          'channel'       => 'voice',
          'direction'     => 'outbound',
          'status'        => 'in_progress',
          'contact_phone' => $to,
          'campaign_id'   => $campaign?->id,
          'assigned_to'   => $userId,
          'started_at'    => now(),
        ]
      );

      $dialAttrs = [
        'callerId' => config('services.twilio.caller_id'),
        'action'   => $callbackUrl,
        'method'   => 'POST',
      ];
      if ($vs->recording_enabled) {
        $dialAttrs['record']            = 'record-from-answer';
        $dialAttrs['recordingChannels'] = $vs->recording_channels;
      }
      $dial = $voiceResponse->dial('', $dialAttrs);

      if (preg_match('/^[\d\+\-\(\) ]+$/', $to)) {
        $dial->number($to);
      } else {
        $dial->client()->identity($to);
      }

    } else {
      $voiceResponse->say('Sorry, no destination was provided.');
    }

    \Log::info('[Twilio] TwiML response', ['xml' => $voiceResponse->__toString()]);

    return response($voiceResponse->__toString(), 200)
      ->header('Content-Type', 'text/xml');
  }

  /**
   * Twilio posts here when the Dial leg finishes.
   * Updates the conversation with final status and duration.
   * POST /api/call/complete  (no CSRF – Twilio webhook)
   */
  public function callComplete(Request $request)
  {
    \Log::info('[Twilio] callComplete', $request->all());

    $callSid      = $request->input('CallSid');
    $dialStatus   = $request->input('DialCallStatus', 'completed');
    $dialDuration = (int) $request->input('DialCallDuration', 0);

    $statusMap = [
      'completed' => 'completed',
      'busy'      => 'abandoned',
      'no-answer' => 'abandoned',
      'failed'    => 'abandoned',
      'canceled'  => 'abandoned',
    ];

    $dialTo      = $request->input('DialTo', '');
    $agentUserId = null;
    if (preg_match('/^client:(?:agent|user)_(\d+)$/', $dialTo, $m)) {
      $agentUserId = (int) $m[1];
    }

    $update = [
      'status'           => $statusMap[$dialStatus] ?? 'completed',
      'duration_seconds' => $dialDuration ?: null,
      'ended_at'         => now(),
    ];

    if ($agentUserId) {
      $update['assigned_to']  = $agentUserId;
      $update['completed_by'] = $agentUserId;
    }

    \App\Models\Conversation::where('call_sid', $callSid)->update($update);

    $vs    = VoiceSetting::instance();
    $voice = new VoiceResponse();

    $ttsMap = [
      'completed' => $vs->tts_completed,
      'busy'      => $vs->tts_busy,
      'no-answer' => $vs->tts_no_answer,
      'failed'    => $vs->tts_failed,
      'canceled'  => $vs->tts_canceled,
    ];

    $message = $ttsMap[$dialStatus] ?? $vs->tts_completed;
    $voice->say($message, ['voice' => $vs->tts_voice, 'language' => $vs->tts_language]);
    $voice->hangup();

    return response($voice->__toString(), 200)
      ->header('Content-Type', 'text/xml');
  }

  /**
   * Mute / unmute — handled entirely client-side via Twilio JS SDK.
   * This endpoint exists for completeness / server-side audit logging.
   */
  public function muteCall(Request $request)
  {
    $request->validate(['call_sid' => 'required|string|max:64', 'muted' => 'required|boolean']);
    // Actual muting is done in the browser SDK; nothing to do server-side.
    return response()->json(['success' => true]);
  }

  /**
   * Put a call on hold — redirects the caller's leg to TwiML that plays
   * hold music on a loop.
   */
  public function holdCall(Request $request)
  {
    $request->validate(['call_sid' => 'required|string|max:64']);

    $client = $this->twilioClient();
    $voice  = new VoiceResponse();
    $voice->play(VoiceSetting::instance()->hold_music_url, ['loop' => 0]);

    $client->calls($request->call_sid)
      ->update(['twiml' => $voice->__toString()]);

    return response()->json(['success' => true]);
  }

  /**
   * Resume a call that is on hold — redirects back to the call-routing webhook
   * so the live agent can be reconnected.
   */
  public function resumeCall(Request $request)
  {
    $request->validate(['call_sid' => 'required|string|max:64']);

    $client = $this->twilioClient();
    $client->calls($request->call_sid)
      ->update(['url' => route('twilio.handleCallRouting'), 'method' => 'POST']);

    return response()->json(['success' => true]);
  }

  /**
   * Blind (cold) transfer — redirect the call to a new destination and
   * disconnect the current agent leg.
   *
   * POST /api/call/transfer
   * { call_sid: "CA…", to: "+15551234567" | "user_42" }
   */
  public function transferCall(Request $request)
  {
    $request->validate([
      'call_sid' => 'required|string|max:64',
      'to'       => 'required|string|max:64',
    ]);

    $to     = $request->to;
    $client = $this->twilioClient();

    // Build TwiML that dials the transfer target
    $voice = new VoiceResponse();
    $dial  = $voice->dial('', ['callerId' => config('services.twilio.caller_id')]);

    if (preg_match('/^[\d\+\-\(\) ]+$/', $to)) {
      $dial->number($to);
    } else {
      $dial->client($to);
    }

    $client->calls($request->call_sid)
      ->update(['twiml' => $voice->__toString()]);

    return response()->json(['success' => true]);
  }

  /**
   * Forward all inbound calls to an external number.
   * Reads the calling agent's (or a fallback global) forward_to number.
   *
   * GET /api/call/forward-twiml?user_id=42
   */
  public function forwardTwiml(Request $request)
  {
    // Try per-user forwarding number first (passed as query param from routing)
    $forwardTo = null;
    $userId    = $request->query('user_id');
    if ($userId) {
      $user = \App\Models\User::find($userId);
      if ($user && (bool) $user->getMeta('call_forwarding_enabled', false)) {
        $forwardTo = (string) $user->getMeta('call_forward_to', '');
      }
    }

    // Fall back to global env value
    if (empty($forwardTo)) {
      $forwardTo = config('services.twilio.forward_to');
    }

    $voice = new VoiceResponse();

    if (empty($forwardTo)) {
      $voice->say('Call forwarding is not configured. Please try again later.');
    } else {
      $dial = $voice->dial('', ['callerId' => config('services.twilio.caller_id')]);
      if (preg_match('/^[\d\+\-\(\) ]+$/', $forwardTo)) {
        $dial->number($forwardTo);
      } else {
        $dial->client($forwardTo);
      }
    }

    return response($voice->__toString(), 200)->header('Content-Type', 'text/xml');
  }

  /**
   * Return online agents that can receive a transferred call.
   * Used to populate the transfer modal's agent list.
   */
  public function availableAgents()
  {
    $agents = \App\Models\AgentStatus::with(['statusType', 'user'])
      ->whereHas('statusType', fn($q) => $q->where('slug', 'phones'))
      ->get()
      ->map(fn($s) => [
        'id'       => $s->user_id,
        'name'     => trim($s->user->first_name . ' ' . $s->user->last_name),
        'identity' => 'user_' . $s->user_id,
      ]);

    return response()->json($agents);
  }

  // ── helpers ──────────────────────────────────────────────────────

  private function twilioClient(): TwilioClient
  {
    return new TwilioClient(
      config('services.twilio.key'),    // API Key SID  (SK…)
      config('services.twilio.secret'), // API Secret
      config('services.twilio.sid')     // Account SID  (AC…)
    );
  }
}
