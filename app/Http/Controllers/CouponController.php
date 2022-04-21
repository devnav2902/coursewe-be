<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartType;
use App\Models\Course;
use App\Models\CourseCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CouponController extends Controller
{
    function checkCouponWithCourses(Request $request)
    {
        $request->validate([
            'courses' => 'array|required',
            'coupon_code' => 'required'
        ]);

        $courses = $request->input('courses');
        $coupon_code = $request->input('coupon_code');

        // chưa kiểm tra mã hết hạn
        $dataCourseWithCoupon = CourseCoupon::whereIn('course_id', $courses)
            ->where('code', $coupon_code)
            ->where('status', 1)
            ->get();

        if (Auth::check()) {
            $cartType = CartType::firstWhere('type', 'cart');
            $cart = Cart::where('user_id', Auth::user()->id)
                ->where('cart_type_id', $cartType->id)
                ->get();
        }

        return $dataCourseWithCoupon->map(function ($item) {
            return [
                'coupon_code' => $item->code,
                'course_id' => $item->course_id,
                'discount_price' => $item->discount_price
            ];
        });
    }

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
