<?php

namespace App\Http\Controllers;

use App\Models\CourseBill;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

  function rate(Request $request)
  {
    $request->validate(
      [
        'course_id' => 'required|numeric',
        'rating' => 'required|min:1|max:5',
        'content' => 'string|nullable'
      ]
    );

    $data = $request->only(['course_id', 'content', 'rating']);

    $registered = CourseBill::where('user_id', Auth::user()->id)
      ->where('course_id', $data['course_id'])
      ->first(['course_id']);

    if (!$registered)
      return response(['message' => 'Bạn chưa đăng ký khóa học này!'], 400);

    Rating::updateOrCreate(
      ['course_id' => $data['course_id'], 'user_id' => Auth::user()->id],
      ['rating' => $data['rating'], 'content' => $data['content']]
    );

    return response(['message' => 'Đánh giá khóa học thành công!']);
  }
}
