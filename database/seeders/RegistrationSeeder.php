<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $courses = DB::table('course')
            // ->take(40)
            ->get(['id', 'author_id', 'title', 'thumbnail', 'price_id']);

        foreach ($courses as $course) {
            for ($i = 0; $i < 8; $i++) {
                $user = DB::table('users')
                    ->where('id', '<>', $course->author_id)
                    ->get()
                    ->random();

                $price = DB::table('price')
                    ->where('id', $course->price_id)
                    ->first()->original_price;

                $existed = DB::table('course_bill')
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->first(['user_id', 'course_id']);

                if (!$existed) {
                    DB::table('course_bill')->insert(
                        [
                            'user_id' => $user->id,
                            'course_id' => $course->id,
                            'user_id' => $user->id,
                            'title' => $course->title,
                            'thumbnail' => $course->thumbnail,
                            'price' => $price,
                            'purchase' => $price,
                        ]
                    );
                }
            }
        }
    }
}
