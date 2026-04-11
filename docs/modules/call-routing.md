# Call Routing — Webhooks, Queue Engine & Diagnostics

This document covers the server-side webhook layer that bridges Twilio and the application: every HTTP endpoint Twilio calls, the CSRF exception requirement, the queue hold-loop mechanics, bug-fix history for the queue engine, and the frontend error-logging pipeline.

---

## Webhook Endpoint Reference

| Route | Controller method | Purpose |
|-------|------------------|---------|
| `POST /api/call-routing` | `handleCallRouting` | Entry point for every inbound call. Resolves DID → IVR / In-Group, or handles outbound leg. |
| `POST /api/call/complete` | `callComplete` | Twilio `statusCallback` after any call leg ends. Updates `Conversation` record. |
| `POST /api/call/no-answer` | `callNoAnswer` | `<Dial>` action when no agent answered or agent hung up first. Decides: re-queue vs. drop. |
| `POST /api/call/queue-check` | `callQueueCheck` | Called every ~15 s while caller waits on hold. Also the redirect target when `AgentBecameAvailable` fires. |
| `POST /api/call/ivr-gather` | `ivrGather` | Receives the digit a caller pressed in an IVR `<Gather>`. |
| `POST /api/dialer/connect-to-agent` | _(Dialer)_ | Outbound dialer: connects queued preview/manual dial lead to an agent. |
| `POST /api/client-log` | `ClientLogController@store` | Browser → server log shipping (throttle: 60 req/min). |

