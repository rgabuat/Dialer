# DIDs Module

## Summary

- Area: Inbound
- Purpose: manage Direct Inward Dial (DID) phone numbers for **inbound** routing, and CID Numbers for the **outbound** caller-ID pool.
- Source: `app/Livewire/Dids/`
- Diagram: [../diagrams/modules/dids.mmd](../diagrams/modules/dids.mmd)

## Concepts

| Concept | Table | Purpose |
|---|---|---|
| **DID** | `dids` | Maps an inbound Twilio phone number to an IVR menu or In-Group |
| **CID Number** | `cid_numbers` | Twilio numbers imported as outbound Caller-ID pool for CID rotation |

These are separate models. A number can exist in one or both tables, but they serve different functions.

## Components

### DID Management

- `DidsIndex`: lists DID numbers with their linked in-group, IVR menu.
- `DidCreate`: registers a new DID; choose destination type (in-group or IVR menu).
- `DidEdit`: updates an existing DID; includes delete with confirmation.

### CID Numbers Management

- `CidNumbersIndex` (`/cid-numbers`): two-section page:
  1. **Imported CID Numbers** — table of all `cid_numbers` records with toggle switches for `is_active` and `in_rotation`.
  2. **Twilio Phone Numbers** — live list from Twilio API; Import button creates a `cid_numbers` record and syncs the voice webhook.

## Related Models

- `Did`: stores `phone_number` (unique), `in_group_id`, `ivr_menu_id`, `is_active`.
- `CidNumber`: stores `phone_number`, `twilio_sid`, `friendly_name`, `is_active`, `in_rotation`.

## CID Number Fields

| Field | Type | Description |
|---|---|---|
| `phone_number` | string (unique) | E.164 formatted number |
| `twilio_sid` | string (nullable, unique) | Twilio phone number SID |
| `friendly_name` | string (nullable) | Display label from Twilio |
| `is_active` | boolean | General availability flag |
| `in_rotation` | boolean | Whether this number is included in campaign CID rotation pools |

## CID Rotation Logic

When `Campaign.cid_rotation = true`, `Campaign::nextCid()` builds the pool from:

```
CidNumber::where('is_active', true)->where('in_rotation', true)
```

Round-robin position is tracked in Redis under `cid_rotation:{campaign_id}`. If the pool is empty, the campaign's fixed `caller_id` is used as fallback.

## Inbound Routing Priority (DID)

When Twilio posts to `POST /api/call-routing`, the controller resolves the called number against `dids.phone_number`. If a matching active DID is found:

1. `ivr_menu_id` set → serve IVR menu first.
2. `in_group_id` set (no IVR) → route directly to in-group.
3. Neither set → hang up with a configuration message.

## Default DID

The `InboundSeeder` creates a default DID using the `TWILIO_PHONE_NUMBER` env value pointed at the **Main Menu** IVR menu.

## Navigation

1. [README.md](README.md)
2. [in-groups.md](in-groups.md)
3. [ivr-menus.md](ivr-menus.md)
4. [../diagrams/inbound-call-flow.mmd](../diagrams/inbound-call-flow.mmd)
5. [../diagrams/outbound-cid-flow.mmd](../diagrams/outbound-cid-flow.mmd)
6. [../diagrams/modules/dids.mmd](../diagrams/modules/dids.mmd)
