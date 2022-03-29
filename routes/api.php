<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

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
Route::get('/course', [CourseController::class, 'getCourse']);
Route::post('/course', [CourseController::class, 'getCourseBySlug']);
Route::get('/instructor/course/{id}', [CourseController::class, 'getCourseOfAuthorById']);

// USER

Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/sign-up', [UserController::class, 'signUp']);
Route::get('/user', [UserController::class, 'getCurrentUser']);
Route::get('/instructor/profile/{slug}', [InstructorController::class, 'profile']);

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
});
