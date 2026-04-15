<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('stores')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        DB::table('stores')->insert([
            [
                'id'        => 1,
                'name'      => 'SecurCare - 0158 - Sandy Springs GA',
                'brand'     => 'SecurCare',
                'type'      => 'Original store',
                'occupancy' => 86.6,
                'address'   => '8457 Roswell Rd NE',
                'city'      => 'Sandy Springs',
                'state'     => 'GA',
                'zip'       => '30350',
                'country'   => 'USA',
                'featured'  => true,
                'pricing'   => json_encode([
                    'small'  => ['size' => '4 x 5',   'price' => 17],
                    'medium' => ['size' => '10 x 12',  'price' => 60],
                    'large'  => ['size' => '10 x 20',  'price' => 107],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'        => 2,
                'name'      => 'SecurCare - 0153 - Doraville GA',
                'brand'     => 'SecurCare',
                'type'      => null,
                'occupancy' => 89.6,
                'address'   => '3751 Longmire Way',
                'city'      => 'Doraville',
                'state'     => 'GA',
                'zip'       => '30340',
                'country'   => 'USA',
                'featured'  => false,
                'pricing'   => json_encode([
                    'small'  => ['size' => '4 x 4',   'price' => 31],
                    'medium' => ['size' => '10 x 10',  'price' => 119],
                    'large'  => ['size' => '10 x 20',  'price' => 290],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'        => 3,
                'name'      => 'SecurCare - 0855 - Norcross GA',
                'brand'     => 'SecurCare',
                'type'      => null,
                'occupancy' => 80.5,
                'address'   => '1 Western Hills Ct',
                'city'      => 'Norcross',
                'state'     => 'GA',
                'zip'       => '30071',
                'country'   => 'USA',
                'featured'  => false,
                'pricing'   => json_encode([
                    'small'  => ['size' => '5 x 5',   'price' => 15],
                    'medium' => ['size' => '10 x 10',  'price' => 39],
                    'large'  => ['size' => '20 x 10',  'price' => 110],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'        => 4,
                'name'      => 'SecurCare - 0880 - Marietta GA',
                'brand'     => 'SecurCare',
                'type'      => null,
                'occupancy' => 88.3,
                'address'   => '523 Wylie Rd SE',
                'city'      => 'Marietta',
                'state'     => 'GA',
                'zip'       => '30067',
                'country'   => 'USA',
                'featured'  => false,
                'pricing'   => json_encode([
                    'small'  => ['size' => '5 x 10',  'price' => 34],
                    'medium' => null,
                    'large'  => ['size' => '10 x 20',  'price' => 108],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'        => 5,
                'name'      => 'SecurCare - 0856 - Marietta GA',
                'brand'     => 'SecurCare',
                'type'      => null,
                'occupancy' => 67.9,
                'address'   => '1185 S Cobb Dr SE',
                'city'      => 'Marietta',
                'state'     => 'GA',
                'zip'       => '30060',
                'country'   => 'USA',
                'featured'  => false,
                'pricing'   => json_encode([
                    'small'  => ['size' => '5 x 5',   'price' => 15],
                    'medium' => ['size' => '10 x 10',  'price' => 48],
                    'large'  => ['size' => '10 x 25',  'price' => 121],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'        => 6,
                'name'      => 'SecurCare - 0152 - Smyrna GA',
                'brand'     => 'SecurCare',
                'type'      => null,
                'occupancy' => 91.5,
                'address'   => '2960 S Cobb Dr SE',
                'city'      => 'Smyrna',
                'state'     => 'GA',
                'zip'       => '30080',
                'country'   => 'USA',
                'featured'  => false,
                'pricing'   => json_encode([
                    'small'  => ['size' => '6 x 10',  'price' => 41],
                    'medium' => ['size' => '10 x 10',  'price' => 72],
                    'large'  => ['size' => '10 x 30',  'price' => 298],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id'        => 7,
                'name'      => 'SecurCare - 0134 - Decatur GA',
                'brand'     => 'SecurCare',
                'type'      => null,
                'occupancy' => 75.2,
                'address'   => '2155 Lawrenceville Hwy',
                'city'      => 'Decatur',
                'state'     => 'GA',
                'zip'       => '30033',
                'country'   => 'USA',
                'featured'  => false,
                'pricing'   => json_encode([
                    'small'  => ['size' => '5 x 5',   'price' => 22],
                    'medium' => ['size' => '10 x 10',  'price' => 55],
                    'large'  => ['size' => '10 x 20',  'price' => 115],
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
