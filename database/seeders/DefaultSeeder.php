<?php

namespace Database\Seeders;

use App\Models\Campaign;
use App\Models\User;
use App\Models\UserGroup;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $admin = User::firstOrCreate(
            ['email' => 'admin@csrpro.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        $superAdmin = Role::firstOrCreate([
            'name' => 'Super Admin',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions(Permission::all());

        $admin->assignRole($superAdmin);

        $campaign = Campaign::firstOrCreate(
            ['name' => 'Admin Campaign'],
            [
                'description' => 'Default campaign assigned to the Super Admin account.',
                'caller_id' => env('TWILIO_PHONE_NUMBER', '+10000000000'),
                'is_active' => true,
            ]
        );

        $group = UserGroup::firstOrCreate(
            ['name' => 'Admin Group'],
            [
                'description' => 'Default user group for Super Admin.',
                'is_active' => true,
            ]
        );

        $group->campaigns()->syncWithoutDetaching([$campaign->id]);

        $admin->update(['user_group_id' => $group->id]);
    }
}
