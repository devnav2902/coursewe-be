<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PriceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        db::table('price')->insert([
            'original_price' => 0,
            'format_price' => 0
        ]);
        $original_delta = 100000;
        $format_delta = 100;

        $original_data49 = 249000;
        $original_data99 = 299000;
        $format_data49 = 249;
        $format_data99 = 299;

        for ($i = 1; $i <= 9; $i++) {
            $original_data49 += $original_delta;
            $original_data99 += $original_delta;
            $format_data49 += $format_delta;
            $format_data99 += $format_delta;

            db::table('price')->insert([
                [
                    'original_price' => $original_data49,
                    'format_price' => number_format($format_data49, 3, '.', '.')
                ],
                [
                    'original_price' => $original_data99,
                    'format_price' => number_format($format_data99, 3, '.', '.')
                ]
            ]);
        }
    }
}
