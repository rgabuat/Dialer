# Application Structure

This document describes how the Dialer repository is organized and where the main application concerns live.

## Navigation

1. [docs/README.md](docs/README.md)
2. [docs/tech-stack.md](docs/tech-stack.md)
3. [docs/modules/README.md](docs/modules/README.md)
4. [docs/diagrams/README.md](docs/diagrams/README.md)

## Diagram

See [docs/diagrams/app-structure.mmd](docs/diagrams/app-structure.mmd).

## Repository Layout

```text
.
|-- app/                     # Main application code
|-- bootstrap/               # Framework bootstrap and cache
|-- config/                  # Laravel and app configuration
|-- dashboard/               # Static dashboard bundle and docs
|-- database/                # Factories, migrations, and seeders
|-- docs/                    # Centralized documentation
|-- public/                  # Public web root
|-- resources/               # Blade views and frontend source assets
|-- routes/                  # Web, API, console, and broadcast routes
|-- storage/                 # Logs, cache, sessions, generated files
|-- tests/                   # Feature and unit tests
|-- artisan                  # Laravel CLI entry point
|-- composer.json            # PHP dependencies and scripts
|-- package.json             # Frontend dependencies and scripts
|-- phpunit.xml              # Test configuration
|-- tailwind.config.js       # Tailwind configuration
`-- vite.config.js           # Vite configuration
```

## Application Code Layout

The `app/` directory contains the core business logic:

- `Console/`: Artisan commands and scheduling.
- `Events/`: domain and application event classes.
- `Exceptions/`: exception handling.
- `Http/`: controllers, middleware, and HTTP kernel.
- `Livewire/`: feature-oriented UI modules.
- `Models/`: Eloquent models for core entities.
- `Observers/`: model lifecycle observers.
- `Providers/`: service providers.
- `Services/`: reusable service-layer logic.
- `View/`: view composition helpers or related classes.
- `helpers/`: globally loaded helper functions.

## Feature Module Layout

Feature-facing UI modules are grouped in `app/Livewire/` by business area:

- Operations: `Activitylogs/`, `Agent/`, `Campaign/`, `Leads/`, `Stores/`
- Access Control: `Auth/`, `Permissions/`, `Roles/`
- Administration: `Settings/`, `UserGroups/`, `Users/`

Detailed module documentation lives in [docs/modules](docs/modules).

## Supporting Areas

- `database/seeders/DefaultSeeder.php`: provisions the default Super Admin user, role, campaign, and group.
- `resources/views/`: Blade templates.
- `resources/js/` and `resources/css/`: frontend source assets compiled through Vite.
- `routes/web.php`, `routes/api.php`, `routes/channels.php`, `routes/console.php`: route entry points by responsibility.