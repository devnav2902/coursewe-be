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
            ->get(['id', 'author_id', 'title', 'thumbnail', 'price_id']);

        foreach ($courses as $course) {
            $user = DB::table('users')
                ->where('id', '<>', $course->author_id)
                ->get()
                ->random();

            // DB::table('registration')
            //     ->insertGetId([
            //         'course_id' => $course->id,
            //         'user_id' => $user->id,
            //     ]);

            $price = DB::table('price')
                ->where('id', $course->price_id)
                ->first()->original_price;

            DB::table('course_bill')
                ->insert([
                    'course_id' => $course->id,
                    'user_id' => $user->id,
                    'title' => $course->title,
                    'thumbnail' => $course->thumbnail,
                    'price' => $price,
                    'purchase' => $price,
                ]);
        }
    }
}
