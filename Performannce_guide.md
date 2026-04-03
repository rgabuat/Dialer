# Performance Optimization Guide

This document covers the full deployment and all performance optimizations applied to this Laravel application on Ubuntu Server with Nginx.

---

## 0. Initial Deployment

### Clone the repository
```bash
cd /var/www
git clone git@github.com:rgabuat/Dialer.git testapplication.sbs
cd testapplication.sbs
```

### Install PHP dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### Create the `.env` file
```bash
cp .env.example .env
```

Then update these values in `.env`:
```env
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://testapplication.sbs
ASSET_URL=https://testapplication.sbs

DB_HOST=127.0.0.1
DB_DATABASE=dialer
DB_USERNAME=dbadmin
DB_PASSWORD=your_password
```

### Generate the application key
```bash
php artisan key:generate
```

### Install Node dependencies and build frontend assets
```bash
npm install
npm run build
```

### Create the database and grant user access
```bash
mysql -u root -e "CREATE DATABASE IF NOT EXISTS dialer CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -e "GRANT ALL PRIVILEGES ON dialer.* TO 'dbadmin'@'127.0.0.1'; FLUSH PRIVILEGES;"
```

### Run migrations and seeders
```bash
php artisan migrate --force
php artisan db:seed --force
```

### Link storage and cache Laravel files
```bash
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Set file permissions
```bash
chown -R www-data:www-data /var/www/testapplication.sbs
chmod -R 755 /var/www/testapplication.sbs
chmod -R 775 /var/www/testapplication.sbs/storage /var/www/testapplication.sbs/bootstrap/cache
```

### Create Nginx site config

Create `/etc/nginx/sites-available/testapplication.sbs`:

```nginx
server {
    server_name testapplication.sbs www.testapplication.sbs;

    root /var/www/testapplication.sbs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 6;
    gzip_types text/plain text/css text/xml text/javascript application/json application/javascript application/xml+rss application/rss+xml font/truetype font/opentype application/vnd.ms-fontobject image/svg+xml;

    client_max_body_size 100M;

    listen 80;
    listen [::]:80;
}
```

### Enable the site and obtain SSL certificate
```bash
ln -sf /etc/nginx/sites-available/testapplication.sbs /etc/nginx/sites-enabled/testapplication.sbs
nginx -t && systemctl reload nginx

certbot --nginx -d testapplication.sbs -d www.testapplication.sbs \
  --non-interactive --agree-tos -m admin@testapplication.sbs
```

---

## 1. Image Optimization

Large images were compressed using `jpegoptim` and `pngquant`.

### Install tools
```bash
apt-get install -y jpegoptim pngquant
```

### Compress JPEG background image
```bash
jpegoptim --max=75 --strip-all resources/images/background/diverImg.jpg
jpegoptim --max=75 --strip-all public/images/background/diverImg.jpg
```

### Compress PNG logo
```bash
pngquant --quality=70-90 --force --output resources/images/logo/logo.png resources/images/logo/logo.png
pngquant --quality=70-90 --force --output public/images/logo/logo.png public/images/logo/logo.png
```

### Rebuild Vite assets after optimizing source images
```bash
npm run build
```

### Results

| File | Before | After | Reduction |
|---|---|---|---|
| `diverImg.jpg` | 13 MB | 1.6 MB | 88% |
| `logo.png` | 1.3 MB | 375 KB | 72% |

---

## 2. OPcache Tuning

File: `/etc/php/8.3/fpm/conf.d/10-opcache.ini`

```ini
zend_extension=opcache.so

opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=20000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1
opcache.jit=tracing
opcache.jit_buffer_size=64M
```

### Key changes from default

| Setting | Before | After | Reason |
|---|---|---|---|
| `memory_consumption` | 128MB | 256MB | Fits all vendor + app PHP files |
| `max_accelerated_files` | default (few hundred) | 20000 | Covers full Laravel vendor tree |
| `validate_timestamps` | 1 | **0** | Stops checking filemtime on every request in production |
| `jit` | off | **tracing** | Fastest JIT mode for web workloads |
| `jit_buffer_size` | — | 64MB | Memory allocated for JIT compiled code |

> **Note:** After any code deployment, clear the OPcache:
> ```bash
> php artisan opcache:clear   # if package installed, otherwise restart php-fpm
> systemctl restart php8.3-fpm
> ```

---

## 3. Nginx Static Asset Caching

File: `/etc/nginx/sites-available/testapplication.sbs`

Add these blocks inside the `server {}` block:

```nginx
# Cache Vite hashed assets forever (filenames change on each build)
location ~* /build/assets/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
}

