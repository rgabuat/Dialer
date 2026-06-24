<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use Illuminate\Console\Command;
use Twilio\Rest\Client as TwilioClient;

class CleanStaleConversations extends Command
{
    protected $signature   = 'conversations:clean-stale
                                {--dry-run : List stale records without updating them}
                                {--minutes=120 : Mark conversations older than this many minutes as abandoned}';

    protected $description = 'Verify live conversations against Twilio and mark stale ones as abandoned';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $dryRun  = (bool) $this->option('dry-run');

        $stale = Conversation::whereIn('status', ['queued', 'in_progress'])
            ->where('started_at', '<', now()->subMinutes($minutes))
            ->get();

        if ($stale->isEmpty()) {
            $this->info('No stale conversations found.');
            return self::SUCCESS;
        }

        $this->info("Found {$stale->count()} conversation(s) stuck as queued/in_progress for >{$minutes} minutes.");

        // Try to verify against Twilio
        $twilioEnabled = config('services.twilio.sid') && config('services.twilio.token');
        $twilio = null;
        if ($twilioEnabled) {
            try {
                $twilio = new TwilioClient(
                    config('services.twilio.sid'),
                    config('services.twilio.token')
                );
            } catch (\Throwable $e) {
                $this->warn('Twilio client unavailable — will clean by age only. Error: ' . $e->getMessage());
            }
        }

        $cleaned  = 0;
        $skipped  = 0;
        $terminal = ['completed', 'canceled', 'failed', 'busy', 'no-answer'];

        foreach ($stale as $conversation) {
            $shouldAbandon = true;

            // If Twilio is available and we have a call SID, verify the live status
            if ($twilio && $conversation->call_sid) {
                try {
                    $call = $twilio->calls($conversation->call_sid)->fetch();
                    if (!in_array($call->status, $terminal, true)) {
                        // Call is genuinely still active on Twilio — skip it
                        $this->line("  SKIP  conv #{$conversation->id} — Twilio status: {$call->status}");
                        $skipped++;
                        $shouldAbandon = false;
                    }
                } catch (\Twilio\Exceptions\RestException $e) {
                    // 404 from Twilio = call no longer exists → safe to abandon
                    if ($e->getStatusCode() !== 404) {
                        $this->warn("  WARN  conv #{$conversation->id} Twilio error ({$e->getStatusCode()}): {$e->getMessage()}");
                        $skipped++;
                        $shouldAbandon = false;
                    }
                } catch (\Throwable $e) {
                    $this->warn("  WARN  conv #{$conversation->id} unexpected error: {$e->getMessage()}");
                    $skipped++;
                    $shouldAbandon = false;
                }
            }

            if ($shouldAbandon) {
                $age = now()->diffForHumans($conversation->started_at, true);
                $this->line("  CLEAN conv #{$conversation->id} | {$conversation->status} | started {$age} ago | SID: {$conversation->call_sid}");

                if (!$dryRun) {
                    $conversation->update([
                        'status'   => 'abandoned',
                        'ended_at' => $conversation->ended_at ?? now(),
                    ]);
                }
                $cleaned++;
            }
        }

        if ($dryRun) {
            $this->warn("DRY RUN — no records updated. Remove --dry-run to apply.");
        } else {
            $this->info("Done. Cleaned: {$cleaned} | Skipped (still live): {$skipped}");
        }

        return self::SUCCESS;
    }
}
