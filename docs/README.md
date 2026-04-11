# Documentation Hub

This directory centralizes application documentation so operational guides, module references, and diagrams are kept outside the runtime code directories.

## Navigation

1. [app-structure.md](app-structure.md)
2. [tech-stack.md](tech-stack.md)
3. [modules/README.md](modules/README.md)
4. [diagrams/README.md](diagrams/README.md)

## Core Documentation

- [app-structure.md](app-structure.md): repository and `app/` layout.
- [tech-stack.md](tech-stack.md): frameworks, libraries, and tooling.

## Campaign System

- [modules/campaign.md](modules/campaign.md): campaign configuration, Vicidial-style dial modes, and inbound-group binding.
- [modules/call-lists.md](modules/call-lists.md): call list management and CSV lead import.
- [modules/dispositions.md](modules/dispositions.md): disposition codes, ACW timer, and callback scheduling.

## Inbound Call System

- [diagrams/inbound-call-flow.mmd](diagrams/inbound-call-flow.mmd): end-to-end inbound call flow (DID → IVR → In-Group → hold queue → Agent).
- [modules/in-groups.md](modules/in-groups.md): in-group queue configuration, routing algorithms, and campaign binding.
- [modules/call-routing.md](modules/call-routing.md): webhook endpoint reference, CSRF exception requirement, queue hold-loop mechanics, frontend error logging, and bug-fix history.
- [modules/dids.md](modules/dids.md): DID (inbound routing) and CID Numbers (outbound caller-ID pool) management.
- [modules/ivr-menus.md](modules/ivr-menus.md): IVR menu and digit-option configuration.

## Outbound Call System

- [diagrams/outbound-cid-flow.mmd](diagrams/outbound-cid-flow.mmd): outbound CID rotation — how `Campaign::nextCid()` selects the caller ID per call.
- [modules/campaign.md](modules/campaign.md): campaign configuration including `cid_rotation` toggle.

## Indexes

- [modules/README.md](modules/README.md): module-by-module documentation.
- [diagrams/README.md](diagrams/README.md): Mermaid source index.

## Source Code Reference

- Feature modules are implemented under `app/Livewire/`.
- Backend domain models are implemented under `app/Models/`.
- Shared services are implemented under `app/Services/`.