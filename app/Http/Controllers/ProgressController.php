<?php

namespace App\Http\Controllers;

use App\Models\Progress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProgressController extends Controller
{
    function updateProgress(Request $request)
    {
        $request->validate([
            'lectureId' => 'required',
            'progress' => 'required'
        ]);

        $lectureId = $request->input('lectureId');
        $user_id = Auth::user()->id;
        $progress = $request->input('progress');

        $existed = Progress::where('user_id', $user_id)->firstWhere('lecture_id', $lectureId);
        if ($existed) {
            Progress::where('user_id', $user_id)
                ->where('lecture_id', $lectureId)
                ->update(['progress' => $progress]);
        } else {
            Progress::create([
                'lecture_id' => $lectureId,
                'progress' => $progress,
                'user_id' => $user_id
            ]);
        }

        return response(['success' => true]);
    }
}
