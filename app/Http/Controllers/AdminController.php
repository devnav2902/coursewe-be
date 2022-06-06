<?php

namespace App\Http\Controllers;

use App\Models\ReviewCourse;

class AdminController extends Controller
{
    function reviewCourses()
    {
        $courses = ReviewCourse::with(['course' => function ($q) {
            $q->setEagerLoads([])->with(['author', 'price']);
        }])->paginate(10);

        return response(['courses' => $courses]);
    }
}
