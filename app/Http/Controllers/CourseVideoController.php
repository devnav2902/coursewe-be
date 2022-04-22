<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseVideoController extends Controller
{
    function updateCourseVideo(Request $req)
    {
        $req->validate([
            'course_id' => 'required',
            'video_demo' => 'required|file'
        ]);

        if ($req->hasFile('video_demo')) {
            $image = $req->file('video_demo');
            $name = $image->getClientOriginalName();
            $path =  $image->storeAs('video_demo', time() . $name);

            $course_id = $req->input('course_id');
            Course::where('id', $course_id)
                ->update(['video_demo' => $path]);

            return response(['success' => true, 'path' => $path]);
        }
    }
}
