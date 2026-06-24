<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Fill hopper every minute for active PROGRESSIVE/PREDICTIVE campaigns
        $schedule->command('dialer:fill-hopper')->everyMinute()->withoutOverlapping();

        // Clean stale conversations every 15 minutes (calls stuck as queued/in_progress)
        $schedule->command('conversations:clean-stale --minutes=120')
            ->everyFifteenMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/clean-stale-conversations.log'));

        // Clear caches for all sites on the server every day at 03:00.
        $schedule->exec('bash /var/www/clear-all-caches.sh')
            ->dailyAt('03:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/clear-all-caches.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
