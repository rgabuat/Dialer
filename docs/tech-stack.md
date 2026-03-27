# Tech Stack

This document lists the core frameworks, libraries, and tooling currently used by the Dialer application.

## Navigation

1. [docs/README.md](docs/README.md)
2. [docs/app-structure.md](docs/app-structure.md)
3. [docs/modules/README.md](docs/modules/README.md)
4. [docs/diagrams/README.md](docs/diagrams/README.md)

## Backend

- `PHP ^8.1`
- `Laravel ^10.10`
- `Livewire ^3.7`
- `Laravel Sanctum ^3.3`
- `Spatie Laravel Permission ^6.24`
- `Pusher PHP Server ^7.2`
- `Twilio PHP SDK ^8.10`
- `Guzzle ^7.2`

## Frontend

- `Vite ^7.2.7`
- `Tailwind CSS ^4.1.18`
- `@tailwindcss/vite ^4.1.18`
- `@tailwindcss/postcss ^4.1.18`
- `Autoprefixer ^10.4.22`
- `Alpine.js ^3.15.2`
- `Axios ^1.6.4`
- `Laravel Echo ^2.3.0`
- `Pusher JS ^8.4.0`
- `@twilio/voice-sdk ^2.18.0`
- `Sass ^1.96.0`

## Authorization And Realtime

- Authentication and API session protection are supported by Laravel and Sanctum.
- Role and permission management is implemented with Spatie Laravel Permission.
- Realtime event delivery uses Pusher-compatible tooling on the backend and frontend.
- Voice and telephony features integrate with Twilio SDKs.

## Development Tooling

- `PHPUnit ^10.1`
- `Laravel Pint ^1.0`
- `Laravel Sail ^1.18`
- `Faker ^1.9.1`
- `Mockery ^1.4.4`
- `Collision ^7.0`
- `Laravel Ignition ^2.0`

## Build And Run

- PHP dependencies are managed with Composer.
- Frontend dependencies are managed with npm.
- Asset compilation and development serving run through Vite.
- Styling is handled through Tailwind CSS and Sass.