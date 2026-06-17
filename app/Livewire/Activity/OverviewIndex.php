<?php

namespace App\Livewire\Activity;

use App\Models\AgentStatus;
use App\Models\CallbackSchedule;
use App\Models\Conversation;
use Livewire\Component;

class OverviewIndex extends Component
{
    public function render()
    {
        $today = today();

        // ── Volume ──────────────────────────────────────────────────
        $offered          = Conversation::whereDate('started_at', $today)->count();
        $callbacksPending = CallbackSchedule::whereIn('status', ['pending', 'scheduled'])->count();
        $avgHandleSecs    = (int) Conversation::where('status', 'completed')
                                ->whereDate('started_at', $today)
                                ->avg('duration_seconds');

        // ── Handled ─────────────────────────────────────────────────
        $totalHandled    = Conversation::whereDate('started_at', $today)->where('status', 'completed')->count();
        $inboundHandled  = Conversation::whereDate('started_at', $today)->where('status', 'completed')->where('direction', 'inbound')->count();
        $outboundHandled = Conversation::whereDate('started_at', $today)->where('status', 'completed')->where('direction', 'outbound')->count();
        $callbackHandled = CallbackSchedule::where('status', 'completed')
                            ->whereDate('updated_at', $today)
                            ->whereNotNull('conversation_id')
                            ->count();

        // ── Service ─────────────────────────────────────────────────
        $answerRate = $offered > 0 ? round($totalHandled / $offered * 100, 1) : 0;

        // ── Abandoned ───────────────────────────────────────────────
        $abandonedCount = Conversation::whereDate('started_at', $today)->where('status', 'abandoned')->count();
        $avgAbandonSecs = (int) Conversation::whereDate('started_at', $today)->where('status', 'abandoned')->avg('duration_seconds');
        $abandonRate    = $offered > 0 ? round($abandonedCount / $offered * 100, 2) : 0;

        // ── Outcomes ────────────────────────────────────────────────
        $dispositioned   = Conversation::whereDate('started_at', $today)->where('status', 'completed')->whereNotNull('disposition_id')->count();
        $dispositionRate = $totalHandled > 0 ? round($dispositioned / $totalHandled * 100, 1) : 0;
        $inProgress      = Conversation::where('status', 'in_progress')->count();

        // ── Agents (current status) ──────────────────────────────────
        $latestStatuses  = AgentStatus::with('statusType')->latest('started_at')->get()->unique('user_id');
        $agentsOnline    = $latestStatuses->filter(fn($s) => $s->statusType?->slug !== 'offline')->count();
        $agentsAvailable = $latestStatuses->filter(fn($s) => $s->statusType?->is_available)->count();
        $agentsOnBreak   = $latestStatuses->filter(fn($s) => $s->statusType?->is_break)->count();

        return view('livewire.activity.overview-index', [
            // Volume
            'offered'          => $offered,
            'callbacksPending' => $callbacksPending,
            'avgHandleMins'    => intdiv($avgHandleSecs, 60),
            'avgHandleRem'     => $avgHandleSecs % 60,
            // Handled
            'totalHandled'    => $totalHandled,
            'inboundHandled'  => $inboundHandled,
            'outboundHandled' => $outboundHandled,
            'callbackHandled' => $callbackHandled,
            // Service
            'answerRate'      => $answerRate,
            // Abandoned
            'abandonedCount'  => $abandonedCount,
            'avgAbandonMins'  => intdiv($avgAbandonSecs, 60),
            'avgAbandonRem'   => $avgAbandonSecs % 60,
            'abandonRate'     => $abandonRate,
            // Outcomes
            'dispositionRate' => $dispositionRate,
            'inProgress'      => $inProgress,
            // Agents
            'agentsOnline'    => $agentsOnline,
            'agentsAvailable' => $agentsAvailable,
            'agentsOnBreak'   => $agentsOnBreak,
        ])->layout('components.layouts.app');
    }
}
