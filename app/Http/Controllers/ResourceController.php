<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResourceController extends Controller
{
    function download(Request $request)
    {
        $lectureId = $request->route('lectureId');
        $courseId = $request->route('courseId');
        $resourceId = $request->route('resourceId');
        $userId = Auth::user()->id;

        try {
            $canDownload = Course::where('course.id', $courseId)
                ->setEagerLoads([])
                ->orWhere('author_id', $userId)
                ->orWhereHas('course_bill', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                })
                ->whereHas('lecture', function ($q) use ($lectureId, $resourceId) {
                    $q->where('lectures.id', $lectureId)
                        ->whereHas('resource', function ($q) use ($resourceId) {
                            $q->where('id', $resourceId);
                        });
                })
                ->first();

            if ($canDownload) {
                $resource = Resource::find($resourceId);

                return response()->download($resource->src, $resource->original_filename);
            }
            return response(['error' => 'Lỗi khi tải file'], 400);
        } catch (\Throwable $th) {
            return response(['error' => 'Lỗi khi tải file'], 400);
        }
    }

    function getByLectureId($lectureId)
    {
        $resources = Resource::where('lecture_id', $lectureId)->get();

        return response(['resources' => $resources]);
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
            $filesize = $helper->niceBytes($file->getSize());
            $filename = 'user' . Auth::user()->id . '_resource_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('resources', $filename);

            $resource = Resource::create([
                'lecture_id' => $lectureId,
                'src' => $path,
                'original_filename' => $original_filename,
                'filesize' => $filesize
            ]);

            return response(['success' => true, 'fileUploaded' => $resource]);
        } catch (\Throwable $th) {
            return response(['success' => false], 400);
        }
    }
    function delete(Request $request)
    {
        $lectureId = $request->route('lectureId');
        $courseId = $request->route('courseId');
        $resourceId = $request->route('resourceId');
        $userId = Auth::user()->id;

        try {
            $exist = Course::where('course.id', $courseId)
                ->where('author_id', $userId)
                ->setEagerLoads([])
                ->whereHas('lecture', function ($q) use ($lectureId, $resourceId) {
                    $q->where('lectures.id', $lectureId)
                        ->whereHas('resource', function ($q) use ($resourceId) {
                            $q->where('id', $resourceId);
                        });
                })
                ->first();

            if ($exist) {
                Resource::destroy($resourceId);
                return response(['success' => true]);
            }

            return response(['success' => false, 'message' => 'resource not exist']);
        } catch (\Throwable $th) {
            return response(['success' => false], 400);
        }
    }
}
