<?php

namespace App\Http\Controllers;

use Twilio\Jwt\AccessToken;
use Illuminate\Http\Request;
use Twilio\Rest\Client as TwilioClient;
use Twilio\TwiML\VoiceResponse;
use Twilio\Jwt\Grants\VoiceGrant;

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
    //Get dialed number to call
    $dialedNumber = $request->input("To") ?? "+17865631232"; //Default to a test number if not provided

    //Setup instance of voice response
    $voiceResponse = new VoiceResponse();

    if ($dialedNumber != config("services.twilio.caller_id")) {
      $dial = $voiceResponse->dial("", [
        "callerId" => config("services.twilio.caller_id"),
      ]);

      if (preg_match('/^[\d\+\-\(\) ]+$/', $dialedNumber)) {
        // Standard outbound phone call to telephone number
        $dial->number($dialedNumber);
      } else {
        // Client-to-client (Agent → Agent) call
        $dial->client($dialedNumber);
      }
    } elseif ($dialedNumber == config("services.twilio.caller_id")) {
      // Incoming call from external number — ring only agents on "Phones" status
      $dial = $voiceResponse->dial("", [
        "callerId" => config("services.twilio.caller_id"),
      ]);

      $availableAgents = \App\Models\AgentStatus::with("statusType")
        ->whereHas("statusType", fn($q) => $q->where("slug", "phones"))
        ->get();

      if ($availableAgents->isEmpty()) {
        $voiceResponse->say(
          "All agents are currently unavailable. Please try again later."
        );
      } else {
        foreach ($availableAgents as $agentStatus) {
          $dial->client("user_" . $agentStatus->user_id);
        }
      }
    } else {
      //Default response for unmatched numbers
      $voiceResponse->say("Sorry, the number you dialed is not recognized.");
    }

    return response($voiceResponse->__toString(), 200)->header(
      "Content-Type",
      "text/xml"
    );
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
    $voice->play('https://demo.twilio.com/docs/classic.mp3', ['loop' => 0]);

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
   * Forward all inbound calls to an external number (stored in config / DB).
   * Returns TwiML used by Twilio webhook when a new inbound call arrives.
   *
   * GET /api/call/forward-twiml
   */
  public function forwardTwiml(Request $request)
  {
    $forwardTo = config('services.twilio.forward_to');

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
