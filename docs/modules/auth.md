# Auth Module

## Purpose

This module handles authentication, registration, and password recovery flows.

## Diagram

See [docs/diagrams/modules/auth.mmd](docs/diagrams/modules/auth.mmd).

## Source Location

- `app/Livewire/Auth/`

## Components

- `Login`: authenticates an existing user.
- `Register`: creates a new account where registration is enabled.
- `ForgotPassword`: starts password reset flow.
- `ResetPassword`: completes password reset.

## Related Domain Objects

- `User`

## Notes

- This module owns entry and recovery flows for application access.