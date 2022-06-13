<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lecture;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LectureController extends Controller
{
    function getByLectureId($lectureId)
    {
        $lecture = Lecture::find($lectureId);

        return response(['lecture' => $lecture]);
    }

    function createLecture(Request $request)
    {
        $request->validate([
            'courseId' => 'required|alpha_num',
            'sectionId' => 'required',
            'title' => 'required|string'
        ]);

        $sectionId = $request->input('sectionId');
        $courseId = $request->input('courseId');
        $title = $request->input('title');

        $existSection = Course::where([
            ['id', $courseId],
            ['author_id', Auth::user()->id]
        ])
            ->select('id', 'author_id')
            ->whereHas('section', function ($q) use ($sectionId) {
                $q->where('id', $sectionId);
            })
            ->first();

        if ($existSection) {
            $maxOrder = Lecture::whereHas('section', fn ($q) => $q->where('id', $sectionId))->max('order') ?? 0;
            $result = Lecture::create(['title' => $title, 'section_id' => $sectionId, 'order' => $maxOrder + 1]);

            return response(['success' => true, 'lecture' => $result]);
        }

        return response(['message' => 'Không thể lưu nội dung này!'], 400);
    }

    function updateTitle(Request $request)
    {
        $request->validate([
            'courseId' => 'required|alpha_num',
            'lectureId' => 'required',
            'title' => 'required|string'
        ]);

        $lectureId = $request->input('lectureId');
        $courseId = $request->input('courseId');
        $title = $request->input('title');

        $existLecture = Course::where([
            ['id', $courseId],
            ['author_id', Auth::user()->id]
        ])
            ->select('id', 'author_id')
            ->whereHas('lecture', function ($q) use ($lectureId) {
                $q->where('lectures.id', $lectureId);
            })
            ->first();

        if ($existLecture) {
            Lecture::where('id', $lectureId)->update(['title' => $title]);

            return response(['success' => true]);
        }

        return response(['message' => 'Không tồn tại bài giảng này!'], 400);
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
            $path = $file->storeAs('lessons', $filename);

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
                return response(['success' => true, 'deleted' => $lectureId]);
            }

            return response(['success' => false, 'message' => 'lecture not exist']);
        } catch (\Throwable $th) {
            return response(['success' => false], 400);
        }
    }

    function reorder(Request $request, $sectionId, $courseId)
    {
        $request->validate([
            'data' => 'required|array',
            'data.*.id' => 'required',
            'data.*.order' => 'required|numeric',
        ]);

        $lectureReordered = $request->input('data');

        $countLectures = Lecture::whereHas(
            'section.course',
            fn ($q) => $q->setEagerLoads([])
                ->select('id', 'author_id')
                ->where([['id', $courseId], ['author_id', Auth::user()->id]])
        )
            ->whereHas('section', function ($q) use ($sectionId) {
                $q->setEagerLoads([])->select('sections.id')->where('id', $sectionId);
            })
            ->select('order', 'id', 'section_id')
            ->whereIn('lectures.id', collect($lectureReordered)->pluck('id')->toArray())
            ->count();

        if (count($lectureReordered) === $countLectures) {
            $data = collect($lectureReordered)
                ->map(function ($lecture) use ($sectionId) {
                    $lecture['section_id'] = $sectionId;

                    return $lecture;
                })
                ->toArray();

            Lecture::upsert($data, ['section_id', 'id'], ['order']);

            return response('success');
        }

        return response('error', 400);
    }
}
