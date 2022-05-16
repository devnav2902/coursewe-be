<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ProgressLogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressLogsController extends Controller
{
    function lastWatchedByCourseId($course_id)
    {
        $dataLastWatched = ProgressLogs::with(['course' => function ($query) {
            $query->setEagerLoads([])->select('id', 'slug');
        }])
            ->orderBy('updated_at', 'desc')
            ->where('user_id', Auth::user()->id)
            ->firstWhere('course_id', $course_id);

        if (!$dataLastWatched) {
            $course = Course::setEagerLoads([])->with(['lecture' => function ($query) {
                $query->select('lectures.id');
            }])->firstWhere('id', $course_id);

            $lecture_id = $course->lecture->max('id');

            return response(['dataLastWatched' => [
                'lecture_id' => $lecture_id,
                'course_id' => $course_id,
                'last_watched_second' => 0,
            ]]);
        };

        return response(['dataLastWatched' => $dataLastWatched]);
    }
    function lastWatchedByLectureId($course_id, $lecture_id)
    {
        $dataLastWatched = ProgressLogs::where('course_id', $course_id)
            ->where('user_id', Auth::user()->id)
            ->firstWhere('lecture_id', $lecture_id);
        return response(['dataLastWatched' => $dataLastWatched]);
    }
    function saveLastWatched($course_id, $lecture_id, $second)
    {
        try {
            $exist = ProgressLogs::firstWhere([
                'course_id' => $course_id,
                'lecture_id' => $lecture_id,
                'user_id' => Auth::user()->id,
            ]);
            if ($exist) {
                ProgressLogs::where([
                    'lecture_id' => $lecture_id,
                    'user_id' => Auth::user()->id,
                ])->update([
                    'last_watched_second' => round($second, 0)
                ]);
            } else {
                ProgressLogs::create(
                    [
                        'course_id' => $course_id,
                        'user_id' => Auth::user()->id,
                        'lecture_id' => $lecture_id,
                        'last_watched_second' => round($second, 0)
                    ],
                );
            }

            return response('success');
        } catch (\Throwable $th) {
            return response($th, 400);
        }
    }
}
