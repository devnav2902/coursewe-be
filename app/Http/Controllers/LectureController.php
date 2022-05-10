<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureController extends Controller
{
    function getByLectureId($lectureId)
    {
        $lecture = Lecture::find($lectureId);

        return response(['lecture' => $lecture]);
    }

    function upload(Request $request)
    {
        // Chưa validate user(là user tạo bài giảng hay không)
        $request->validate(
            [
                'lectureId' => 'required',
                'file' => 'required|file'
            ]
        );

        $lectureId = $request->input('lectureId');
        $file = $request->file('file');

        try {
            $original_filename = $file->getClientOriginalName();

            $helper = new HelperController();
            // $filesize = $helper->niceBytes($file->getSize());
            $filename = 'user' . Auth::user()->id . '_lectures_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('lectures', $filename);

            $dataDuration = $helper->getDuration($path);

            Lecture::where('id', $lectureId)->update([
                'src' => $path,
                'original_filename' => $original_filename,
                'playtime_string' => $dataDuration['playtime_string'],
                'playtime_seconds' => $dataDuration['playtime_seconds']
            ]);

            $lecture = Lecture::find($lectureId);

            return response(['success' => true, 'fileUploaded' => $lecture]);
        } catch (\Throwable $th) {
            return response(['success' => false, 'error' => $th], 400);
        }
    }

    function delete(Request $request)
    {
        $lectureId = $request->route('lectureId');
        $courseId = $request->route('courseId');
        $userId = Auth::user()->id;

        try {
            $exist = Course::where('course.id', $courseId)
                ->where('author_id', $userId)
                ->setEagerLoads([])
                ->whereHas('lecture', function ($q) use ($lectureId) {
                    $q->where('lectures.id', $lectureId);
                })
                ->first();

            if ($exist) {
                Lecture::destroy($lectureId);
                return response(['success' => true]);
            }

            return response(['success' => false, 'message' => 'lecture not exist']);
        } catch (\Throwable $th) {
            return response(['success' => false], 400);
        }
    }
}
