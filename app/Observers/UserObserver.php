<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AgentStatusType;
use App\Services\ActivityLogger;
use App\Services\AgentStatusService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        //
        ActivityLogger::info(
            type: 'audit',
            event: 'created',
            action: 'Created user',
            actor: auth()->user()?->id ? auth()->user() : null,
            subject: $user,
            properties: [
                'created' => collect($user->getAttributes())
                    ->except([
                        'password',
                        'remember_token',
                    ])
                    ->toArray(),
            ]
        );

        $offlineStatus = AgentStatusType::where('slug', 'offline')->first();
        if ($offlineStatus) {
            AgentStatusService::change($user, $offlineStatus->id);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        //
        $changes = $user->getChanges();

        if (empty($changes)) {
            return;
        }

        ActivityLogger::log(
            type: 'audit',
            event: 'updated',
            action: 'Updated user',
            actor: auth()->user(),
            subject: $user,
            properties: [
                'before' => array_intersect_key(
                    $user->getOriginal(),
                    $changes
                ),
                'after' => $changes,
            ]
        );
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        ActivityLogger::info(
            type: 'audit',
            event: 'deleted',
            action: 'Deleted user',
            actor: auth()->user(),
            subject: $user,
            properties: [
                'user' => [
                    'id'    => $user->id,
                    'email' => $user->email,
                    'name'  => $user->first_name . ' ' . $user->last_name,
                ],
            ]
        );
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
