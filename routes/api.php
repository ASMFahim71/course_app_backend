<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\LessonController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\SslCommerzPaymentController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




    Route::post('/login',[MemberController::class,'login']);

    Route::group(['middleware'=>'auth:sanctum'],function(){
        Route::any('/courseList',[CourseController::class,'courseList']);
        Route::any('/courseDetail',[CourseController::class,'courseDetail']);
        Route::any('/coursesBought',[CourseController::class,'coursesBought']);
        Route::any('/lessonList',[LessonController::class,'lessonList']);
        Route::any('/lessonDetail',[LessonController::class,'lessonDetail']);
        Route::any('/checkout',[PaymentController::class,'checkout']);
        Route::any('/coursesSearchDefault',[CourseController::class,'coursesSearchDefault']);
        Route::any('/coursesSearch',[CourseController::class,'coursesSearch']);
       
    });

    Route::any('/webGoHooks',[PaymentController::class,'webGoHooks']);

    //Route::post('/pay-via-ajax', [SslCommerzPaymentController::class, 'payViaAjax']);