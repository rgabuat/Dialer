<?php

namespace Database\Seeders;

use App\Models\AgentStatusType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AgentStatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        AgentStatusType::insert([
            [
                'name' => 'Phones',
                'slug' => 'phones',
                'color' => '#22c55e',
                'is_available' => true,
                'is_productive' => true,
                'is_break' => false,
            ],
            [
                'name' => 'Outbound',
                'slug' => 'outbound',
                'color' => '#0ea5e9',
                'is_available' => true,
                'is_productive' => true,
                'is_break' => false,
            ],
            [
                'name' => 'Break',
                'slug' => 'break',
                'color' => '#ec4899',
                'is_available' => false,
                'is_productive' => false,
                'is_break' => true,
            ],
            [
                'name' => 'Lunch',
                'slug' => 'lunch',
                'color' => '#facc15',
                'is_available' => false,
                'is_productive' => false,
                'is_break' => true,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'color' => '#6b7280',
                'is_available' => false,
                'is_productive' => false,
                'is_break' => false,
            ],
        ]);
    }
}
