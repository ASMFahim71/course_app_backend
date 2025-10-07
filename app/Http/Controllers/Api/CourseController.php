<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Illuminate\Support\Facades\Cache;
use App\Models\Member;

class CourseController extends Controller
{
    public function courseList(Request $request)
    {


        $courses = Course::select('name', 'thumbnail', 'price', 'lesson_num', 'price', 'id')->get();


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

    public function coursesBought(Request $request)
    {

        $user = $request->user();

        $courses = Course::join('orders', 'courses.id', '=', 'orders.course_id')
        ->where('orders.user_token', '=', $user->token)
        ->where('orders.status', '=', 1)
        ->select('courses.name', 'courses.thumbnail', 'courses.price', 'courses.lesson_num', 'courses.id')
        ->get();

        return response()->json([
            'code' => 200,
            'msg' => 'The courses you have bought',
            'data' => $courses
        ], 200);
    }
    public function coursesSearchDefault(Request $request)
    {
        $user = request()->user();
        $result = Course::where('recommended', '=', '1')
        ->select('name', 'thumbnail', 'price', 'lesson_num', 'price', 'id')->get();

        return response()->json(
            [
                'code' => 200,
                'msg' => 'Recommended Courses',
                'data' => $result
            ], 200);
            
        
    }   
    
    public function coursesSearch(Request $request)
    {
        $user = request()->user();
        $search = $request->search;
        $result = Course::where('name', 'like', '%' . $search . '%')
        ->select('name', 'thumbnail', 'price', 'lesson_num', 'price', 'id')->get();

        return response()->json(
            [
                'code' => 200,
                'msg' => 'Search Courses',
                'data' => $result
            ], 200);
            
        
    }

    public function authorCourseList(Request $request){
        $token=$request->token;
        $courses=Course::where('user_token','=',$token)
        ->select('name','thumbnail','price','lesson_num','price','id')->get();
        return response()->json([
            'code' => 200,
            'msg' => 'Author Course List',
            'data' => $courses
        ], 200);
    }
    
    public function courseAuthor(Request $request){
        $token=$request->token;
        $author=Member::where('token','=',$token)
        ->select('name','email','avatar','token','description','job')->first();
        return response()->json([
            'code' => 200,
            'msg' => 'Author Info',
            'data' => $author
        ], 200);
    }

}


