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

- [diagrams/inbound-call-flow.mmd](diagrams/inbound-call-flow.mmd): end-to-end call flow diagram (DID → IVR → In-Group → Agent).
- [modules/in-groups.md](modules/in-groups.md): in-group queue configuration, routing algorithms, and campaign binding.
- [modules/dids.md](modules/dids.md): DID phone number management.
- [modules/ivr-menus.md](modules/ivr-menus.md): IVR menu and digit-option configuration.

## Indexes

- [modules/README.md](modules/README.md): module-by-module documentation.
- [diagrams/README.md](diagrams/README.md): Mermaid source index.

## Source Code Reference

- Feature modules are implemented under `app/Livewire/`.
- Backend domain models are implemented under `app/Models/`.
- Shared services are implemented under `app/Services/`.