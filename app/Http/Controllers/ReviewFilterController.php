<?php

namespace App\Http\Controllers;

use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewFilterController extends Controller
{
    function get(Request $request)
    {
        $request->validate([
            'rating' => 'digits_between:1,5',
            'has_a_comment' => 'boolean',
            'sort_by' => 'regex:/^[new,old]*$/'
        ]);

        $data = Rating::with([
            'course' => fn ($q) => $q->setEagerLoads([])->select('id', 'title', 'thumbnail'),
            'user:id,fullname,avatar'
        ])
            ->whereHas('course', fn ($q) => $q->where('author_id', Auth::user()->id));

        if ($request->rating) $data->where('rating', $request->rating);
        if ($request->has_a_comment === '1') $data->whereNotNull('content');
        if ($request->sort_by)
            $request->sort_by === 'new'
                ? $data->orderBy('created_at', 'desc')
                : $data->orderBy('created_at', 'asc');

        return response()->json(['ratingData' => $data->paginate(10)]);
    }
}
