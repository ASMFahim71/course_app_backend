<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;
use Stripe\Customer;
use Stripe\Price;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\Exception\UnexpectedValueException;
use Stripe\Exception\SignatureVerificationException;
use App\Models\Course;
use App\Models\Order;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function checkout(Request $request)
    {
        try {
            $courseId = $request->id;
            $user = $request->user();
            //key from stripe
            Stripe::setApiKey(env('STRIPE_SK'));

            $searchCourse = Course::where('id', '=', $courseId)->first();
            if (empty($searchCourse)) {
                return response()->json([
                    'code' => 204,
                    'msg' => 'Course not found',
                    'data' => ""
                ], 200);
            }


            $orderMap = [];
            $orderMap["course_id"] = $courseId;
            $orderMap["user_token"] = $user->token;
            $orderMap["status"] = 1;
            $orderRes = Order::where($orderMap)->first();
            //already have an order with same user and course_id
            if (!empty($orderRes)) {
                return response()->json([
                    'code' => 409,
                    'msg' => 'Already have an order with same user and course_id',
                    'data' => ""
                ], 200);
            }

            $your_domain = '';
            $map = [];
            $map['user_token'] = $user->token;
            $map['course_id'] = $courseId;
            $map['status'] = 0;
            $map['total_amount'] = $searchCourse->price;
            $map['created_at'] = Carbon::now();

            //we will get the order number
            $orderNum = Order::insertGetId($map);


            $checkOutSession = Session::create([
                'line_items' => [
                    [
                        'price_data' => [

                            'currency' => 'usd',
                            'product_data' => [
                                'name' => $searchCourse->name,
                                'description' => $searchCourse->description,

                            ],
                            'unit_amount' => intval($searchCourse->price * 100),



                        ],
                        'quantity' => 1,
                    ],

                ],
                'payment_intent_data' => [
                    'metadata' => [
                        'order_id' => $orderNum,
                        'user_token' => $user->token,

                    ]
                ],
                'metadata' => [
                    'order_id' => $orderNum,
                    'user_token' => $user->token,

                ],
                'mode' => 'payment',
                'success_url' => $your_domain . 'success',
                'cancel_url' => $your_domain . 'cancel',


            ]);
            return response()->json([
                'code' => 200,
                'msg' => 'Checkout Session Created',
                'data' => $checkOutSession->url
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'msg' => 'Lesson List Load Failed',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function webGoHooks(Request $request)
    {

        Log::info('starts here...');

        Stripe::setApiKey(env('STRIPE_SK'));
        $endPointSecret = env('STRIPE_WEBHOOK_SECRET');
        $payload = @file_get_contents('php://input'); //get the payload from the request
        $signHeader = $_SERVER['HTTP_STRIPE_SIGNATURE']; //get the signature from the request
        $event = null;
        Log::info('set up butt finished');
        try {

            $event = \Stripe\Webhook::constructEvent($payload, $signHeader, $endPointSecret);


        } catch (\UnexpectedValueException $e) {

            Log::info('UnexpectedValueException' . $e);
            http_response_code(400);
            exit();
        }catch(\Stripe\Exception\SignatureVerificationException $e){
            Log::info('SignatureVerificationException' . $e);
            http_response_code(400);
            exit(); 
        }
        if($event->type=='charge.succeeded'){
            $session=$event->data->object;

            $metadata=$session['metadata'];
            $orderNum=$metadata->order_id;
            $userToken=$metadata->user_token;
           Log::info('order id '.$orderNum);
           $map=[];
           $map['status']=1;
           $map['updated_at']=Carbon::now();
           $whereMap=[];
           $whereMap['user_token']=$userToken;
           $whereMap['id']=$orderNum;
           Order::where($whereMap)->update($map);



        }
        http_response_code(200);
    }
}
