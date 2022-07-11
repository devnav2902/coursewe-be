<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\QualityReviewTeam;
use App\Models\RatingQuality;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingQualityController extends Controller
{
    function get()
    {
        $data = RatingQuality::whereHas(
            'user',
            fn ($q) => $q->where('id', Auth::user()->id)
        )
            ->get();

        return response()->json(['ratingQuality' => '']);
    }

    function rate(Request $request)
    {
        $request->validate(['rating' => 'required|numeric|digits_between:1,10', 'course_id' => 'required']);

        $arrValues = $request->only(['rating', 'course_id']);
        RatingQuality::create(array_merge($arrValues, ['user_id' => Auth::user()->id]));

        return response('success');
    }

    function listCourses()
    {
        $categoryByUser  = QualityReviewTeam::where('user_id', Auth::user()->id)
            ->get()
            ->pluck('category_id');

        $data = Course::setEagerLoads([])
            ->with([
                'categories' => fn ($q) => $q->select('categories.category_id', 'title'),
                'rating_quality' => fn ($q) =>
                $q->select('user_id', 'id', 'rating', 'course_id')
                    ->with(
                        ['user' => fn ($q) => $q->select('fullname', 'avatar', 'gender', 'id')]
                    ),
            ])
            ->select('title', 'thumbnail', 'id', 'updated_at')
            ->withAvg('rating_quality', 'rating')
            ->whereHas(
                'categories',
                fn ($q) => $q->whereIn('categories.category_id', $categoryByUser)
            )
            ->paginate(9);

        $data->getCollection()->transform(function ($item) {
            $rated = $item->rating_quality->firstWhere('user_id', Auth::user()->id);
            $item->rated = $rated;

            return $item;
        });

        return response()->json(['listCourses' => $data]);
    }

    function statisticByCourse()
    {
    }
}
