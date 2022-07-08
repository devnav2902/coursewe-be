<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('role')->insert([
            'name' => 'admin'
        ]);
        DB::table('role')->insert([
            'name' => 'user'
        ]);
        DB::table('role')->insert([
            'name' => 'quality_review'
        ]);
    }
}
