<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Cache;

class CourseController extends Controller
{
    public function courseList(Request $request)
    {
        // Cache for 60 minutes (3600 seconds)
        $courses = Cache::remember('courses.list', 3600, function () {
            return Course::select('name', 'thumbnail', 'price', 'lesson_num', 'price', 'id')->get();
        });

        return response()->json([
            'code' => 200,
            'msg' => 'Course List',
            'data' => $courses
        ], 200);
    }

    public function courseDetail(Request $request)
    {
        $id = $request->id;
        try {
            $result = Course::where('id', $id)->select(
                'id',
                'name',
                'user_token',
                'description',
                'price',
                'lesson_num',
                'video_length',
                'follow',
                'thumbnail',
                'score'
            )->first();

            return response()->json([
                'code' => 200,
                'msg' => 'Course Detail',
                'data' => $result
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => 'Course Detail Load Failed',
                'data' => []
            ], 500);
        }
    }
}
