# User Groups Module

## Purpose

This module organizes users into groups and associates those groups with campaigns.

## Diagram

See [docs/diagrams/modules/user-groups.mmd](docs/diagrams/modules/user-groups.mmd).

## Source Location

- `app/Livewire/UserGroups/`

## Components

- `UserGroupsIndex`: lists user groups.
- `UserGroupCreate`: creates a group.
- `UserGroupEdit`: updates a group.

## Related Domain Objects

- `UserGroup`
- `Campaign`

## Notes

- This module connects users to shared operational campaign context.