<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courseBill = DB::table('course_bill')->get(['id', 'user_id']);

        for ($i = 0; $i < count($courseBill); $i++) {
            $random = random_int(10, 99);
            $countries = [0 => 'United States', 1 => 'Viet Nam', 2 => 'Japan'];
            $languages = [0 => 'English', 1 => 'Tiếng Việt', 2 => 'Japanese'];
            $country_code = [0 => 'US', 1 => 'VN', 2 => 'JP'];

            $randomCountry = random_int(0, count($countries) - 1);

            DB::table('location')->insert([
                'ip' => '116.110.43.' . $random,
                'user_id' => $courseBill[$i]->user_id,
                'country' => $countries[$randomCountry],
                'language' => $languages[$randomCountry],
                'country_code' => $country_code[$randomCountry]
            ]);
        }
    }
}
