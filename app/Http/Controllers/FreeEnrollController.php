<?php

namespace App\Http\Controllers;

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
            'course_id' => 'required',
            'coupon' => 'uuid',
            'code' => 'regex:/[A-Z0-9\-\.\_]/', 'min:6', 'max:20'
        ]);

        $course_id = $request->input('course_id');
        $code = $request->input('code') ?? '';
        $coupon = null;

        if ($code) {
            $coupon = CourseCoupon::with('coupon')
                ->where([['code', $request->input('code')], ['course_id', $course_id]])
                ->first();

            if (empty($coupon)) return response(['message' => 'Đăng ký khóa học không thành công!'], 403);
        }

        $course = Course::where('course.id', $course_id)
            ->where(function ($q) use ($code) {
                $q
                    ->whereHas('price', function ($query) {
                        $query->where('original_price', 0);
                    })
                    ->orWhereHas('coupon', function ($query) use ($code) {
                        $query->where('code', $code);
                    });
            })
            ->first(['id', 'price_id', 'slug', 'title', 'thumbnail']);

        if (empty($course)) return response(['message' => 'Khóa học này không thể đăng ký miễn phí!'], 403);

        try {
            CourseBill::create(
                [
                    'user_id' => Auth::user()->id,
                    'course_id' => $course->id,
                    'title' => $course->title,
                    'thumbnail' => $course->thumbnail,
                    'purchase' => 0,
                    'price' => $course->price->original_price,
                    'promo_code' => $code
                ]
            );

            if (!empty($coupon)) {
                $currently_enrolled = $coupon->currently_enrolled + 1;
                CourseCoupon::where('code', $code)
                    ->where('course_id', $course_id)
                    ->update(['currently_enrolled' => $currently_enrolled]);
            }

            return response(['message' => 'Đăng ký khóa học thành công!']);
        } catch (\Throwable $th) {
            return response(['message' => 'Lỗi trong quá trình đăng ký khóa học!'], 403);
        }
    }
}
