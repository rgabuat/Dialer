<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Deploy extends Command
{
    protected $signature = 'app:deploy
                            {--skip-pull     : Skip git pull}
                            {--skip-composer : Skip composer install}
                            {--skip-migrate  : Skip artisan migrate}
                            {--seed          : Run db:seed after migrate}
                            {--skip-npm      : Skip npm run build}
                            {--skip-fpm      : Skip PHP-FPM restart}';

    protected $description = 'Full deployment: git pull → composer install → migrate → (seed) → clear-optimize → npm build';

    public function handle(): int
    {
        $root = base_path();

        // ── 1. Git Pull ───────────────────────────────────────────────────────
        if (!$this->option('skip-pull')) {
            $this->info('=== Git Pull ===');
            exec("cd {$root} && git pull 2>&1", $out, $exit);
            foreach ($out as $line) {
                $this->line('  ' . $line);
            }
            if ($exit !== 0) {
                $this->error('  git pull failed (exit ' . $exit . '). Aborting.');
                return self::FAILURE;
            }
            $this->newLine();
        } else {
            $this->warn('  git pull skipped.');
        }

        // ── 2. Composer Install ───────────────────────────────────────────────
        if (!$this->option('skip-composer')) {
            $this->info('=== Composer Install ===');
            exec("cd {$root} && composer install --no-dev --optimize-autoloader 2>&1", $out, $exit);
            foreach ($out as $line) {
                $this->line('  ' . $line);
            }
            if ($exit !== 0) {
                $this->error('  composer install failed (exit ' . $exit . '). Aborting.');
                return self::FAILURE;
            }
            $this->newLine();
        } else {
            $this->warn('  composer install skipped.');
        }

        // ── 3. Migrate ────────────────────────────────────────────────────────
        if (!$this->option('skip-migrate')) {
            $this->info('=== Running Migrations ===');
            $this->call('migrate', ['--force' => true]);
            $this->newLine();
        } else {
            $this->warn('  Migrations skipped.');
        }

        // ── 4. Seed (optional, only when --seed flag passed) ──────────────────
        if ($this->option('seed')) {
            $this->info('=== Seeding Database ===');
            $this->call('db:seed', ['--force' => true]);
            $this->newLine();
        }

        // ── 5. Clear caches + FPM + optimize + npm build ─────────────────────
        $optimizeArgs = [];
        if ($this->option('skip-fpm')) {
            $optimizeArgs['--skip-fpm'] = true;
        }
        if ($this->option('skip-npm')) {
            $optimizeArgs['--skip-npm'] = true;
        }

        $this->call('app:clear-optimize', $optimizeArgs);

        return self::SUCCESS;
    }
}
