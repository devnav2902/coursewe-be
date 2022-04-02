<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InstructionallevelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('instructional_level')->insert(
            [
                ['level' => 'Beginner Level', 'id' => 1],
                ['level' => 'Intermediate Level', 'id' => 2],
                ['level' => 'Expert Level', 'id' => 3],
                ['level' => 'All Levels', 'id' => 0],
            ]
        );
    }
}
