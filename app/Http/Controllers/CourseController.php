<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    function getCourse(Request $req)
    {
        $query =  Course::where('isPublished', 1)
            ->orderBy('created_at', 'desc');

        if ($req->has('limit')) {
            $course = $query->paginate($req->input('limit', 10));
        } else {
            $course = $query->get();
        }

        return $course;
    }
    function getCourseByCurrentUser()
    {
        $courses = Course::where('author_id', Auth::user()->id)
            ->where('isPublished', 1)
            ->get();

        return response(['courses' => $courses]);
    }
    function getCourseOfAuthorById($id)
    {

        $course = Course::where('id', $id)
            ->with([
                'lecture',
                'section'
            ])
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->firstWhere('author_id', Auth::user()->id);


        if (!$course) abort(404);

        return response()->json(['course' => $course]);
    }
    function getCourseBySlug(Request $req)
    {
        $req->validate([
            'slug' => 'required'
        ]);

        $course = Course::where('slug', $req->slug)
            ->where('isPublished', 1)
            ->with([
                'lecture',
                'section'
                // 'lecture.progress' => function ($q) {
                //     $q->where('progress', 1);
                // }
            ])
            ->withAvg('rating', 'rating')
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->get();

        if (!count($course)) abort(404);

        return $course[0];

        // $course->transform(function ($course) {
        //     $course->setRelation('rating', $course->rating()->paginate(10));

        //     $helper = new HelperController;
        //     $count = $helper->countProgress($course->lecture);
        //     $course->count_progress = $count;
        //     return $course;
        // });

        // $course = $course[0];

        // $isPurchased = false;
        // $isFree = $course->price->price === 0.0 ? true : false;

        // if (Auth::check()) {
        //     $result = Auth::user()
        //         ->enrollment
        //         ->firstWhere('course_id', $course->id);

        //     $isPurchased = $result ? true : false;

        //     $course->hasCommented =
        //         Rating::where('user_id', Auth::user()->id)
        //         ->select('course_id')
        //         ->firstWhere('course_id', $course->id);
        // }

        // // RATING
        // $graph = $this->ratingGraph($course);

        // if ($request->isMethod('GET'))
        //     return view('pages.course-lesson', compact(['graph', 'course', 'isPurchased', 'isFree']));

        // $code = $request->input('coupon-input');

        // $helper = new HelperController;

        // $coupon = $helper->getCoupon($code, $course->id);

        // $couponJSON = collect($coupon)
        //     ->only(['course_id', 'coupon_id', 'code', 'discount_price'])->toJson();

        // $saleOff = 100;
        // $isFreeCoupon = false;

        // if ($coupon) {
        //     $original_price = $course->price->price;
        //     $discount_price = $coupon->discount_price;

        //     $total = $original_price - $discount_price;
        //     if ($total == $original_price) $isFreeCoupon = true;
        //     else $saleOff = round(($total / $original_price) * 100);
        // }

        // return view(
        //     'pages.course-lesson',
        //     compact(
        //         ['isPurchased', 'course', 'graph', 'coupon', 'couponJSON', 'saleOff', 'isFreeCoupon', 'isFree']
        //     )
        // );
    }
}