All Twilio endpoints must be listed in `VerifyCsrfToken::$except` — see [CSRF Exceptions](#csrf-exceptions) below.

---

## CSRF Exceptions

### Why Twilio Webhooks Fail Without an Exception

Laravel's `VerifyCsrfToken` middleware normally rejects unauthenticated `POST` requests with `419 Page Expired`. Twilio webhooks are server-to-server HTTP posts that carry no CSRF token, so they would always result in 419 and silently drop the call.

A second, subtler issue: when Twilio's REST API redirects a call (e.g., `AgentBecameAvailable` calls `$call->update(['url' => ...])`) the HTTP `Referer` header is set to the same domain. Laravel Sanctum's `EnsureFrontendRequestsAreStateful` middleware then treats the request as a same-origin browser request and enforces CSRF — even for calls explicitly hitting `/api/*` routes.

### Required Exceptions

File: `app/Http/Middleware/VerifyCsrfToken.php`

```php
protected $except = [
    'api/call-routing',
    'api/call/complete',
    'api/call/no-answer',
    'api/call/queue-check',      // ← previously missing; caused 419 on REST-API redirects
    'api/call/ivr-gather',
    'api/call/forward-twiml',
    'api/dialer/connect-to-agent',
    'api/client-log',
];
```

---

## Queue Hold-Loop Mechanics

### Overview

When `routeToInGroup` finds no available agents and `queue_max_wait_seconds > 0`, the caller enters the hold queue:

1. `conversation.status` set to `queued`.  
2. TwiML returns `<Gather timeout="15" finishOnKey="">` wrapping a `<Play>` of hold music.  
3. Twilio calls `POST /api/call/queue-check` after 15 seconds (Gather timeout) **or immediately if the caller hangs up** (Gather also fires on hangup with `CallStatus=completed`).  
4. `callQueueCheck` checks, in order:
   - **Hangup detection**: if `CallStatus` is `completed`, marks the conversation `abandoned` and returns empty TwiML.
   - **Idempotency guard**: if `conversation.status` is already `in_progress`, returns empty TwiML (prevents the double-Dial race condition — see bugs below).
   - **Max-wait exceeded**: if `now - conversation.started_at >= queue_max_wait_seconds`, executes the in-group `drop_action`.
   - **Agent available**: calls `selectAgents()`. If any match, sets `conversation.status = in_progress`, returns `<Dial>` TwiML with `<Client>` elements.
   - **Still no agents**: returns another `<Gather timeout="15">` hold loop.

### Why `<Gather>` and Not `<Play><Redirect>`

`<Play>` plays an audio file for its full duration; a following `<Redirect>` only fires after the track completes (~3 min). `<Gather>` fires after its `timeout` seconds **regardless of the audio length**, and also fires immediately when the caller disconnects. This means:

- Hold polling interval is consistently ~15 s, not 3 min.
- Caller hangups are detected without waiting for the next webhook cycle.

---

## Agent-Became-Available Shortcut

`AgentBecameAvailable` (listener on `AgentStatusUpdated`) fires synchronously every time an agent's status switches to one with `handles_inbound = true`.

**Scan logic (FIFO):**

1. Query up to 10 oldest `queued` conversations ordered by `started_at ASC`.  
2. For each, verify the call is still live via the Twilio REST API (`$call->fetch()`).  
3. If Twilio reports the call as `completed` or `canceled`, mark the conversation `completed` and continue to the next candidate.  
4. If the call is live, call `$call->update(['url' => route('call.queue-check')])` to trigger an immediate `callQueueCheck` (bypassing the 15-second hold wait).  
5. Set `$redirected = true` and **break** — only one caller is redirected per event.

**Why scan 10 instead of 1:**  
A single stale DB row (conversation status `queued` but Twilio side already `completed`) would have previously blocked all subsequent callers. Scanning 10 lets the listener skip completed rows and reach the first genuinely live queued call.

---

## callNoAnswer — Re-queue vs. Drop Decision

`POST /api/call/no-answer` is the `<Dial>` action callback. It receives `DialCallStatus`.

| Condition | Action |
|-----------|--------|
| `DialCallStatus = completed` | Call ended normally (caller hung up during or after dial). Return `<Say> goodbye + <Hangup>`. |
| No-answer / busy / failed, within `queue_max_wait_seconds` | Set `status = queued`, return `<Gather timeout=15>` hold music (back to queue loop). |
| No-answer / busy / failed, max wait exceeded | Set `status = abandoned`, execute `drop_action`. |
| No in-group context found | Log warning, execute `drop_action`. |

**Important**: `callComplete` (the `statusCallback`) skips updating a conversation that is still `queued`, so a freshly re-queued caller is not prematurely marked completed by a late-arriving status webhook.

---

## Terminal-Status Guard in handleCallRouting

Every Twilio call fires `POST /api/call-routing` once at pickup **and** may fire again for recording callbacks or late status webhooks. The guard at the top of `handleCallRouting` prevents ended calls from being re-processed:

```
If CallStatus ∈ {completed, canceled, failed, busy, no-answer}
  → mark conversation abandoned, return empty <Response/>

If RecordingSid present in parameters
  → recording callback, return empty <Response/>
```

---

## Frontend Error Logging

### ClientLogController

`app/Http/Controllers/ClientLogController.php` receives structured browser log entries:

- **Route**: `POST /api/client-log` (throttle: 60 requests/min, no auth required, CSRF exempt)
- **Payload**: `level` (error/warn/info/debug), `message` (max 2000 chars), `url`, optional `context` object
- **Security**: control characters stripped to prevent log injection; user ID, IP, URL, and User-Agent attached automatically
- **Output**: written to `storage/logs/laravel.log` with `[Frontend]` prefix at the logged level

### JavaScript Capturer

Injected into `resources/views/components/layouts/app.blade.php` as an IIFE before `</body>`:

| Hook | What it captures |
|------|-----------------|
| `window.onerror` | Uncaught JS exceptions (file, line, col, stack) |
| `window.onunhandledrejection` | Unhandled Promise rejections |
| `document` `livewire:error` event | Livewire component error events |
| `Livewire.hook('request.error')` | Livewire v3 network/server errors |
| `Livewire.hook('commit.error')` | Livewire v3 commit failures |
| `console.error` wrapper | Explicit `console.error()` calls from app code |

Entries are batched (max 20, flushed every 500 ms) to avoid flooding the endpoint.

**Manual logging from the browser console:**

```javascript
window._clientLog('error', 'Something went wrong', { extra: 'data' });
```

---

## Diagnostic Log Prefixes

All server-side log entries use a consistent prefix for easy `grep`:

| Prefix | Source |
|--------|--------|
| `[Twilio]` | `TwilioController` — all webhook entry/exit points |
| `[AgentBecameAvailable]` | `AgentBecameAvailable` listener |
| `[AgentStatusService]` | `AgentStatusService` — status DB writes and event dispatch |
| `[StatusSwitcher]` | `StatusSwitcher` Livewire component — mount, render, setStatus |
| `[Frontend]` | `ClientLogController` — browser-captured errors |

### Useful grep commands

```bash
# All Twilio webhook activity
grep '\[Twilio\]' storage/logs/laravel.log

# Only errors (any prefix)
grep '\[Twilio\]\|\[AgentBecameAvailable\]\|\[AgentStatusService\]\|\[StatusSwitcher\]\|\[Frontend\]' storage/logs/laravel.log | grep '"level":"error"'

# Follow live
tail -f storage/logs/laravel.log | grep '\[Twilio\]\|\[Frontend\]'
```

---

## Bug-Fix History

The following bugs were diagnosed and resolved through log analysis.

### Bug 1 — Caller Hangup Not Detected (Hold Loop Too Slow)

**Symptom**: Callers who hung up during hold music remained in `queued` state for up to 3 minutes.  
**Root cause**: `<Play url=...><Redirect>` pattern — Redirect only fires after the full audio track finishes.  
**Fix**: Replaced with `<Gather timeout="15" finishOnKey=""><Play>`. Gather fires on timeout and immediately on hangup.

### Bug 2 — Stale DB Row Blocks Queue

**Symptom**: A `queued` conversation whose Twilio call had already ended prevented any subsequent callers from being connected.  
**Root cause**: `AgentBecameAvailable` used `->limit(1)` and never skipped completed calls.  
**Fix**: Changed to `->limit(10)`, verify each call via Twilio REST API, skip and mark completed if Twilio reports it ended.

### Bug 3 — 419 CSRF on Queue-Check (Root Cause of Call Drops)

**Symptom**: Calls would be redirected by `AgentBecameAvailable` but then drop immediately with no audio.  
**Root cause**: `api/call/queue-check` was not in `VerifyCsrfToken::$except`. Twilio REST API redirect carried a same-domain `Referer` header, causing Sanctum to treat it as a browser request and enforce CSRF → 419 response.  
**Fix**: Added `api/call/queue-check` (and other missing paths) to `$except`.

### Bug 4 — Double-Dial Re-Queues Caller on Hangup

**Symptom**: After an agent hung up, the caller heard hold music again instead of a goodbye message. Log showed two identical `callQueueCheck` POST requests one second apart for the same `CallSid`.  
**Root cause**: The old `<Gather><Play></Gather><Redirect>` pattern fired **both** the Gather action and the subsequent Redirect simultaneously, creating two competing `<Dial>` TwiML responses. The second Dial reported `DialCallStatus=busy` and triggered the re-queue logic.  
**Fix**:
  - Removed all `<Redirect>` fallbacks from `callQueueCheck` and `routeToInGroup` hold loops.
  - Added `in_progress` idempotency guard: if `conversation.status = in_progress` on entry, return empty TwiML immediately.

### Bug 5 — broadcast() Did Not Fire PHP Listeners

**Symptom**: `AgentBecameAvailable` listener never fired; queue callers were never redirected on agent status change.  
**Root cause**: `broadcast()` uses `BROADCAST_DRIVER` (Pusher) and sends events to the WebSocket server, bypassing PHP listeners. `event()` dispatches to registered PHP listeners.  
**Fix**: Changed `app()->terminating(fn() => broadcast())` to `dispatch(fn() => event(new AgentStatusUpdated($payload)))->afterResponse()`.

### Bug 6 — Null Status Type Crash

**Symptom**: Application error when changing agent status via the `StatusSwitcher` component.  
**Root cause**: `AgentStatusType::find($statusTypeId)` returned `null` for a deleted/invalid status type; the code did not null-check before using the result.  
**Fix**: Added null-checks with early returns and error logs in both `AgentStatusService::change()` and `StatusSwitcher::setStatus()`.

### Bug 7 — Terminal Webhook Re-Routes Ended Calls

**Symptom**: Ended calls were occasionally re-processed by `handleCallRouting`, creating phantom `Conversation` updates.  
**Root cause**: Twilio sends a final status webhook (`CallStatus=completed`) to the call's original URL. Without a guard, the controller would attempt to route an already-ended call.  
**Fix**: Added terminal-status guard at the top of `handleCallRouting` (see [Terminal-Status Guard](#terminal-status-guard-in-handlecallrouting)).
