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

1. Agent selects a status → `StatusSwitcher.setStatus()` → `AgentStatusService.change()`.
2. Service updates `agent_statuses`, logs to `agent_status_logs`, broadcasts `AgentStatusUpdated` event.
3. Livewire dispatches `agent-status-changed` immediately (includes `handles_inbound`/`handles_outbound`).
4. Echo listener in `app.blade.php` also re-dispatches `agent-status-changed` on the broadcast channel (also includes the flags to prevent race-condition device teardown).

## Related Types

- `AgentStatus`
- `AgentStatusLog`
- `AgentStatusType` (with `handles_inbound`, `handles_outbound`)
- `AgentStatusService`
- `AgentStatusUpdated` event

## Navigation

1. [README.md](README.md)
2. [activitylogs.md](activitylogs.md)
3. [campaign.md](campaign.md)
4. [../diagrams/modules/agent.mmd](../diagrams/modules/agent.mmd)