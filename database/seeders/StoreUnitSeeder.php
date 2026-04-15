<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreUnitSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('store_units')->where('store_id', 1)->delete();

        DB::table('store_units')->insert([
            [
                'store_id'    => 1,
                'size'        => '4 x 5',
                'category'    => 'Small Storage',
                'available'   => 2,
                'street_rate' => 16,
                'push_rate'   => 17,
                'features'    => json_encode(['Inside', 'Temperature Control', '1st Floor', 'A/C + Heat', 'Swing Door']),
                'promos'      => json_encode(["50% Off First Month'S Rent", '1st Month Free', '5% Military & First Responder', 'Second Month Free', 'First Month Half Off']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'store_id'    => 1,
                'size'        => '6 x 6',
                'category'    => 'Small Storage',
                'available'   => 12,
                'street_rate' => 22,
                'push_rate'   => 24,
                'features'    => json_encode(['Inside', 'Drive Up', 'Non Climate', 'Swing Door']),
                'promos'      => json_encode(["50% Off First Month'S Rent", '1st Month Free', 'First Month Free', '5% Military & First Responder', 'Second Month Free', 'First Month Half Off']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'store_id'    => 1,
                'size'        => '5 x 10',
                'category'    => 'Small Storage',
                'available'   => 2,
                'street_rate' => 29,
                'push_rate'   => 31,
                'features'    => json_encode(['Outside', 'Drive Up', 'Non Climate', 'Roll Up Door']),
                'promos'      => json_encode(["50% Off First Month'S Rent", '1st Month Free', '5% Military & First Responder', 'Second Month Free']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'store_id'    => 1,
                'size'        => '10 x 10',
                'category'    => 'Medium Storage',
                'available'   => 5,
                'street_rate' => 55,
                'push_rate'   => 60,
                'features'    => json_encode(['Inside', 'Temperature Control', '1st Floor', 'A/C + Heat']),
                'promos'      => json_encode(["50% Off First Month'S Rent", '1st Month Free', '5% Military & First Responder']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'store_id'    => 1,
                'size'        => '10 x 12',
                'category'    => 'Medium Storage',
                'available'   => 3,
                'street_rate' => 58,
                'push_rate'   => 60,
                'features'    => json_encode(['Inside', 'Drive Up', 'Non Climate']),
                'promos'      => json_encode(['1st Month Free', 'Second Month Free']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'store_id'    => 1,
                'size'        => '10 x 20',
                'category'    => 'Large Storage',
                'available'   => 1,
                'street_rate' => 99,
                'push_rate'   => 107,
                'features'    => json_encode(['Outside', 'Drive Up', 'Non Climate', 'Roll Up Door']),
                'promos'      => json_encode(["50% Off First Month'S Rent", '5% Military & First Responder', 'First Month Half Off']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
