# Agent Module

## Purpose

This module manages agent status visibility and agent-side status switching.

## Diagram

See [docs/diagrams/modules/agent.mmd](docs/diagrams/modules/agent.mmd).

## Source Location

- `app/Livewire/Agent/`

## Components

- `AgentStatusIndex`: presents agent status data.
- `StatusSwitcher`: lets an agent change status within the dialer workflow.

## Related Domain Objects And Services

- `AgentStatus`
- `AgentStatusLog`
- `AgentStatusType`
- `AgentStatusService`

## Notes

- This module sits close to real-time operational behavior and status tracking.