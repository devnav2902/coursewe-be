<?php

namespace App\Http\Controllers;

use App\Models\CourseBill;
use App\Models\CourseCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    function purchase(Request $request)
    {
        return $request->all();
        $request->validate(['courses' => 'required|json']);

        $courses = json_decode($request->input('courses'));

        foreach ($courses as $course) {
            $purchase = $course->price->price;
            $price = $course->price->price;
            $code = '';

            if (!empty($course->coupon)) {
                $coupon = $course->coupon;
                $code = $coupon->code;
                $purchase = $coupon->discount_price;

                $query = CourseCoupon::where('code', $code)
                    ->where('course_id', $course->id);

                $cloneQuery = clone $query;
                $getCurrentlyEnrolled = $cloneQuery
                    ->first(['currently_enrolled']);

                $currently_enrolled = $getCurrentlyEnrolled->currently_enrolled + 1;

                $queryUpdate = clone $query;
                $queryUpdate->update(
                    ['currently_enrolled' => $currently_enrolled]
                );
            }


            CourseBill::create(
                [
                    'user_id' => Auth::user()->id,
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'thumbnail' => $course->thumbnail,
                    'purchase' => $purchase,
                    'price' => $price,
                    'promo_code' => $code
                ]
            );
        }

        return 'success';
    }
}
