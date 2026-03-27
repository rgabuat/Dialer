# Dialer

Dialer is a Laravel application for managing campaigns, leads, agents, permissions, settings, and related operational workflows.

## Overview

- Framework: Laravel 10 + Livewire 3
- Frontend: Vite, Tailwind CSS, Alpine.js
- Integrations: Twilio, Pusher, Sanctum, Spatie Permission
- Documentation: [docs/README.md](docs/README.md)

## Project Layout

This repository follows a standard Laravel layout with feature-driven Livewire modules and centralized project documentation under `docs/`.

```text
.
|-- app/          # Core application code
|-- config/       # Framework and app configuration
|-- database/     # Migrations, factories, and seeders
|-- docs/         # Documentation, module guides, and diagrams
|-- public/       # Web root
|-- resources/    # Views and frontend source assets
|-- routes/       # Web, API, console, and broadcast routes
|-- storage/      # Logs, cache, sessions, generated files
`-- tests/        # Feature and unit tests
```

## Core Docs

1. [docs/README.md](docs/README.md)
2. [docs/app-structure.md](docs/app-structure.md)
3. [docs/tech-stack.md](docs/tech-stack.md)
4. [docs/modules/README.md](docs/modules/README.md)
5. [docs/diagrams/README.md](docs/diagrams/README.md)

## Notes

- Default seed data is provisioned through `database/seeders/DefaultSeeder.php`.
- Detailed module guides live under `docs/modules/`.
- Mermaid sources live under `docs/diagrams/`.

