<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ClientLogController extends Controller
{
    /**
     * POST /api/client-log
     *
     * Accepts structured log entries from the browser and writes them to
     * the Laravel log so they appear alongside server-side events.
     * Rate-limited to 60 req/min per IP via the route definition.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'level'   => ['required', 'string', 'in:error,warn,info,debug'],
            'message' => ['required', 'string', 'max:2000'],
            'url'     => ['nullable', 'string', 'max:500'],
            'context' => ['nullable', 'array'],
            'context.*' => ['nullable'],
        ]);

        // Strip control characters from message to prevent log-injection.
        $message = preg_replace('/[\r\n\t]+/', ' ', $data['message']);

        $context = [
            'user_id'    => auth()->id(),
            'ip'         => $request->ip(),
            'url'        => isset($data['url']) ? substr($data['url'], 0, 500) : null,
            'user_agent' => substr((string) $request->userAgent(), 0, 200),
            'context'    => $data['context'] ?? [],
        ];

        match ($data['level']) {
            'error' => Log::error("[Frontend] {$message}", $context),
            'warn'  => Log::warning("[Frontend] {$message}", $context),
            'debug' => Log::debug("[Frontend] {$message}", $context),
            default => Log::info("[Frontend] {$message}", $context),
        };

        return response()->noContent();
    }
}
