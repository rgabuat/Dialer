<?php

namespace App\Services;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionRegistrar
{
    /**
     * All page-level permissions, organized by nav group.
     * Key = permission name, value = display label.
     * Used in the Roles UI to render the "Page Access" section.
     */
    public static array $pageGroups = [
        'Dashboard' => [
            'page.dashboard' => 'Dashboard',
        ],
        'Campaign' => [
            'page.campaigns'    => 'All Campaigns',
            'page.call_lists'   => 'Call Lists',
            'page.dispositions' => 'Dispositions',
            'page.callbacks'    => 'Callbacks',
        ],
        'People' => [
            'page.users'       => 'Users',
            'page.roles'       => 'Roles & Permissions',
            'page.user_groups' => 'User Groups',
        ],
        'Activity' => [
            'page.activity_overview' => 'Overview',
            'page.activity_logs'     => 'Activity Logs',
            'page.agent_status'      => 'Agent Status',
            'page.shift_monitoring'  => 'Shift Monitoring',
            'page.queue_monitor'     => 'Queue Monitor',
        ],
        'Operations' => [
            'page.leads'  => 'Leads',
            'page.stores' => 'Stores',
        ],
        'Reports' => [
            'page.reports' => 'Reports',
        ],
        'Conversations' => [
            'page.conversations' => 'Conversations',
        ],
        'Workforce' => [
            'page.workforce' => 'Rosters',
        ],
        'Inbound' => [
            'page.cid_numbers' => 'CID Numbers',
            'page.in_groups'   => 'In-Groups',
            'page.dids'        => 'DIDs',
            'page.ivr_menus'   => 'IVR Menus',
        ],
        'Settings' => [
            'page.settings' => 'Settings',
        ],
    ];

    /**
     * Maps each sidebar section to the CRUD module slugs that live under it.
     * Used in the Roles UI to group module permissions by nav section.
     */
    public static array $crudGroups = [
        'Dashboard'     => [],
        'Campaign'      => ['campaign', 'call_list', 'callback_schedule', 'disposition'],
        'People'        => ['user', 'user_group'],
        'Activity'      => ['activity_log', 'agent_status', 'agent_status_log', 'agent_status_type', 'audit_log'],
        'Operations'    => ['lead', 'store', 'store_unit'],
        'Reports'       => [],
        'Conversations' => ['conversation', 'conversation_note'],
        'Workforce'     => ['roster', 'roster_shift', 'shift_activity', 'staffing_interval'],
        'Inbound'       => ['cid_number', 'did', 'in_group', 'ivr_menu', 'ivr_menu_option'],
        'Settings'      => ['voice_setting', 'users_meta', 'dialer_hopper'],
    ];

    /**
     * Seed all page-level permissions into the database.
     */
    public static function registerPagePermissions(): void
    {
        foreach (static::$pageGroups as $pages) {
            foreach (array_keys($pages) as $permissionName) {
                Permission::firstOrCreate([
                    'name'       => $permissionName,
                    'guard_name' => 'web',
                ]);
            }
        }
    }

    public static function for(string $module, array $actions = ['view', 'create', 'update', 'delete']): void
    {
        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'name'       => "{$module}.{$action}",
                'guard_name' => 'web',
            ]);
        }
    }

    public static function forAll(): array
    {
        $modelPath = app_path('Models');
        $created   = [];
        $skipped   = [];

        foreach (glob("{$modelPath}/*.php") as $file) {
            $class = 'App\\Models\\' . basename($file, '.php');

            if (! class_exists($class)) {
                continue;
            }

            $module = Str::snake(class_basename($class));

            // Skip entire module if all 4 permissions already exist
            $existing = Permission::where('guard_name', 'web')
                ->where('name', 'like', "{$module}.%")
                ->count();

            if ($existing >= 4) {
                $skipped[] = $module;
                continue;
            }

            static::for($module);
            $created[] = $module;
        }

        return ['created' => $created, 'skipped' => $skipped];
    }
}