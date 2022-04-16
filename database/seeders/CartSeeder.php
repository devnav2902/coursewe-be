<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $courses = DB::table('course')->take(5)->get(['author_id', 'id']);

        $users = DB::table('users')
            ->whereNotIn('id', collect($courses)->pluck('author_id')->toArray())
            ->take(5)
            ->get();

        foreach ($users as $value) {
            foreach ($courses as $course) {
                DB::table('cart')->insert(
                    [
                        'user_id' => $value->id,
                        'course_id' => $course->id,
                        'cart_type_id' => random_int(1, 3)
                    ],
                );
            }
        }
    }
}
