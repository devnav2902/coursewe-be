<?php

namespace App\Http\Controllers;

use App\Models\Rating;

class RatingController extends Controller
{
  function getRatingByCourseId($courseId)
  {
    $rating = Rating::where('course_id', $courseId)->paginate(10);

    if ($rating->isEmpty()) {
      return response(['message' => 'Khóa học này hiện chưa có đánh giá!']);
    }

    return response(['rating' => $rating]);
  }
}
