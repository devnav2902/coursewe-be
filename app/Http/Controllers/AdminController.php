<?php

namespace App\Http\Controllers;

use App\Models\ReviewCourse;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    function reviewCourses()
    {
        $courses = ReviewCourse::with(['course'])->get();
        return response()->json(['courses' => $courses]);
    }
}
