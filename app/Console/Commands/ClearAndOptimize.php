<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAndOptimize extends Command
{
    protected $signature = 'app:clear-optimize
                            {--skip-fpm : Skip restarting PHP-FPM}';

    protected $description = 'Clear all caches, restart PHP-FPM, then run artisan optimize';

    public function handle(): int
    {
        $this->info('=== Clearing caches ===');

        $steps = [
            ['config:clear',             [],               'Config cache'],
            ['route:clear',              [],               'Route cache'],
            ['view:clear',               [],               'View cache'],
            ['cache:clear',              [],               'Application cache'],
            ['event:clear',              [],               'Event cache'],
            ['queue:clear',              ['--force' => true], 'Queue cache (default)'],
            ['clear-compiled',           [],               'Compiled services'],
        ];

        foreach ($steps as [$command, $args, $label]) {
            $this->line("  → {$label}");
            $this->callSilent($command, $args);
        }

        $this->line('  → OPcache');
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->line('    OPcache reset via PHP function.');
        } else {
            $this->warn('    OPcache not available in CLI context (normal). PHP-FPM restart will flush it.');
        }

        if (!$this->option('skip-fpm')) {
            $this->newLine();
            $this->info('=== Restarting PHP-FPM (php8.3-fpm) ===');

            exec('sudo systemctl restart php8.3-fpm 2>&1', $output, $exitCode);

            if ($exitCode === 0) {
                $this->line('  PHP-FPM restarted successfully.');
            } else {
                $this->error('  PHP-FPM restart failed (exit ' . $exitCode . ').');
                foreach ($output as $line) {
                    $this->line('  ' . $line);
                }
                $this->warn('  Continuing without FPM restart. Run manually: sudo systemctl restart php8.3-fpm');
            }
        } else {
            $this->warn('  PHP-FPM restart skipped (--skip-fpm flag).');
        }

        $this->newLine();
        $this->info('=== Running artisan optimize ===');
        $this->call('optimize');

        $this->newLine();
        $this->info('✔ Done.');

        return self::SUCCESS;
    }
}
