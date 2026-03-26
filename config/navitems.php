<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Main
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'icon'  => 'heroicon-o-squares-2x2',
        'segment' => null,
        'permission' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Management
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Users',
        'route' => 'users.index',
        'icon'  => 'heroicon-o-users',
        'segment' => 'users',
        'permission' => 'user.view',
    ],

    [
        'label' => 'Roles & Permissions',
        'route' => 'roles.index',
        'icon'  => 'heroicon-o-shield-check',
        'segment' => 'roles',
        'permission' => 'user.view',
    ],

    /*
    |--------------------------------------------------------------------------
    | Content / Data
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Reports',
        'route' => 'reports.index',
        'icon'  => 'heroicon-o-chart-bar',
        'segment' => 'reports',
        'permission' => 'audit_log.view',
    ],

    [
        'label' => 'Activity Logs',
        'route' => 'activitylogs.index',
        'icon'  => 'heroicon-o-clock',
        'segment' => 'activitylogs',
        'permission' => 'activity_log.view',
    ],

    [
        'label' => 'Agent Status',
        'route' => 'agent.status.index',
        'icon'  => 'heroicon-o-signal',
        'segment' => 'agent-status',
        'permission' => 'agent_status.view',
    ],

    [
        'label' => 'Stores',
        'route' => 'stores.index',
        'icon'  => 'heroicon-o-building-storefront',
        'segment' => 'stores',
        'permission' => 'store.view',
    ],

    [
        'label' => 'Leads',
        'route' => 'leads.index',
        'icon'  => 'heroicon-o-user-group',
        'segment' => 'leads',
        'permission' => 'lead.view',
    ],

    /*
    |--------------------------------------------------------------------------
    | Settings
    |--------------------------------------------------------------------------
    */

    [
        'label' => 'Settings',
        'route' => 'settings.profile',
        'icon'  => 'heroicon-o-cog-6-tooth',
        'segment' => 'settings',
        'permission' => null,
    ],

];
