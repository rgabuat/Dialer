<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class ActivityLogger
{
    /**
     * Base logger (do not call directly unless needed)
     */
    public static function log(
        string $type,
        string $event,
        string $action,
        ?Model $actor = null,
        ?Model $subject = null,
        array $properties = [],
        string $severity = 'info'
    ): void {
        ActivityLog::create([
            'type'     => $type,
            'severity' => $severity,

            'event'  => $event,
            'action' => $action,

            'actor_type' => $actor ? $actor::class : null,
            'actor_id'   => $actor?->getKey(),

            'subject_type' => $subject ? $subject::class : null,
            'subject_id'   => $subject?->getKey(),

            'properties' => empty($properties) ? null : $properties,
            'batch_id'   => request()->attributes->get('activity_batch'),

            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'source'     => self::detectSource(),

            'performed_at' => now(),
        ]);
    }

    /**
     * INFO — normal business activity
     */
    public static function info(
        string $type,
        string $event,
        string $action,
        ?Model $actor = null,
        ?Model $subject = null,
        array $properties = []
    ): void {
        self::log($type, $event, $action, $actor, $subject, $properties, 'info');
    }

    /**
     * WARNING — suspicious or risky behavior
     */
    public static function warning(
        string $type,
        string $event,
        string $action,
        ?Model $actor = null,
        ?Model $subject = null,
        array $properties = []
    ): void {
        self::log($type, $event, $action, $actor, $subject, $properties, 'warning');
    }

    /**
     * CRITICAL — security or compliance-sensitive actions
     */
    public static function critical(
        string $type,
        string $event,
        string $action,
        ?Model $actor = null,
        ?Model $subject = null,
        array $properties = []
    ): void {
        self::log($type, $event, $action, $actor, $subject, $properties, 'critical');
    }

    protected static function detectSource(): string
    {
        if (app()->runningInConsole()) {
            return 'system';
        }

        if (request()->expectsJson()) {
            return 'api';
        }

        return 'web';
    }
}
