<?php

use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\HelperController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\PublishCourseController;
use App\Http\Controllers\UserManagementController;
use App\Models\Course;
use App\Models\CourseCoupon;
use App\Models\InstructionalLevel;
use App\Models\Price;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/check-price', function () {
    $input = ['289.000.k'];
    $original_price = 289.000;

    Validator::make($input, [
        'discount_price' => [
            'regex:/^(\d{1}\.\d{3}\.0{3})$|^(\d{3}\.0{3})$/',
            'numeric|gt:0|lt:' . $original_price
        ],
    ])->validate();
    return $input;
});
Route::get('/check-coupon', function () {

    DB::enableQueryLog();
    $coupons = CourseCoupon::where("status", 1)
        ->whereHas('coupon', function ($queryCoupon) {
            $queryCoupon
                ->where(function ($q) {
                    $q
                        ->where('enrollment_limit', '<>', 0)
                        ->whereColumn('course_coupon.currently_enrolled', 'enrollment_limit');
                })
                ->orWhere(function ($q) {
                    $q
                        ->whereDate('course_coupon.expires', '<=', Carbon::now('Asia/Ho_Chi_Minh'))
                        ->whereTime('course_coupon.expires', '<=', Carbon::now('Asia/Ho_Chi_Minh'));
                });
        })
        ->update(['status' => 0]);
    // ->get();
    // return DB::getQueryLog();
    return $coupons;
});
Route::get('/get-coupon', function () {
    $controller = new PromotionsController();
    return $controller->getExpiredCoupons(5);
});
Route::get('/checking-course/{courseId}', function ($courseId) {
    $controller = new PublishCourseController();
    return $controller->checkingPublishRequirements($courseId);
});
Route::get('/get-category/{slug}', function ($slug) {
    DB::enableQueryLog();
    $categories = DB::table('categories as t1')
        ->leftJoin('categories as t2', 't1.category_id', '=', 't2.parent_id')
        ->leftJoin('categories as t3', 't2.category_id', '=', 't3.parent_id')
        ->whereNull('t1.parent_id')
        ->where(function ($q) use ($slug) {
            $q
                ->where('t1.slug', '=', $slug)
                ->orWhere('t2.slug', '=', $slug)
                ->orWhere('t3.slug', '=', $slug);
        })
        ->get(
            [
                't1.title AS level1_title', 't1.slug AS level1_slug', 't1.category_id AS category_id',
                't2.title  AS level2_title', 't2.slug AS level2_slug', 't2.category_id  AS subcategory_id',
                't3.title AS level3_title', 't3.slug AS level3_slug', 't3.category_id AS topic_id'
            ]
        );

    if (count($categories) === 1) {
        return $categories[0];
    }

    $uniqueSubcategory = collect($categories)->unique('subcategory_id');
    if (count($uniqueSubcategory) === 1) {
        $subcategory = $uniqueSubcategory->first();
        return collect($subcategory)->except(['topic_id', 'level3_slug', 'level3_title']);
    } else {
        $topLevel = $uniqueSubcategory->unique('category_id')->first();
        return collect($topLevel)->only(['level1_title', 'level1_slug', 'category_id']);
    }
});

Route::get('/get-courses/{category_slug}', function ($slug) {
    // $helper = new HelperController();
    DB::enableQueryLog();

    $controller = new HelperController();
    $categoryQueryBase = $controller->categoryQueryBase();
    $groupedCategory = $categoryQueryBase
        ->where(function ($q) use ($slug) {
            $q
                ->where('t1.slug', '=', $slug)
                ->orWhere('t2.slug', '=', $slug)
                ->orWhere('t3.slug', '=', $slug);
        })
        ->get();

    $categories_id = $controller->getCategoriesIdToGetCourses($groupedCategory);

    // Lỗi ONLY_FULL_GROUP_BY => Xuất hiện column(select) kh nằm trong groupBy
    DB::statement("SET sql_mode=''");
    $authors = Course::setEagerLoads([])
        ->whereHas(
            'categories',
            function ($query) use ($categories_id) {
                $query
                    ->select('categories.category_id', 'parent_id', 'title', 'slug')
                    ->whereIn('categories.category_id', $categories_id);
            }
        )
        ->select('author_id')
        ->withAvg('rating', 'rating')
        ->groupBy('author_id')
        ->having('rating_avg_rating', '>=', 4)
        ->take(10)
        ->get()
        ->pluck(['author_id']);

    $popularInstructors = User::with(
        [
            'course' => function ($q) use ($categories_id) {
                $q
                    ->setEagerLoads([])
                    ->whereHas(
                        'categories',
                        function ($query) use ($categories_id) {
                            $query
                                ->select('categories.category_id', 'parent_id')
                                ->whereIn('categories.category_id', $categories_id);
                        }
                    )
                    ->select('id', 'author_id')
                    ->withCount(['course_bill'])
                    ->withAvg('rating', 'rating')
                    ->having('rating_avg_rating', '>=', 4);
            }
        ]
    )
        ->without(['role'])
        ->whereIn('id', $authors)
        ->get(["id", "fullname", "slug", "avatar"]);

    return $popularInstructors->map(function ($author) {
        $author->totalStudents = $author->course->sum('course_bill_count');
        $avgCourses = $author->course->avg('rating_avg_rating');
        $author->roundedAvgCourses = round($avgCourses, 1);
        $author->numberOfCourses = $author->course->count();

        unset($author->course);
        return $author;
    });

    return DB::getQueryLog();
});

Route::get('/test', function () {



    return (new UserManagementController())->instructorManagement();
});

// Khóa học featured, rated >= 4
// ->select('title', 'id', 'author_id', 'slug', 'price_id', 'thumbnail', 'updated_at', 'instructional_level_id', 'subtitle')
//         ->withCount(['course_bill', 'rating', 'section', 'lecture'])
//         ->withAvg('rating', 'rating')
//         ->with([
//             'categories:category_id,parent_id,title,slug',
//             'course_outcome:id,course_id,order,description',
//             'author:id,fullname,avatar,slug,role_id',
//             'course_requirements:id,course_id,order,description'
//         ])