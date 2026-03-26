<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\DefaultSeeder;
use Database\Seeders\AgentStatusTypeSeeder;
use Database\Seeders\PermissionSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        $this->call(DefaultSeeder::class);
        $this->call(TimezoneSeeder::class);
        $this->call(AgentStatusTypeSeeder::class);
        $this->call(PermissionSeeder::class);
    }
}
