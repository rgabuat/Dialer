# Campaign Module

## Purpose

This module manages campaign lifecycle actions such as listing, creating, editing, and selecting campaigns.

## Diagram

See [docs/diagrams/modules/campaign.mmd](docs/diagrams/modules/campaign.mmd).

## Source Location

- `app/Livewire/Campaign/`

## Components

- `CampaignsIndex`: lists available campaigns.
- `CampaignCreate`: creates a new campaign.
- `CampaignEdit`: updates an existing campaign.
- `CampaignSelect`: selects campaign context for workflow use.

## Related Domain Objects

- `Campaign`

## Notes

- Campaigns are a central organizing entity for dialer operations.