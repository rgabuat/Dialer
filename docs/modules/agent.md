# Agent Module

## Summary

- Area: Operations / Activity
- Purpose: manage agent visibility, status switching, and Twilio device lifecycle.
- Source: `app/Livewire/Agent/`
- Diagram: [../diagrams/modules/agent.mmd](../diagrams/modules/agent.mmd)

## Components

- `AgentStatusIndex`: presents all agents and their current status.
- `StatusSwitcher`: changes the agent's own status during a shift; dispatches `agent-status-changed` browser event.

## Agent Status Types

Each `AgentStatusType` record has two capability flags that control the Twilio calling device:

| Flag | Effect |
|---|---|  
| `handles_inbound` | Device registers and listens for incoming Twilio calls. |
| `handles_outbound` | Dial button is shown; agent can place outbound calls. |

When **neither** flag is set, `disableCalling()` is called and the Twilio `Device` is destroyed.

## Twilio Device Lifecycle

The device is managed in `resources/views/components/agent-control-bar.blade.php` (`agentPhone()` Alpine component):

1. On mount, `init()` checks `window._twilioDevice` globals to restore state after `wire:navigate`.
2. `enableCalling()` fetches a token from `GET /twilio/token` and registers the device.
3. `disableCalling()` disconnects any active call and destroys the device.
4. `_rebindDeviceEvents()` reattaches `registered`, `error`, and `incoming` handlers after navigation.

## Status Change Flow

1. Agent selects a status → `StatusSwitcher.setStatus()` validates the status type and auth, then calls `AgentStatusService.change()`.
2. Service updates `agent_statuses`, closes the open `agent_status_logs` row, creates a new log entry, then dispatches `AgentStatusUpdated` via `dispatch()->afterResponse()` so the Livewire response returns before the event fires.
3. Livewire dispatches `agent-status-changed` immediately (includes `handles_inbound`/`handles_outbound`).
4. Echo listener in `app.blade.php` also re-dispatches `agent-status-changed` on the broadcast channel (also includes the flags to prevent race-condition device teardown).

## AgentBecameAvailable Listener

`app/Listeners/AgentBecameAvailable.php` handles `AgentStatusUpdated` events.

**Flow:**

1. Checks `handles_inbound` on the new status — skips the rest if false.
2. Queries `in_group_user` pivot to find in-groups where the agent is active.
3. Fetches up to 10 oldest `queued` conversations (`started_at ASC`, `ended_at IS NULL`) across those groups — FIFO order.
4. For each candidate, fetches the live Twilio call status via REST API:
   - If Twilio reports the call as non-active (`completed`, `canceled`, etc.), marks the conversation `completed` and moves to the next candidate.
   - If the call is live (`in-progress`, `ringing`, `queued`), redirects it to `POST /api/call/queue-check?in_group_id={id}` and stops — one redirect per event.
5. If no active candidate is found after scanning all candidates, logs the outcome and exits.

**Why 10 candidates instead of 1:** A single stale queued row (Twilio already completed the call, DB not yet updated) used to block the listener entirely. Scanning a small batch lets it skip stale rows and reach the first genuinely waiting caller.

## Related Types

- `AgentStatus`
- `AgentStatusLog`
- `AgentStatusType` (with `handles_inbound`, `handles_outbound`)
- `AgentStatusService`
- `AgentStatusUpdated` event
- `AgentBecameAvailable` listener

## Navigation

1. [README.md](README.md)
2. [activitylogs.md](activitylogs.md)
3. [campaign.md](campaign.md)
4. [../diagrams/modules/agent.mmd](../diagrams/modules/agent.mmd)