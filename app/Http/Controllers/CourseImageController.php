<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseImageController extends Controller
{
    function updateCourseImage(Request $req)
    {
        $req->validate([
            'course_id' => 'required',
            'thumbnail' => 'required|file'
        ]);

        if ($req->hasFile('thumbnail')) {
            $image = $req->file('thumbnail');
            $name = $image->getClientOriginalName();
            $path =  $image->storeAs('thumbnail', time() . $name);

            $course_id = $req->input('course_id');
            Course::where('id', $course_id)
                ->update(['thumbnail' => $path]);

            return response(['success' => true, 'path' => $path]);
        }
    }
}
