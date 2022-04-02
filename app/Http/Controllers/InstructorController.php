<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorController extends Controller
{
    public function profile($slug)
    {
        // $slug = $request->input('slug');
        $author = User
            // ::with('bio')
            ::where('slug', $slug)
            ->first(['id', 'avatar', 'fullname']);

        if (!$author) return abort(404);

        $course = Course::where('author_id', $author->id)
            ->where('isPublished', 1)
            ->withCount('course_bill')
            ->withCount('rating');

        $queryTotalStudents = clone $course;
        $totalStudents = $queryTotalStudents
            ->get(['id'])
            ->sum('course_bill_count');

        $queryTotalCourses = clone $course;
        $totalCourses = $queryTotalCourses->get(['id'])->count();

        $queryTotalReviews = clone $course;
        $totalReviews = $queryTotalReviews->whereHas('rating', function ($q) {
            $q->where('content', '<>', '');
        })
            ->get(['id'])
            ->sum('rating_count');

        $courses = $course->orderBy('created_at', 'desc')
            ->withAvg('rating', 'rating')
            ->paginate(
                6,
                ['title', 'id', 'author_id', 'created_at', 'price', 'slug']
            );

        return response()->json(compact(['author', 'courses', 'totalStudents', 'totalReviews', 'totalCourses']));
    }

    function getCourseById($id)
    {
        $course =  Course::where('author_id', Auth::user()->id)
            ->with(
                [
                    'section',
                    'lecture',
                    'course_outcome',
                    'course_requirements'
                ]
            )
            ->firstWhere('id', $id);

        return response(['course' => $course]);
    }
}
