# Dialer

Dialer is a Laravel application for managing campaigns, leads, agents, permissions, settings, and related operational workflows.

## Application Structure

This repository follows a standard Laravel layout, with additional Livewire modules and a bundled static dashboard.

```text
.
|-- app/
|   |-- Console/            # Artisan commands and console kernel
|   |-- Events/             # Domain and application events
|   |-- Exceptions/         # Exception handling
|   |-- Http/               # Controllers, middleware, and HTTP kernel
|   |-- Livewire/           # Feature-driven Livewire components
|   |-- Models/             # Eloquent models
|   |-- Observers/          # Model observers
|   |-- Providers/          # Service providers
|   |-- Services/           # Application service classes
|   |-- View/               # View-related classes and helpers
|   `-- helpers/            # Global helper functions
|-- bootstrap/              # Framework bootstrap files and cache
|-- config/                 # Laravel and application configuration
|-- dashboard/              # Bundled static dashboard assets and docs
|-- database/
|   |-- factories/          # Model factories
|   |-- migrations/         # Database schema changes
|   `-- seeders/            # Seed data, including default app setup
|-- public/                 # Web root and public assets
|-- resources/
|   |-- css/                # Source styles
|   |-- images/             # Source images
|   |-- js/                 # Frontend JavaScript
|   `-- views/              # Blade views
|-- routes/                 # Web, API, console, and channel routes
|-- storage/                # Logs, cache, sessions, and generated files
|-- tests/
|   |-- Feature/            # Feature and integration tests
|   `-- Unit/               # Unit tests
|-- artisan                 # Laravel CLI entry point
|-- composer.json           # PHP dependencies and scripts
|-- package.json            # Frontend dependencies and scripts
|-- phpunit.xml             # PHPUnit configuration
|-- tailwind.config.js      # Tailwind CSS configuration
`-- vite.config.js          # Vite build configuration
```

## `app/` Directory Breakdown

The `app/` directory contains the main business logic for the platform:

- `Console/`: custom Artisan commands and scheduled task registration.
- `Events/`: application events such as campaign deletion and agent status updates.
- `Exceptions/`: global exception handling and reporting.
- `Http/`: controllers and middleware for the request lifecycle.
- `Livewire/`: feature modules organized by domain, including activity logs, agents, auth, campaigns, leads, permissions, roles, settings, stores, user groups, and users.
- `Models/`: core entities such as users, campaigns, leads, stores, timezones, and audit or agent-status records.
- `Observers/`: model lifecycle hooks.
- `Providers/`: framework and app service registration.
- `Services/`: reusable service-layer logic.
- `View/`: custom view composition logic.
- `helpers/`: shared helper functions loaded by the application.

## Module Documentation

The application is organized primarily around Livewire feature modules under `app/Livewire/`. All documentation is centralized under `docs/`.

- Documentation hub: [docs/README.md](docs/README.md)
- Root module map: [docs/diagrams/modules.mmd](docs/diagrams/modules.mmd)

- [docs/modules/activitylogs.md](docs/modules/activitylogs.md): activity history and audit visibility.
- [docs/modules/agent.md](docs/modules/agent.md): agent status management.
- [docs/modules/auth.md](docs/modules/auth.md): authentication flows.
- [docs/modules/campaign.md](docs/modules/campaign.md): campaign creation and selection.
- [docs/modules/leads.md](docs/modules/leads.md): lead management.
- [docs/modules/permissions.md](docs/modules/permissions.md): permission administration.
- [docs/modules/roles.md](docs/modules/roles.md): role administration.
- [docs/modules/settings.md](docs/modules/settings.md): user settings and preferences.
- [docs/modules/stores.md](docs/modules/stores.md): store management.
- [docs/modules/user-groups.md](docs/modules/user-groups.md): group-based user organization.
- [docs/modules/users.md](docs/modules/users.md): user administration.

## Key Supporting Areas

- `database/seeders/DefaultSeeder.php` provisions a default Super Admin user, role, campaign, and group for initial setup.
- `resources/views/` contains Blade templates rendered by the application.
- `resources/js/` and `resources/css/` contain frontend source assets compiled with Vite.
- `dashboard/` contains a separate static dashboard bundle and documentation assets.

## Routing

Routes are split by responsibility:

- `routes/web.php`: browser routes.
- `routes/api.php`: API endpoints.
- `routes/channels.php`: broadcast channel authorization.
- `routes/console.php`: console route definitions.

## Testing

Tests are organized into:

- `tests/Feature/` for end-to-end and integration-style coverage.
- `tests/Unit/` for isolated logic.

## Development Notes

- PHP dependencies are managed with Composer.
- Frontend assets are managed with npm and built through Vite.
- Styling is configured through Tailwind CSS.

