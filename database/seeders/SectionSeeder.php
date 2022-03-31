<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 1; $i <= 20; $i++) {
            DB::table('sections')->insert(
                [
                    'title' => 'Chương ' . $i,
                    'course_id' => $i,
                    'order' => $i
                ]
            );
        }
    }
}
