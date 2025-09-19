<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\CourseController;
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');




    Route::post('/login',[MemberController::class,'login']);

    Route::group(['middleware'=>'auth:sanctum'],function(){
        Route::any('/courseList',[CourseController::class,'courseList']);
        Route::any('/courseDetail',[CourseController::class,'courseDetail']);
    });


