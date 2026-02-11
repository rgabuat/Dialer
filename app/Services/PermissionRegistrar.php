<?php 

namespace App\Services;

use Spatie\Permission\Models\Permission;

class PermissionRegistrar
{
    public static function for(string $module)
    {
        foreach (['view', 'create', 'update', 'delete'] as $action) {
            Permission::firstOrCreate([
                'name' => "{$module}.{$action}",
            ]);
        }
    }
}