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
    function getCategoriesByCourseId($courses_id)
    {
        return DB::select('SELECT t1.title AS topic_title,t1.category_id AS topic_id,
        t2.title  AS subcategory_title,t2.category_id AS subcategory_id,
        t3.title AS category_title,t3.category_id AS category_id
        FROM (SELECT * FROM categories WHERE category_id IN (' . implode(', ', $courses_id) . ') ) as t1
        LEFT JOIN categories AS t2 ON t1.parent_id = t2.category_id
        LEFT JOIN categories AS t3 ON t2.parent_id = t3.category_id');
    }

    function getTopLevelCategories($categories_id, $limit)
    {
        $categories = $this->getCategoriesByCourseId($categories_id);

        $top_level = collect($categories)->map(function ($level) {
            if ($level->category_id)
                return ['top_level_id' => $level->category_id, 'name' => $level->category_title];

            return ['top_level_id' => $level->subcategory_id, 'name' => $level->subcategory_title];
        });

        return $top_level->unique()->values();
    }

    function queryCategory($where)
    {
        return "SELECT t1.title AS level1,t1.slug AS level1_slug,t1.category_id AS category_id,
                t2.title  AS level2,t2.slug AS level2_slug,t2.category_id  AS subcategory_id,
                t3.title AS level3,t3.slug AS level3_slug, t3.category_id AS topic_id
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
            ->select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'created_at', 'instructional_level_id', 'subtitle')
            ->withCount(['course_bill', 'rating', 'section', 'lecture'])
            ->withAvg('rating', 'rating')
            ->with(['categories:category_id,parent_id,title,slug', 'course_outcome']);

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
            ->first(['expires', 'created_at', 'enrollment_limit', 'currently_enrolled', 'course_id', 'coupon_id', 'code', 'discount_price', 'status']);

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


    function niceBytes($bytes)
    {
        $units = ['bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        $l = 0;
        $n = intval($bytes, 10);

        while ($n >= 1024 && ++$l) {
            $n = $n / 1024;
        }

        return (number_format($n, $n < 10 && $l > 0 ? 1 : 0) . ' ' . $units[$l]);
    }

    function getDuration($video_path)
    {
        $getID3 = new \getID3;
        $file = $getID3->analyze($video_path);

        return [
            'playtime_string' => $file['playtime_string'],
            'playtime_seconds' => $file['playtime_seconds']
        ];
    }
}
