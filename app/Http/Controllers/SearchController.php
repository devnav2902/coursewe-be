<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Course;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->input('input-search');
        $courses = Course::with('category')
            ->orderBy('title', 'asc')
            ->withAvg('rating', 'rating')
            ->withCount('rating')

            ->where('isPublished', 1)
            ->where('title', 'like', '%' . $request->input('input-search') . '%')
            ->paginate(5, ['title', 'id', 'slug', 'thumbnail']);

        $categories = Categories::with(
            [
                'course' => function ($q) {
                    $q->where('isPublished', 1);
                }
            ]
        )
            ->get(['title', 'id', 'slug']);
        return response()->json(['courses' => $courses, 'keyword' => $keyword, 'categories' => $categories]);
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
