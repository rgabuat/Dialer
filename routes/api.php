<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwilioController;
use App\Http\Controllers\ClientLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware("auth:sanctum")->get("/user", function (Request $request) {
  return $request->user();
});

// ── Frontend error reporting — throttled, no auth required ───────────────
Route::post('/client-log', [ClientLogController::class, 'store'])
    ->middleware('throttle:60,1')
    ->name('client.log');

Route::post("/call-routing", [
  TwilioController::class,
  "handleCallRouting",
])->name("twilio.handleCallRouting");

// ── Call actions (auth required) ──────────────────────────────────────────
Route::middleware("auth:sanctum")->group(function () {
  Route::post("/call/hold", [TwilioController::class, "holdCall"])->name(
    "twilio.holdCall"
  );
  Route::post("/call/resume", [TwilioController::class, "resumeCall"])->name(
    "twilio.resumeCall"
  );
  Route::post("/call/transfer", [
    TwilioController::class,
    "transferCall",
  ])->name("twilio.transferCall");
  Route::post("/call/mute", [TwilioController::class, "muteCall"])->name(
    "twilio.muteCall"
  );
  Route::get("/call/agents", [
    TwilioController::class,
    "availableAgents",
  ])->name("twilio.availableAgents");
});

// ── Twilio webhooks — no auth, called directly by Twilio ─────────────────
Route::post("/call/complete", [TwilioController::class, "callComplete"])->name(
  "twilio.callComplete"
);
Route::post("/call/no-answer", [TwilioController::class, "callNoAnswer"])->name(
  "twilio.callNoAnswer"
);
Route::post("/call/queue-check", [TwilioController::class, "callQueueCheck"])->name(
  "twilio.callQueueCheck"
);
Route::post("/call/ivr-gather", [TwilioController::class, "ivrGather"])->name(
  "twilio.ivrGather"
);
Route::get("/call/forward-twiml", [
  TwilioController::class,
  "forwardTwiml",
])->name("twilio.forwardTwiml");

// ── Dialer (auto-dial — auth required) ───────────────────────────────────
Route::middleware("auth:sanctum")->post("/dialer/autodial", [
  TwilioController::class,
  "autodial",
])->name("dialer.autodial");

// ── Dialer connect-to-agent TwiML webhook (called by Twilio — no auth) ───
Route::get("/dialer/connect-to-agent", [
  TwilioController::class,
  "connectToAgent",
])->name("dialer.connectToAgent");
