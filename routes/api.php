<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TwilioController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/call-routing', [TwilioController::class, 'handleCallRouting'])->name('twilio.handleCallRouting');

// ── Call actions (auth required) ──────────────────────────────────────────
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/call/hold',     [TwilioController::class, 'holdCall'])->name('twilio.holdCall');
    Route::post('/call/resume',   [TwilioController::class, 'resumeCall'])->name('twilio.resumeCall');
    Route::post('/call/transfer', [TwilioController::class, 'transferCall'])->name('twilio.transferCall');
    Route::post('/call/mute',     [TwilioController::class, 'muteCall'])->name('twilio.muteCall');
    Route::get('/call/agents',    [TwilioController::class, 'availableAgents'])->name('twilio.availableAgents');
});

// ── Forward TwiML — called directly by Twilio (no auth) ──────────────────
Route::get('/call/forward-twiml', [TwilioController::class, 'forwardTwiml'])->name('twilio.forwardTwiml');

