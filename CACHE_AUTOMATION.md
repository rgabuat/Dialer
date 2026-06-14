# Cache Clearing Automation

## Overview

Two layers of cache-clearing automation exist on this server:

1. **`/var/www/clear-all-caches.sh`** — a standalone shell script that clears caches for every Laravel site on the server and restarts PHP-FPM.
2. **`app:clear-server-cache`** — an Artisan command for clearing caches on a single site (this app only).

The shell script is the primary tool for server-wide use. The Artisan command exists for single-app use or programmatic calls.

---

## Shell Script — `/var/www/clear-all-caches.sh`

### What it does (per site)

1. `optimize:clear` — clears config, route, view, event, application cache, and compiled files
2. `queue:clear` — clears the default queue
3. `package:discover` — regenerates the package manifest
4. `config:cache` — rebuilds the config cache
5. `route:cache` — rebuilds the route cache
6. `view:cache` — precompiles Blade views
7. Restarts `php8.3-fpm` once at the end (flushes OPcache for all sites)

### Usage

```bash
# All sites + PHP-FPM restart (recommended after deployments)
sudo bash /var/www/clear-all-caches.sh

# All sites, skip FPM restart
sudo bash /var/www/clear-all-caches.sh --skip-fpm

# Single site only
sudo bash /var/www/clear-all-caches.sh /var/www/testapplication.sbs

# Use a specific PHP binary
sudo PHP_BIN=php8.3 bash /var/www/clear-all-caches.sh

# Override the FPM service name
sudo FPM_SERVICE=php8.2-fpm bash /var/www/clear-all-caches.sh
```

### Sites auto-discovered

The script scans `/var/www/` for any directory containing an `artisan` file:

| Site | Path |
|------|------|
| testapplication.sbs | `/var/www/testapplication.sbs` |
| mechfinder.sbs | `/var/www/mechfinder.sbs` |
| phil-iri.sbs | `/var/www/phil-iri.sbs` |

> **Note:** `mechfinder.sbs` uses the `database` cache driver and its `cache` table does not yet exist. `optimize:clear` will print a DB warning for that site but the script will continue without aborting.  
> Fix: run `php artisan migrate` inside `/var/www/mechfinder.sbs`.

### Scheduler (automated daily run)

The script is scheduled via Laravel's task scheduler in `app/Console/Kernel.php`:

```php
$schedule->exec('bash /var/www/clear-all-caches.sh')
    ->dailyAt('03:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/clear-all-caches.log'));
```

Output is logged to: `storage/logs/clear-all-caches.log`

For this to run automatically, the system cron must be active:

```bash
# Verify the cron entry exists
crontab -l | grep artisan

# Expected entry
* * * * * php /var/www/testapplication.sbs/artisan schedule:run >> /dev/null 2>&1
```

---

## Artisan Command — `app:clear-server-cache`

Located at `app/Console/Commands/ClearServerCache.php`.

Clears caches for **this app only**. Use when you only need to clear one site without touching others.

### What it does

1. `optimize:clear` — config, route, view, event, application cache, compiled files
2. `queue:clear --force` — default queue
3. OPcache reset (via PHP function, CLI only)
4. Optionally restarts PHP-FPM (`--restart-fpm`)

### Usage

```bash
# Clear caches only
php artisan app:clear-server-cache

# Clear caches + restart PHP-FPM
php artisan app:clear-server-cache --restart-fpm

# Clear caches + restart a specific FPM service
php artisan app:clear-server-cache --restart-fpm --fpm-service=php8.2-fpm
```

---

## Related Commands

| Command | Purpose |
|---------|---------|
| `php artisan app:clear-optimize` | Clear all caches + run `optimize` + optional npm build |
| `php artisan app:deploy` | Full deploy: git pull → composer → migrate → clear-optimize |
| `php artisan schedule:list` | View all scheduled tasks and next run times |
| `sudo systemctl restart php8.3-fpm` | Manually flush OPcache on all FPM workers |

---

## Why OPcache Matters

PHP-FPM caches compiled bytecode in OPcache. If you deploy a code change without restarting FPM, workers continue serving stale bytecode even after `optimize:clear` succeeds. This was the root cause of the reports page 500 error (June 2026).

**Rule of thumb:** Always restart FPM after any deployment or Kernel/middleware change.
