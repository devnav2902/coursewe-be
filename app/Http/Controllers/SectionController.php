<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SectionController extends Controller
{
    function reorder(Request $request, $courseId)
    {
        $request->validate([
            'data' => 'required|array',
        ]);

        $sectionReordered = $request->input('data');

        $countSection = Section::whereHas(
            'course',
            fn ($q) => $q->setEagerLoads([])
                ->select('id', 'author_id')
                ->where([['id', $courseId], ['author_id', Auth::user()->id]])
        )
            ->select('order', 'id')
            ->whereIn('sections.id', collect($sectionReordered)->pluck('id')->toArray())
            ->count();

        if (count($sectionReordered) === $countSection) {
            $data = collect($sectionReordered)
                ->map(function ($section) use ($courseId) {
                    $section['course_id'] = $courseId;

                    return $section;
                })
                ->toArray();

            Section::upsert($data, ['course_id', 'id'], ['order']);

            return response('success');
        }

        return response('error', 400);
    }

    function getSectionById($sectionId)
    {
        $result = Section::without('resource')->find($sectionId);

        return response(['section' => $result]);
    }

    function getSectionsByCourseId($courseId)
    {
        $result = Section::where('course_id', $courseId)->orderBy('order', 'asc')->get();

        return response(['sections' => $result]);
    }

    function createSection(Request $request)
    {
        $request->validate([
            'courseId' => 'required|alpha_num',
            'title' => 'required|string'
        ]);

        $courseId = $request->input('courseId');
        $title = $request->input('title');

        $existSection = Course::where([
            ['id', $courseId],
            ['author_id', Auth::user()->id]
        ])
            ->select('id', 'author_id')
            ->first();

        if ($existSection) {
            $maxOrder = Section::whereHas('course', fn ($q) => $q->where('id', $courseId))->max('order') ?? 0;
            $result = Section::create(['title' => $title, 'course_id' => $courseId, 'order' => $maxOrder + 1]);

            return response(['success' => true, 'section' => $result]);
        }

        return response(['message' => 'Không thể lưu nội dung này!'], 400);
    }

    function updateTitle(Request $request)
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
                $q->where('sections.id', $sectionId);
            })
            ->first();

        if ($existSection) {
            Section::where('id', $sectionId)->update(['title' => $title]);

            return response(['success' => true]);
        }

        return response(['message' => 'Không tồn tại chương học này!'], 400);
    }

    function delete(Request $request)
    {
        $sectionId = $request->route('sectionId');
        $courseId = $request->route('courseId');
        $userId = Auth::user()->id;

        try {
            $exist = Course::where('course.id', $courseId)
                ->where('author_id', $userId)
                ->setEagerLoads([])
                ->whereHas('section', function ($q) use ($sectionId) {
                    $q->where('sections.id', $sectionId);
                })
                ->first();

            if ($exist) {
                Section::destroy($sectionId);
                return response(['success' => true]);
            }

            return response(['success' => false, 'message' => 'Không thể xóa chương học này']);
        } catch (\Throwable $th) {
            return response(['success' => false], 400);
        }
    }
}
