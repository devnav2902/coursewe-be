<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\ReviewCourse;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    function reviewCourses($limit = 5)
    {
        $courses = ReviewCourse::with(['course' => function ($q) {
            $q->setEagerLoads([])->with(['author', 'price']);
        }])->paginate(10);

        return response(['courses' => $courses]);
    }
    public function getCourseOfAuthorAndAdminById($id)
    {
        $isAdmin = Auth::user()->role->name === "admin";
        if ($isAdmin) {
            $course = Course::with(['lecture', 'section'])
                ->firstWhere('id', $id);
        } else {
            $course = Course::where('id', $id)
                ->with([
                    'lecture',
                    'section',
                ])
                ->firstWhere('author_id', Auth::user()->id);
        }
        if (!$course) abort(404);



        return response()->json(['course' => $course]);
    }
}
