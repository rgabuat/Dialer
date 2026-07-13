<?php

namespace Database\Seeders;

use App\Models\AgentStatusType;
use Illuminate\Database\Seeder;

class AgentStatusTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $types = [
            [
                'name' => 'Phones',
                'slug' => 'phones',
                'color' => '#22c55e',
                'is_available' => true,
                'is_productive' => true,
                'is_break' => false,
                'handles_inbound' => true,
                'handles_outbound' => true,
            ],
            [
                'name' => 'Outbound',
                'slug' => 'outbound',
                'color' => '#0ea5e9',
                'is_available' => true,
                'is_productive' => true,
                'is_break' => false,
                'handles_inbound' => false,
                'handles_outbound' => true,
            ],
            [
                'name' => 'Break',
                'slug' => 'break',
                'color' => '#ec4899',
                'is_available' => false,
                'is_productive' => false,
                'is_break' => true,
                'handles_inbound' => false,
                'handles_outbound' => false,
            ],
            [
                'name' => 'Lunch',
                'slug' => 'lunch',
                'color' => '#facc15',
                'is_available' => false,
                'is_productive' => false,
                'is_break' => true,
                'handles_inbound' => false,
                'handles_outbound' => false,
            ],
            [
                'name' => 'Other',
                'slug' => 'other',
                'color' => '#6b7280',
                'is_available' => false,
                'is_productive' => false,
                'is_break' => false,
                'handles_inbound' => false,
                'handles_outbound' => false,
            ],
            [
                'name' => 'Offline',
                'slug' => 'offline',
                'color' => '#6b7280',
                'is_available' => false,
                'is_productive' => false,
                'is_break' => false,
                'handles_inbound' => false,
                'handles_outbound' => false,
            ],
        ];

        foreach ($types as $type) {
            AgentStatusType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
