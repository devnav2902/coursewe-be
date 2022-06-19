<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Course;
use App\Models\CourseBill;
use App\Models\CourseCoupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FreeEnrollController extends Controller
{
    function freeEnroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|array',
            'code' => 'array',
            'code.*' => 'regex:/^[A-Z0-9\-\.\_]{6,20}$/',
        ]);

        $course_id = $request->input('course_id');
        $code = $request->input('code', []);
        $coupon = null;

        if (count($code)) {
            // DB::enableQueryLog();
            $coupon = CourseCoupon::with('coupon')
                ->where('status', 1)
                ->whereIn('code', $code)
                ->whereIn('course_id', $course_id)
                ->get();

            if (count($coupon) < 1)
                return response(['message' => 'Đăng ký khóa học không thành công!'], 403);
        }

        $course = Course::whereIn('course.id', $course_id)
            ->setEagerLoads([])
            ->with(['coupon'])
            ->where(function ($q) use ($code) {
                $q
                    ->whereHas('price', function ($query) {
                        $query->where('original_price', 0);
                    })
                    ->orWhereHas('coupon', function ($query) use ($code) {
                        $query->whereIn('code', $code);
                    });
            })
            ->get(['id', 'price_id', 'slug', 'title', 'thumbnail'])
            ->map(function ($course) {
                return $course->only(['id', 'coupon', 'title', 'thumbnail', 'price']);
            });

        if (count($course) < count($course_id))
            return response(['message' => 'Mã giảm giá hết hạn hoặc lỗi trong quá trình đăng ký khóa học !'], 403);

        $dataToCreate = [];

        $course->each(function ($course) use (&$dataToCreate) {
            $dataToCreate[] = [
                'course_id' => $course['id'],
                'title' => $course['title'],
                'user_id' => Auth::user()->id,
                'thumbnail' => $course['thumbnail'],
                'purchase' => 0,
                'price' => $course['price']->original_price,
                'promo_code' => $course['coupon'][0]->code
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

            return response(['message' => 'Đăng ký khóa học thành công!']);
        } catch (\Throwable $th) {
            return response(['message' => 'Lỗi trong quá trình đăng ký khóa học!'], 403);
        }
    }
}
