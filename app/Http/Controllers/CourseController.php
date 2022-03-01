<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    function getCourse(Request $req)
    {
        $query =  Course::where('isPublished', 1)
            ->orderBy('created_at', 'desc');

        if ($req->has('limit')) {
            $course = $query->paginate($req->input('limit', 10));
        } else {
            $course = $query->get();
        }

        return $course;
    }
}
