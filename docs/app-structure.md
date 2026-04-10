# Application Structure

This document describes how the Dialer repository is organized and where the main application concerns live.

## Navigation

1. [README.md](README.md)
2. [tech-stack.md](tech-stack.md)
3. [modules/README.md](modules/README.md)
4. [diagrams/README.md](diagrams/README.md)

## Diagram

See [diagrams/app-structure.mmd](diagrams/app-structure.mmd).

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

- Campaign: `Campaign/`, `CallLists/`, `Conversations/`
- Inbound: `Dids/`, `InGroups/`, `IvrMenus/`
- Operations: `Activitylogs/`, `Agent/`, `Leads/`, `Stores/`
- Workforce: `Workforce/`
- Access Control: `Auth/`, `Permissions/`, `Roles/`
- Administration: `Settings/`, `UserGroups/`, `Users/`

Detailed module documentation lives in [modules/README.md](modules/README.md).

## Supporting Areas

- `database/seeders/DefaultSeeder.php`: provisions the default Super Admin user, role, campaign, and group.
- `resources/views/`: Blade templates.
- `resources/js/` and `resources/css/`: frontend source assets compiled through Vite.
- `routes/web.php`, `routes/api.php`, `routes/channels.php`, `routes/console.php`: route entry points by responsibility.