<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InstructorRevenueController extends Controller
{
    function get()
    {
        $data =  Course::with([
            'course_bill' => fn ($q) => $q->setEagerLoads([])
        ])
            ->setEagerLoads([])
            ->with(['price'])
            ->where('author_id', Auth::user()->id)
            ->select('id', 'author_id', 'price_id', 'thumbnail', 'title', 'created_at')
            ->withSum('course_bill', 'purchase')
            ->withCount('course_bill')
            ->withAvg('rating', 'rating')
            ->paginate(10);

        $revenue = CourseBill::whereHas('course', function ($q) {
            $q
                ->setEagerLoads([])
                ->where('author_id', Auth::user()->id)
                ->select('id', 'author_id', 'price_id');
        })
            ->orderBy('created_at', 'asc')
            ->select('purchase', 'course_id', 'created_at')
            ->sum('purchase');

        $bestCourse = CourseBill::whereHas('course', function ($q) {
            $q
                ->setEagerLoads([])
                ->where('author_id', Auth::user()->id)
                ->select('id', 'author_id', 'price_id');
        })
            ->orderBy('created_at', 'asc')
            ->select('purchase', 'course_id', 'created_at')
            ->get();

        $bestRevenueCourse = $bestCourse->groupBy('course_id')->map(function ($course) {
            return $course->sum('purchase');
        });

        $bestRevenueCourse = $bestRevenueCourse->max();

        $revenueLatestCourse = Course::with([
            'course_bill' => fn ($q) => $q->setEagerLoads([])
        ])
            ->setEagerLoads([])
            ->with(['price'])
            ->where('author_id', Auth::user()->id)
            ->select('id', 'author_id', 'price_id', 'thumbnail', 'title', 'created_at')
            ->withSum('course_bill', 'purchase')
            ->withCount('course_bill')
            ->latest()
            ->first();

        return response()->json([
            'courses' => $data,
            'totalRevenue' => $revenue,
            'bestRevenueCourse' => $bestRevenueCourse,
            'revenueLatestCourse' => $revenueLatestCourse ? $revenueLatestCourse->course_bill_sum_purchase : 0
        ]);
    }
}
