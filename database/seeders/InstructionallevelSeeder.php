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
                ['level' => 'Cơ bản', 'id' => 1],
                ['level' => 'Trung cấp', 'id' => 2],
                ['level' => 'Nâng cao', 'id' => 3],
                ['level' => 'Tất cả trình độ', 'id' => 0],
            ]
        );
    }
}
