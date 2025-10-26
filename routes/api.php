<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\SslCommerzController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




Route::post('/login', [MemberController::class, 'login']);

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::any('/courseList', [CourseController::class, 'courseList']);
    Route::any('/courseNewestList', [CourseController::class, 'courseNewestList']);
    Route::any('/coursePopularList', [CourseController::class, 'coursePopularList']);
    Route::any('/courseDetail', [CourseController::class, 'courseDetail']);
    Route::any('/coursesBought', [CourseController::class, 'coursesBought']);
    Route::any('/coursePurchaseStatus', [CourseController::class, 'coursePurchaseStatus']);
    Route::any('/lessonList', [LessonController::class, 'lessonList']);
    Route::any('/lessonDetail', [LessonController::class, 'lessonDetail']);
    Route::any('/checkout', [PaymentController::class, 'checkout']);
    Route::any('/coursesSearchDefault', [CourseController::class, 'coursesSearchDefault']);
    Route::any('/coursesSearch', [CourseController::class, 'coursesSearch']);
    Route::any('/authorCourseList', [CourseController::class, 'authorCourseList']);
    Route::any('/courseAuthor', [CourseController::class, 'courseAuthor']);
});

Route::any('/webGoHooks', [PaymentController::class, 'webGoHooks']);

//Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);

Route::post('/sslcommerz/create', [SslCommerzController::class, 'createPayment']);
Route::match(['get', 'post'], '/sslcommerz/success', [SslCommerzController::class, 'success']);
Route::match(['get', 'post'], '/sslcommerz/fail', [SslCommerzController::class, 'fail']);
Route::match(['get', 'post'], '/sslcommerz/cancel', [SslCommerzController::class, 'cancel']);
Route::post('/sslcommerz/ipn', [SslCommerzController::class, 'ipn']);
Route::post('/sslcommerz/validate', [SslCommerzController::class, 'validatePayment']);

Route::get('/uploads/{filename}', function ($filename) {
    $path = public_path('uploads/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
});

// Serve storage files with CORS headers - same pattern as uploads
Route::get('/storage/{path}', function ($path) {
    $fullPath = storage_path('app/public/' . $path);

    if (!file_exists($fullPath)) {
        abort(404);
    }

    return response()->file($fullPath, [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'Access-Control-Allow-Headers' => '*',
    ]);
})->where('path', '.*');
//dummy