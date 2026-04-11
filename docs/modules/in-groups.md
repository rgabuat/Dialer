# In-Groups Module

## Summary

- Area: Inbound
- Purpose: manage inbound call queues — agent assignment, routing algorithm, operating hours, and drop/after-hours actions.
- Source: `app/Livewire/InGroups/`
- Diagram: [../diagrams/modules/in-groups.mmd](../diagrams/modules/in-groups.mmd)

## Components

- `InGroupsIndex`: lists in-groups with search and pagination.
- `InGroupCreate`: creates a new in-group including per-day operating hours.
- `InGroupEdit`: edits an existing in-group; three tabs — Settings, Agents, DIDs.

## Related Models

- `InGroup`: core queue record.
- `in_group_user` pivot: links users to in-groups with `priority`, `last_call_at`, `is_active`.
- `Conversation`: stores `in_group_id` once a call is routed.
- `Campaign`: `in_groups.campaign_id` (nullable FK) — an in-group is assigned to at most one campaign. The assignment is managed from the campaign edit page.

## Agent Routing Algorithms

| Value | Behaviour |
|---|---|
| `ring_all` | Dial every available agent simultaneously. First to answer gets the call. |
| `round_robin` | Dial the single agent with the oldest `last_call_at` in the pivot. |
| `fewest_calls` | Dial the single agent with fewest conversations assigned today in this group. |
| `longest_idle` | Dial the single agent with the earliest `agent_statuses.started_at`. |

`ring_all` is best for small teams where fastest pickup matters. All other modes dial exactly one agent per attempt; if that agent doesn't answer the caller is re-queued.

## Queue Hold Flow

When `routeToInGroup()` finds no available agents and `queue_max_wait_seconds > 0`, the caller enters a hold queue:

1. **Enter queue** — conversation row is set to `status = queued`. Caller hears the hold announcement and music.
2. **Hold loop** — TwiML uses `<Gather timeout="15" finishOnKey="">` wrapping `<Play>`. Gather fires every 15 seconds to `POST /api/call/queue-check?in_group_id={id}`.
3. **Hangup detection** — if the caller hangs up during hold, Twilio posts `CallStatus=completed` to the same `callQueueCheck` endpoint. The controller marks the conversation `abandoned` and returns empty TwiML immediately.
4. **Agent becomes available** — `AgentBecameAvailable` listener redirects the live queued call directly to `callQueueCheck` via the Twilio REST API, bypassing the 15-second wait.
5. **Agent check** — `callQueueCheck` calls `selectAgents()`. If agents are found, it sets conversation to `in_progress` and returns `<Dial>` TwiML. An idempotency guard prevents duplicate `<Dial>` responses if the webhook fires twice.
6. **No answer** — if the dialled agent doesn't answer, `POST /api/call/no-answer` fires. If the caller is still within `queue_max_wait_seconds`, the conversation is re-queued and hold music resumes. Otherwise the conversation is marked `abandoned` and `drop_action` executes.
7. **Max wait exceeded** — once `now - started_at >= queue_max_wait_seconds`, `callQueueCheck` marks the conversation `abandoned` and executes `drop_action`.

### Key Queue Fields

| Field | Default | Description |
|---|---|---|
| `queue_max_wait_seconds` | 300 | Maximum seconds a caller waits before drop action fires. `0` = skip queue, drop immediately. |
| `max_wait_seconds` | 20 | Dial timeout (ring duration) per agent attempt. |
| `drop_action` | — | `hangup`, `transfer`, or `voicemail`. Executed on queue expiry or no-agent. |
| `drop_destination` | — | Transfer target when `drop_action = transfer`. |

## Operating Hours

`hours_json` is a JSON object keyed by short day name (`mon`–`sun`). Each enabled day has `open` and `close` time strings (`HH:MM`). A `null` value means always open.  
After-hours calls execute `after_hours_action` (`hangup`, `transfer`, or `voicemail`).

## Drop Actions

When no agent is available and queueing is disabled (or max wait is exceeded), `drop_action` executes: `hangup`, `transfer` to `drop_destination`, or `voicemail`.

## Navigation

1. [README.md](README.md)
2. [dids.md](dids.md)
3. [ivr-menus.md](ivr-menus.md)
4. [../diagrams/inbound-call-flow.mmd](../diagrams/inbound-call-flow.mmd)
5. [../diagrams/modules/in-groups.mmd](../diagrams/modules/in-groups.mmd)
