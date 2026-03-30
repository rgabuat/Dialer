<?php

namespace App\Http\Controllers;

use Twilio\Jwt\AccessToken;
use Illuminate\Http\Request;
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
      // Incoming call from external number — ring all available agents
      $dial = $voiceResponse->dial("", [
        "callerId" => config("services.twilio.caller_id"),
      ]);

      $availableAgents = \App\Models\AgentStatus::with("statusType")
        ->whereHas("statusType", fn($q) => $q->where("is_available", true))
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
}
