<?php

namespace App\Services;

use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;

class PermissionRegistrar
{
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