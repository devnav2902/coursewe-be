<?php


use App\Http\Controllers\InstructionalLevelController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseImageController;
use App\Http\Controllers\CourseVideoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreateCourseController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FreeEnrollController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProgressLogsController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\PublishCourseController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Models\Coupon;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// INSTRUCTOR


// COURSE
Route::get('/course/best-selling', [CourseController::class, 'bestSellingCourses']); // !lấy theo tuần
Route::get('/course/latest', [CourseController::class, 'getLatestCourses']);
Route::get('/course', [CourseController::class, 'getCourse']);
Route::get('/course/get/{slug}', [CourseController::class, 'getCourseBySlug']);
Route::get('/instructor/course/{id}', [CourseController::class, 'getCourseOfAuthorById']);
Route::get('/course/instructional-level', [InstructionalLevelController::class, 'get']);
Route::get('/course/{id}', [CourseController::class, 'getCourseById']);
Route::get('/course/has-purchased/{course_id}', [CourseController::class, 'checkUserHasPurchased']);
Route::get('/course/has-rated/{course_id}', [CourseController::class, 'checkUserHasRated']);

// CATEGORY
Route::get('/featured-courses/{limit}', [CategoriesController::class, 'featuredCourses']);
Route::get('/category/featured-courses/{topLevelCategoryId}', [CategoriesController::class, 'featuredCoursesByCategoryId']);
Route::get('/featured-categories/{limit}', [CategoriesController::class, 'featuredCategories']);
Route::get('/categories', [CategoriesController::class, 'getCategories']);
Route::get('/categories/get-courses/{slug}', [CategoriesController::class, 'getCoursesByCategorySlug']);
Route::get('/categories/popular-instructors/{slug}', [CategoriesController::class, 'getPopularInstructors']);
Route::get('/categories/discovery-units/{slug}', [CategoriesController::class, 'discoveryUnits']);
Route::get('/categories/breadcrumb/{slug}', [CategoriesController::class, 'getBreadcrumbByCategory']);
Route::get('/categories/courses-beginner/{slug}', [CategoriesController::class, 'coursesBeginner']);


// USER
Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/sign-up', [UserController::class, 'signUp']);
Route::get('/user', [UserController::class, 'getCurrentUser']);
Route::get('/instructor/profile/{slug}', [InstructorController::class, 'profile']);

//ADMIN
Route::get('/admin/submission-courses-list', [AdminController::class, 'reviewCourses']);

// INSTRUCTIONAL LEVEL

// RATING

//  SEARCH  
Route::post('/autocomplete/search', [SearchController::class, 'search']);
Route::get('/search', [SearchController::class, 'index'])->name('search');
// COUPON
Route::post('/coupon/apply-coupon', [CouponController::class, 'checkCoupon']);
Route::post('/coupon/courses/apply-coupon', [CouponController::class, 'checkCouponWithCourses']);
// CURRENCY
Route::get('/currency/{from}/{to}/{money}', [CurrencyController::class, 'convert']);

// CART
Route::get('/cart/me', [CartController::class, 'get']);
Route::post('/cart', [CartController::class, 'cart']);
Route::delete('/cart/{id}', [CartController::class, 'delete']);
Route::patch('/saved-for-later', [CartController::class, 'savedForLater']);

