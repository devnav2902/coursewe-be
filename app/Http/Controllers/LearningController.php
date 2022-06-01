<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture;
use App\Models\ProgressLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LearningController extends Controller
{
    public function myLearning()
    {
        $courses = Course::setEagerLoads([])
            ->with(
                [
                    'author',
                    'rating' => function ($q) {
                        $q->where('user_id', Auth::user()->id);
                    },
                ]
            )
            ->whereHas(
                'course_bill',
                function ($q) {
                    $q->where('user_id', Auth::user()->id);
                }
            )
            ->paginate(12, ['id', 'title', 'slug', 'author_id', 'thumbnail']);

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

        if (empty($course)) return response(['Khóa học không tồn tại!'], 404);

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
        $course = Course::setEagerLoads([])
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

        if (!$course || empty($course->lecture[0])) return response(['message' => 'Không có bài giảng này!'], 404);
        $lecture = $course->lecture[0];

        $dataLastWatched = ProgressLogs::where('course_id', $course->id)
            ->where('user_id', Auth::user()->id)
            ->firstWhere('lecture_id', $lecture->id);

        return response(['lecture' => $lecture, 'dataLastWatched' => $dataLastWatched]);
    }
}
