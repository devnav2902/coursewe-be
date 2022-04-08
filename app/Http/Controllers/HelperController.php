<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCoupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HelperController extends Controller
{

    function queryCategory($where)
    {
        return "SELECT t1.title AS level1,t1.slug AS level1_slug,
                t2.title  AS level2,t2.slug AS level2_slug,
                t3.title AS level3,t3.slug AS level3_slug
            FROM categories as t1
            LEFT JOIN categories AS t2 ON t1.category_id = t2.parent_id
            LEFT JOIN categories AS t3 ON t2.category_id = t3.parent_id WHERE " . $where;
    }

    function getCoursesByCategorySlug($slug, $pagination = true)
    {
        $where = "t1.slug = '" . $slug . "' OR " . "t2.slug = '" . $slug . "' OR " . "t3.slug = '" . $slug . "'";
        $category_query = $this->queryCategory($where);
        $result = DB::select($category_query);

        $topics_slug = collect($result)->map(function ($level) {
            if ($level->level3_slug)
                return $level->level3_slug;

            return $level->level2_slug;
        });

        $courses = null;

        $queryGetCourses = Course::whereHas('categories', function ($query) use ($topics_slug) {
            $query->whereIn('slug', $topics_slug);
        })
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->withAvg('rating', 'rating')
            ->with('course_outcome');

        if ($pagination) {
            $courses = $queryGetCourses->paginate(5);
        } else {
            $courses = $queryGetCourses->get();
        }

        return $courses;
    }

    function getCourseOfInstructor($course_id)
    {
        return Course::where('author_id', Auth::user()->id)
            ->with(
                [
                    'section',
                    'lecture',
                ]
            )
            ->firstWhere('id', $course_id);
    }

    function countProgress($lectures)
    {
        $progress = array();
        foreach ($lectures as $lecture) {
            if (!empty($lecture->progress))
                $progress[] = $lecture->progress;
        }

        return collect($progress)->count();
    }

    function getCoupon($code, $course_id)
    {
        $query = CourseCoupon::with('coupon')
            ->where('code', $code)
            ->where('course_id', $course_id)
            ->where("status", 1);

        $queryCourseCoupon = clone $query;
        $courseCoupon = $queryCourseCoupon
            ->first(['expires', 'enrollment_limit', 'currently_enrolled', 'course_id', 'coupon_id', 'code', 'discount_price', 'status']);

        if (!$courseCoupon) return null;

        $coupon = $courseCoupon->coupon;
        $enrollment_limit = $coupon->enrollment_limit;

        $isExpired = null;
        // UNLIMITED
        if ($enrollment_limit) {
            $query = clone $query;
            $isExpired = $query
                ->whereDate('expires', '<', Carbon::now())
                ->orWhereDate('expires', Carbon::now())
                ->whereTime('expires', '<', Carbon::now())
                ->orWhere('currently_enrolled', '>=', $enrollment_limit);
        } else {
            // CHECK DATETIME
            $query = clone $query;
            $isExpired = $query
                ->whereDate('expires', '<', Carbon::now())
                ->orWhereDate('expires', Carbon::now())
                ->whereTime('expires', '<', Carbon::now());
        }

        $isExpired = $isExpired->first(['course_id']);

        if ($isExpired) {
            $this->updateStateCoupon($code, $course_id);

            return null;
        }

        return $courseCoupon;
    }

    private function updateStateCoupon($code, $course_id)
    {
        CourseCoupon::where('course_id', $course_id)
            ->where('code', $code)
            ->update(['status' => 0]);
    }
}
