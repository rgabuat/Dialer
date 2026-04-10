<?php

namespace App\Listeners;

use App\Events\AgentStatusUpdated;
use App\Models\Conversation;
use App\Models\InGroup;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class AgentBecameAvailable
{
  public function handle(AgentStatusUpdated $event): void
  {
    try {
      $payload = $event->payload;

      Log::info('[AgentBecameAvailable] Event received', [
        'user_id'         => $payload['user_id'] ?? null,
        'user_email'      => $payload['user_email'] ?? null,
        'status_name'     => $payload['status_name'] ?? null,
        'handles_inbound' => $payload['handles_inbound'] ?? false,
      ]);

      // Only react when this status can receive inbound calls.
      if (!(bool) ($payload['handles_inbound'] ?? false)) {
        Log::debug('[AgentBecameAvailable] Skipped — status does not handle inbound', [
          'user_id'     => $payload['user_id'] ?? null,
          'status_name' => $payload['status_name'] ?? null,
        ]);
        return;
      }

      $agentUserId = (int) ($payload['user_id'] ?? 0);
      if ($agentUserId <= 0) {
        return;
      }

      // Find in-groups where this agent is active.
      $inGroupIds = InGroup::query()
        ->whereHas('users', function ($q) use ($agentUserId) {
          $q->where('users.id', $agentUserId)
            ->where('in_group_user.is_active', true);
        })
        ->pluck('id');

      if ($inGroupIds->isEmpty()) {
        Log::debug('[AgentBecameAvailable] No active in-groups for agent', [
          'user_id' => $agentUserId,
        ]);
        return;
      }

      Log::info('[AgentBecameAvailable] Searching queued calls', [
        'user_id'     => $agentUserId,
        'in_group_ids' => $inGroupIds->toArray(),
      ]);

      // Find several oldest queued candidates (FIFO).
      // We still redirect only one caller per event, but scanning a small
      // batch avoids getting stuck on a single stale queued row.
      $queuedCalls = Conversation::query()
        ->where('status', 'queued')
        ->whereIn('in_group_id', $inGroupIds)
        ->whereNull('ended_at')
        ->whereNotNull('call_sid')
        ->orderBy('started_at')
        ->limit(10)
        ->get(['call_sid', 'in_group_id']);

      if ($queuedCalls->isEmpty()) {
        Log::info('[AgentBecameAvailable] No queued calls found for agent\'s in-groups', [
          'user_id'      => $agentUserId,
          'in_group_ids' => $inGroupIds->toArray(),
        ]);
        return;
      }

      Log::info('[AgentBecameAvailable] Found queued call, attempting redirect', [
        'user_id'  => $agentUserId,
        'call_sid' => $queuedCalls->first()->call_sid,
        'in_group_id' => $queuedCalls->first()->in_group_id,
      ]);

      $twilio = new TwilioClient(
        config('services.twilio.key'),
        config('services.twilio.secret'),
        config('services.twilio.sid')
      );

      $redirected = false;

      foreach ($queuedCalls as $conversation) {
        try {
          $call = $twilio->calls($conversation->call_sid)->fetch();
          $callStatus = strtolower((string) ($call->status ?? ''));

          // Only live calls can be redirected to queue-check.
          if (!in_array($callStatus, ['queued', 'ringing', 'in-progress'], true)) {
            Conversation::query()
              ->where('call_sid', $conversation->call_sid)
              ->update([
                'status'   => 'completed',
                'ended_at' => now(),
              ]);

            Log::info('[AgentBecameAvailable] Skipped non-active queued call', [
              'call_sid'    => $conversation->call_sid,
              'in_group_id' => $conversation->in_group_id,
              'twilio_status' => $callStatus,
            ]);
            continue;
          }

          $queueCheckUrl = rtrim(config('app.url'), '/')
            . '/api/call/queue-check?in_group_id=' . $conversation->in_group_id;

          $twilio->calls($conversation->call_sid)->update([
            'url'    => $queueCheckUrl,
            'method' => 'POST',
          ]);

          Log::info('[AgentBecameAvailable] Redirected queued call to queue-check', [
            'call_sid'        => $conversation->call_sid,
            'in_group_id'     => $conversation->in_group_id,
            'queue_check_url' => $queueCheckUrl,
          ]);

          $redirected = true;
          break;
        } catch (\Throwable $e) {
          $message = $e->getMessage();

          // Twilio reports stale calls this way; stop retrying those rows.
          if (stripos($message, 'Call is not in-progress') !== false) {
            Conversation::query()
              ->where('call_sid', $conversation->call_sid)
              ->update([
                'status'   => 'completed',
                'ended_at' => now(),
              ]);

            Log::info('[AgentBecameAvailable] Skipped stale queued call', [
              'call_sid'    => $conversation->call_sid,
              'in_group_id' => $conversation->in_group_id,
            ]);
            continue;
          }

          Log::warning('[AgentBecameAvailable] Failed to refresh queued call', [
            'call_sid'    => $conversation->call_sid,
            'in_group_id' => $conversation->in_group_id,
            'error'       => $message,
          ]);
        }
      }

      if (!$redirected) {
        Log::info('[AgentBecameAvailable] No redirect sent after scanning queued candidates', [
          'user_id' => $agentUserId,
          'candidate_count' => $queuedCalls->count(),
        ]);
      }
    } catch (\Throwable $e) {
      // This listener is best-effort and must never break agent status updates.
      Log::warning('[AgentBecameAvailable] Listener failed unexpectedly', [
        'error' => $e->getMessage(),
      ]);
    }
  }
}
