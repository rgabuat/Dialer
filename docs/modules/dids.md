# DIDs Module

## Summary

- Area: Inbound
- Purpose: manage Direct Inward Dial phone numbers and map each number to an IVR menu, an in-group, or a campaign.
- Source: `app/Livewire/Dids/`
- Diagram: [../diagrams/modules/dids.mmd](../diagrams/modules/dids.mmd)

## Components

- `DidsIndex`: lists DID numbers with their linked in-group, IVR menu, and campaign.
- `DidCreate`: registers a new DID; choose destination type (in-group or IVR menu).
- `DidEdit`: updates an existing DID; includes delete with confirmation.

## Related Models

- `Did`: stores `phone_number` (unique), `in_group_id`, `ivr_menu_id`, `campaign_id`, `is_active`.

## Routing Priority

When Twilio posts to `POST /api/call-routing`, the controller resolves the called number against `dids.phone_number`. If a matching active DID is found:

1. `ivr_menu_id` set → serve IVR menu first.
2. `in_group_id` set (no IVR) → route directly to in-group.
3. Neither set → hang up with a configuration message.

If no DID matches, the call falls through to the legacy campaign phone-number lookup or outbound handling.

## Default DID

The `InboundSeeder` creates a default DID using the `TWILIO_PHONE_NUMBER` env value pointed at the **Main Menu** IVR menu.

## Navigation

1. [README.md](README.md)
2. [in-groups.md](in-groups.md)
3. [ivr-menus.md](ivr-menus.md)
4. [../diagrams/inbound-call-flow.mmd](../diagrams/inbound-call-flow.mmd)
5. [../diagrams/modules/dids.mmd](../diagrams/modules/dids.mmd)
