<?php

namespace Database\Seeders;

use Carbon\Carbon;
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
            ->where('isPublished', 1)
            ->get(['id', 'author_id', 'title', 'thumbnail', 'price_id']);

        foreach ($courses as $course) {
            for ($i = 0; $i < 100; $i++) {
                $user = DB::table('users')
                    ->where('id', '<>', $course->author_id)
                    ->where('id', '<>', 1)
                    ->where('id', '<>', 2)
                    ->get()
                    ->random();

                $price = DB::table('price')
                    ->where('id', $course->price_id)
                    ->first()->original_price;

                $existed = DB::table('course_bill')
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->first(['user_id', 'course_id']);

                $created_at = Carbon::now()->subDays(rand(1, 365));

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
                            'created_at' => $created_at
                        ]
                    );
                }
            }
        }
    }
}
