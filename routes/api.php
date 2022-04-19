<?php


use App\Http\Controllers\InstructionalLevelController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CourseController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CreateCourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\RatingController;
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
Route::get('/categories/amount-courses-in-topics/{slug}', [CategoriesController::class, 'amountCoursesInTopics']);

// USER
Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/sign-up', [UserController::class, 'signUp']);
Route::get('/user', [UserController::class, 'getCurrentUser']);
Route::get('/instructor/profile/{slug}', [InstructorController::class, 'profile']);

//ADMIN
Route::get('/admin/submission-courses-list', [AdminController::class, 'reviewCourses']);

// INSTRUCTIONAL LEVEL
Route::get('/instructional-level/amount-courses/{slug}', [InstructionalLevelController::class, 'amountCoursesByInstructionalLevel']);

// RATING
Route::get('/rating/filter-rating/{slug}', [RatingController::class, 'filterRatingByCategorySlug']);

// COUPON
Route::post('/coupon/apply-coupon', [CouponController::class, 'checkCoupon']);
Route::post('/coupon/courses/apply-coupon', [CouponController::class, 'checkCouponWithCourses']);

// CART
Route::get('/cart/me', [CartController::class, 'get']);
Route::post('/cart', [CartController::class, 'cart']);
Route::delete('/cart/{id}', [CartController::class, 'delete']);
Route::patch('/saved-for-later', [CartController::class, 'savedForLater']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/revenue', [OverviewController::class, 'chartJSYear']);
    Route::post('/enrollments', [OverviewController::class, 'chartEnrollments']);
    Route::post('/rating', [OverviewController::class, 'chartRating']);
    Route::post('/courses', [OverviewController::class, 'chartCourses']);

    Route::get('/instructor/overview', [OverviewController::class, 'getOverview']);
    Route::get('/user/courses', [CourseController::class, 'getCourseByCurrentUser']);
    Route::get('/user/logout', [UserController::class, 'logout']);
    Route::get('/purchase/history', [PurchaseHistoryController::class, 'purchaseHistory']);
    //ProFile
    Route::get('/user/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/user/bio', [ProfileController::class, 'getBio'])->name('bio');
    Route::post('/profile', [ProfileController::class, 'save'])->name('saveProfile');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])
        ->name('changePassword');
    Route::post('/change-bio', [ProfileController::class, 'changeBio'])
        ->name('changeBio');
    Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar'])
        ->name('uploadAvatar');
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
    Route::post('/course/update-information/{id}', [CourseController::class, 'updateInformation']);
    Route::get('/user/logout', [UserController::class, 'logout']);
    Route::get('/purchase/history', [PurchaseHistoryController::class, 'purchaseHistory']);
    Route::get('/instructor/course/{id}', [InstructorController::class, 'getCourseById']);
    Route::get('/get-price', [PriceController::class, 'getPrice']);

    Route::post('/create-course', [CreateCourseController::class, 'create']);

    // MY LEARNING
    Route::get('/my-learning', [LearningController::class, 'myLearning']);
    // LEARNING
    Route::get('/progress/{course_id}', [LearningController::class, 'getProgress']);
    Route::get('/learning/{slug}', [LearningController::class, 'learning']);
    Route::get('/sections/{course_id}', [LearningController::class, 'getSections']);
    Route::post('/progress', [ProgressController::class, 'updateProgress']);
});
