<?php

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Services\HopperService;
use Illuminate\Console\Command;

class FillDialerHopper extends Command
{
    protected $signature   = 'dialer:fill-hopper {--campaign= : Specific campaign ID}';
    protected $description = 'Fill the dialer hopper for active outbound campaigns';

    public function __construct(private HopperService $hopper)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = Campaign::where('is_active', true)
            ->whereIn('type', ['OUTBOUND', 'BLENDED'])
            ->whereIn('dial_mode', ['PROGRESSIVE', 'PREDICTIVE']);

        if ($id = $this->option('campaign')) {
            $query->where('id', $id);
        }

        $campaigns = $query->get();

        if ($campaigns->isEmpty()) {
            $this->info('No active outbound campaigns to fill.');
            return self::SUCCESS;
        }

        foreach ($campaigns as $campaign) {
            $added = $this->hopper->fill($campaign);
            $this->line("Campaign [{$campaign->id}] {$campaign->name}: +{$added} leads added to hopper.");
        }

        return self::SUCCESS;
    }
}
