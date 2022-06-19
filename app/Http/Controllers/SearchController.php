<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Course;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('inputSearch');
        $courses = Course::orderBy('title', 'asc')
            ->withAvg('rating', 'rating')
            ->with(['author:role_id,fullname,slug,email,avatar,id'])
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->where('isPublished', 1)
            ->where('title', 'like', '%' . $keyword . '%')
            ->paginate(5, ['title', 'id', 'slug', 'thumbnail']);

        // $categories = Categories::with(
        //     [
        //         'course' => function ($q) {
        //             $q->where('isPublished', 1);
        //         }
        //     ]
        // )
        //     ->get(['title', 'id', 'slug']);
        return response()->json(['courses' => $courses, 'keyword' => $keyword]);
    }
    public function search(Request $request)
    {
        $request->validate([
            'inputSearch' => 'min:2'
        ]);
        $value = $request->input('inputSearch');

        $data = Course::setEagerLoads([])
            ->orderBy('created_at', 'desc')
            ->where('isPublished', 1)
            ->where('title', 'like', '%' . $value . '%')
            ->paginate(5, ['title', 'id', 'slug', 'thumbnail']);

        return $data;
    }
}
