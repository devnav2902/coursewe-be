<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Course;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request) // page
    {
        $keyword = $request->input('inputSearch');
        $courses = Course::with('categories')
            ->orderBy('title', 'asc')
            ->withAvg('rating', 'rating')
            ->withCount('rating', 'lecture')
            ->where('isPublished', 1)
            ->where('title', 'like', '%' . $keyword . '%')
            ->paginate(5, ['title', 'id', 'slug', 'thumbnail']);

        return response()->json(['courses' => $courses, 'keyword' => $keyword]);
    }
    public function search(Request $request)
    {
        $request->validate([
            'inputSearch' => 'min:2'
        ]);
        $value = $request->input('inputSearch');

        $data = Course::setEagerLoads([])
            ->with(['author:fullname,id,slug'])
            ->orderBy('created_at', 'desc')
            ->where('isPublished', 1)
            ->where('title', 'like', '%' . $value . '%')
            ->paginate(5, ['title', 'id', 'slug', 'thumbnail', 'author_id']);

        return $data;
    }
}
