<?php

namespace Database\Seeders;

use DateTime;
use DateTimeZone;
use App\Models\Timezone;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TimezoneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $timezones = collect(timezone_identifiers_list())
            ->map(function ($tz) {
                $now = new DateTime('now', new DateTimeZone($tz));
                return [
                    'identifier' => $tz,
                    'utc_offset' => $now->format('P'),
                    'label'      => '(UTC'.$now->format('P').') '.$tz,
                ];
            })
            ->unique('identifier')
            ->values();

        foreach ($timezones as $tz) {
            Timezone::updateOrCreate(
                ['identifier' => $tz['identifier']],
                $tz
            );
        }
    }
}
