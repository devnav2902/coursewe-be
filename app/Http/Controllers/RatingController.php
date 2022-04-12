<?php

namespace App\Http\Controllers;

class RatingController extends Controller
{
    function filterRatingByCategorySlug($slug)
    {
        $helperController = new HelperController();
        $coursesBySlug = $helperController->getCoursesByCategorySlug($slug, false);

        $ratingArr = $coursesBySlug->pluck('rating_avg_rating');

        $data = [
            '4.5' => ['amount' => 0],
            '4.0' => ['amount' => 0],
            '3.5' => ['amount' => 0],
            '3.0' => ['amount' => 0],
        ];

        foreach ($ratingArr as $valueRating) {
            $value = floatval($valueRating);

            if ($value >= 4.5) $data['4.5']['amount'] += 1;
            if ($value >= 4) $data['4.0']['amount'] += 1;
            if ($value >= 3.5) $data['3.5']['amount'] += 1;
            if ($value >= 3.0) $data['3.0']['amount'] += 1;
        }

        return response()->json(['filterRating' => $data]);
    }
}
