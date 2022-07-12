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
use App\Http\Controllers\InstructorRevenueController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PriceController;
use App\Http\Controllers\ProgressController;
use App\Http\Controllers\ProgressLogsController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\PublishCourseController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\QualityReviewTeamController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RatingQualityController;
use App\Http\Controllers\SearchController;

use App\Http\Controllers\ResourceController;
use App\Http\Controllers\ReviewFilterController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;

// COURSE
Route::get('/course/best-selling', [CourseController::class, 'bestSellingCourses']); // !lấy theo tuần
Route::get('/course/latest', [CourseController::class, 'getLatestCourses']);
Route::get('/course', [CourseController::class, 'getCourse']);
Route::get('/course/get/{slug}', [CourseController::class, 'getCourseBySlug']);
Route::get('/instructor/course/{id}', [CourseController::class, 'getCourseOfAuthorById']);
Route::get('/course/instructional-level', [InstructionalLevelController::class, 'get']);
Route::get('/course/{id}', [CourseController::class, 'getCourseById']);
Route::get('/course/draft/{id}', [CourseController::class, 'getDraftCourseById']);
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

// INSTRUCTIONAL LEVEL

// RATING
Route::get('/course/{courseId}/rating', [RatingController::class, 'getRatingByCourseId']);

//  SEARCH  
Route::get('/autocomplete/search', [SearchController::class, 'search']);
Route::get('/search', [SearchController::class, 'index']);
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
    // NOTIFICATION
    Route::prefix('notification')->group(function () {
        Route::get('/', [NotificationController::class, 'get']);
        Route::patch('mark-as-read', [NotificationController::class, 'markAsRead']);
        Route::patch('mark-all-as-read', [NotificationController::class, 'markAllAsRead']);
    });

    // IMAGE COURSE
    Route::post('/course-image', [CourseImageController::class, 'updateCourseImage']);
    // VIDEO COURSE
    Route::post('/course-video', [CourseVideoController::class, 'updateCourseVideo']);

    // PERFORMANCE
    Route::prefix('performance')->group(function () {
        Route::get('revenue', [OverviewController::class, 'getRevenue']);
        Route::get('enrollments', [OverviewController::class, 'getEnrollments']);
        Route::get('rating', [OverviewController::class, 'getChartRating']);
        Route::get('courses', [OverviewController::class, 'amountCoursesByCategory']);
    });
    // EXPORT
    Route::get('/export/revenue', [ExportController::class, 'revenueExport']);


    Route::get('/instructor/overview', [OverviewController::class, 'getOverview']);
    Route::get('/user/courses', [InstructorController::class, 'getCoursesByCurrentUser']);
    Route::get('/user/logout', [UserController::class, 'logout']);
    Route::get('/purchase/history', [PurchaseHistoryController::class, 'purchaseHistory']);
    //ProFile
    Route::get('/user/profile', [ProfileController::class, 'index'])->name('profile');
    Route::patch('/change-profile', [ProfileController::class, 'changeProfile'])->name('changeProfile');
    Route::post('/change-avatar', [ProfileController::class, 'uploadAvatar'])->name('uploadAvatar');
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
    Route::delete('/user/me/taught-courses/{courseId}/lectures/{lectureId}', [LectureController::class, 'delete']);

    Route::prefix('lecture')->group(function () {
        Route::get('id/{lectureId}', [LectureController::class, 'getByLectureId']);
        Route::post('upload', [LectureController::class, 'upload']);
        Route::post('create', [LectureController::class, 'createLecture']);
        Route::patch('update', [LectureController::class, 'updateTitle']);
        Route::patch('re-order/section/{sectionId}/course/{courseId}', [LectureController::class, 'reorder']);
    });
    // SECTION
    Route::delete('/user/me/taught-courses/{courseId}/sections/{sectionId}', [SectionController::class, 'delete']);

    Route::prefix('section')->group(function () {
        Route::get('/course/{courseId}', [SectionController::class, 'getSectionsByCourseId']);
        Route::get('/{id}', [SectionController::class, 'getSectionById']);
        Route::post('/create', [SectionController::class, 'createSection']);
        Route::patch('/update', [SectionController::class, 'updateTitle']);
        Route::patch('/re-order/course/{courseId}', [SectionController::class, 'reorder']);
    });

    // PROMOTIONS
    Route::prefix('promotions')->group(function () {
        Route::post('create-coupon/', [PromotionsController::class, 'createCoupon']);
        Route::get('scheduled-coupons/{courseId}', [PromotionsController::class, 'getScheduledCoupons']);
        Route::get('expired-coupons/{courseId}', [PromotionsController::class, 'getExpiredCoupons']);
        Route::get('coupon-types', [PromotionsController::class, 'getCouponTypes']);
        Route::get('information-create-coupon/{courseId}', [PromotionsController::class, 'getInformationCreateCoupon']);
    });

    //PROGRESS LOGS
    Route::prefix('last-watched')->group(function () {
        Route::get('/{course_id}', [ProgressLogsController::class, 'lastWatchedByCourseId']);
        Route::get('/course/{course_id}/lecture/{lectureId}', [ProgressLogsController::class, 'lastWatchedByLectureId']);
        Route::post('/course/{course_id}/lecture/{lecture_id}/last_watched_second/{second}', [ProgressLogsController::class, 'saveLastWatched']);
    });

    // SUBMIT FOR REVIEW
    Route::get('/checking-publish-requirements/{courseId}', [PublishCourseController::class, 'checkingPublishRequirements']);
    Route::post('/submit-for-review', [PublishCourseController::class, 'submitForReview']);

    //ADMIN
    Route::prefix('admin')->group(function () {
        Route::get('/submission-courses-list', [AdminController::class, 'reviewCourses']);
        Route::post('/quality-review', [AdminController::class, 'qualityReview']);
        Route::get('/management-instructor', [UserManagementController::class, 'instructorManagement']);
        Route::get('/management-user', [UserManagementController::class, 'userManagement']);
    });

    // ENROLLMENT
    Route::post('/free-enroll', [FreeEnrollController::class, 'freeEnroll']);

    // PREVIEW
    Route::get('/course/preview/{courseId}', [CourseController::class, 'coursePreview']);

    // RATING
    Route::post('/course/rate', [RatingController::class, 'rate']);

    // QUALITY REVIEW
    Route::get('/quality-review-team', [QualityReviewTeamController::class, 'get']);
    Route::post('/quality-review-team/create', [QualityReviewTeamController::class, 'create']);
    Route::get('/quality-review-team/statistic', [QualityReviewTeamController::class, 'statistic']);

    // CATEGORIES
    Route::get('/categories/get-list', [CategoriesController::class, 'getList']);

    // RATING QUALITY
    Route::get('/rating-quality/list-courses', [RatingQualityController::class, 'listCourses']);
    Route::post('/rating-quality/me/rate', [RatingQualityController::class, 'rate']);

    // REVIEW FILTER
    Route::get('/review-filter/get', [ReviewFilterController::class, 'get']);

    // INSTRUCTOR REVENUE
    Route::get('/instructor-revenue/get', [InstructorRevenueController::class, 'get']);

    // LOCATION
    Route::get('/students/analytics', [LocationController::class, 'getByInstructor']);
});
