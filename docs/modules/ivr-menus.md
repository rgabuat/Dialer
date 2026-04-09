# IVR Menus Module

## Summary

- Area: Inbound
- Purpose: configure interactive voice response menus — greeting, digit options, invalid-input handling.
- Source: `app/Livewire/IvrMenus/`
- Diagram: [../diagrams/modules/ivr-menus.mmd](../diagrams/modules/ivr-menus.mmd)

## Components

- `IvrMenusIndex`: lists IVR menus with option count and status.
- `IvrMenuCreate`: creates a new menu (name, greeting, timeout, invalid config).
- `IvrMenuEdit`: edits a menu; two tabs — Settings and Digit Options.

## Related Models

- `IvrMenu`: greeting config, gather timeout, invalid-input handling.
- `IvrMenuOption`: one row per digit (0–9, `*`, `#`); stores `action` and `destination`.

## Greeting Types

| Type | Behaviour |
|---|---|
| `tts` | Twilio reads `greeting_value` as text-to-speech. |
| `audio` | Twilio plays `greeting_value` as a public audio URL. |

## Digit Option Actions

| Action | Destination field |
|---|---|
| `in_group` | `InGroup.id` — routes call to that queue. |
| `ivr_menu` | `IvrMenu.id` — nests into another menu. |
| `transfer` | E.164 phone number. |
| `voicemail` | No destination; Twilio `<Record>` is returned. |
| `hangup` | No destination; call is ended. |

## Invalid Input Handling

If the caller does not press a valid digit within `gather_timeout` seconds, or presses an unrecognised key, the attempt counter increments. Once `invalid_attempts_max` is reached, `invalid_action` fires:

- `repeat` — replay the menu from the start (counter resets).
- `transfer` — transfer to `invalid_destination` phone number.
- `hangup` — end the call.

## IVR Gather Webhook

Twilio posts digit input to `POST /api/call/ivr-gather?menu_id=N&attempts=N`.  
The controller resolves the matching `IvrMenuOption` and executes the configured action.

## Default IVR Menu

The `InboundSeeder` creates a **Main Menu** with:
- Digit `1` → General Support in-group.
- Digit `0` → repeat the Main Menu.

## Navigation

1. [README.md](README.md)
2. [in-groups.md](in-groups.md)
3. [dids.md](dids.md)
4. [../diagrams/inbound-call-flow.mmd](../diagrams/inbound-call-flow.mmd)
5. [../diagrams/modules/ivr-menus.mmd](../diagrams/modules/ivr-menus.mmd)
