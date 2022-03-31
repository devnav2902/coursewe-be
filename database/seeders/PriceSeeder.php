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
        $delta = 5;
        $default = 12.99;

        db::table('price')->insert([
            'price' => 0
        ]);

        for ($i = 1; $i <= 20; $i++) {
            $default += $delta;

            db::table('price')->insert([
                'price' => $default
            ]);
        }
    }
}