# Cache images and fonts for 30 days
location ~* \.(jpg|jpeg|png|gif|ico|svg|webp|woff|woff2|ttf|eot)$ {
    expires 30d;
    add_header Cache-Control "public, no-transform";
    access_log off;
}
```

Reload Nginx after changes:
```bash
nginx -t && systemctl reload nginx
```

---

## 4. Redis — Cache & Session Driver

### Install Redis and PHP extension
```bash
apt-get install -y redis-server php8.3-redis
systemctl enable redis-server
systemctl start redis-server
```

### Update `.env`
```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Redis configuration

After installing, harden the Redis config:

```bash
# Cap memory to 512MB — prevents OOM on a shared server
redis-cli config set maxmemory 512mb

# Evict least-recently-used keys when full (correct for a cache workload)
redis-cli config set maxmemory-policy allkeys-lru

# Disable RDB snapshots — cache/sessions don't need disk persistence
redis-cli config set save ""

# Write settings to /etc/redis/redis.conf (persists across reboots)
redis-cli config rewrite
```

### Final Redis config summary

| Setting | Value | Reason |
|---|---|---|
| `maxmemory` | 512MB | Prevents Redis from consuming all RAM |
| `maxmemory-policy` | `allkeys-lru` | Graceful eviction instead of erroring |
| `save` | disabled | No disk I/O needed for ephemeral cache/sessions |

### Benchmark results (Redis 7.0.15)

| Operation | Throughput | p50 latency |
|---|---|---|
| GET | 208,333 req/s | 0.27ms |
| SET | 200,000 req/s | 0.30ms |
| HSET | 263,157 req/s | 0.25ms |
| INCR | 238,095 req/s | 0.27ms |

---

## 5. PHP-FPM Worker Tuning

File: `/etc/php/8.3/fpm/pool.d/www.conf`

```ini
pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500
```

### Changes from default

| Setting | Before | After | Reason |
|---|---|---|---|
| `max_children` | 5 | 20 | Allow more concurrent PHP workers on 4GB RAM |
| `start_servers` | 2 | 4 | Pre-warm workers on boot |
| `min_spare_servers` | 1 | 2 | Keep more idle workers ready |
| `max_spare_servers` | 3 | 6 | Don't kill workers aggressively |
| `max_requests` | 0 (unlimited) | 500 | Recycle workers to prevent slow memory leaks |

Restart PHP-FPM after changes:
```bash
systemctl restart php8.3-fpm
```

---

## 6. Laravel Production Caches

Always run these after deploying:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

To clear all caches:
```bash
php artisan optimize:clear
```

---

## 7. Clear All Caches & Restart FPM (One Command)

A custom Artisan command was added to handle the full cache-clear + FPM restart + optimize cycle in one step:

**File:** `app/Console/Commands/ClearAndOptimize.php`

### Usage

```bash
# Full run: clear all caches → restart php8.3-fpm → artisan optimize
php artisan app:clear-optimize

# Skip FPM restart (e.g. local dev or CI)
php artisan app:clear-optimize --skip-fpm
```

### What it does (in order)

| Step | Detail |
|---|---|
| Config cache clear | `config:clear` |
| Route cache clear | `route:clear` |
| View cache clear | `view:clear` |
| Application cache clear | `cache:clear` |
| Event cache clear | `event:clear` |
| Queue cache clear | `queue:clear --force` |
| Compiled services clear | `clear-compiled` |
| OPcache reset | `opcache_reset()` via PHP CLI |
| PHP-FPM restart | `sudo systemctl restart php8.3-fpm` (flushes web-process OPcache) |
| Optimize | `artisan optimize` (re-caches config + routes) |

---

## Summary

| Area | Optimization | Impact |
|---|---|---|
| Images | jpegoptim + pngquant | 72–88% smaller files, faster page loads |
| PHP | OPcache JIT tracing, 256MB, no timestamp checks | Faster PHP execution, fewer disk reads |
| Nginx | Immutable cache for Vite assets, 30d for images/fonts | Repeat visitors load from browser cache |
| Cache/Sessions | Redis instead of file driver | In-memory I/O, sub-millisecond response |
| Redis | 512MB cap, allkeys-lru, no RDB snapshotting | Stable memory, no disk overhead |
| PHP-FPM | 20 workers, max_requests=500 | Handles more concurrent users, prevents memory leaks |
| Deployment | `php artisan app:clear-optimize` | One command clears all caches, restarts FPM, re-optimizes |
