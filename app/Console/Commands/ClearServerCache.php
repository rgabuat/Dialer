<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearServerCache extends Command
{
    protected $signature = 'app:clear-server-cache
                            {--restart-fpm : Restart PHP-FPM after clearing caches}
                            {--fpm-service=php8.3-fpm : PHP-FPM service name when using --restart-fpm}';

    protected $description = 'Clear Laravel caches for the server and optionally restart PHP-FPM';

    public function handle(): int
    {
        $this->info('=== Clearing Laravel caches ===');

        // optimize:clear clears config, route, view, event, cache, and compiled files.
        $this->call('optimize:clear');

        $this->line('  -> Queue cache (default queue)');
        $this->callSilent('queue:clear', ['--force' => true]);

        $this->line('  -> OPcache');
        if (function_exists('opcache_reset')) {
            opcache_reset();
            $this->line('     OPcache reset via PHP function.');
        } else {
            $this->warn('     OPcache reset not available in CLI context.');
        }

        if ($this->option('restart-fpm')) {
            $service = (string) $this->option('fpm-service');
            $this->newLine();
            $this->info("=== Restarting {$service} ===");

            exec("sudo systemctl restart {$service} 2>&1", $output, $exitCode);

            if ($exitCode === 0) {
                $this->line('  PHP-FPM restarted successfully.');
            } else {
                $this->error('  PHP-FPM restart failed (exit '.$exitCode.').');
                foreach ($output as $line) {
                    $this->line('  '.$line);
                }
            }
        }

        $this->newLine();
        $this->info('Cache clear automation run completed.');

        return self::SUCCESS;
    }
}
