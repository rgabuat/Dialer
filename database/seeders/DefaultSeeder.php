<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DefaultSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        User::firstOrCreate(
            ['email' => 'admin@csrpro.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'Admin',
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
    }
}
