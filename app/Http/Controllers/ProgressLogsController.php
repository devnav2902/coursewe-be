<?php

namespace App\Http\Controllers;

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
        return response(['dataLastWatched' => $dataLastWatched]);
    }
    function lastWatchedByLectureId($course_id, $lecture_id)
    {
        $dataLastWatched = ProgressLogs::where('course_id', $course_id)
            ->where('user_id', Auth::user()->id)
            ->firstWhere('lecture_id', $lecture_id);
        return response(['dataLastWatched' => $dataLastWatched]);
    }
}
