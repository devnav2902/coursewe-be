<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatingQualitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $courses = DB::table('course')->where('submit_for_review', 1)->get(['id']);

        foreach ($courses as $course) {
            $categories = DB::table('categories_course')->where('course_id', $course->id)->get(['category_id']);

            $users = DB::table('quality_review_team')
                ->whereIn('category_id', collect($categories)->pluck('category_id'))
                ->get(['user_id']);

            $arrUserId = $users->pluck('user_id');

            $dataToCreate = $arrUserId
                ->map(function ($item) use ($course) {
                    return ['user_id' => $item, 'rating' => random_int(7, 10), 'course_id' => $course->id];
                })
                ->toArray();

            DB::table('rating_quality')->insert($dataToCreate);
        }
    }
}