Route::middleware('auth:sanctum')->group(function () {
    // IMAGE COURSE
    Route::post('/course-image', [CourseImageController::class, 'updateCourseImage']);
    // VIDEO COURSE
    Route::post('/course-video', [CourseVideoController::class, 'updateCourseVideo']);

    // PERFORMANCE
    Route::get('/performance/revenue', [OverviewController::class, 'getRevenue']);
    Route::get('/performance/enrollments', [OverviewController::class, 'getEnrollments']);
    Route::get('/performance/rating', [OverviewController::class, 'getChartRating']);
    Route::get('/performance/courses', [OverviewController::class, 'amountCoursesByCategory']);
    // EXPORT
    Route::get('/export/revenue', [ExportController::class, 'revenueExport']);

    Route::get('/instructor/overview', [OverviewController::class, 'getOverview']);
    Route::get('/user/courses', [InstructorController::class, 'getCoursesByCurrentUser']);
    Route::get('/user/logout', [UserController::class, 'logout']);
    Route::get('/purchase/history', [PurchaseHistoryController::class, 'purchaseHistory']);
    //ProFile
    Route::get('/user/profile', [ProfileController::class, 'index'])->name('profile');
    Route::patch('/change-profile', [ProfileController::class, 'changeProfile'])->name('changeProfile');
    Route::get('/check-instructor-profile-before-publish-course', [ProfileController::class, 'checkInstructorProfileBeforePublishCourse']);

    Route::delete(
        '/course/delete-course-outcome/{id}',
        [CourseController::class, 'deleteCourseOutcome']
    );
    Route::patch('/course/update-course-outcome/{id}', [CourseController::class, 'updateCourseOutcome']);
    Route::delete(
        '/course/delete-course-requirements/{id}',
        [CourseController::class, 'deleteCourseRequirements']
    );
    Route::patch('/course/update-course-requirements/{id}', [CourseController::class, 'updateCourseRequirements']);
    Route::patch('/course/update-information/{id}', [CourseController::class, 'updateInformation']);
    Route::get('/user/logout', [UserController::class, 'logout']);
    Route::get('/purchase/history', [PurchaseHistoryController::class, 'purchaseHistory']);
    Route::get('/instructor/course/{id}', [InstructorController::class, 'getCourseById']);

    // PRICE
    Route::get('/get-price/{courseId}', [PriceController::class, 'getPriceByCourseId']);
    Route::get('/get-price-list', [PriceController::class, 'getPrice']);
    Route::patch('/update-price', [PriceController::class, 'updatePrice']);

    Route::post('/create-course', [CreateCourseController::class, 'create']);

    // PURCHASE
    Route::post('/purchase', [PurchaseController::class, 'purchase']);
    // MY LEARNING
    Route::get('/my-learning', [LearningController::class, 'myLearning']);
    // LEARNING
    Route::get('/progress/{course_id}', [LearningController::class, 'getProgress']);
    Route::get('/learning/{slug}', [LearningController::class, 'learning']);
    Route::get('/sections/{course_id}', [LearningController::class, 'getSections']);
    Route::post('/progress', [ProgressController::class, 'updateProgress']);
    Route::get('/course/{course_slug}/lecture/{lectureId}', [LearningController::class, 'getVideo']);
    // RESOURCE
    Route::delete('/user/me/taught-courses/{courseId}/lectures/{lectureId}/resources/{resourceId}/', [ResourceController::class, 'delete']);
    Route::post('/resources/upload', [ResourceController::class, 'upload']);
    Route::get('/resources/lecture-id/{lectureId}', [ResourceController::class, 'getByLectureId']);
    Route::get('/users/me/subscribed-courses/{courseId}/lectures/{lectureId}/assets/{resourceId}/download', [ResourceController::class, 'download']);
    // LECTURE
    Route::get('/lecture/id/{lectureId}', [LectureController::class, 'getByLectureId']);
    Route::post('/lecture/upload', [LectureController::class, 'upload']);
    Route::delete('/user/me/taught-courses/{courseId}/lectures/{lectureId}', [LectureController::class, 'delete']);
    Route::post('/lecture/create', [LectureController::class, 'createLecture']);
    Route::patch('/lecture/update', [LectureController::class, 'updateTitle']);
    Route::patch('/lecture/re-order/section/{sectionId}/course/{courseId}', [LectureController::class, 'reorder']);
    // SECTION
    Route::get('/section/course/{courseId}', [SectionController::class, 'getSectionsByCourseId']);
    Route::get('/section/{id}', [SectionController::class, 'getSectionById']);
    Route::delete('/user/me/taught-courses/{courseId}/sections/{sectionId}', [SectionController::class, 'delete']);
    Route::post('/section/create', [SectionController::class, 'createSection']);
    Route::patch('/section/update', [SectionController::class, 'updateTitle']);
    Route::patch('/section/re-order/course/{courseId}', [SectionController::class, 'reorder']);

    // PROMOTIONS
    Route::get('/promotions/scheduled-coupons/{courseId}', [PromotionsController::class, 'getScheduledCoupons']);
    Route::get('/promotions/expired-coupons/{courseId}', [PromotionsController::class, 'getExpiredCoupons']);
    Route::get('/promotions/coupon-types', [PromotionsController::class, 'getCouponTypes']);
    Route::get('/promotions/information-create-coupon/{courseId}', [PromotionsController::class, 'getInformationCreateCoupon']);

    //Progresslogs
    Route::get('/last-watched/{course_id}', [ProgressLogsController::class, 'lastWatchedByCourseId']);
    Route::get('/last-watched/course/{course_id}/lecture/{lectureId}', [ProgressLogsController::class, 'lastWatchedByLectureId']);
    Route::post('/last-watched/course/{course_id}/lecture/{lecture_id}/last_watched_second/{second}', [ProgressLogsController::class, 'saveLastWatched']);
    Route::post('/promotions/create-coupon/', [PromotionsController::class, 'createCoupon']);
    // SUBMIT FOR REVIEW
    Route::get('/checking-publish-requirements/{courseId}', [PublishCourseController::class, 'checkingPublishRequirements']);

    // ENROLLMENT
    Route::post('/free-enroll', [FreeEnrollController::class, 'freeEnroll']);
});
