# Users Module

## Purpose

This module manages user administration flows including creation, editing, listing, and deletion.

## Diagram

See [docs/diagrams/modules/users.mmd](docs/diagrams/modules/users.mmd).

## Source Location

- `app/Livewire/Users/`

## Components

- `UsersIndex`: lists users.
- `UserCreate`: creates a user.
- `UserEdit`: updates user data.
- `UserDelete`: removes a user.

## Related Domain Objects

- `User`
- `UsersMeta`
- `UserGroup`

## Notes

- This module is the main administrative surface for user lifecycle management.