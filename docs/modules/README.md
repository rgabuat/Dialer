# Module Documentation

Module guides are split by domain so feature documentation stays small and easy to scan.

## Navigation

1. [../README.md](../README.md)
2. [../app-structure.md](../app-structure.md)
3. [../tech-stack.md](../tech-stack.md)
4. [../diagrams/README.md](../diagrams/README.md)

## Inbound

- [in-groups.md](in-groups.md): inbound call queues, agent routing algorithms, operating hours, and campaign binding.
- [dids.md](dids.md): DID inbound routing numbers and CID Numbers outbound caller-ID pool.
- [ivr-menus.md](ivr-menus.md): IVR menu configuration and digit-option routing.

## Campaign

- [campaign.md](campaign.md): campaign creation, dialer settings, Vicidial-style dial modes, and inbound-group binding.
- [call-lists.md](call-lists.md): call list management and CSV lead import per campaign.
- [dispositions.md](dispositions.md): call disposition codes, ACW timer, and callback scheduling.

## Operations

- [activitylogs.md](activitylogs.md): activity history and audit visibility.
- [agent.md](agent.md): agent status management, calling capability flags, and Twilio device lifecycle.
- [leads.md](leads.md): lead management.
- [stores.md](stores.md): store management.

## Access Control

- [auth.md](auth.md): authentication flows.
- [permissions.md](permissions.md): permission administration.
- [roles.md](roles.md): role administration.

## Administration

- [settings.md](settings.md): user settings and preferences.
- [user-groups.md](user-groups.md): group-based user organization.
- [users.md](users.md): user administration.

## Related Diagrams

- High-level module map: [../diagrams/modules.mmd](../diagrams/modules.mmd)
- Module diagram sources: [../diagrams/README.md](../diagrams/README.md)

## Source Location

Application feature code lives under `app/Livewire/`.