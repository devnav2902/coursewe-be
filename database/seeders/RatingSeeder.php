<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

include_once __DIR__ . '/RandomDataSeeder/comment.php';
class RatingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $comment = comment();

        $courses = DB::table('course_bill')
            ->get(['course_id', 'user_id']);


        foreach ($courses as $course) {
            $commentRandom =
                $comment[random_int(0, count($comment) - 1)];

            DB::table('rating')->insert([
                'course_id' => $course->course_id,
                'user_id' => $course->user_id,
                'content' => $commentRandom,
                'rating' => random_int(3, 5),
            ]);
        }
    }
}
