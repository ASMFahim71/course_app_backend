<?php

namespace App\Http\Controllers\Api;

use App\Models\Member;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MemberController extends Controller
{
    /**
     * Create User\
     * @param Request $request
     * @return User 
     */
    public function login(Request $request)
    {
        
        
        
        try {
            //Validated
            $validateUser = Validator::make(
                $request->all(),
                [
                    'avatar' => 'required',
                   
                    'open_id' => 'required',
                    'name' => 'required',
                    'email' => 'required',
                  //  'password' => 'required'
                ]
            );

            if ($validateUser->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

            $validated = $validateUser->validated();
            $map = [];
         
            $map['open_id'] = $validated['open_id'];

            $user = Member::where($map)->first();
           
           
            if (empty($user->id)) {
                $validated["token"] = md5(uniqid() . rand(1000, 9999));
                $validated['created_at'] = Carbon::now();
              //  $validated['password'] = Hash::make($validated['password']);
                $userID = Member::insertGetId($validated);
                $userInfo = Member::where('id', '=', $userID)->first();   
                $accesstoken = $userInfo->createToken(uniqid())->plainTextToken;
                $userInfo->access_token = $accesstoken;
                Member::where('id', '=', $userID)->update(['access_token' => $accesstoken]);

                return response()->json([
                    'code' => 200,
                    'msg' => 'User Created Successfully',
                    'data' => $userInfo
                ], 200);
            }

            $accesstoken = $user->createToken(uniqid())->plainTextToken;
            $user->access_token = $accesstoken;
            Member::where('id', '=', $user->id)->update(['access_token' => $accesstoken]);

            return response()->json([
                'code' => 200,
                'msg' => 'Userlogged in Successfully',
                'data' => $user
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 500,
                'msg' => $th->getMessage()
            ], 500);
        }
    }

}
