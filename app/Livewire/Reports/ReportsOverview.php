<?php

namespace App\Livewire\Reports;

use Livewire\Component;
use App\Models\Campaign;
use App\Models\Conversation;
use App\Models\Lead;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsOverview extends Component
{
    public string $tab   = 'overview';
    public string $range = 'last_30';

    protected function window(): array
    {
        return match ($this->range) {
            'today'      => [now()->startOfDay(),               now()->endOfDay()],
            'yesterday'  => [now()->subDay()->startOfDay(),     now()->subDay()->endOfDay()],
            'last_7'     => [now()->subDays(6)->startOfDay(),   now()->endOfDay()],
            'this_month' => [now()->startOfMonth(),             now()->endOfDay()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            default      => [now()->subDays(29)->startOfDay(),  now()->endOfDay()],
        };
    }

    /** Stream a CSV file to the browser. */
    public function export(): StreamedResponse
    {
        [$from, $to] = $this->window();

        $rangeSlug = str_replace(['_', ' '], '-', $this->range);
        $filename  = "report-{$this->tab}-{$rangeSlug}-" . now()->format('Ymd') . '.csv';

        $rows = match ($this->tab) {
            'conversations' => $this->exportConversations($from, $to),
            'leads'         => $this->exportLeads($from, $to),
            'agents'        => $this->exportAgents($from, $to),
            'campaigns'     => $this->exportCampaigns($from, $to),
            default         => $this->exportOverview($from, $to),
        };

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // ── Export helpers ────────────────────────────────────────────────────────

    private function exportOverview($from, $to): array
    {
        $total      = Conversation::whereBetween('started_at', [$from, $to])->count();
        $completed  = Conversation::whereBetween('started_at', [$from, $to])->where('status', 'completed')->count();
        $abandoned  = Conversation::whereBetween('started_at', [$from, $to])->where('status', 'abandoned')->count();
        $avgDur     = (int) Conversation::whereBetween('started_at', [$from, $to])->whereNotNull('duration_seconds')->avg('duration_seconds');
        $totalLeads = Lead::whereBetween('created_at', [$from, $to])->count();
        $converted  = Lead::whereBetween('created_at', [$from, $to])->where('pipeline_stage', 'converted')->count();

        return [
            ['Metric', 'Value'],
            ['Period', $from->toDateString() . ' to ' . $to->toDateString()],
            ['Total Conversations', $total],
            ['Completed Conversations', $completed],
            ['Completion Rate %', $total ? round($completed / $total * 100) : 0],
            ['Abandoned Conversations', $abandoned],
            ['Abandon Rate %', $total ? round($abandoned / $total * 100) : 0],
            ['Avg Handle Time (seconds)', $avgDur],
            ['Total Leads', $totalLeads],
            ['Converted Leads', $converted],
            ['Conversion Rate %', $totalLeads ? round($converted / $totalLeads * 100) : 0],
        ];
    }

    private function exportConversations($from, $to): array
    {
        $headers = ['ID', 'Contact Name', 'Contact Phone', 'Channel', 'Direction', 'Status',
                    'Campaign', 'Queue', 'Assigned To', 'Completed By', 'Disposition',
                    'Duration (sec)', 'Started At', 'Ended At'];

        $out = [$headers];

        Conversation::whereBetween('started_at', [$from, $to])
            ->with(['campaign:id,name', 'assignedAgent:id,name', 'completedByAgent:id,name', 'disposition:id,name'])
            ->orderBy('started_at')
            ->chunk(500, function ($rows) use (&$out) {
                foreach ($rows as $c) {
                    $out[] = [
                        $c->id,
                        $c->contact_name ?? '',
                        $c->contact_phone ?? '',
                        $c->channel ?? '',
                        $c->direction ?? '',
                        $c->status ?? '',
                        $c->campaign?->name ?? '',
                        $c->queue ?? '',
                        $c->assignedAgent?->name ?? '',
                        $c->completedByAgent?->name ?? '',
                        $c->disposition?->name ?? '',
                        $c->duration_seconds ?? '',
                        $c->started_at?->toDateTimeString() ?? '',
                        $c->ended_at?->toDateTimeString() ?? '',
                    ];
                }
            });

        return $out;
    }

    private function exportLeads($from, $to): array
    {
        $headers = ['ID', 'First Name', 'Last Name', 'Phone', 'Email',
                    'Lead Type', 'Status', 'Pipeline Stage', 'Store',
                    'Move In Date', 'Created By', 'Created At'];

        $out = [$headers];

        Lead::whereBetween('created_at', [$from, $to])
            ->with(['store:id,name', 'creator:id,name'])
            ->orderBy('created_at')
            ->chunk(500, function ($rows) use (&$out) {
                foreach ($rows as $l) {
                    $out[] = [
                        $l->id,
                        $l->first_name ?? '',
                        $l->last_name ?? '',
                        $l->phone ?? '',
                        $l->email ?? '',
                        $l->lead_type ?? '',
                        $l->status ?? '',
                        $l->pipeline_stage ?? '',
                        $l->store?->name ?? '',
                        $l->move_in_date?->toDateString() ?? '',
                        $l->creator?->name ?? '',
                        $l->created_at?->toDateTimeString() ?? '',
                    ];
                }
            });

        return $out;
    }

    private function exportAgents($from, $to): array
    {
        $rows = Conversation::whereBetween('started_at', [$from, $to])
            ->with('assignedAgent:id,name')
            ->whereNotNull('assigned_to')
            ->select(
                'assigned_to',
                DB::raw('count(*) as total'),
                DB::raw('round(avg(duration_seconds)) as avg_dur'),
                DB::raw("sum(case when status='completed' then 1 else 0 end) as completed")
            )
            ->groupBy('assigned_to')
            ->orderByDesc('total')
            ->get();

        $leadsPerAgent = Lead::whereBetween('created_at', [$from, $to])
            ->whereNotNull('created_by')
            ->select('created_by', DB::raw('count(*) as total'))
            ->groupBy('created_by')
            ->pluck('total', 'created_by');

        $out = [['Agent', 'Conversations', 'Completed', 'Completion %', 'Avg Duration (sec)', 'Leads Created']];

        foreach ($rows as $r) {
            $out[] = [
                $r->assignedAgent?->name ?? 'Unassigned',
                $r->total,
                $r->completed,
                $r->total ? round($r->completed / $r->total * 100) : 0,
                $r->avg_dur ?? 0,
                $leadsPerAgent[$r->assigned_to] ?? 0,
            ];
        }

        return $out;
    }

    private function exportCampaigns($from, $to): array
    {
        $userGroupId     = auth()->user()->user_group_id;
        $userCampaignIds = $userGroupId
            ? Campaign::whereHas('userGroups', fn($q) => $q->where('user_group_id', $userGroupId))->pluck('id')
            : collect();

        $rows = Conversation::whereBetween('started_at', [$from, $to])
            ->when($userCampaignIds->isNotEmpty(), fn($q) => $q->whereIn('campaign_id', $userCampaignIds))
            ->when($userCampaignIds->isEmpty(), fn($q) => $q->whereRaw('0=1'))
            ->with('campaign:id,name')
            ->select(
                'campaign_id',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when direction='inbound' then 1 else 0 end) as inbound"),
                DB::raw("sum(case when direction='outbound' then 1 else 0 end) as outbound"),
                DB::raw("sum(case when status='completed' then 1 else 0 end) as completed"),
                DB::raw("sum(case when status='abandoned' then 1 else 0 end) as abandoned"),
                DB::raw('round(avg(duration_seconds)) as avg_dur')
            )
            ->groupBy('campaign_id')
            ->orderByDesc('total')
            ->get();

        $out = [['Campaign', 'Total Calls', 'Inbound', 'Outbound', 'Completed', 'Abandoned', 'Completion %', 'Avg Duration (sec)']];

        foreach ($rows as $r) {
            $out[] = [
                $r->campaign?->name ?? 'No Campaign',
                $r->total,
                $r->inbound ?? 0,
                $r->outbound ?? 0,
                $r->completed,
                $r->abandoned ?? 0,
                $r->total ? round($r->completed / $r->total * 100) : 0,
                $r->avg_dur ?? 0,
            ];
        }

        return $out;
    }

    public function render()
    {
        [$from, $to] = $this->window();

        // ── Global KPIs ───────────────────────────────────────────────────────
        $totalConversations    = Conversation::whereBetween('started_at', [$from, $to])->count();
        $completedConversations = Conversation::whereBetween('started_at', [$from, $to])->where('status', 'completed')->count();
        $abandonedConversations = Conversation::whereBetween('started_at', [$from, $to])->where('status', 'abandoned')->count();
        $avgDuration           = (int) Conversation::whereBetween('started_at', [$from, $to])
                                        ->whereNotNull('duration_seconds')
                                        ->avg('duration_seconds');
        $totalLeads            = Lead::whereBetween('created_at', [$from, $to])->count();
        $convertedLeads        = Lead::whereBetween('created_at', [$from, $to])->where('pipeline_stage', 'converted')->count();
        $activeAgents          = Conversation::whereBetween('started_at', [$from, $to])
                                        ->whereNotNull('assigned_to')
                                        ->distinct('assigned_to')
                                        ->count('assigned_to');

        // ── Conversations by channel ───────────────────────────────────────────
        $byChannel = Conversation::whereBetween('started_at', [$from, $to])
            ->select('channel', DB::raw('count(*) as total'), DB::raw('round(avg(duration_seconds)) as avg_dur'))
            ->groupBy('channel')
            ->orderByDesc('total')
            ->get();

        // ── Conversations by direction ─────────────────────────────────────────
        $byDirection = Conversation::whereBetween('started_at', [$from, $to])
            ->select('direction', DB::raw('count(*) as total'))
            ->groupBy('direction')
            ->get();

        // ── Conversations by status breakdown ─────────────────────────────────
        $byStatus = Conversation::whereBetween('started_at', [$from, $to])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        // ── By campaign (global – used in Overview top-campaigns widget) ─────
        $byCampaign = Conversation::whereBetween('started_at', [$from, $to])
            ->with('campaign:id,name')
            ->select('campaign_id', DB::raw('count(*) as total'), DB::raw('round(avg(duration_seconds)) as avg_dur'),
                DB::raw("sum(case when status='completed' then 1 else 0 end) as completed"))
            ->groupBy('campaign_id')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        // ── Call Volume – user-scoped campaign breakdown ──────────────────────
        $userGroupId     = auth()->user()->user_group_id;
        $userCampaignIds = $userGroupId
            ? Campaign::whereHas('userGroups', fn($q) => $q->where('user_group_id', $userGroupId))->pluck('id')
            : collect();

        $callVolumeByCampaign = Conversation::whereBetween('started_at', [$from, $to])
            ->when($userCampaignIds->isNotEmpty(), fn($q) => $q->whereIn('campaign_id', $userCampaignIds))
            ->when($userCampaignIds->isEmpty(), fn($q) => $q->whereRaw('0=1'))
            ->with('campaign:id,name,type,dial_mode')
            ->select(
                'campaign_id',
                DB::raw('count(*) as total'),
                DB::raw("sum(case when direction='inbound' then 1 else 0 end) as inbound"),
                DB::raw("sum(case when direction='outbound' then 1 else 0 end) as outbound"),
                DB::raw("sum(case when status='completed' then 1 else 0 end) as completed"),
                DB::raw("sum(case when status='abandoned' then 1 else 0 end) as abandoned"),
                DB::raw('round(avg(duration_seconds)) as avg_dur')
            )
            ->groupBy('campaign_id')
            ->orderByDesc('total')
            ->get();

        $cvTotalCalls  = $callVolumeByCampaign->sum('total');
        $cvInbound     = $callVolumeByCampaign->sum('inbound');
        $cvOutbound    = $callVolumeByCampaign->sum('outbound');
        $cvCompleted   = $callVolumeByCampaign->sum('completed');
        $cvAbandoned   = $callVolumeByCampaign->sum('abandoned');
        $cvAvgDur      = $cvTotalCalls > 0
            ? (int) round($callVolumeByCampaign->sum(fn($r) => ($r->avg_dur ?? 0) * $r->total) / $cvTotalCalls)
            : 0;
        $cvMaxTotal    = $callVolumeByCampaign->max('total') ?: 1;

        // ── By disposition ────────────────────────────────────────────────────
        $byDisposition = Conversation::whereBetween('started_at', [$from, $to])
            ->with('disposition:id,name,code')
            ->whereNotNull('disposition_id')
            ->select('disposition_id', DB::raw('count(*) as total'))
            ->groupBy('disposition_id')
            ->orderByDesc('total')
            ->get();

        // ── By agent (conversations) ───────────────────────────────────────────
        $byAgent = Conversation::whereBetween('started_at', [$from, $to])
            ->with('assignedAgent:id,name')
            ->whereNotNull('assigned_to')
            ->select(
                'assigned_to',
                DB::raw('count(*) as total'),
                DB::raw('round(avg(duration_seconds)) as avg_dur'),
                DB::raw("sum(case when status='completed' then 1 else 0 end) as completed")
            )
            ->groupBy('assigned_to')
            ->orderByDesc('total')
            ->get();

        // Lead counts per agent (for agents tab)
        $leadsPerAgent = Lead::whereBetween('created_at', [$from, $to])
            ->whereNotNull('created_by')
            ->select('created_by', DB::raw('count(*) as total'))
            ->groupBy('created_by')
            ->pluck('total', 'created_by');

        // ── Leads by type ─────────────────────────────────────────────────────
        $leadsByType = Lead::whereBetween('created_at', [$from, $to])
            ->select('lead_type', DB::raw('count(*) as total'))
            ->groupBy('lead_type')
            ->get();

        // ── Leads by pipeline stage ───────────────────────────────────────────
        $leadsByStage = Lead::whereBetween('created_at', [$from, $to])
            ->select('pipeline_stage', DB::raw('count(*) as total'))
            ->groupBy('pipeline_stage')
            ->get();

        // ── Leads by store ────────────────────────────────────────────────────
        $leadsByStore = Lead::whereBetween('created_at', [$from, $to])
            ->with('store:id,name,city,state,brand')
            ->whereNotNull('store_id')
            ->select('store_id', DB::raw('count(*) as total'))
            ->groupBy('store_id')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        // ── Leads by creator ──────────────────────────────────────────────────
        $leadsByAgent = Lead::whereBetween('created_at', [$from, $to])
            ->with('creator:id,name')
            ->whereNotNull('created_by')
            ->select('created_by', DB::raw('count(*) as total'))
            ->groupBy('created_by')
            ->orderByDesc('total')
            ->get();

        // ── Daily conversation volume (sparkline) ─────────────────────────────
        $dailyConvRaw = Conversation::whereBetween('started_at', [$from, $to])
            ->select(DB::raw('DATE(started_at) as day'), DB::raw('count(*) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        // Fill zero-days in the range
        $dailyVolume = [];
        $cursor = $from->copy()->startOfDay();
        while ($cursor->lte($to)) {
            $dailyVolume[$cursor->toDateString()] = $dailyConvRaw[$cursor->toDateString()] ?? 0;
            $cursor->addDay();
        }
        $maxDailyVolume = max($dailyVolume) ?: 1;

        // ── Lead value totals ─────────────────────────────────────────────────
        $allLeadsInPeriod = Lead::whereBetween('created_at', [$from, $to])
            ->whereNotNull('selected_units')
            ->get(['selected_units', 'lead_type', 'pipeline_stage']);

        $totalLeadValue = $allLeadsInPeriod->sum(function ($l) {
            return collect($l->selected_units ?? [])->sum(fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1));
        });

        $convertedLeadValue = $allLeadsInPeriod->where('pipeline_stage', 'converted')->sum(function ($l) {
            return collect($l->selected_units ?? [])->sum(fn($u) => ($u['push_rate'] ?? 0) * max(1, $u['qty'] ?? 1));
        });

        return view('livewire.reports.reports-overview', compact(
            'totalConversations', 'completedConversations', 'abandonedConversations',
            'avgDuration', 'totalLeads', 'convertedLeads', 'activeAgents',
            'byChannel', 'byDirection', 'byStatus', 'byCampaign', 'byDisposition', 'byAgent',
            'leadsPerAgent', 'leadsByType', 'leadsByStage', 'leadsByStore', 'leadsByAgent',
            'dailyVolume', 'maxDailyVolume', 'totalLeadValue', 'convertedLeadValue',
            'callVolumeByCampaign', 'cvTotalCalls', 'cvInbound', 'cvOutbound',
            'cvCompleted', 'cvAbandoned', 'cvAvgDur', 'cvMaxTotal'
        ))->layout('components.layouts.app');
    }
}
