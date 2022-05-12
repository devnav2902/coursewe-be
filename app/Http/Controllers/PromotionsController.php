<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseCoupon;
use App\Models\Price;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PromotionsController extends Controller
{
    private $helper;
    private $maxCouponInAMonth = 3;
    private $discountConst = 50000; // giá trị sử dụng để tính giảm giá. vd khoảng giảm giá là [349.000, 549.000]=> khuyến mãi trong khoảng [299.000, 499.000]

    function __construct()
    {
        $this->helper = new HelperController();
    }

    function createCoupon(Request $request)
    {
        $request->validate([
            'course_id' => 'required',
            'code' => ['required', 'regex:/[A-Z0-9\-\.\_]/', 'min:6', 'max:20'],
            'start-date' => 'required|date',
            'end-date' => 'required|date',
        ]);

        $input = array_filter($request
            ->only(
                ['code', 'coupon_type', 'discount_price', 'end-date', 'start-date']
            ));

        $course = Course::setEagerLoads([])
            ->where('author_id', Auth::user()->id)
            ->with(['price'])
            ->find(
                $request->input('course_id'),
                ['id', 'price_id']
            );

        if (!$course) return response(['message' => 'Khóa học không tồn tại!'], 400);

        $format_price = $course->price->format_price;

        Validator::make($input, [
            'discount_price' => [
                'regex:/^(\d{1}\.\d{3}\.0{3})$|^(\d{3}\.0{3})$/',
                'lt:' . $format_price,
                'numeric',
                'gt:0'
            ],
        ])->validate();

        // $coupon_type = Coupon::where('id', $input['coupon_type'])->first();

        $isValid = $this->checkToCreateCoupon($course->id);
        if (!$isValid)  return response(['success' => false, 'Lỗi trong quá trình tạo mã giảm giá!'], 400);

        $discount_price =
            isset($input['discount_price'])
            ? $input['discount_price']
            : 0;

        $startDate = Carbon::parse($input['start-date']);
        $endDate =  Carbon::parse($input['end-date']);

        CourseCoupon::create([
            'code' => $input['code'],
            'coupon_id' => $input['coupon_type'],
            'status' => 1,
            'course_id' => $course->id,
            'discount_price' => $discount_price,
            'created_at' => $startDate,
            'expires' => $endDate,
        ]);

        return response(['success' => true]);
    }

    function getExpiredCoupons($course_id)
    {
        $expiredCoupons = CourseCoupon::where('course_id', $course_id)
            ->where('status', 0)
            ->get();

        return response(['expiredCoupons' => $expiredCoupons]);
    }

    function getScheduledCoupons($course_id)
    {
        $active_coupons = CourseCoupon::where('course_id', $course_id)
            ->where('status', 1)
            ->get()
            ->map(function ($coupon) {
                $expires = Carbon::parse($coupon->expires);
                $startDate = Carbon::parse($coupon->created_at);

                // Ngày mã giảm giá bắt đầu có hiệu lực > ngày hiện tại => lấy thời gian từ dtb
                if ($startDate->greaterThanOrEqualTo(Carbon::now())) {
                    $time_remaining = $coupon->coupon->limited_time;
                } else {
                    $time_remaining = $expires->diffInDays(Carbon::now());
                }

                $coupon->time_remaining = $time_remaining;

                $coupon->expires = Carbon::parse($expires)
                    ->isoFormat('DD/MM/YYYY HH:mm A');
                $coupon->created_at = Carbon::parse($coupon->created_at)
                    ->isoFormat('DD/MM/YYYY HH:mm A');

                return $coupon;
            });

        return response(['scheduledCoupons' => $active_coupons]);
    }

    function getCouponTypes()
    {
        $arrCoupons = Coupon::all();
        $arrCoupons->transform(function ($coupon) {
            if ($coupon->type === 'CUSTOM_PRICE') {
                $coupon->discountConst = $this->discountConst;
            }

            return $coupon;
        });
        return response(['couponTypes' => $arrCoupons]);
    }

    function getInformationCreateCoupon($course_id)
    {
        $course = Course::setEagerLoads([])
            ->with(['price'])
            ->select('id', 'price_id')
            ->where('author_id', Auth::user()->id)
            ->find($course_id);

        if (!$course) return response(['message' => 'not exist this course'], 400);

        $isFreeCourse = $course->price->original_price === 0 ? true : false;
        $maxCouponInAMonth = $this->maxCouponInAMonth;
        $couponsCreationRemaining = $this->maxCouponInAMonth - $this->couponsInMonth($course_id);
        $canCreate = ($couponsCreationRemaining > 0 ? true : false) && !$isFreeCourse;

        return response(compact('couponsCreationRemaining', 'maxCouponInAMonth', 'canCreate', 'isFreeCourse'));
    }

    private function couponsInMonth($course_id)
    {
        $couponInMonth =
            $this->queryCouponByCourseId($course_id)
            ->where('status', 1)
            ->get();

        return $couponInMonth->count();
    }

    private function checkToCreateCoupon($course_id)
    {
        $numberCouponInMonth = $this->couponsInMonth($course_id);

        if ($numberCouponInMonth < $this->maxCouponInAMonth) return true;
        else return false;
    }

    private function queryCouponByCourseId($course_id)
    {
        $current_month = Carbon::now()->month;
        $current_year = Carbon::now()->year;

        $couponInMonth =
            CourseCoupon::where('course_id', $course_id)
            ->whereMonth('created_at', $current_month)
            ->whereYear('created_at', $current_year);

        return $couponInMonth;
    }
}
