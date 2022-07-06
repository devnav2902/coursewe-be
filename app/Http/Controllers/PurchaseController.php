<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Course;
use App\Models\CourseBill;
use App\Models\CourseCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    function purchase(Request $request)
    {
        $request->validate([
            'course_id' => 'required|array',
            'coupon_code' => 'array',
            'coupon_code.*' => 'regex:/^[A-Z0-9\-\.\_]{6,20}$/',
        ]);

        $course_id = $request->input('course_id');
        $code = $request->input('coupon_code');

        $course = Course::whereIn('course.id', $course_id)
            ->setEagerLoads([])
            ->with(['coupon' => function ($q) use ($code) {
                $q->whereIn('code', $code);
            }])
            ->get(['id', 'price_id', 'slug', 'title', 'thumbnail'])
            ->map(function ($course) {
                return $course->only(['id', 'coupon', 'title', 'thumbnail', 'price']);
            });

        $dataToCreate = [];

        $course->each(function ($course) use (&$dataToCreate) {
            $original_price = $course['price']->original_price;
            $removedDot = empty($course['coupon'][0])
                ? $original_price
                : str_replace('.', '', $course['coupon'][0]->discount_price);

            $dataToCreate[] = [
                'course_id' => $course['id'],
                'title' => $course['title'],
                'user_id' => Auth::user()->id,
                'thumbnail' => $course['thumbnail'],
                'purchase' => empty($course['coupon'][0])
                    ? $original_price
                    : ($original_price === $removedDot
                        ? 0
                        : $removedDot),
                'price' => $course['price']->original_price,
                'promo_code' => empty($course['coupon'][0]) ? '' : $course['coupon'][0]->code
            ];
        });

        try {
            // Cập nhật lại dữ liệu mã giảm giá từng khóa học(số người nhập mã,...)
            $dataToUpdate = [];

            $arrCourseCoupon = CourseCoupon::whereIn('course_id', $course_id)
                ->whereIn('code', $code)
                ->get();

            collect($dataToCreate)->each(function ($course) use ($arrCourseCoupon, &$dataToUpdate) {
                $courseCoupon = $arrCourseCoupon
                    ->where('course_id', $course['course_id'])
                    ->where('code', $course['promo_code'])
                    ->first();

                if (isset($courseCoupon)) {
                    $dataToUpdate[] = [
                        'currently_enrolled' => $courseCoupon->currently_enrolled + 1,
                        'course_id' => $course['course_id'],
                        'code' => $course['promo_code'],
                        'coupon_id' => $courseCoupon->coupon_id
                    ];
                }
            });

            CourseBill::insert($dataToCreate);

            if (count($dataToUpdate)) {
                CourseCoupon::upsert(
                    $dataToUpdate,
                    ['course_id', 'code', 'coupon_id'],
                    ['currently_enrolled'],
                );
            }

            $coursesInCourseBill = collect($dataToCreate)->pluck('course_id');
            Cart::where('user_id', Auth::user()->id)
                ->whereIn('course_id', $coursesInCourseBill)
                ->delete();

            return response(['message' => 'Mua khóa học thành công!']);
        } catch (\Throwable $th) {
            return response(['message' => 'Lỗi trong quá trình mua khóa học!'], 403);
        }
    }
}
