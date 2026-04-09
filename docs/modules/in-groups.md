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

## Agent Routing Algorithms

| Value | Behaviour |
|---|---|
| `ring_all` | Dial every available agent simultaneously. |
| `round_robin` | Dial the agent with the oldest `last_call_at` in the pivot. |
| `fewest_calls` | Dial the agent with fewest conversations assigned today in this group. |
| `longest_idle` | Dial the agent with the earliest `agent_statuses.started_at`. |

## Operating Hours

`hours_json` is a JSON object keyed by short day name (`mon`–`sun`). Each enabled day has `open` and `close` time strings (`HH:MM`). A `null` value means always open.  
After-hours calls execute `after_hours_action` (`hangup`, `transfer`, or `voicemail`).

## Drop Actions

When no agent is available, `drop_action` is executed: `hangup`, `transfer` to `drop_destination`, or `voicemail`.

## Navigation

1. [README.md](README.md)
2. [dids.md](dids.md)
3. [ivr-menus.md](ivr-menus.md)
4. [../diagrams/inbound-call-flow.mmd](../diagrams/inbound-call-flow.mmd)
5. [../diagrams/modules/in-groups.mmd](../diagrams/modules/in-groups.mmd)
