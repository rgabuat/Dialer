# Campaign Module

## Summary

- Area: Campaign (top-level sidebar section)
- Purpose: manage campaigns and their associated dial settings, call lists, dispositions, and inbound-group bindings.
- Source: `app/Livewire/Campaign/`
- Diagram: [../diagrams/modules/campaign.mmd](../diagrams/modules/campaign.mmd)

## Components

- `CampaignsIndex`: lists available campaigns with status badges.
- `CampaignCreate`: creates a new campaign.
- `CampaignEdit`: updates an existing campaign — details, dialer settings, inbound-group binding, and agent script.
- `CampaignSelect`: selects the active campaign context for an agent.

## Campaign Fields

| Field | Type | Description |
|---|---|---|
| `name` | string | Display name |
| `phone_number` | string | Inbound DID match number |
| `description` | string (nullable) | Free-text description |
| `is_active` | boolean | Whether the campaign accepts calls |
| `type` | enum | `OUTBOUND`, `INBOUND`, `BLENDED` |
| `dial_mode` | enum | `MANUAL`, `PREVIEW`, `PROGRESSIVE`, `PREDICTIVE` |
| `dial_level` | decimal | Predictive dial ratio (agents × dial_level = simultaneous dials) |
| `caller_id` | string (nullable) | Outbound CLI; falls back to system default |
| `script` | text (nullable) | Agent script shown in conversation Script tab |
| `acw_seconds` | integer | After-Call Work timer in seconds (0 = disabled) |
| `hopper_level` | integer | Number of leads to pre-load into the dialer hopper |
| `max_calls` | integer (nullable) | Maximum simultaneous outbound call legs |

## Dial Modes

| Mode | Behaviour |
|---|---|
| `MANUAL` | Agent manually dials each number. |
| `PREVIEW` | Agent reviews the lead record before the call is placed. |
| `PROGRESSIVE` | Server places one call per available agent automatically. |
| `PREDICTIVE` | Server dials at `dial_level × available agents` to maximise connect time. |

## Inbound-Group Binding

A campaign may be linked to one or more **InGroups**. The binding is stored via `in_groups.campaign_id` (nullable FK). On `CampaignEdit` save, selected in-group IDs have `campaign_id` set to this campaign; previously selected but now-deselected groups are set back to `null`.

## Dialer Hopper

`FillDialerHopper` (`app/Console/Commands/`) runs every minute via the scheduler. It pulls un-called leads from the campaign's call lists and inserts them into `dialer_hopper` up to `hopper_level`.

## Related Models

- `Campaign`
- `CallList` — `hasMany`; CSV-imported lead lists per campaign.
- `Disposition` — `hasMany`; dial result codes with ACW and callback support.
- `InGroup` — `hasMany`; in-groups routed to this campaign.
- `CallbackSchedule` — `hasMany`; scheduled callbacks from dispositions.
- `DialerHopper` — pre-queued leads for automated dial modes.

## Navigation

1. [README.md](README.md)
2. [call-lists.md](call-lists.md)
3. [dispositions.md](dispositions.md)
4. [in-groups.md](in-groups.md)
5. [../diagrams/modules/campaign.mmd](../diagrams/modules/campaign.mmd)
