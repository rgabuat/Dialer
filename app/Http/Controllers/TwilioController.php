<?php

namespace App\Http\Controllers;

use App\Models\AgentStatus;
use App\Models\Campaign;
use App\Models\Conversation;
use App\Models\Did;
use App\Models\InGroup;
use App\Models\IvrMenu;
use App\Services\ActivityLogger;
use App\Services\HopperService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VoiceGrant;
use Twilio\Rest\Client as TwilioClient;
use Twilio\TwiML\VoiceResponse;

class TwilioController extends Controller
{
    public function getAccessToken(Request $request)
    {
        $user = auth()->user();
        $identity = $user ? 'user_'.$user->id : 'guest';

        $token = new AccessToken(
            config('services.twilio.sid'), // Account SID (AC…)
            config('services.twilio.key'), // API Key (SK…)
            config('services.twilio.secret'), // API Secret
            3600,
            $identity
        );

        $voiceGrant = new VoiceGrant;
        $voiceGrant->setOutgoingApplicationSid(
            config('services.twilio.twiml_app_sid')
        );
        $voiceGrant->setIncomingAllow(true);
        $token->addGrant($voiceGrant);

        return response()->json([
            'identity' => $identity,
            'token' => $token->toJWT(),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Main Twilio webhook  POST /api/call-routing
    // ──────────────────────────────────────────────────────────────────────────

    public function handleCallRouting(Request $request)
    {
        \Log::info('[Twilio] handleCallRouting', $request->all());

        $callSid = $request->input('CallSid');
        $callStatus = strtolower((string) $request->input('CallStatus', ''));

        \Log::debug('[Twilio] handleCallRouting: normalized params', [
            'call_sid' => $callSid,
            'call_status' => $callStatus,
            'to' => $request->input('To'),
            'from' => $request->input('From'),
            'agent_param' => $request->input('agent'),
        ]);

        // Twilio may call this webhook with terminal statuses during hangup.
        // Do not attempt any routing for ended calls.
        if (in_array($callStatus, ['completed', 'canceled', 'failed', 'busy', 'no-answer'], true)) {
            $affected = 0;
            if ($callSid) {
                $affected = Conversation::where('call_sid', $callSid)
                    ->whereIn('status', ['queued', 'in_progress'])
                    ->update([
                        'status' => 'abandoned',
                        'ended_at' => now(),
                    ]);
                if ($affected) {
                    $this->broadcastConversationStatus($callSid);
                }
            }

            \Log::info('[Twilio] handleCallRouting: terminal status early-exit', [
                'call_sid' => $callSid,
                'call_status' => $callStatus,
                'rows_affected' => $affected,
            ]);

            return response('<?xml version="1.0" encoding="UTF-8"?><Response/>', 200)
                ->header('Content-Type', 'text/xml');
        }

        // ── Recording status callback — not a routing request, ignore it ──
        // Twilio fires these to the voice webhook URL when recording segments
        // are ready. Return empty TwiML so Twilio does not re-route the call.
        if ($request->filled('RecordingSid')) {
            \Log::info('[Twilio] handleCallRouting: recording callback ignored', [
                'call_sid' => $callSid,
                'recording_sid' => $request->input('RecordingSid'),
                'recording_status' => $request->input('RecordingStatus'),
            ]);

            return response('<?xml version="1.0" encoding="UTF-8"?><Response/>', 200)
                ->header('Content-Type', 'text/xml');
        }

        $to = $request->input('To', '');
        $from = $request->input('From', '');
        $agentParam = $request->input('agent');
        $callbackUrl = rtrim(config('app.url'), '/').'/api/call/complete';
        $voice = new VoiceResponse;

        // Twilio geo / caller metadata — captured once and passed to conversation create
        $twilioMeta = [
            'caller_name' => $request->input('CallerName') ?: null,
            'caller_city' => $request->input('CallerCity') ?: $request->input('FromCity') ?: null,
            'caller_state' => $request->input('CallerState') ?: $request->input('FromState') ?: null,
            'caller_country' => $request->input('CallerCountry') ?: $request->input('FromCountry') ?: null,
            'caller_zip' => $request->input('CallerZip') ?: $request->input('FromZip') ?: null,
            'to_number' => $to ?: null,
            'forwarded_from' => $request->input('ForwardedFrom') ?: null,
        ];

        if (empty($to)) {
            \Log::warning('[Twilio] handleCallRouting: missing destination', [
                'call_sid' => $callSid,
                'from' => $from,
                'call_status' => $callStatus,
            ]);
            $voice->say('Sorry, no destination was provided.');

            return $this->twimlResponse($voice);
        }

        // ── 1. Try DID lookup ──────────────────────────────────────────────
        $did = Did::where('phone_number', $to)->where('is_active', true)->first();

        if ($did) {
            \Log::info('[Twilio] handleCallRouting: matched DID', [
                'call_sid' => $callSid,
                'did_id' => $did->id,
                'to' => $to,
                'ivr_menu_id' => $did->ivr_menu_id,
                'in_group_id' => $did->in_group_id,
            ]);

            return $this->handleInboundDid(
                $did,
                $from,
                $callSid,
                $callbackUrl,
                $voice,
                $twilioMeta
            );
        }

        // ── 2. Legacy fallback: global Twilio number (pre-DID inbound) ───────
        $isTwilioNumber = $to === config('services.twilio.phone_number');

        if ($isTwilioNumber) {
            \Log::info('[Twilio] handleCallRouting: legacy inbound fallback', [
                'call_sid' => $callSid,
                'to' => $to,
                'from' => $from,
            ]);

            return $this->handleLegacyInbound(
                $to,
                $from,
                $callSid,
                $callbackUrl,
                $voice,
                $twilioMeta
            );
        }

        // ── 3. Outbound: browser client dialling a number / agent ─────────
        \Log::info('[Twilio] handleCallRouting: outbound branch', [
            'call_sid' => $callSid,
            'to' => $to,
            'from' => $from,
            'agent_param' => $agentParam,
        ]);

        return $this->handleOutbound(
            $to,
            $from,
            $callSid,
            $agentParam,
            $callbackUrl,
            $voice
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  IVR gather  POST /api/call/ivr-gather
    // ──────────────────────────────────────────────────────────────────────────

    public function ivrGather(Request $request)
    {
        \Log::info('[Twilio] ivrGather', $request->all());

        $menuId = (int) $request->input('menu_id');
        $callSid = $request->input('CallSid');
        $from = $request->input('From', '');
        $digit = $request->input('Digits', '');
        $attempts = (int) $request->input('attempts', 0);
        $callbackUrl = rtrim(config('app.url'), '/').'/api/call/complete';
        $voice = new VoiceResponse;

        $menu = IvrMenu::with('options')->find($menuId);

        if (! $menu || ! $menu->is_active) {
            $voice->say('This menu is no longer available. Goodbye.');
            $voice->hangup();

            return $this->twimlResponse($voice);
        }

        $option = $menu->options->firstWhere('digit', $digit);

        if (! $option) {
            $attempts++;
            if ($attempts >= $menu->invalid_attempts_max) {
                return $this->executeIvrInvalidAction(
                    $menu,
                    $callSid,
                    $from,
                    $callbackUrl,
                    $voice
                );
            }

            // Re-play the menu
            return $this->serveIvrMenu($menu, $voice, $attempts);
        }

        return $this->executeIvrOption(
            $option,
            $menu,
            $callSid,
            $from,
            $callbackUrl,
            $voice
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Conversation lookup  GET /api/call/conversation?call_sid=CA...
    //  Returns the web URL to the conversation page for a given CallSid.
    //  Used by the browser to navigate the agent to the conversation on accept.
    // ──────────────────────────────────────────────────────────────────────────

    public function conversationBySid(Request $request)
    {
        $callSid = $request->input('call_sid');
        $from = $request->input('from');

        if (! $callSid && ! $from) {
            return response()->json(['url' => null], 422);
        }

        // Primary lookup: by the exact call SID stored when the webhook ran.
        // For in-group queue calls, the SDK's CallSid IS the parent SID.
        $conversation = $callSid
          ? Conversation::where('call_sid', $callSid)->first()
          : null;

        // Fallback lookup: find the most recent in-progress or queued conversation
        // matching the caller's phone number.  This handles the case where the
        // agent's SDK leg carries a child CallSid that differs from the parent SID
        // stored in the DB (can happen with certain <Dial> leg configurations).
        if (! $conversation && $from) {
            $conversation = Conversation::where('contact_phone', $from)
                ->whereIn('status', ['in_progress', 'queued'])
                ->latest('started_at')
                ->first();
        }

        if (! $conversation) {
            // Return 200 {url: null} — the JS retry loop checks for a truthy url.
            return response()->json(['url' => null]);
        }

        // Stamp assigned_to on inbound calls that weren't pre-assigned
        // (inbound queue calls are created without an assigned agent).
        if (! $conversation->assigned_to && auth()->check()) {
            $conversation->update(['assigned_to' => auth()->id()]);
            event(new \App\Events\AgentStatusUpdated(['user_id' => auth()->id()]));
        }

        return response()->json([
            'url' => route('conversations.show', $conversation),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Broadcast helper — fires CallQueueUpdated + AgentStatusUpdated after a
    //  mass DB update (mass updates bypass the ConversationObserver).
    // ──────────────────────────────────────────────────────────────────────────

    private function broadcastConversationStatus(string $callSid, ?int $agentUserId = null): void
    {
        dispatch(function () use ($callSid, $agentUserId) {
            $conversation = Conversation::where('call_sid', $callSid)->first();
            if (! $conversation) {
                return;
            }
            try {
                event(new \App\Events\CallQueueUpdated([
                    'action'          => 'updated',
                    'conversation_id' => $conversation->id,
                    'status'          => $conversation->status,
                    'in_group_id'     => $conversation->in_group_id,
                    'assigned_to'     => $conversation->assigned_to,
                ]));
                $uid = $agentUserId ?? $conversation->assigned_to;
                if ($uid) {
                    event(new \App\Events\AgentStatusUpdated(['user_id' => $uid]));
                }
            } catch (\Throwable $e) {
                \Log::warning('[TwilioController] broadcastConversationStatus failed', [
                    'call_sid' => $callSid,
                    'error'    => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Call complete  POST /api/call/complete
    // ──────────────────────────────────────────────────────────────────────────

    public function callComplete(Request $request)
    {
        \Log::info('[Twilio] callComplete', $request->all());

        $callSid = $request->input('CallSid');

        // Guard: Twilio conference statusCallbacks have ConferenceSid but NO
        // CallSid. Proceeding with null would execute WHERE call_sid IS NULL
        // and mass-update every unassigned conversation row. Instead, extract
        // the original call SID from the FriendlyName (format: monitor_CAXXXX),
        // broadcast the update, and return early.
        if (! $callSid) {
            if ($request->has('ConferenceSid')) {
                $friendlyName = (string) $request->input('FriendlyName', '');
                if (preg_match('/^monitor_(CA[a-f0-9]+)$/i', $friendlyName, $m)) {
                    $this->broadcastConversationStatus($m[1]);
                }
            }
            return response('<?xml version="1.0" encoding="UTF-8"?><Response/>', 200)
                ->header('Content-Type', 'text/xml');
        }
        $dialStatus = $request->input('DialCallStatus', 'completed');
        $dialDuration = (int) $request->input('DialCallDuration', 0);

        $statusMap = [
            'completed' => 'completed',
            'busy' => 'abandoned',
            'no-answer' => 'abandoned',
            'failed' => 'abandoned',
            'canceled' => 'abandoned',
        ];

        $dialTo = $request->input('DialTo', '');
        $agentUserId = null;
        if (preg_match('/^client:(?:agent|user)_(\d+)$/', $dialTo, $m)) {
            $agentUserId = (int) $m[1];
        }

        $update = [
            'status' => $statusMap[$dialStatus] ?? 'completed',
            'duration_seconds' => $dialDuration ?: null,
            'ended_at' => now(),
        ];

        if ($agentUserId) {
            $update['assigned_to'] = $agentUserId;
            $update['completed_by'] = $agentUserId;
        }

        \Log::info('[Twilio] callComplete: resolving conversation', [
            'call_sid' => $callSid,
            'dial_status' => $dialStatus,
            'dial_duration' => $dialDuration,
            'dial_to' => $dialTo,
            'agent_user_id' => $agentUserId,
            'resolved_status' => $update['status'],
        ]);

        // Do not overwrite 'queued' status — callNoAnswer may have re-queued this
        // caller while they wait for the next available agent.
        $affected = Conversation::where('call_sid', $callSid)
            ->where('status', '!=', 'queued')
            ->update($update);

        \Log::info('[Twilio] callComplete: DB update result', [
            'call_sid' => $callSid,
            'rows_affected' => $affected,
            'skipped_if_zero' => $affected === 0 ? 'conversation was queued (re-queued caller), skipped intentionally' : null,
        ]);

        // Broadcast to queue monitor and users table (mass updates bypass the observer).
        $this->broadcastConversationStatus($callSid, $agentUserId);

        // statusCallback response body is ignored by Twilio — return empty TwiML.
        return response('<?xml version="1.0" encoding="UTF-8"?><Response/>', 200)
            ->header('Content-Type', 'text/xml');
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  No-answer handler  POST /api/call/no-answer
    //  Fired by Twilio when <Dial> times out (agents didn't answer).
    //  Executes the InGroup's drop_action instead of a generic TTS message.
    // ──────────────────────────────────────────────────────────────────────────

    public function callNoAnswer(Request $request)
    {
        \Log::info('[Twilio] callNoAnswer', $request->all());

        $callSid = $request->input('CallSid');
        $dialStatus = strtolower($request->input('DialCallStatus', 'no-answer'));
        $inGroupId = (int) $request->input('in_group_id');
        $callbackUrl = rtrim(config('app.url'), '/').'/api/call/complete';
        $voice = new VoiceResponse;

        $inGroup = InGroup::find($inGroupId);
        $conversation = Conversation::where('call_sid', $callSid)->first();
        $campaign = $this->campaignForCall($callSid, $inGroup);

        \Log::debug('[Twilio] callNoAnswer: resolved context', [
            'call_sid' => $callSid,
            'dial_status' => $dialStatus,
            'in_group_id' => $inGroupId,
            'in_group_found' => (bool) $inGroup,
            'conversation_found' => (bool) $conversation,
            'conversation_status' => $conversation?->status,
        ]);

        // ── Call connected and ended normally ────────────────────────────────────
        // DialCallStatus=completed means the agent answered and the call finished.
        // Say goodbye, hang up. callComplete (statusCallback) handles the DB update.
        if ($dialStatus === 'completed') {
            \Log::info('[Twilio] callNoAnswer: call completed normally', [
                'call_sid' => $callSid,
                'dial_status' => $dialStatus,
            ]);
            $voice->say(
                $campaign?->tts_completed ?: 'Thank you for calling. Goodbye.',
                [
                    'voice' => $campaign?->tts_voice ?: 'alice',
                    'language' => $campaign?->tts_language ?: 'en-US',
                ]
            );
            $voice->hangup();

            return $this->twimlResponse($voice);
        }

        // ── No-answer / busy / failed — attempt re-queue ─────────────────────────
        // Re-queue the caller if they are still within their group's max wait time.
        // Only re-queue for: no-answer (ring timeout), busy, failed, canceled.
        if ($inGroup && $conversation) {
            $waitedSeconds = now()->diffInSeconds($conversation->started_at);
            $maxQueueWait = $inGroup->queue_max_wait_seconds ?: 300;

            if ($waitedSeconds < $maxQueueWait) {
                Conversation::where('call_sid', $callSid)->update([
                    'status' => 'queued',
                    'ended_at' => null,
                ]);
                $this->broadcastConversationStatus($callSid);

                $holdMusic = $campaign?->hold_music_url ?: 'https://demo.twilio.com/docs/classic.mp3';
                $queueCheckUrl = rtrim(config('app.url'), '/')
                               ."/api/call/queue-check?in_group_id={$inGroup->id}";

                \Log::info('[Twilio] callNoAnswer: re-queuing caller', [
                    'call_sid' => $callSid,
                    'dial_status' => $dialStatus,
                    'waited_seconds' => $waitedSeconds,
                    'max_wait' => $maxQueueWait,
                ]);

                $voice->say(
                    'All agents are still busy. Please continue to hold.',
                    [
                        'voice' => $campaign?->tts_voice ?: 'alice',
                        'language' => $campaign?->tts_language ?: 'en-US',
                    ]
                );
                $gather = $voice->gather([
                    'action' => $queueCheckUrl,
                    'method' => 'POST',
                    'timeout' => 15,
                    'finishOnKey' => '',
                ]);
                $gather->play($holdMusic);
                $voice->redirect($queueCheckUrl, ['method' => 'POST']);

                return $this->twimlResponse($voice);
            }

            \Log::info('[Twilio] callNoAnswer: max queue wait exceeded', [
                'call_sid' => $callSid,
                'dial_status' => $dialStatus,
                'waited_seconds' => $waitedSeconds,
                'max_wait' => $maxQueueWait,
            ]);
        } else {
            \Log::warning('[Twilio] callNoAnswer: missing in-group context for requeue', [
                'call_sid' => $callSid,
                'dial_status' => $dialStatus,
                'in_group_id' => $inGroupId,
                'in_group_found' => (bool) $inGroup,
                'conversation_found' => (bool) $conversation,
            ]);
        }

        // ── Time expired or no in-group context — abandon ────────────────────────
        Conversation::where('call_sid', $callSid)->update([
            'status' => 'abandoned',
            'ended_at' => now(),
        ]);
        $this->broadcastConversationStatus($callSid);

        \Log::info('[Twilio] callNoAnswer: abandoning call', [
            'call_sid' => $callSid,
            'dial_status' => $dialStatus,
            'in_group_id' => $inGroupId,
            'drop_action' => $inGroup?->drop_action,
            'drop_destination' => $inGroup?->drop_destination,
        ]);

        if (! $inGroup) {
            $voice->say(
                $campaign?->tts_no_answer ?: 'We are sorry, no agents are currently available. Please call back later. Goodbye.',
                [
                    'voice' => $campaign?->tts_voice ?: 'alice',
                    'language' => $campaign?->tts_language ?: 'en-US',
                ]
            );
            $voice->hangup();

            return $this->twimlResponse($voice);
        }

        return $this->executeDropAction(
            $inGroup->drop_action,
            $inGroup->drop_destination,
            $callbackUrl,
            $voice
        );
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Queue check  POST /api/call/queue-check
    //  Fired by <Gather> action while caller is holding. Polls for available
    //  agents every 15 s, loops with hold music, or executes drop_action when
    //  max wait expires. Also handles caller hangup (CallStatus=completed).
    // ──────────────────────────────────────────────────────────────────────────

    public function callQueueCheck(Request $request)
    {
        \Log::info('[Twilio] callQueueCheck', $request->only(['CallSid', 'CallStatus', 'in_group_id']));

        $callSid = $request->input('CallSid');
        $callStatus = strtolower((string) $request->input('CallStatus', ''));
        $inGroupId = (int) $request->input('in_group_id');
        $callbackUrl = rtrim(config('app.url'), '/').'/api/call/complete';
        $voice = new VoiceResponse;

        // ── Caller hung up while holding — clean up and return empty response ──
        if (in_array($callStatus, ['completed', 'canceled', 'failed', 'busy', 'no-answer'], true)) {
            Conversation::where('call_sid', $callSid)
                ->whereIn('status', ['queued', 'in_progress'])
                ->update(['status' => 'abandoned', 'ended_at' => now()]);
            $this->broadcastConversationStatus($callSid);
            \Log::info('[Twilio] callQueueCheck: caller hung up during hold', [
                'call_sid' => $callSid,
                'call_status' => $callStatus,
            ]);

            return $this->twimlResponse($voice); // empty <Response/> — Twilio ignores it
        }

        $inGroup = InGroup::find($inGroupId);
        if (! $inGroup) {
            \Log::error('[Twilio] callQueueCheck: in-group not found', [
                'call_sid' => $callSid,
                'in_group_id' => $inGroupId,
            ]);
            $voice->say('Configuration error. Goodbye.');
            $voice->hangup();

            return $this->twimlResponse($voice);
        }

        $campaign = $this->campaignForCall($callSid, $inGroup);

        // Check if caller has exceeded max queue wait
        $conversation = Conversation::where('call_sid', $callSid)->first();
        $waitedSeconds = $conversation ? now()->diffInSeconds($conversation->started_at) : 0;
        $maxQueueWait = $inGroup->queue_max_wait_seconds ?: 300;

        if ($waitedSeconds >= $maxQueueWait) {
            Conversation::where('call_sid', $callSid)->update([
                'status' => 'abandoned',
                'ended_at' => now(),
            ]);
            $this->broadcastConversationStatus($callSid);
            \Log::info('[Twilio] callQueueCheck: queue wait exceeded, executing drop action', [
                'call_sid' => $callSid,
                'in_group_id' => $inGroupId,
                'waited_s' => $waitedSeconds,
                'max_wait_s' => $maxQueueWait,
                'drop_action' => $inGroup->drop_action,
                'drop_destination' => $inGroup->drop_destination,
            ]);

            return $this->executeDropAction(
                $inGroup->drop_action,
                $inGroup->drop_destination,
                $callbackUrl,
                $voice
            );
        }

        // Guard: if the call is already in_progress (being dialed), a duplicate
        // callQueueCheck request (Gather + Redirect race) has arrived — ignore it.
        if ($conversation && $conversation->status === 'in_progress') {
            \Log::info('[Twilio] callQueueCheck: duplicate request ignored (call already in_progress)', [
                'call_sid' => $callSid,
                'in_group_id' => $inGroupId,
            ]);

            return $this->twimlResponse($voice);
        }

        // Check for newly available agents
        $agents = $this->selectAgents($inGroup);

        if (! $agents->isEmpty()) {
            // Agent now available — connect the call
            $noAnswerUrl = rtrim(config('app.url'), '/')
              ."/api/call/no-answer?in_group_id={$inGroup->id}";

            \Log::info('[Twilio] callQueueCheck: agent(s) available, connecting call', [
                'call_sid' => $callSid,
                'in_group_id' => $inGroupId,
                'waited_s' => $waitedSeconds,
                'agent_count' => $agents->count(),
                'agent_ids' => $agents->pluck('user_id')->toArray(),
            ]);

            // Lift status back to in_progress now that we're dialing
            Conversation::where('call_sid', $callSid)->update(['status' => 'in_progress']);
            $this->broadcastConversationStatus($callSid);

            $dial = $voice->dial('', [
                'callerId' => config('services.twilio.caller_id'),
                'timeout' => $inGroup->max_wait_seconds ?: 20,
                'action' => $noAnswerUrl,
                'method' => 'POST',
                'statusCallback' => $callbackUrl,
                'statusCallbackEvent' => 'completed',
            ]);

            foreach ($agents as $agent) {
                $dial->client('user_'.$agent['user_id']);
            }

            // Update round-robin pivot for single-agent algorithms
            if (
                in_array($inGroup->agent_routing, ['round_robin', 'fewest_calls', 'longest_idle']) &&
                $agents->count() === 1
            ) {
                $inGroup->users()->updateExistingPivot($agents->first()['user_id'], [
                    'last_call_at' => now(),
                ]);
            }

            return $this->twimlResponse($voice);
        }

        // Still no agents — keep status queued, play 15 s of hold music, then
        // post back to this endpoint via <Gather> action (fires on timeout AND
        // on caller hangup, giving us immediate hangup detection).
        Conversation::where('call_sid', $callSid)->update(['status' => 'queued']);
        $this->broadcastConversationStatus($callSid);

        \Log::info('[Twilio] callQueueCheck: still no agents, looping hold', [
            'call_sid' => $callSid,
            'in_group_id' => $inGroupId,
            'waited_s' => $waitedSeconds,
            'max_wait_s' => $maxQueueWait,
        ]);

        $holdMusic = $campaign?->hold_music_url ?: 'https://demo.twilio.com/docs/classic.mp3';
        $queueCheckUrl = rtrim(config('app.url'), '/')
                       ."/api/call/queue-check?in_group_id={$inGroup->id}";

        // <Gather> fires on timeout (15 s) OR on caller hangup (CallStatus=completed).
        // No <Redirect> fallback — it caused simultaneous double-POSTs which created
        // two competing <Dial> responses and a re-queue race condition.
        $gather = $voice->gather([
            'action' => $queueCheckUrl,
            'method' => 'POST',
            'timeout' => 15,
            'finishOnKey' => '',
        ]);
        $gather->play($holdMusic);

        return $this->twimlResponse($voice);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Call control actions
    // ──────────────────────────────────────────────────────────────────────────

    public function muteCall(Request $request)
    {
        $request->validate([
            'call_sid' => 'required|string|max:64',
            'muted' => 'required|boolean',
        ]);

        return response()->json(['success' => true]);
    }

    public function holdCall(Request $request)
    {
        $request->validate(['call_sid' => 'required|string|max:64']);

        $client = $this->twilioClient();
        $voice = new VoiceResponse;
        $conversation = Conversation::with('campaign')
            ->where('call_sid', $request->input('call_sid'))->first();
        $holdMusic = $conversation?->campaign?->hold_music_url
          ?: 'https://demo.twilio.com/docs/classic.mp3';
        $voice->play($holdMusic, ['loop' => 0]);

        $client
            ->calls($request->call_sid)
            ->update(['twiml' => $voice->__toString()]);

        return response()->json(['success' => true]);
    }

    public function resumeCall(Request $request)
    {
        $request->validate([
            'call_sid' => 'required|string|max:64',
            'agent_identity' => 'nullable|string|max:64',
        ]);

        $client = $this->twilioClient();
        $agentIdentity = (string) ($request->input('agent_identity') ?: ('user_'.auth()->id()));
        $callbackUrl = rtrim(config('app.url'), '/').'/api/call/complete';
        $voice = new VoiceResponse;

        if (! preg_match('/^user_\d+$/', $agentIdentity)) {
            return response()->json([
                'message' => 'Invalid agent identity.',
            ], 422);
        }

        $dial = $voice->dial('', [
            'callerId' => config('services.twilio.caller_id'),
            'timeout' => 30,
            'action' => $callbackUrl,
            'method' => 'POST',
            'statusCallback' => $callbackUrl,
            'statusCallbackEvent' => 'completed',
        ]);
        $dial->client($agentIdentity);

        $client->calls($request->call_sid)->update([
            'twiml' => $voice->__toString(),
        ]);

        return response()->json(['success' => true]);
    }

    public function transferCall(Request $request)
    {
        $request->validate([
            'call_sid' => 'required|string|max:64',
            'to' => 'required|string|max:64',
        ]);

        $to = $request->to;
        $client = $this->twilioClient();
        $voice = new VoiceResponse;
        $dial = $voice->dial('', [
            'callerId' => config('services.twilio.caller_id'),
        ]);

        if (preg_match('/^[\d\+\-\(\) ]+$/', $to)) {
            $dial->number($to);
        } else {
            $dial->client($to);
        }

        $client
            ->calls($request->call_sid)
            ->update(['twiml' => $voice->__toString()]);

        return response()->json(['success' => true]);
    }

    public function forwardTwiml(Request $request)
    {
        $forwardTo = null;
        $userId = $request->query('user_id');

        if ($userId) {
            $user = \App\Models\User::find($userId);
            if ($user && (bool) $user->getMeta('call_forwarding_enabled', false)) {
                $forwardTo = (string) $user->getMeta('call_forward_to', '');
            }
        }

        if (empty($forwardTo)) {
            $forwardTo = config('services.twilio.forward_to');
        }

        $voice = new VoiceResponse;

        if (empty($forwardTo)) {
            $voice->say('Call forwarding is not configured. Please try again later.');
        } else {
            $dial = $voice->dial('', [
                'callerId' => config('services.twilio.caller_id'),
            ]);
            if (preg_match('/^[\d\+\-\(\) ]+$/', $forwardTo)) {
                $dial->number($forwardTo);
            } else {
                $dial->client($forwardTo);
            }
        }

        return response($voice->__toString(), 200)->header(
            'Content-Type',
            'text/xml'
        );
    }

    public function availableAgents()
    {
        $agents = AgentStatus::with(['statusType', 'user'])
            ->whereHas('statusType', fn ($q) => $q->where('handles_inbound', true))
            ->get()
            ->map(
                fn ($s) => [
                    'id' => $s->user_id,
                    'name' => trim($s->user->first_name.' '.$s->user->last_name),
                    'identity' => 'user_'.$s->user_id,
                ]
            )
            ->filter(fn ($agent) => $agent['id'] !== auth()->id())
            ->values();

        return response()->json($agents);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Supervisor monitoring  POST /api/call/monitor
    //  Allows a supervisor to listen (muted) or barge (unmuted) into a live
    //  agent call by moving both legs into a named Twilio conference room.
    // ──────────────────────────────────────────────────────────────────────────

    public function monitorCall(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'mode'    => 'required|in:listen,barge',
        ]);

        $agentId      = (int) $request->input('user_id');
        $mode         = $request->input('mode');   // 'listen' | 'barge'
        $supervisorId = auth()->id();

        // Find the agent's current active conversation
        $conversation = Conversation::where('assigned_to', $agentId)
            ->where('status', 'in_progress')
            ->whereNotNull('call_sid')
            ->latest('started_at')
            ->first();

        if (! $conversation || ! $conversation->call_sid) {
            return response()->json(['message' => 'Agent is not on an active call.'], 404);
        }

        $callSid        = $conversation->call_sid;
        $conferenceName = 'monitor_' . $callSid;
        $client         = $this->twilioClient();
        $callbackUrl    = rtrim(config('app.url'), '/') . '/api/call/complete';

        // ── Step 1: Notify the agent's browser to auto-accept the next incoming call
        // Twilio does NOT allow redirecting the child "dialed leg" of a <Dial>, so
        // the agent's original call leg will be disconnected when the caller is moved
        // to the conference. Broadcasting MonitorPrepare sets _twilioAutoAcceptNextIncoming
        // on the agent's browser so they seamlessly rejoin the conference without a
        // manual accept.
        event(new \App\Events\MonitorPrepare($agentId, $conferenceName));

        // ── Step 2: Redirect the CALLER to the conference ─────────────────────────
        // This ends the <Dial>, which disconnects the agent's original call leg.
        // The agent will be re-dialled into the same conference in step 3.
        $callerTwiml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Response><Dial>'
            . '<Conference beep="false" startConferenceOnEnter="true" endConferenceOnExit="false">'
            . htmlspecialchars($conferenceName)
            . '</Conference></Dial></Response>';

        try {
            $client->calls($callSid)->update(['twiml' => $callerTwiml]);
        } catch (\Exception $e) {
            \Log::error('[Monitor] Failed to redirect caller to conference', [
                'call_sid' => $callSid,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Could not redirect call. ' . $e->getMessage()], 500);
        }

        // ── Step 3: Re-dial the agent into the same conference ────────────────────
        // _twilioAutoAcceptNextIncoming is already set on the agent's browser so this
        // call will be accepted automatically. The agent's exit ends the conference.
        $agentTwiml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Response><Dial>'
            . '<Conference beep="false" startConferenceOnEnter="true" endConferenceOnExit="true"'
            . ' statusCallback="' . $callbackUrl . '" statusCallbackEvent="end">'
            . htmlspecialchars($conferenceName)
            . '</Conference></Dial></Response>';

        try {
            $client->calls->create(
                'client:user_' . $agentId,
                config('services.twilio.caller_id'),
                ['twiml' => $agentTwiml, 'method' => 'POST']
            );
        } catch (\Exception $e) {
            \Log::error('[Monitor] Failed to re-dial agent into conference', [
                'agent_id' => $agentId,
                'error'    => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Could not re-dial agent. ' . $e->getMessage()], 500);
        }

        // ── Step 4: Dial the supervisor into the conference ───────────────────────
        // Supervisor exit does NOT end the conference — they are a silent observer.
        $muted = $mode === 'listen' ? 'true' : 'false';

        $supervisorTwiml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Response><Dial>'
            . '<Conference beep="false" startConferenceOnEnter="false" endConferenceOnExit="false"'
            . ' muted="' . $muted . '">'
            . htmlspecialchars($conferenceName)
            . '</Conference></Dial></Response>';

        try {
            $client->calls->create(
                'client:user_' . $supervisorId,
                config('services.twilio.caller_id'),
                ['twiml' => $supervisorTwiml, 'method' => 'POST']
            );
        } catch (\Exception $e) {
            \Log::error('[Monitor] Failed to dial supervisor into conference', [
                'supervisor_id' => $supervisorId,
                'conference'    => $conferenceName,
                'error'         => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Could not dial supervisor. ' . $e->getMessage()], 500);
        }

        \Log::info('[Monitor] Supervisor monitoring started', [
            'supervisor_id'   => $supervisorId,
            'agent_id'        => $agentId,
            'call_sid'        => $callSid,
            'agent_call_sid'  => $agentCallSid,
            'conference_name' => $conferenceName,
            'mode'            => $mode,
        ]);

        return response()->json([
            'message'         => ucfirst($mode) . ' session started.',
            'conference_name' => $conferenceName,
        ]);
    }

    /**
     * POST /api/dialer/autodial
     * PROGRESSIVE/PREDICTIVE: server pulls next lead from hopper and dials it.
     * Auth: sanctum
     */
    public function autodial(Request $request, HopperService $hopper)
    {
        $campaignId = $request->input('campaign_id') ?? session('active_campaign_id');

        $campaign = Campaign::where('id', $campaignId)->where('is_active', true)->firstOrFail();

        // Ensure hopper has leads
        $hopper->fill($campaign);

        $entry = $hopper->nextLead($campaign);
        if (! $entry) {
            return response()->json(['message' => 'No leads available in hopper.'], 404);
        }

        $hopper->markDialing($entry);

        $agentIdentity = 'user_'.auth()->id();
        $connectUrl = rtrim(config('app.url'), '/').route('dialer.connectToAgent', [], false)
            .'?hopper_id='.$entry->id.'&agent='.urlencode($agentIdentity);

        $cidModel = $campaign->nextCidModel();
        $callerId = $cidModel?->phone_number ?? $campaign->caller_id ?: config('services.twilio.caller_id');

        try {
            $call = $this->twilioClient()->calls->create(
                $entry->phone_number,
                $callerId,
                [
                    'url' => $connectUrl,
                    'method' => 'GET',
                    'statusCallback' => rtrim(config('app.url'), '/').'/api/call/complete',
                    'statusCallbackMethod' => 'POST',
                    'statusCallbackEvent' => ['completed'],
                ]
            );
        } catch (\Exception $e) {
            $hopper->skipLead($entry);
            \Log::error('[Dialer] autodial failed: '.$e->getMessage());

            return response()->json(['message' => 'Failed to initiate call.', 'error' => $e->getMessage()], 500);
        }

        ActivityLogger::info(
            'call',
            'cid_rotation',
            'Autodial CID selected: '.$callerId,
            auth()->user(),
            $campaign,
            [
                'cid' => $callerId,
                'cid_id' => $cidModel?->id,
                'rotation_on' => (bool) $campaign->cid_rotation,
                'to' => $entry->phone_number,
                'lead_id' => $entry->lead_id,
                'hopper_id' => $entry->id,
                'call_sid' => $call->sid,
                'source' => 'autodial',
            ]
        );

        // Create/update conversation record idempotently (Twilio can retry callbacks).
        $conversation = Conversation::updateOrCreate(
            ['call_sid' => $call->sid],
            [
                'channel' => 'voice',
                'direction' => 'outbound',
                'status' => 'in_progress',
                'contact_phone' => $entry->phone_number,
                'contact_name' => optional($entry->lead)->first_name.' '.optional($entry->lead)->last_name,
                'campaign_id' => $campaign->id,
                'cid_number_id' => $cidModel?->id,
                'lead_id' => $entry->lead_id,
                'assigned_to' => auth()->id(),
                'started_at' => now(),
            ]
        );

        return response()->json([
            'call_sid' => $call->sid,
            'phone_number' => $entry->phone_number,
            'conversation_id' => $conversation->id,
            'lead_id' => $entry->lead_id,
        ]);
    }

    /**
     * GET /api/dialer/connect-to-agent
     * TwiML webhook: bridges the answered lead call to the agent's browser Device.
     * This endpoint is public (called by Twilio, not by auth users).
     */
    public function connectToAgent(Request $request)
    {
        $hopperId = (int) $request->input('hopper_id');
        $agentIdentity = $request->input('agent', '');
        $callbackUrl = rtrim(config('app.url'), '/').'/api/call/complete';

        $voice = new VoiceResponse;

        if (empty($agentIdentity) || ! preg_match('/^user_\d+$/', $agentIdentity)) {
            $voice->say('Configuration error. Goodbye.');
            $voice->hangup();

            return $this->twimlResponse($voice);
        }

        $dial = $voice->dial('', [
            'callerId' => config('services.twilio.caller_id'),
            'timeout' => 30,
            'action' => $callbackUrl,
            'method' => 'POST',
            'statusCallback' => $callbackUrl,
            'statusCallbackEvent' => 'completed',
        ]);

        $dial->client($agentIdentity);

        // Mark hopper entry as still dialing (already set, no change needed)

        return $this->twimlResponse($voice);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Handle an inbound call that matched a DID record.
     */
    private function handleInboundDid(
        Did $did,
        string $from,
        ?string $callSid,
        string $callbackUrl,
        VoiceResponse $voice,
        array $twilioMeta = []
    ): \Illuminate\Http\Response {
        \Log::info('[Twilio] handleInboundDid', [
            'call_sid' => $callSid,
            'did_id' => $did->id,
            'ivr_menu_id' => $did->ivr_menu_id,
            'in_group_id' => $did->in_group_id,
            'from' => $from,
        ]);

        if ($did->ivr_menu_id) {
            // ── IVR path ──────────────────────────────────────────────────
            $menu = IvrMenu::with('options')->find($did->ivr_menu_id);

            Conversation::firstOrCreate(
                ['call_sid' => $callSid],
                array_merge([
                    'channel' => 'voice',
                    'direction' => 'inbound',
                    'status' => 'in_progress',
                    'contact_phone' => $from,
                    'started_at' => now(),
                ], $twilioMeta)
            );

            if (! $menu || ! $menu->is_active) {
                $voice->say('This service is temporarily unavailable. Goodbye.');
                $voice->hangup();

                return $this->twimlResponse($voice);
            }

            return $this->serveIvrMenu($menu, $voice, 0);
        }

        if ($did->in_group_id) {
            // ── In-Group path ─────────────────────────────────────────────
            $inGroup = InGroup::find($did->in_group_id);

            $conversation = Conversation::firstOrCreate(
                ['call_sid' => $callSid],
                array_merge([
                    'channel' => 'voice',
                    'direction' => 'inbound',
                    'status' => 'in_progress',
                    'contact_phone' => $from,
                    'in_group_id' => $inGroup?->id,
                    'campaign_id' => $inGroup?->campaign_id,
                    'started_at' => now(),
                ], $twilioMeta)
            );

            if (! $inGroup || ! $inGroup->is_active) {
                $voice->say('This queue is not available. Please try again later.');
                $voice->hangup();

                return $this->twimlResponse($voice);
            }

            return $this->routeToInGroup(
                $inGroup,
                $voice,
                $callSid,
                $from,
                $callbackUrl
            );
        }

        // DID exists but no destination configured
        \Log::warning('[Twilio] handleInboundDid: DID has no destination', [
            'call_sid' => $callSid,
            'did_id' => $did->id,
            'from' => $from,
        ]);
        $voice->say(
            'This number is not currently configured. Please try again later.'
        );
        $voice->hangup();

        return $this->twimlResponse($voice);
    }

    /**
     * Legacy inbound: no DID record, falls back to global Twilio number.
     */
    private function handleLegacyInbound(
        string $to,
        string $from,
        ?string $callSid,
        string $callbackUrl,
        VoiceResponse $voice,
        array $twilioMeta = []
    ): \Illuminate\Http\Response {
        $availableAgents = AgentStatus::with(['statusType', 'user'])
            ->whereHas('statusType', fn ($q) => $q->where('is_available', true)->where('handles_inbound', true))
            ->get();

        Conversation::firstOrCreate(
            ['call_sid' => $callSid],
            array_merge([
                'channel' => 'voice',
                'direction' => 'inbound',
                'status' => 'in_progress',
                'contact_phone' => $from,
                'started_at' => now(),
            ], $twilioMeta)
        );

        \Log::info('[Twilio] handleLegacyInbound: availability check', [
            'call_sid' => $callSid,
            'to' => $to,
            'from' => $from,
            'available_agents' => $availableAgents->count(),
            'agent_ids' => $availableAgents->pluck('user_id')->toArray(),
        ]);

        if ($availableAgents->isEmpty()) {
            $voice->say(
                'All agents are currently unavailable. Please try again later.'
            );

            return $this->twimlResponse($voice);
        }

        $dial = $voice->dial('', [
            'callerId' => config('services.twilio.caller_id'),
            'timeout' => 20,
            'action' => $callbackUrl,
            'method' => 'POST',
            'statusCallback' => $callbackUrl,
            'statusCallbackEvent' => 'completed',
        ]);

        foreach ($availableAgents as $status) {
            $dial->client('user_'.$status->user_id);
        }

        return $this->twimlResponse($voice);
    }

    /**
     * Handle outbound calls placed from the browser JS client.
     */
    private function handleOutbound(
        string $to,
        string $from,
        ?string $callSid,
        ?string $agentParam,
        string $callbackUrl,
        VoiceResponse $voice
    ): \Illuminate\Http\Response {
        $isWhisperConsult = request()->boolean('whisper_consult');
        $userId = null;
        if (
            $agentParam &&
            preg_match('/^(?:agent|user)_(\d+)$/', $agentParam, $m)
        ) {
            $userId = (int) $m[1];
        }

        $campaign = null;
        if ($userId) {
            $userGroupId = \App\Models\User::where('id', $userId)->value('user_group_id');

            $campaign = Campaign::where('is_active', true)
                ->where(function ($q) use ($userId, $userGroupId) {
                    $q->whereHas('users', fn ($inner) => $inner->where('users.id', $userId));
                    if ($userGroupId) {
                        $q->orWhereHas('userGroups', fn ($inner) => $inner->where('user_groups.id', $userGroupId));
                    }
                })
                ->first();
        }

        // Resolve caller ID: use campaign CID rotation if enabled, else static config
        $cidModel = $campaign?->nextCidModel();
        $callerId = $cidModel?->phone_number ?? $campaign?->caller_id ?? config('services.twilio.caller_id');

        \Log::info('[Twilio] handleOutbound: resolved dial context', [
            'call_sid' => $callSid,
            'to' => $to,
            'from' => $from,
            'resolved_user_id' => $userId,
            'campaign_id' => $campaign?->id,
            'cid_number_id' => $cidModel?->id,
            'caller_id' => $callerId,
            'is_whisper_consult' => $isWhisperConsult,
        ]);

        if (! $isWhisperConsult) {
            Conversation::create([
                'call_sid' => $callSid,
                'channel' => 'voice',
                'direction' => 'outbound',
                'status' => 'in_progress',
                'contact_phone' => $to,
                'campaign_id' => $campaign?->id,
                'cid_number_id' => $cidModel?->id,
                'assigned_to' => $userId,
                'started_at' => now(),
            ]);

            ActivityLogger::info(
                'call',
                'cid_rotation',
                'Outbound CID selected: '.$callerId,
                auth()->user(),
                $campaign,
                [
                    'cid' => $callerId,
                    'cid_id' => $cidModel?->id,
                    'rotation_on' => (bool) $campaign?->cid_rotation,
                    'to' => $to,
                    'call_sid' => $callSid,
                    'source' => 'browser_dial',
                ]
            );
        }

        $dial = $voice->dial('', [
            'callerId' => $callerId,
            'action' => $callbackUrl,
            'method' => 'POST',
            'statusCallback' => $callbackUrl,
            'statusCallbackEvent' => 'completed',
        ]);

        if (preg_match('/^[\d\+\-\(\) ]+$/', $to)) {
            $dial->number($to);
        } else {
            $dial->client($to);
        }

        return $this->twimlResponse($voice);
    }

    /**
     * Core In-Group routing:
     *  1. After-hours check
     *  2. Select agents based on routing algorithm
     *  3. Build <Dial> TwiML
     */
    private function routeToInGroup(
        InGroup $inGroup,
        VoiceResponse $voice,
        ?string $callSid,
        string $from,
        string $callbackUrl
    ): \Illuminate\Http\Response {
        // ── After-hours check ──────────────────────────────────────────────
        if ($inGroup->hours_json && ! $this->isWithinHours($inGroup)) {
            \Log::info('[Twilio] routeToInGroup: after-hours drop action', [
                'call_sid' => $callSid,
                'in_group_id' => $inGroup->id,
                'after_hours_action' => $inGroup->after_hours_action,
                'after_hours_destination' => $inGroup->after_hours_destination,
            ]);

            return $this->executeDropAction(
                $inGroup->after_hours_action,
                $inGroup->after_hours_destination,
                $callbackUrl,
                $voice
            );
        }

        // ── Select agents ──────────────────────────────────────────────────
        $agents = $this->selectAgents($inGroup);

        \Log::info('[Twilio] routeToInGroup: agent selection', [
            'in_group_id' => $inGroup->id,
            'in_group_name' => $inGroup->name,
            'call_sid' => $callSid,
            'agents_found' => $agents->count(),
            'agent_routing' => $inGroup->agent_routing,
            'agent_ids' => $agents->pluck('user_id')->toArray(),
        ]);

        if ($agents->isEmpty()) {
            // queue_max_wait_seconds = 0 → skip queue and drop immediately
            $maxQueueWait = $inGroup->queue_max_wait_seconds ?? 300;
            if ($maxQueueWait === 0) {
                return $this->executeDropAction(
                    $inGroup->drop_action,
                    $inGroup->drop_destination,
                    $callbackUrl,
                    $voice
                );
            }

            // Enter hold queue: announce, play hold music, then poll for agents
            $campaign = $inGroup->campaign;
            $holdMusic = $campaign?->hold_music_url ?: 'https://demo.twilio.com/docs/classic.mp3';
            $queueCheckUrl = rtrim(config('app.url'), '/')
                           ."/api/call/queue-check?in_group_id={$inGroup->id}";

            // Mark as queued so the AgentBecameAvailable listener can redirect it
            Conversation::where('call_sid', $callSid)->update(['status' => 'queued']);

            \Log::info('[Twilio] routeToInGroup: no agents, entering hold queue', [
                'in_group_id' => $inGroup->id,
                'call_sid' => $callSid,
                'queue_check_url' => $queueCheckUrl,
                'queue_max_wait_s' => $inGroup->queue_max_wait_seconds ?? 300,
            ]);

            $voice->say(
                'All agents are currently busy. Please hold and we will connect you shortly.',
                [
                    'voice' => $campaign?->tts_voice ?: 'alice',
                    'language' => $campaign?->tts_language ?: 'en-US',
                ]
            );
            // <Gather> fires on timeout (15 s) AND on caller hangup, giving us
            // immediate detection without waiting for the full music track.
            $gather = $voice->gather([
                'action' => $queueCheckUrl,
                'method' => 'POST',
                'timeout' => 15,
                'finishOnKey' => '',
            ]);
            $gather->play($holdMusic);

            // No <Redirect> fallback — it caused double-fires alongside <Gather>
            // creating duplicate simultaneous <Dial> responses.
            return $this->twimlResponse($voice);
        }

        // ── Build <Dial> TwiML ─────────────────────────────────────────────
        $timeout = $inGroup->max_wait_seconds ?: 20;
        $noAnswerUrl = rtrim(config('app.url'), '/')
          ."/api/call/no-answer?in_group_id={$inGroup->id}";

        $dial = $voice->dial('', [
            'callerId' => config('services.twilio.caller_id'),
            'timeout' => $timeout,
            'action' => $noAnswerUrl,
            'method' => 'POST',
            'statusCallback' => $callbackUrl,
            'statusCallbackEvent' => 'completed',
        ]);

        foreach ($agents as $agent) {
            $dial->client('user_'.$agent['user_id']);
        }

        \Log::info('[Twilio] routeToInGroup: dialing selected agents', [
            'call_sid' => $callSid,
            'in_group_id' => $inGroup->id,
            'timeout' => $timeout,
            'no_answer_url' => $noAnswerUrl,
            'agent_count' => $agents->count(),
            'agent_ids' => $agents->pluck('user_id')->toArray(),
        ]);

        // Update round-robin pivot for any single-agent algorithms
        if (
            in_array($inGroup->agent_routing, [
                'round_robin',
                'fewest_calls',
                'longest_idle',
            ]) &&
            $agents->count() === 1
        ) {
            $inGroup->users()->updateExistingPivot($agents->first()['user_id'], [
                'last_call_at' => now(),
            ]);
        }

        return $this->twimlResponse($voice);
    }

    /**
     * Serve an IVR menu as a <Gather> TwiML response.
     */
    private function serveIvrMenu(
        IvrMenu $menu,
        VoiceResponse $voice,
        int $attempts
    ): \Illuminate\Http\Response {
        $gatherUrl =
          rtrim(config('app.url'), '/').
          '/api/call/ivr-gather?menu_id='.
          $menu->id.
          '&attempts='.
          $attempts;

        $gather = $voice->gather([
            'numDigits' => 1,
            'timeout' => $menu->gather_timeout,
            'action' => $gatherUrl,
            'method' => 'POST',
        ]);

        if ($menu->greeting_type === 'audio') {
            $gather->play($menu->greeting_value ?? '');
        } else {
            $gather->say($menu->greeting_value ?? 'Please press a key to continue.');
        }

        // If caller doesn't press anything, re-post to gather with empty digit
        $voice->redirect($gatherUrl);

        return $this->twimlResponse($voice);
    }

    /**
     * Execute a matched IVR option.
     */
    private function executeIvrOption(
        \App\Models\IvrMenuOption $option,
        IvrMenu $menu,
        ?string $callSid,
        string $from,
        string $callbackUrl,
        VoiceResponse $voice
    ): \Illuminate\Http\Response {
        switch ($option->action) {
            case 'in_group':
                $inGroup = InGroup::find((int) $option->destination);
                if (! $inGroup || ! $inGroup->is_active) {
                    $voice->say('That option is not available. Goodbye.');
                    $voice->hangup();

                    return $this->twimlResponse($voice);
                }
                // Update conversation with the in_group
                Conversation::where('call_sid', $callSid)->update([
                    'in_group_id' => $inGroup->id,
                ]);

                return $this->routeToInGroup(
                    $inGroup,
                    $voice,
                    $callSid,
                    $from,
                    $callbackUrl
                );

            case 'ivr_menu':
                $nested = IvrMenu::with('options')->find((int) $option->destination);
                if (! $nested || ! $nested->is_active) {
                    $voice->say('That option is not available. Goodbye.');
                    $voice->hangup();

                    return $this->twimlResponse($voice);
                }

                return $this->serveIvrMenu($nested, $voice, 0);

            case 'transfer':
                $dial = $voice->dial('', [
                    'callerId' => config('services.twilio.caller_id'),
                ]);
                if (
                    $option->destination &&
                    preg_match('/^[\d\+\-\(\) ]+$/', $option->destination)
                ) {
                    $dial->number($option->destination);
                } else {
                    $voice->say('Transfer destination is not configured. Goodbye.');
                    $voice->hangup();
                }

                return $this->twimlResponse($voice);

            case 'voicemail':
                $voice->say('Please leave a message after the tone.');
                $voice->record([
                    'maxLength' => 120,
                    'transcribe' => false,
                    'recordingCallback' => rtrim(config('app.url'), '/').'/api/call/complete',
                ]);

                return $this->twimlResponse($voice);

            case 'hangup':
            default:
                $voice->say('Thank you. Goodbye.');
                $voice->hangup();

                return $this->twimlResponse($voice);
        }
    }

    /**
     * Execute an IVR invalid action (exceeded max attempts).
     */
    private function executeIvrInvalidAction(
        IvrMenu $menu,
        ?string $callSid,
        string $from,
        string $callbackUrl,
        VoiceResponse $voice
    ): \Illuminate\Http\Response {
        switch ($menu->invalid_action) {
            case 'transfer':
                $dial = $voice->dial('', [
                    'callerId' => config('services.twilio.caller_id'),
                ]);
                if (
                    $menu->invalid_destination &&
                    preg_match('/^[\d\+\-\(\) ]+$/', $menu->invalid_destination)
                ) {
                    $dial->number($menu->invalid_destination);
                } else {
                    $voice->say('We were unable to process your request. Goodbye.');
                    $voice->hangup();
                }

                return $this->twimlResponse($voice);

            case 'repeat':
                $voice->say('We did not receive a valid input.');
                $menu->load('options');

                return $this->serveIvrMenu($menu, $voice, 0);

            case 'hangup':
            default:
                $voice->say('We were unable to process your request. Goodbye.');
                $voice->hangup();

                return $this->twimlResponse($voice);
        }
    }

    /**
     * Execute a drop or after-hours action.
     */
    private function executeDropAction(
        string $action,
        ?string $destination,
        string $callbackUrl,
        VoiceResponse $voice
    ): \Illuminate\Http\Response {
        switch ($action) {
            case 'transfer':
                $dial = $voice->dial('', [
                    'callerId' => config('services.twilio.caller_id'),
                    'action' => $callbackUrl,
                    'method' => 'POST',
                    'statusCallback' => $callbackUrl,
                    'statusCallbackEvent' => 'completed',
                ]);
                if ($destination && preg_match('/^[\d\+\-\(\) ]+$/', $destination)) {
                    $dial->number($destination);
                } else {
                    $voice->say(
                        'We are unable to connect your call. Please try again later.'
                    );
                    $voice->hangup();
                }

                return $this->twimlResponse($voice);

            case 'voicemail':
                $voice->say(
                    'All agents are currently unavailable. Please leave a message after the tone.'
                );
                $voice->record([
                    'maxLength' => 120,
                    'transcribe' => false,
                    'recordingCallback' => $callbackUrl,
                ]);

                return $this->twimlResponse($voice);

            case 'hangup':
            default:
                $voice->say(
                    'All agents are currently unavailable. Please try again later. Goodbye.'
                );
                $voice->hangup();

                return $this->twimlResponse($voice);
        }
    }

    /**
     * Resolve the Campaign for an active call via Conversation → campaign,
     * or Conversation → InGroup → campaign, or the supplied $inGroup directly.
     */
    private function campaignForCall(?string $callSid, ?InGroup $inGroup = null): ?Campaign
    {
        if ($callSid) {
            $conv = Conversation::with(['campaign', 'inGroup.campaign'])
                ->where('call_sid', $callSid)->first();
            if ($conv?->campaign) {
                return $conv->campaign;
            }
            if ($conv?->inGroup?->campaign) {
                return $conv->inGroup->campaign;
            }
        }
        if ($inGroup?->campaign_id) {
            return $inGroup->campaign ?? Campaign::find($inGroup->campaign_id);
        }

        return null;
    }

    /**
     * Select agents from an In-Group based on the configured routing algorithm.
     * Returns a collection of [user_id => int] arrays.
     */
    private function selectAgents(
        InGroup $inGroup
    ): \Illuminate\Support\Collection {
        // Base: get agents who are (a) in the group, (b) active in pivot, (c) available
        $availableUserIds = AgentStatus::whereHas(
            'statusType',
            fn ($q) => $q->where('is_available', true)->where('handles_inbound', true)
        )->pluck('user_id');

        $pivotQuery = $inGroup
            ->users()
            ->wherePivot('is_active', true)
            ->whereIn('users.id', $availableUserIds);

        switch ($inGroup->agent_routing) {
            case 'ring_all':
                return $pivotQuery->get()->map(fn ($u) => ['user_id' => $u->id]);

            case 'round_robin':
                // Agent with the oldest (or null) last_call_at
                $agent = $pivotQuery
                    ->orderByRaw('COALESCE(in_group_user.last_call_at, "1970-01-01") ASC')
                    ->first();

                return $agent ? collect([['user_id' => $agent->id]]) : collect();

            case 'fewest_calls':
                // Agent with fewest conversations assigned today in this in_group
                $agent = $pivotQuery
                    ->get()
                    ->sortBy(function ($user) use ($inGroup) {
                        return Conversation::where('assigned_to', $user->id)
                            ->where('in_group_id', $inGroup->id)
                            ->whereDate('started_at', today())
                            ->count();
                    })
                    ->first();

                return $agent ? collect([['user_id' => $agent->id]]) : collect();

            case 'longest_idle':
                // Agent with earliest AgentStatus.started_at (idle longest)
                $agent = $pivotQuery
                    ->join('agent_statuses', 'agent_statuses.user_id', '=', 'users.id')
                    ->orderBy('agent_statuses.started_at', 'asc')
                    ->select('users.*')
                    ->first();

                return $agent ? collect([['user_id' => $agent->id]]) : collect();

            default:
                return collect();
        }
    }

    /**
     * Determine if the current time is within the in-group's operating hours.
     */
    private function isWithinHours(InGroup $inGroup): bool
    {
        $tz = $inGroup->timezone ?: 'UTC';
        $now = Carbon::now($tz);
        $day = strtolower($now->format('D')); // mon, tue, wed, thu, fri, sat, sun
        $hours = $inGroup->hours_json;

        if (empty($hours[$day])) {
            return false; // closed today
        }

        $open = Carbon::createFromTimeString($hours[$day]['open'] ?? '00:00', $tz);
        $close = Carbon::createFromTimeString(
            $hours[$day]['close'] ?? '23:59',
            $tz
        );

        return $now->between($open, $close);
    }

    /**
     * Return a TwiML XML response.
     */
    private function twimlResponse(
        VoiceResponse $voice
    ): \Illuminate\Http\Response {
        \Log::info('[Twilio] TwiML response', ['xml' => $voice->__toString()]);

        return response($voice->__toString(), 200)->header(
            'Content-Type',
            'text/xml'
        );
    }

    private function twilioClient(): TwilioClient
    {
        return new TwilioClient(
            config('services.twilio.key'), // API Key SID  (SK…)
            config('services.twilio.secret'), // API Secret
            config('services.twilio.sid') // Account SID  (AC…)
        );
    }
}
