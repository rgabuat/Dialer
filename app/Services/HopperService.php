<?php

namespace App\Services;

use App\Models\Campaign;
use App\Models\CallbackSchedule;
use App\Models\DialerHopper;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class HopperService
{
    /**
     * Fill the hopper for a campaign up to its hopper_level setting.
     * Prioritises pending callbacks, then NEW leads.
     */
    public function fill(Campaign $campaign): int
    {
        $capacity  = max(1, (int) $campaign->hopper_level);
        $current   = DialerHopper::where('campaign_id', $campaign->id)
            ->whereIn('status', ['pending', 'dialing'])
            ->count();

        $slots = $capacity - $current;
        if ($slots <= 0) {
            return 0;
        }

        $added = 0;

        // 1. Pending callbacks due now
        $callbacks = CallbackSchedule::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->where('scheduled_at', '<=', now())
            ->with('lead')
            ->orderBy('scheduled_at')
            ->limit($slots)
            ->get();

        foreach ($callbacks as $cb) {
            if (!$cb->lead) {
                continue;
            }
            $inserted = $this->insertLead($campaign, $cb->lead);
            if ($inserted) {
                $added++;
                $slots--;
            }
            if ($slots <= 0) {
                break;
            }
        }

        if ($slots <= 0) {
            return $added;
        }

        // 2. Fresh leads from active call lists (status = NEW)
        $alreadyInHopper = DialerHopper::where('campaign_id', $campaign->id)
            ->pluck('lead_id');

        $leads = Lead::whereHas('callList', function ($q) use ($campaign) {
            $q->where('campaign_id', $campaign->id)->where('is_active', true);
        })
            ->whereIn('status', ['NEW', 'RETRY'])
            ->whereNotIn('id', $alreadyInHopper)
            ->orderBy('id')
            ->limit($slots)
            ->get();

        foreach ($leads as $lead) {
            $inserted = $this->insertLead($campaign, $lead);
            if ($inserted) {
                $added++;
            }
        }

        return $added;
    }

    /**
     * Return the next pending hopper entry for a campaign.
     */
    public function nextLead(Campaign $campaign): ?DialerHopper
    {
        return DialerHopper::where('campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at', '<=', now());
            })
            ->orderBy('scheduled_at')
            ->orderBy('id')
            ->first();
    }

    /**
     * Mark a hopper entry as dialing (locks it so another agent doesn't grab it).
     */
    public function markDialing(DialerHopper $entry): void
    {
        $entry->update(['status' => 'dialing']);
    }

    /**
     * Mark a hopper entry + lead as completed.
     */
    public function completeLead(DialerHopper $entry, string $leadStatus = 'CALLED'): void
    {
        DB::transaction(function () use ($entry, $leadStatus) {
            $entry->update(['status' => 'completed']);
            Lead::where('id', $entry->lead_id)->update([
                'status'        => $leadStatus,
                'last_called_at' => now(),
            ]);
            Lead::where('id', $entry->lead_id)->increment('call_count');
        });
    }

    /**
     * Skip a lead (put back as pending for next cycle).
     */
    public function skipLead(DialerHopper $entry): void
    {
        $entry->update(['status' => 'skipped']);
    }

    // ---------------------------------------------------------------

    private function insertLead(Campaign $campaign, Lead $lead): bool
    {
        $phone = $lead->phone_number ?? null;
        if (!$phone) {
            return false;
        }

        try {
            DialerHopper::create([
                'campaign_id'  => $campaign->id,
                'lead_id'      => $lead->id,
                'phone_number' => $phone,
                'status'       => 'pending',
                'attempt'      => ($lead->call_count ?? 0) + 1,
                'scheduled_at' => null,
            ]);
            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            // Duplicate unique key — lead already in hopper
            return false;
        }
    }
}
