<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    public function myLearning()
    {
        $courses = Course::withOnly(
            [
                'lecture' => function ($q) {
                    $q->select('lectures.id');
                },
                'lecture.progress' => function ($q) {
                    $q
                        ->select('lecture_id', 'progress')
                        ->where('progress', 1);
                },
                'author',
                'rating' => function ($q) {
                    $q->where('user_id', Auth::user()->id);
                }
            ]
        )
            ->whereHas(
                'course_bill',
                function ($q) {
                    $q
                        ->orderBy('created_at', 'desc')
                        ->where('user_id', Auth::user()->id);
                }
            )
            ->paginate(5, ['id', 'title', 'slug', 'author_id', 'thumbnail']);

        $courses->transform(function ($course) {
            $totalLecture = $course->lecture->count();

            $helper = new HelperController;
            $count = $helper->countProgress($course->lecture);

            $course->count_progress = number_format($count / $totalLecture, 2);
            return $course;
        });

        return response()->json(['courses' => $courses]);
    }

    function learning($url)
    {
        $course = Course::with(
            [
                'lecture' => function ($q) {
                    $q->select('lectures.title', 'lectures.id', 'src', 'original_filename', 'lectures.order');
                },
                'lecture.progress' => function ($q) {
                    $q->select('lecture_id', 'progress');
                },
                'section.countProgress'
            ]
        )
            ->without('category', 'course_bill')
            ->where('slug', $url)
            ->get();

        if (count($course)) $course = $course[0];

        $author = $course->author;

        $data_progress = $course->lecture
            ->map(function ($lecture) {
                return collect($lecture->progress)->all();
            })
            ->filter()
            ->values(); // reset key

        return response()->json(compact(
            ['course', 'data_progress', 'author']
        ));
    }
}
