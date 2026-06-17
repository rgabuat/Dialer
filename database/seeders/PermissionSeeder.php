<?php

namespace Database\Seeders;

use App\Services\PermissionRegistrar;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->call('permissions:generate', ['--all' => true]);

        PermissionRegistrar::registerPagePermissions();

        // Re-sync Super Admin so it always holds every permission including new page ones
        $superAdmin = Role::where('name', 'Super Admin')->where('guard_name', 'web')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }
    }
}
