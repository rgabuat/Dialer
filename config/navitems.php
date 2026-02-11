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
        'permission' => null,
    ],

    [
        'label' => 'Roles & Permissions',
        'route' => 'roles.index',
        'icon'  => 'heroicon-o-shield-check',
        'segment' => 'roles',
        'permission' => null,
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
        'permission' => null,
    ],

    [
        'label' => 'Activity Logs',
        'route' => 'activitylogs.index',
        'icon'  => 'heroicon-o-clock',
        'segment' => 'activitylogs',
        'permission' => null,
    ],

    [
        'label' => 'Stores',
        'route' => 'stores.index',
        'icon'  => 'heroicon-o-building-storefront',
        'segment' => 'stores',
        'permission' => null,
    ],

    [
        'label' => 'Leads',
        'route' => 'leads.index',
        'icon'  => 'heroicon-o-user-group',
        'segment' => 'leads',
        'permission' => null,
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
