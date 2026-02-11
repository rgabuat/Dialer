<?php

namespace App\Observers;

use App\Models\UsersMeta;
use App\Services\ActivityLogger;

class UserMetaObserver
{
    public function created(UsersMeta $meta): void
    {
        ActivityLogger::info(
            type: 'audit',
            event: 'meta_created',
            action: 'Set user meta',
            actor: auth()->user(),
            subject: $meta->user,
            properties: [
                'key'   => $meta->key,
                'value' => $meta->value,
            ]
        );
    }

    public function saved(UsersMeta $meta): void
    {
        
        $before = $meta->getOriginal('value');
        $after  = $meta->value;

        // // Skip if nothing actually changed
        if ((string) $before === (string) $after) {
            return;
        }

        ActivityLogger::info(
            type: 'activity',
            event: 'user_meta_updated',
            action: 'Updated user preference',
            actor: auth()->user(),          // who caused it
            subject: $meta->user,           // who was affected
            properties: [
                'key'    => $meta->key,
                'before' => $before,
                'after'  => $after,
            ]
        );

    }
    
}
