<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Twilio webhook endpoints — called server-to-server, no CSRF token
        'api/call-routing',
        'api/call/complete',
        'api/call/no-answer',
        'api/call/queue-check',
        'api/call/ivr-gather',
        'api/call/forward-twiml',
        'api/dialer/connect-to-agent',
        // Frontend error logger — no session context when called from JS
        'api/client-log',
    ];
}
