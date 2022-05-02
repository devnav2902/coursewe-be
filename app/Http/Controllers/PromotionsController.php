<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\CourseCoupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PromotionsController extends Controller
{
    private $helper;
    private $maxCouponInAMonth = 3;

    function __construct()
    {
        $this->helper = new HelperController();
    }

    function getScheduledCoupons($course_id)
    {
        $query = CourseCoupon::without('coupon')
            ->where('course_id', $course_id)
            ->where('status', 1);

        $queryCoupons = clone $query;
        $arrCoupons = $queryCoupons->get();

        $active_coupons  = [];
        foreach ($arrCoupons as $item) {
            $active_coupons[] =
                $this->helper->getCoupon($item->code, $course_id);
        }

        $active_coupons = collect($active_coupons)
            ->map(function ($coupon) {
                $expires = $coupon->expires;
                $time_remaining = Carbon::now()->diffInDays(
                    $expires,
                    false
                );

                if ($time_remaining <= $coupon->coupon->limited_time)
                    $coupon->time_remaining = $time_remaining;

                $coupon->expires = Carbon::parse($expires)
                    ->isoFormat('DD/MM/YYYY HH:mm A zz');

                return $coupon;
            });

        return response(['scheduledCoupons' => $active_coupons]);
    }

    function getCouponTypes()
    {
        return response(['couponTypes' => Coupon::all()]);
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

    private function  couponsInMonth($course_id)
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
