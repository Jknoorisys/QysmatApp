<?php

namespace App\Http\Controllers\api\apple_pay;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BankDetails;
use App\Models\Charges;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\Subscriptions;
use App\Models\Transactions;
use App\Notifications\AdminNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class InAppSubscriptions extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userFound($_POST['login_id'], $_POST['user_type']);
        }
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
            'user_type' => [
                'required' ,
                Rule::in(['singleton','parent']),
            ],
            'plan_id' => [
                'required' ,
                Rule::in(['2','3']),
            ],
            'payment_method' => [
                'required' ,
                Rule::in(['stripe','in-app']),
            ],
            'amount' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $user_id = $request->login_id;
            $user_type = $request->user_type;
            $other_user_ids  = $request->other_user_id ? explode(',',$request->other_user_id) : null;
            $other_user_type = $request->other_user_type;
            $plan2 = Subscriptions::where('id', '=', '2')->first();
            $plan3 = Subscriptions::where('id', '=', '3')->first();

            $stripe_plan_id = $plan2 ? $plan2->stripe_plan_id : '';
            $stripe_joint_plan_id = $plan3 ? $plan3->stripe_plan_id : '';
            if (!$stripe_plan_id || !$stripe_joint_plan_id) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.stripe.session.failure'),
                ],400);
            }

            if ($user_type == 'singleton') {
                $user = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                $user_name = $user->name;
                $user_email = $user->email;
            } else {
                $user = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                $user_name = $user->name;
                $user_email = $user->email;
            }

            $success_url = url('api/apple/success');
            $cancel_url = url('api/apple/fail');

            if ($request->plan_id == 2) {
                $line_items = [
                    ['price' => $stripe_plan_id, 'quantity' => 1]
                ];
            } elseif ($request->plan_id == 3) {

                if ($request->user_type == 'singleton') {
                    $parent = ParentChild::leftJoin('parents','parent_children.parent_id','=','parents.id')
                                        ->where([['parent_children.singleton_id', '=', $request->login_id], ['parent_children.status','=','Linked']])
                                        ->where('parents.active_subscription_id', '!=', '1')
                                        ->first();
                    if (!empty($parent)) {
                        $line_items = [
                            [
                                'price' => $stripe_joint_plan_id,
                                'quantity' => 1
                            ]
                        ];
                    } else {
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.stripe.session.parent-not-premium'),
                        ],400);
                    }
                } elseif ($request->user_type == 'parent') {
                    $validator = Validator::make($request->all(), [
                        'other_user_id'   => ['required_if:plan_id,3' ,'required_if:user_type,parent'],
                        'other_user_type' => [
                            'required_if:plan_id,3' ,'required_if:user_type,parent',
                            Rule::in(['singleton']),
                        ],
                    ]);

                    if($validator->fails()){
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.Validation Failed!'),
                            'errors'    => $validator->errors()
                        ],400);
                    }

                    $line_items = [
                        ['price' => $stripe_plan_id, 'quantity' => 1],
                        [
                            'price' => $stripe_joint_plan_id,
                            'quantity' => count($other_user_ids)
                        ]
                    ];
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.stripe.session.failure'),
                    ],400);
                }

            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.stripe.session.invalid'),
                ],400);
            }

            if (!$line_items) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.stripe.session.failure'),
                ],400);
            }

            $session_id = 'in_app_'.Str::uuid();

            $sub_booking_data = [
                'session_id' => $session_id,
                'payment_method' => $request->payment_method,
                'user_id' => $user_id,
                'user_type' => $user_type,
                'user_name' => $user_name,
                'user_email' => $user_email,
                'other_user_id' => $request->other_user_id ? $request->other_user_id : '',
                'other_user_type' => $other_user_type ? $other_user_type : '',
                'active_subscription_id' => $request->plan_id,
                'currency' => env('STRIPE_CURRENCY'),
                'amount_paid' => $request->amount,
                'payment_status' => 'unpaid',
                'session_status' => 'open',
                'created_at' => Carbon::now()
            ];

            $data = [
                'session_id' => $session_id,
                'success_url' => $success_url,
                'cancel_url' => $cancel_url
            ];

            $booking_id = DB::table('bookings')->insertGetId($sub_booking_data);
            if($booking_id){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.apple.session.success'),
                    'data'      => $data
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.apple.session.failure'),
                ],400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function paymentSuccess(Request $request){

        $payload = $request->getContent();
        $payloadObject = json_decode($payload, true);
        return $payloadObject;
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'session_id'   => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try{
            $session_id = $request->session_id;
            $payment_details = DB::table('bookings')->where('session_id', '=', $session_id)->first();
            return $payment_details;
            if (!empty($payment_details)) {
                $user_id = $payment_details->user_id ? $payment_details->user_id : '';
                $user_type = $payment_details->user_type ? $payment_details->user_type : '';
                $user_name = $payment_details->user_name ? $payment_details->user_name : '';
                $user_email = $payment_details->user_email ? $payment_details->user_email : '';
                $other_user_id = $payment_details->other_user_id ? $payment_details->other_user_id : '';
                $other_user_ids = $other_user_id ? explode(',', $other_user_id) : null;
                $other_user_type = $payment_details->other_user_type ? $payment_details->other_user_type : '';
                $booking_id = $payment_details->id;
                $active_subscription_id = $payment_details->active_subscription_id;

                $session = \Stripe\Checkout\Session::Retrieve(
                    $payment_details->session_id,
                    []
                );

                if($session->payment_status == "paid" && $session->status == "complete"){
                    $subscription = \Stripe\Subscription::Retrieve($session->subscription);
                    $item1 = $subscription->items->data[0];
                    $item2 = count($subscription->items->data) == 2 ? $subscription->items->data[1] : null;

                    $update_booking  =  [
                        'subscription_id' => $session->subscription,
                        'customer_id' => $session->customer,
                        'amount_paid' => $session->amount_total/100,
                        'payment_status' => 'paid',
                        'session_status' => 'complete',
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    $update = DB::table('bookings')->where('id', '=', $payment_details->id)->update($update_booking);

                    $sub_data = [
                        'booking_id' => $booking_id,
                        'user_id' => $user_id,
                        'user_type' => $user_type,
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'other_user_id' => $other_user_id ? $other_user_id : '',
                        'other_user_type' => $other_user_type ? $other_user_type : '',
                        'active_subscription_id' => $active_subscription_id,
                        'customer_id' => $session->customer,
                        'subscription_id' => $session->subscription,
                        'subscription_item1_id' => $item1->id,
                        'subscription_item2_id' => $item2 ? $item2->id : '',
                        'item1_plan_id' => $item1->plan->id,
                        'item2_plan_id' => $item2 ? $item2->plan->id : '',
                        'item1_unit_amount' => $item1->plan->amount/100,
                        'item2_unit_amount' => $item2 ? $item2->plan->amount/100 : '',
                        'item1_quantity' => $item1->quantity,
                        'item2_quantity' => $item2 ? $item2->quantity : '',
                        'amount_paid' => $session->amount_total/100,
                        'currency' => $session->currency,
                        'plan_interval' => $item1->plan->interval,
                        'plan_interval_count' => $item1->plan->interval_count,
                        'payer_email' => $session->customer_details ? $session->customer_details->email : '',
                        'plan_period_start' => $subscription ? date("Y-m-d H:i:s", $subscription->current_period_start) : '',
                        'plan_period_end' => $subscription ? date("Y-m-d H:i:s", $subscription->current_period_end) : '',
                        'payment_status' => $session->payment_status,
                        'subs_status' => $subscription->status,
                        'created_at' => date("Y-m-d H:i:s", $subscription->created)
                    ];

                    $insert = DB::table('transactions')->insert($sub_data);
                    if($update){
                        $update_sub_data = [
                            'customer_id'            => $session->customer,
                            'active_subscription_id' => $active_subscription_id,
                            'stripe_plan_id'         => $item1->plan->id,
                            'subscription_item_id'   => $item1->id
                        ];

                        if ($user_type == 'singleton') {
                            Singleton::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                        } else {
                            ParentsModel::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                        }

                        $update_sub_data1 = [
                            'customer_id'            => $session->customer,
                            'active_subscription_id' => $active_subscription_id,
                            'stripe_plan_id'         => $item2 ? $item2->plan->id : $item1->plan->id,
                            'subscription_item_id'   => $item2 ? $item2->id : $item1->id
                        ];

                        if ($active_subscription_id == 3 && $other_user_id) {
                            if ($other_user_type == 'singleton') {
                                foreach ($other_user_ids as $id) {
                                    Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data1);
                                }
                            } elseif ($other_user_type == 'parent') {
                                foreach ($other_user_ids as $id) {
                                    ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data1);
                                }
                            }
                        }

                        $admin = Admin::find(1);
                        $details = [
                            'title' => __('msg.New Subscription'),
                            'msg'   => __('msg.has Subscribed.'),
                        ];
                        if ($user_type == 'singleton') {
                            $user = Singleton::where([['id','=',$user_id],['status','=','Unblocked']])->first();
                        } else {
                            $user = ParentsModel::where([['id','=',$user_id],['status','=','Unblocked']])->first();
                        }
                        $admin->notify(new AdminNotification($user, 'admin', 0, $details));
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.stripe.success'),
                        ],200);
                    }
                } else if ($session->payment_status == "unpaid" && $session->status == "open") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.apple.failure'),
                        'apple'  => [
                            'session_id'  => $session['id'],
                            'url'         => $session['url'],
                        ],
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.apple.failure'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.apple.invalid'),
                ],400);
            }
        }  catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function paymentFail(Request $request){
        die('subscription failed');
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'session_id'   => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try{
            $session_id = $request->session_id;
            $payment_details = DB::table('bookings')->where('session_id', '=', $session_id)->first();
            if (!empty($payment_details)) {
                $user_id = $payment_details->user_id ? $payment_details->user_id : '';
                $user_type = $payment_details->user_type ? $payment_details->user_type : '';
                $user_name = $payment_details->user_name ? $payment_details->user_name : '';
                $user_email = $payment_details->user_email ? $payment_details->user_email : '';
                $other_user_id = $payment_details->other_user_id ? $payment_details->other_user_id : '';
                $other_user_ids = $other_user_id ? explode(',', $other_user_id) : null;
                $other_user_type = $payment_details->other_user_type ? $payment_details->other_user_type : '';
                $booking_id = $payment_details->id;

                $session = \Stripe\Checkout\Session::Retrieve(
                    $payment_details->session_id,
                    []
                );
                if($session->status == "open"){
                    // $expire = $this->stripe->checkout->sessions->expire(
                    //     $payment_details->session_id,
                    //     []
                    // );

                    $update_booking  =  [
                        // 'active_subscription_id' => 1,
                        // 'payment_status'         => $expire->payment_status,
                        // 'session_status' => $expire->status,
                        // 'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    $update = DB::table('bookings')->where('id', '=', $payment_details->id)->update($update_booking);

                    $sub_data = [
                        'booking_id' => $booking_id,
                        'user_id' => $user_id,
                        'user_type' => $user_type,
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'other_user_id' => $other_user_id ? $other_user_id : '',
                        'other_user_type' => $other_user_type ? $other_user_type : '',
                        'active_subscription_id' => 1,
                        'payment_status' => $session->payment_status,
                        'subs_status' => 'inactive',
                        'created_at' => date('Y-m-d h:i:s')
                    ];

                    $insert = DB::table('transactions')->insert($sub_data);

                    if($update){
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.apple.failure'),
                        ],400);
                    }
                } else if ($session->status == "complete") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.apple.paid'),
                    ],400);
                } else if ($session->status == "expired") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.apple.invalid'),
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.apple.cancel'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.apple.invalid'),
                ],400);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function updateSubscription(Request $request)
    {
        // $payload = $request->getContent();
        // $payloadObject = json_decode($payload, true);
        // $data        = file_get_contents('php://input');
        // $isVar = json_decode($data, true );
        // $file1 =  'payload1.json';
        // Storage::disk('local')->put($file1, $isVar);
        // Storage::disk('local')->put($file1, $data);
        $payload = $request->getContent();
        $encoded_data = base64_decode($payload);
        $payloadObject = json_decode(base64_decode($payload), true);
        
        // Save the payload object as JSON data in a file
        $file = 'payload.json';
        // $data = json_encode($payloadObject);
        Storage::disk('local')->put('payload.json', $encoded_data);
        Storage::disk('local')->put('payload1.json', $payloadObject);

        return $payloadObject;
    }
}
