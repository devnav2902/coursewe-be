<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture;
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

    function getSections($course_id)
    {
        $course = Course::with(
            [
                'lecture.progress' => function ($q) {
                    $q->select('lecture_id', 'progress', 'user_id');
                },
                'section' => function ($q) {
                    $q->withCount('progressInLectures');
                },
                'section.lecture' => function ($q) {
                    $q->with('progress');
                }
            ]
        )
            ->without('category', 'course_bill')
            ->firstWhere('id', $course_id);

        $sections = $course->section;

        return response()->json(compact(
            ['sections']
        ));
    }

    function learning($url)
    {
        $course = Course::without('category', 'course_bill')
            ->firstWhere('slug', $url);

        return response()->json(compact(
            ['course']
        ));
    }

    function getProgress($course_id)
    {
        $course = Course::setEagerLoads([])
            ->with(
                [
                    'lecture.progress' => function ($q) {
                        $q->select('lecture_id', 'progress');
                    },
                ]
            )
            ->select('id')
            ->withCount('lecture')
            ->firstWhere('id', $course_id);

        $data_progress = $course->lecture
            ->map(function ($lecture) {
                return $lecture->progress;
            })
            ->filter()
            ->values(); // reset key

        $total = $course->lecture_count;
        $complete = count($data_progress);

        return response()->json(compact(
            ['total', 'data_progress', 'complete']
        ));
    }
    function getVideo($course_slug, $lectureId)
    {
        $video = Course::setEagerLoads([])
            ->with(
                [
                    'lecture' => function ($query) use ($lectureId) {
                        $query->without('resource')
                            ->where('lectures.id', $lectureId)
                            ->select('lectures.id', 'src', 'lectures.title');
                    }

                ]
            )
            ->select(['id'])
            ->firstWhere('slug', $course_slug);

        $lecture = $video->lecture;
        if (!count($lecture)) return response(['message' => 'Không có bài giảng này!'], 404);

        return response(['lecture' => $lecture[0]]);
    }
}
