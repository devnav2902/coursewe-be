<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    function checkCoupon(Request $request)
    {
        $code = $request->input('couponInput');
        $course_id = $request->input('courseId');

        $course = Course::select('price_id', 'id')->firstWhere('id', $course_id);

        if (!$course) abort(422, 'Không tồn tại khóa học này!');

        $helper = new HelperController;

        $coupon = $helper->getCoupon($code, $course->id);

        if (empty($coupon)) {
            return abort(420, 'Mã vừa nhập không chính xác!');
        }

        $coupon = collect($coupon)->only(['course_id', 'coupon_id', 'code', 'discount_price']);

        $saleOff = 100;
        $isFreeCoupon = false;

        if ($coupon) {
            $original_price = $course->price->format_price; // 999.000
            $discount_price = $coupon['discount_price']; // 999.000

            $total = $original_price - $discount_price;
            if ($total == 0) $isFreeCoupon = true;
            else $saleOff = round(($total / $original_price) * 100);
        }

        return response(['saleOff' => $saleOff, 'coupon' => $coupon, 'isFreeCoupon' => $isFreeCoupon]);
    }
}
