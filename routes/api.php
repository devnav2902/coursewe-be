<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\InstructorController;
use App\Http\Controllers\LearningController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\UserController;

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
Route::get('/course/{id}', [CourseController::class, 'getCourseById']);

// USER
Route::post('/user/login', [UserController::class, 'login']);
Route::post('/user/sign-up', [UserController::class, 'signUp']);
Route::get('/user', [UserController::class, 'getCurrentUser']);
Route::get('/instructor/profile/{slug}', [InstructorController::class, 'profile']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/logout', [UserController::class, 'logout']);
    Route::get('/purchase/history', [PurchaseHistoryController::class, 'purchaseHistory']);
    Route::get('/my-learning', [LearningController::class, 'myLearning']);
    Route::get('/instructor/course/{id}', [InstructorController::class, 'getCourseById']);
});
