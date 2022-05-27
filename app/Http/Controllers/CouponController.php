<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartType;
use App\Models\Course;
use App\Models\CourseCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

        $dataCourseWithCoupon = CourseCoupon::whereIn('course_id', $courses)
            ->where('code', $coupon_code)
            ->where('status', 1)
            ->get();


        $cartType = CartType::firstWhere('type', 'cart');

        if (Auth::check()) {
            $cart = Cart::where('user_id', 2)
                ->without(['course', 'cartType'])
                ->where('cart_type_id', $cartType->id)
                ->get();
        } else {
            $cart = Cart::where('session_id', Session::get('anonymous_cart'))
                ->without(['course', 'cartType'])
                ->where('cart_type_id', $cartType->id)
                ->get();
        }

        // $base = [
        //     [
        //         ['course_id' => 2, 'user_id' => 3, 'cart_type_id' => 1, 'coupon_code' => 1],
        //         ['course_id' => 3, 'user_id' => 5, 'cart_type_id' => 1, 'coupon_code' => 1],
        //     ],
        //     ['course_id', 'cart_type_id', 'user_id'],
        //     ['coupon_code']
        // ];


        $courseKeys = $dataCourseWithCoupon->mapWithKeys(function ($item) {
            return [
                $item['course_id'] => [
                    'coupon_code' => $item['code'],
                ]
            ];
        });

        $newData = $cart->map(function ($item) use ($courseKeys) {
            if (isset($courseKeys[$item['course_id']])) {
                return array_merge($item->toArray(), $courseKeys[$item['course_id']]);
            }

            return $item;
        });

        // dataCartToUpdate(sample)
        // [
        //     ['course_id' => 2, 'session_id', 'user_id' => ..., 
        //      'cart_type_id' => 1, 'coupon_code' => ''],
        // ]
        $dataCartToUpdate = $newData->map(
            fn ($data) => empty($data['coupon_code'])
                ? null
                : collect($data)
                ->only('course_id', 'user_id', 'cart_type_id', 'coupon_code', 'session_id')
        )
            ->filter()
            ->values()
            ->toArray();

        $arrayCheckToUpdate = Auth::check() ? ['course_id', 'cart_type_id', 'user_id'] : ['course_id', 'cart_type_id', 'session_id'];

        Cart::upsert(
            $dataCartToUpdate,
            $arrayCheckToUpdate,
            ['coupon_code']
        );

        return $dataCartToUpdate;
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
