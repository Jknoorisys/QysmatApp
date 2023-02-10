<?php

namespace App\Http\Controllers\api\stripe;

use App\Http\Controllers\Controller;
use App\Models\BankDetails;
use App\Models\Charges;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\Subscriptions;
use App\Models\Transactions;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Carbon\Carbon;
use PDF;
use Dompdf\Adapter\PDFLib;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Stripe\Stripe;
use Symfony\Component\Mime\Part\TextPart;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class StripeSubscription extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userFound($_POST['login_id'], $_POST['user_type']);
        }

        $this->stripe = new \Stripe\StripeClient(
            env('STRIPE_SECRET_KEY')
          );
    }

    // public function index(Request $request)
    // {
    //     ini_set('memory_limit', '8G');

    //     $validator = Validator::make($request->all(), [
    //         'language' => [
    //             'required' ,
    //             Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
    //         ],
    //         'login_id'   => 'required||numeric',
    //         'user_type' => [
    //             'required' ,
    //             Rule::in(['singleton','parent']),
    //         ],
    //         'stripe_plan_id'   => 'required',
    //         'plan_id' => [
    //             'required' ,
    //             Rule::in(['1','2','3']),
    //         ],
    //         // 'stripe_token' => 'required',
    //         'payment_method'   => [
    //                 'required' ,
    //                 Rule::in(['stripe','in-app']),
    //             ],
    //         'stripe_email'    => 'required||email',
    //         // 'other_user_id'   => ['required_if:user_type,parent', 'required_if:plan_id,3'],
    //         // 'other_user_type' => [
    //         //     'required_if:plan_id,3' ,'required_if:user_type,parent',
    //         //     Rule::in(['singleton','parent']),
    //         // ],
    //     ]);

    //     if($validator->fails()){
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.Validation Failed!'),
    //             'errors'    => $validator->errors()
    //         ],400);
    //     }

    //     try {
    //         $stripe = Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
    //         // $token = $request->stripe_token;
    //         $email = $request->stripe_email;
    //         $user_id = $request->login_id;
    //         $user_type = $request->user_type;
    //         $other_user_ids  = $request->other_user_id ? explode(',',$request->other_user_id) : null;
    //         $other_user_type = $request->other_user_type;

    //         if ($user_type == 'singleton') {
    //             $user = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
    //             $user_name = $user->name;
    //             $user_email = $user->email;
    //         } else {
    //             $user = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
    //             $user_name = $user->name;
    //             $user_email = $user->email;
    //         }

    //         if (!empty($user->stripe_id) && $user->stripe_id != null) {
    //             $customer_id = $user->stripe_id;
    //         } else {
    //             $card = BankDetails::where([['user_id', '=', $user_id],['user_type', '=', $user_type]])->first();

    //             if (!empty($card)) {
    //                 $month_year = explode('/',$card->month_year,2);
    //                 $month = $month_year[0];
    //                 $year = $month_year[1];
    //                 $token = \Stripe\Token::create(array(
    //                     "card" => array(
    //                     "number" => $card->card_number,
    //                     "exp_month" => $month,
    //                     "exp_year" => $year,
    //                     "cvc" => $card->cvv,
    //                     'name'  => $card->card_holder_name,
    //                     )
    //                 ));

    //                 $customer = \Stripe\Customer::create([
    //                     'name'  => $user_name,
    //                     'email' => $email,
    //                     'phone'  => $user->mobile,
    //                     'source'  => $token,
    //                 ]);
    //                 $customer_id = $customer->id;
    //             }else {
    //                 return response()->json([
    //                     'status'    => 'no_card_available',
    //                     'message'   => __('msg.stripe.card'),
    //                 ],400);
    //             }
    //         }

    //         if ($request->plan_id == 3 && $request->user_type == 'parent') {
    //             $validator = Validator::make($request->all(), [
    //                 'other_user_id'   => ['required_if:plan_id,3' ,'required_if:user_type,parent'],
    //                 'other_user_type' => [
    //                     'required_if:plan_id,3' ,'required_if:user_type,parent',
    //                     Rule::in(['singleton']),
    //                 ],
    //             ]);

    //             if($validator->fails()){
    //                 return response()->json([
    //                     'status'    => 'failed',
    //                     'message'   => __('msg.Validation Failed!'),
    //                     'errors'    => $validator->errors()
    //                 ],400);
    //             }

    //             $quantity = count($other_user_ids);
    //             $subscription = \Stripe\Subscription::create(array(
    //                 "customer" =>  $customer_id,
    //                 "items" => array(
    //                     array(
    //                         "price" => $request->stripe_plan_id,
    //                         "quantity" => $quantity + 1
    //                     ),
    //                 ),
    //             ));
    //         }elseif ($request->plan_id == 3 && $request->user_type == 'singleton') {
    //             $parent = ParentChild::leftJoin('parents','parent_children.parent_id','=','parents.id')
    //                                     ->where([['parent_children.singleton_id', '=', $request->login_id], ['parent_children.status','=','Linked']])
    //                                     ->where('parents.active_subscription_id', '!=', '1')
    //                                     ->first();
    //             if (empty($parent)) {
    //                 $validator = Validator::make($request->all(), [
    //                     'other_user_id'   => ['required_if:plan_id,3' ,'required_if:user_type,parent'],
    //                     'other_user_type' => [
    //                         'required_if:plan_id,3' ,'required_if:user_type,parent',
    //                         Rule::in(['parent']),
    //                     ],
    //                 ]);

    //                 if($validator->fails()){
    //                     return response()->json([
    //                         'status'    => 'failed',
    //                         'message'   => __('msg.Validation Failed!'),
    //                         'errors'    => $validator->errors()
    //                     ],400);
    //                 }

    //                 $quantity = count($other_user_ids);
    //                 $subscription = \Stripe\Subscription::create(array(
    //                     "customer" =>  $customer_id,
    //                     "items" => array(
    //                         array(
    //                             "price" => $request->stripe_plan_id,
    //                             "quantity" => $quantity + 1
    //                         ),
    //                     ),
    //                 ));
    //             } else {
    //                 // $quantity = count($other_user_ids);
    //                 $subscription = \Stripe\Subscription::create(array(
    //                     "customer" =>  $customer_id,
    //                     "items" => array(
    //                         array(
    //                             "price" => $request->stripe_plan_id,
    //                             // "quantity" => $quantity
    //                         ),
    //                     ),
    //                 ));
    //             }
    //         }

    //         // if ($request->plan_id == 3) {
    //         //     $quantity = count($other_user_ids);
    //         //     $subscription = \Stripe\Subscription::create(array(
    //         //         "customer" =>  $customer_id,
    //         //         "items" => array(
    //         //             array(
    //         //                 "price" => $request->stripe_plan_id,
    //         //                 "quantity" => $quantity
    //         //             ),
    //         //         ),
    //         //     ));
    //         // }
    //         else {
    //             $subscription = \Stripe\Subscription::create(array(
    //                 "customer" =>  $customer_id,
    //                 "items" => array(
    //                     array(
    //                         "price" => $request->stripe_plan_id,
    //                     ),
    //                 ),
    //             ));
    //         }

    //         if (!empty($subscription)) {
    //             $status = $subscription->status;
    //             $stripe_subscription_id = $subscription->id;
    //             $stripe_customer_id = $subscription->customer;
    //             $stripe_plan_id = $subscription->plan->id;
    //             $plan_amount = $subscription->plan->amount;
    //             $plan_amount_currency = $subscription->plan->currency;
    //             $plan_interval = $subscription->plan->interval;
    //             $plan_interval_count = $subscription->plan->interval_count;
    //             $sub_created = $subscription->current_period_start;
    //             $plan_period_end = $subscription->current_period_end;
    //             $sub_convert_date = date('Y-m-d H:i:s', $sub_created);
    //             $plan_convert_date = date('Y-m-d H:i:s', $plan_period_end);

    //             $sub_booking_data = [
    //                 'user_id' => $user_id,
    //                 'user_type' => $user_type,
    //                 'user_name' => $user_name,
    //                 'user_email' => $user_email,
    //                 'other_user_id' => $request->other_user_id ? $request->other_user_id : '',
    //                 'other_user_type' => $other_user_type ? $other_user_type : '',
    //                 'payment_method' => "stripe",
    //                 'stripe_subscription_id' => $stripe_subscription_id,
    //                 'stripe_customer_id' => $stripe_customer_id,
    //                 'stripe_plan_id' => $stripe_plan_id,
    //                 'plan_amount' => $plan_amount,
    //                 'amount_paid' => $other_user_ids ? ($plan_amount * (count($other_user_ids) + 1) / 100)  : $plan_amount/100,
    //                 'plan_amount_currency' => $plan_amount_currency,
    //                 'plan_interval' => $plan_interval,
    //                 'payer_email' => $email,
    //                 'transaction_datetime' => $sub_convert_date,
    //                 'sub_created' => $sub_convert_date,
    //                 'plan_period_start' => $sub_convert_date,
    //                 'plan_period_end' => $plan_convert_date,
    //                 'status' => $status,
    //                 'created_at' => date('Y-m-d h:i:s')
    //             ];

    //             $booking_id = DB::table('bookings')->insertGetId($sub_booking_data);

    //             if ($status == 'active') {
    //                 $sub_data = [
    //                     'booking_id' => $booking_id,
    //                     'user_id' => $user_id,
    //                     'user_type' => $user_type,
    //                     'user_name' => $user_name,
    //                     'user_email' => $user_email,
    //                     'other_user_id' => $request->other_user_id ? $request->other_user_id : '',
    //                     'other_user_type' => $other_user_type ? $other_user_type : '',
    //                     'payment_method' => "stripe",
    //                     'stripe_subscription_id' => $stripe_subscription_id,
    //                     'stripe_customer_id' => $stripe_customer_id,
    //                     'stripe_plan_id' => $stripe_plan_id,
    //                     'plan_amount' => $plan_amount,
    //                     'amount_paid' => $other_user_ids ? ($plan_amount * (count($other_user_ids) + 1) / 100)  : $plan_amount/100,
    //                     'plan_amount_currency' => $plan_amount_currency,
    //                     'plan_interval' => $plan_interval,
    //                     'payer_email' => $email,
    //                     'transaction_datetime' => $sub_convert_date,
    //                     'sub_created' => $sub_convert_date,
    //                     'plan_period_start' => $sub_convert_date,
    //                     'plan_period_end' => $plan_convert_date,
    //                     'status' => $status,
    //                     'created_at' => date('Y-m-d h:i:s')
    //                 ];

    //                 $insert = DB::table('transactions')->insert($sub_data);

    //                 $update_sub_data = [
    //                     'stripe_id'              => $stripe_customer_id,
    //                     'active_subscription_id' => $request->plan_id,
    //                     'stripe_plan_id'         => $request->stripe_plan_id,
    //                 ];

    //                 if ($user_type == 'singleton') {
    //                     Singleton::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
    //                 } else {
    //                     ParentsModel::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
    //                 }

    //                 if ($request->plan_id == 3) {
    //                     if ($other_user_type == 'singleton') {
    //                         foreach ($other_user_ids as $id) {
    //                             Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
    //                         }
    //                     } elseif ($other_user_type == 'parent') {
    //                         foreach ($other_user_ids as $id) {
    //                             ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
    //                         }
    //                     }
    //                 }

    //                 return response()->json([
    //                     'status'    => 'success',
    //                     'message'   => __('msg.stripe.success'),
    //                 ],200);
    //                 // return $pdf->download('invoice.pdf');
    //             } else {
    //                 return response()->json([
    //                     'status'    => 'failed',
    //                     'message'   => __('msg.stripe.invalid'),
    //                 ],400);
    //             }
    //         }else{
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.stripe.failure'),
    //             ],400);
    //         }
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.error'),
    //             'error'     => $e->getMessage()
    //         ],500);
    //     }
    // }

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
            'stripe_plan_id'   => 'required',
            'plan_id' => [
                'required' ,
                Rule::in(['1','2','3']),
            ],
            // 'stripe_token' => 'required',
            'payment_method'   => [
                    'required' ,
                    Rule::in(['stripe','in-app']),
                ],
            'stripe_email'    => 'required||email',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $stripe = Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            // $token = $request->stripe_token;
            $email = $request->stripe_email;
            $user_id = $request->login_id;
            $user_type = $request->user_type;
            $other_user_ids  = $request->other_user_id ? explode(',',$request->other_user_id) : null;
            $other_user_type = $request->other_user_type;

            if ($user_type == 'singleton') {
                $user = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                $user_name = $user->name;
                $user_email = $user->email;
            } else {
                $user = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                $user_name = $user->name;
                $user_email = $user->email;
            }

            $success_url = 'http://127.0.0.1:8000/api/stripe/success';
            $cancel_url = 'http://127.0.0.1:8000/api/stripe/fail';

            if ($request->plan_id == 3 && $request->user_type == 'parent') {
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

                $qty = count($other_user_ids) + 1;
               
            }elseif ($request->plan_id == 3 && $request->user_type == 'singleton') {
                $parent = ParentChild::leftJoin('parents','parent_children.parent_id','=','parents.id')
                                        ->where([['parent_children.singleton_id', '=', $request->login_id], ['parent_children.status','=','Linked']])
                                        ->where('parents.active_subscription_id', '!=', '1')
                                        ->first();
                if (empty($parent)) {
                    $validator = Validator::make($request->all(), [
                        'other_user_id'   => ['required_if:plan_id,3' ,'required_if:user_type,parent'],
                        'other_user_type' => [
                            'required_if:plan_id,3' ,'required_if:user_type,parent',
                            Rule::in(['parent']),
                        ],
                    ]);

                    if($validator->fails()){
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.Validation Failed!'),
                            'errors'    => $validator->errors()
                        ],400);
                    }

                    $qty = count($other_user_ids) + 1;
                    
                } else {
                    $qty = 1;
                }
            } elseif ( $request->plan_id == 2 ){
                $qty = 1;
            }

            $session = \Stripe\Checkout\Session::Create([
                'success_url' => $success_url,
                'cancel_url' => $cancel_url,
                'payment_method_types' => ['card'],
                'line_items' => [
                  [
                    'price' => $request->stripe_plan_id,
                    'quantity' => $qty,
                  ],
                ],
                'mode' => 'subscription',
                'customer_email' => $email,
                'currency' => env('STRIPE_CURRENCY'),
              ]);
           
            $sub_booking_data = [
                'stripe_session_id' => $session->id,
                'user_id' => $user_id,
                'user_type' => $user_type,
                'user_name' => $user_name,
                'user_email' => $user_email,
                'other_user_id' => $request->other_user_id ? $request->other_user_id : '',
                'other_user_type' => $other_user_type ? $other_user_type : '',
                'payment_method' => "stripe",
                'active_subscription_id' => $request->plan_id,
                'stripe_subscription_id' => '',
                'quantity' => $qty,
                'stripe_customer_id' => '',
                'stripe_plan_id' => $request->stripe_plan_id,
                'plan_amount' => '',
                'amount_paid' => $session->amount_total/100,
                'plan_amount_currency' => $session->currency,
                'plan_interval' => 'monthly',
                'payer_email' => $session->customer_details ? $session->customer_details->email : '',
                'transaction_datetime' => Carbon::now(),
                'sub_created' => $session->created ? date("Y-m-d H:i:s", $session->created) : '',
                'plan_period_start' => $session->created ? date("Y-m-d H:i:s", $session->created) : '',
                'plan_period_end' => $session->expires_at ? date("Y-m-d H:i:s", $session->expires_at) : '',
                'status' => $session->payment_status,
                'session_status' => $session->status,
                'created_at' => date('Y-m-d h:i:s')
            ];

            $booking_id = DB::table('bookings')->insertGetId($sub_booking_data);
            if($booking_id){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.stripe.session.success'),
                    'data'      => $session
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.stripe.session.failure'),
                ],400);
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $err  = 'Status:' . $e->getHttpStatus() . '<br>';
            $err  .= 'Type:' . $e->getError()->type . '<br>';
            $err  .= 'Code:' . $e->getError()->code . '<br>';
            // param is '' in this case
            $err  .= 'Param:' . $e->getError()->param . '<br>';
            $err  .= 'Message:' . $e->getError()->message . '<br>';
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $err
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
             return response()->json([
                 'status'    => 'failed',
                 'message'   => __('msg.error'),
                 'error'     => $e->getMessage()
             ],500);
         } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function paymentSuccess(Request $request){
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
            $stripe = Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            $session_id = $request->session_id;
            $payment_details = DB::table('bookings')->where('stripe_session_id', '=', $session_id)->first();
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
                    $payment_details->stripe_session_id,
                    []
                );
                
                if($session->payment_status == "paid" && $session->status == "complete"){
                    $subscription = \Stripe\Subscription::Retrieve($session->subscription);

                    $update_booking  =  [
                        'stripe_subscription_id' => $session->subscription,
                        'stripe_customer_id' => $session->customer,
                        'stripe_plan_id' => $payment_details->stripe_plan_id,
                        'plan_amount' => $subscription->plan->amount/100,
                        'amount_paid' => $session->amount_total/100,
                        'quantity' => $subscription->quantity,
                        'plan_interval' => $subscription->plan->interval,
                        'plan_interval_count' => $subscription->plan->interval_count,
                        'sub_created' => $subscription->created ? date("Y-m-d H:i:s", $subscription->created) : '',
                        'plan_period_start' => $subscription ? date("Y-m-d H:i:s", $subscription->current_period_start) : '',
                        'plan_period_end' => $subscription ? date("Y-m-d H:i:s", $subscription->current_period_end) : '',
                        'status'         => $session->payment_status,
                        'session_status' => $session->status,
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
                        'payment_method' => "stripe",
                        'active_subscription_id' => $active_subscription_id,
                        'stripe_subscription_id' => $session->subscription,
                        'stripe_customer_id' => $session->customer,
                        'stripe_plan_id' => $payment_details->stripe_plan_id,
                        'plan_amount' => $subscription->plan->amount/100,
                        'amount_paid' => $session->amount_total/100,
                        'quantity' => $subscription->quantity,
                        'plan_amount_currency' => $session->currency,
                        'plan_interval' => $subscription->plan->interval,
                        'plan_interval_count' => $subscription->plan->interval_count,
                        'payer_email' => $session->customer_details ? $session->customer_details->email : '',
                        'transaction_datetime' => Carbon::now(),
                        'sub_created' => $subscription->created ? date("Y-m-d H:i:s", $subscription->created) : '',
                        'plan_period_start' => $subscription ? date("Y-m-d H:i:s", $subscription->current_period_start) : '',
                        'plan_period_end' => $subscription ? date("Y-m-d H:i:s", $subscription->current_period_end) : '',
                        'payment_status' => $session->payment_status,
                        'status' => $subscription->status,
                        'created_at' => date('Y-m-d h:i:s')
                    ];
                    
                    $insert = DB::table('transactions')->insert($sub_data);
                    
                    if($update){
                        $update_sub_data = [
                            'stripe_id'              => $session['customer'],
                            'active_subscription_id' => $active_subscription_id,
                            'stripe_plan_id'         => $payment_details->stripe_plan_id,
                        ];
    
                        if ($user_type == 'singleton') {
                            Singleton::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                        } else {
                            ParentsModel::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                        }
    
                        if ($active_subscription_id == 3 && $other_user_id) {
                            if ($other_user_type == 'singleton') {
                                foreach ($other_user_ids as $id) {
                                    Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                                }
                            } elseif ($other_user_type == 'parent') {
                                foreach ($other_user_ids as $id) {
                                    ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                                }
                            }
                        }

                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.stripe.success'),
                        ],200);
                    }
                } else if ($session->payment_status == "unpaid" && $session->status == "open") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.stripe.failure'),
                        'stripe'  => [
                            'session_id'  => $session['id'],
                            'url'         => $session['url'],
                        ],
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.stripe.failure'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.stripe.invalid'),
                ],400);
            }
        }  catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $err  = 'Status:' . $e->getHttpStatus() . '<br>';
            $err  .= 'Type:' . $e->getError()->type . '<br>';
            $err  .= 'Code:' . $e->getError()->code . '<br>';
            // param is '' in this case
            $err  .= 'Param:' . $e->getError()->param . '<br>';
            $err  .= 'Message:' . $e->getError()->message . '<br>';
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $err
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
           // Network communication with Stripe failed
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function paymentFail(Request $request){
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
            $stripe = Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            $session_id = $request->session_id;
            $payment_details = DB::table('bookings')->where('stripe_session_id', '=', $session_id)->first();
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
                    $payment_details->stripe_session_id,
                    []
                );
                if($session->status == "open"){
                    $expire = $this->stripe->checkout->sessions->expire(
                        $payment_details->stripe_session_id,
                        []
                    );

                    $update_booking  =  [
                        'active_subscription_id' => 1,
                        'status'         => $expire->payment_status,
                        'session_status' => $expire->status,
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    $update = DB::table('bookings')->where('id', '=', $payment_details->id)->update($update_booking);

                    $sub_data = [
                        'booking_id' => $booking_id,
                        'user_id' => $user_id,
                        'user_type' => $user_type,
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'other_user_id' => $request->other_user_id ? $request->other_user_id : '',
                        'other_user_type' => $other_user_type ? $other_user_type : '',
                        'payment_method' => "stripe",
                        'active_subscription_id' => 1,
                        'stripe_plan_id' => $payment_details->stripe_plan_id,
                        'plan_amount' => $payment_details->plan_amount,
                        'plan_amount_currency' => $session->currency,
                        'transaction_datetime' => Carbon::now(),
                        'status' => $session->payment_status,
                        'created_at' => date('Y-m-d h:i:s')
                    ];
                    
                    $insert = DB::table('transactions')->insert($sub_data);
                    
                    if($update){
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.stripe.failure'),
                        ],400);
                    }
                } else if ($session->status == "complete") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.stripe.paid'),
                    ],400);
                } else if ($session->status == "expired") {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.stripe.invalid'),
                    ],400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.stripe.cancel'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.stripe.invalid'),
                ],400);
            }
        } catch (\Stripe\Exception\CardException $e) {
            // Since it's a decline, \Stripe\Exception\CardException will be caught
            $err  = 'Status:' . $e->getHttpStatus() . '<br>';
            $err  .= 'Type:' . $e->getError()->type . '<br>';
            $err  .= 'Code:' . $e->getError()->code . '<br>';
            // param is '' in this case
            $err  .= 'Param:' . $e->getError()->param . '<br>';
            $err  .= 'Message:' . $e->getError()->message . '<br>';
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $err
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
            // $this->session->set_flashdata('error',  $err);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters were supplied to Stripe's API
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            // (maybe you changed API keys recently)
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Stripe\Exception\ApiConnectionException $e) {
           // Network communication with Stripe failed
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function webhookHandler(Request $request)
    {

        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

        $payload = $request->getContent();
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
        // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
            http_response_code(400);
            exit();
        }

        // Handle the event
        // switch ($event->type) {
        //     case 'charge.failed':
        //         $paymentMethod = $event->data->object;
        //         $charge_id = $paymentMethod->id;
        //         $object = $paymentMethod->object;
        //         $customer_id = $paymentMethod->customer;
        //         $balance_transaction = $paymentMethod->balance_transaction;
        //         $amount_captured     = $paymentMethod->amount_captured;
        //         $name     = $paymentMethod->billing_details->name;
        //         $currency = $paymentMethod->currency;
        //         $charge_time     = $paymentMethod->created;
        //         $sub_convert_date = date('Y-m-d H:i:s', $charge_time);
        //         $description     = $paymentMethod->description;
        //         $invoice = $paymentMethod->invoice;
        //         $paid_status = $paymentMethod->paid;
        //         $ayment_intent = $paymentMethod->payment_intent;
        //         $payment_method = $paymentMethod->payment_method;
        //         $card_brand = $paymentMethod->payment_method_details->card->brand;
        //         $country = $paymentMethod->payment_method_details->card->country;
        //         $exp_month = $paymentMethod->payment_method_details->card->exp_month;
        //         $exp_year = $paymentMethod->payment_method_details->card->exp_year;
        //         $funding = $paymentMethod->payment_method_details->card->funding;
        //         $last4 = $paymentMethod->payment_method_details->card->last4;
        //         $network = $paymentMethod->payment_method_details->card->network;
        //         $card_type = $paymentMethod->payment_method_details->type;
        //         $paid_status = $paymentMethod->paid;
        //         $status = $paymentMethod->status;
        //         $seller_message = $paymentMethod->outcome['seller_message'];
        //         $charge_data = [
        //             'charge_id' => $charge_id,
        //             'object' => $object,
        //             'charge_customer_id' => $customer_id,
        //             'balance_transaction' => $balance_transaction,
        //             'plan_amount' => $amount_captured,
        //             'payer_email' => $name,
        //             'plan_amount_currency' => $currency,
        //             'charge_create' => $sub_convert_date,
        //             'charge_currency' => $currency,
        //             'charge_description' => $description,
        //             'charge_invoice' => $invoice,
        //             'seller_message' => $seller_message,
        //             'payment_intent' => $ayment_intent,
        //             'payment_method' => $payment_method,
        //             'paid_status' => $paid_status,
        //             'charge_country' => $country,
        //             'exp_month' => $exp_month,
        //             'funding' => $funding,
        //             'last4' => $last4,
        //             'network' => $network,
        //             'type'=> $card_type,
        //             'status'=> $status,
        //             'updated_at' => date('Y-m-d h:i:s')
        //         ];

        //         $query = Charges::insert($charge_data);


        //         if($status == 'failed')
        //         {
        //             $user_sub_data= Transactions::where('stripe_customer_id', $customer_id)->first();

        //             $user_id   = $user_sub_data->user_id;
        //             $user_type = $user_sub_data->user_type;
        //             $user_name = $user_sub_data->user_name;
        //             $user_email = $user_sub_data->user_email;
        //             $other_user_id = $user_sub_data->other_user_id;
        //             $other_user_type = $user_sub_data->other_user_type; 
        //             $sub_table_id = $user_sub_data->id;

        //             $update_sub_data = ['active_subscription_id' => '1'];

        //             if ($user_type == 'singleton') {
        //                 $update = Singleton::where([['id','=',$user_id],['stripe_id','=', $customer_id],['status','=','Unblocked']])->update($update_sub_data);
        //             } else {
        //                 $update = ParentsModel::where([['id','=',$user_id],['stripe_id','=', $customer_id],['status','=','Unblocked']])->update($update_sub_data);
        //             }

        //             if ($other_user_id) {
        //                 $other_user_ids  = explode(',',$other_user_id);
        //                 if ($other_user_type == 'singleton') {
        //                     foreach ($other_user_ids as $id) {
        //                         Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
        //                     }
        //                 } else {
        //                     foreach ($other_user_ids as $id) {
        //                         ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
        //                     }
        //                 }
        //             }

        //             if($update)
        //             {
        //                 $inactive = ['status' => 'inactive'];
        //                 Transactions::where('id', '=',  $sub_table_id)->update($inactive);
        //             }
        //         }
        //         // echo json_encode($charge_data);die();
        //         break;
        //     case 'charge.succeeded':
        //         $paymentMethod = $event->data->object;
        //         $charge_id = $paymentMethod->id;
        //         $object = $paymentMethod->object;
        //         $customer_id = $paymentMethod->customer;
        //         $balance_transaction = $paymentMethod->balance_transaction;
        //         $amount_captured     = $paymentMethod->amount_captured;
        //         $name     = $paymentMethod->billing_details->name;
        //         $currency = $paymentMethod->currency;
        //         $charge_time     = $paymentMethod->created;
        //         $sub_convert_date = date('Y-m-d H:i:s', $charge_time);
        //         $description     = $paymentMethod->description;
        //         $invoice = $paymentMethod->invoice;
        //         $paid_status = $paymentMethod->paid;
        //         $ayment_intent = $paymentMethod->payment_intent;
        //         $payment_method = $paymentMethod->payment_method;
        //         $card_brand = $paymentMethod->payment_method_details->card->brand;
        //         $country = $paymentMethod->payment_method_details->card->country;
        //         $exp_month = $paymentMethod->payment_method_details->card->exp_month;
        //         $exp_year = $paymentMethod->payment_method_details->card->exp_year;
        //         $funding = $paymentMethod->payment_method_details->card->funding;
        //         $last4 = $paymentMethod->payment_method_details->card->last4;
        //         $network = $paymentMethod->payment_method_details->card->network;
        //         $card_type = $paymentMethod->payment_method_details->type;
        //         $paid_status = $paymentMethod->paid;
        //         $status = $paymentMethod->status;
        //         $seller_message = $paymentMethod->outcome['seller_message'];

        //         if ($status == 'succeeded') {
        //             $charge_data = [
        //                 'charge_id' => $charge_id,
        //                 'object' => $object,
        //                 'charge_customer_id' => $customer_id,
        //                 'balance_transaction' => $balance_transaction,
        //                 'plan_amount' => $amount_captured,
        //                 'payer_email' => $name,
        //                 'plan_amount_currency' => $currency,
        //                 'charge_create' => $sub_convert_date,
        //                 'charge_currency' => $currency,
        //                 'charge_description' => $description,
        //                 'charge_invoice' => $invoice,
        //                 'seller_message' => $seller_message,
        //                 'payment_intent' => $ayment_intent,
        //                 'payment_method' => $payment_method,
        //                 'paid_status' => $paid_status,
        //                 'charge_country' => $country,
        //                 'exp_month' => $exp_month,
        //                 'funding' => $funding,
        //                 'last4' => $last4,
        //                 'network' => $network,
        //                 'type'=> $card_type,
        //                 'status'=> $status,
        //                 'created_at' => date('Y-m-d h:i:s')
        //             ];
        //             $query = Charges::insert($charge_data);
        //         }

        //         break;
        //     case 'subscription_schedule.created':
        //         $externalAccount = $event->data->object;
        //     case 'account.updated':
        //         $account = $event->data->object;
        //     case 'customer.created':
        //         $customer = $event->data->object;
        //     case 'customer.deleted':
        //         $customer = $event->data->object;
        //     case 'customer.updated':
        //         $customer = $event->data->object;
        //     case 'customer.subscription.created':
        //         $subscription = $event->data->object;
        //     case 'customer.subscription.deleted':
        //         $subscription = $event->data->object;
        //     case 'customer.subscription.updated':
        //         $paymentMethod = $event->data->object;
        //         $sub_id = $paymentMethod->id;
        //         $status = $paymentMethod->status;
        //         $des_obj = $paymentMethod->object;
        //         $sub_created = $paymentMethod->created;
        //         $sub_period_start = $paymentMethod->current_period_start;
        //         $plan_period_end = $paymentMethod->current_period_end;
        //         $sub_convert_date = date('Y-m-d H:i:s', $sub_period_start);
        //         $plan_convert_date = date('Y-m-d H:i:s', $plan_period_end);
        //         $customer_id = $paymentMethod->customer;
        //         $plan_id = $paymentMethod->plan->id;
        //         $plan_amount = $paymentMethod->plan->amount;
        //         $plan_amount_currency = $paymentMethod->plan->currency;
        //         $plan_interval = $paymentMethod->plan->interval;
        //         $plan_interval_count = $paymentMethod->plan->interval_count;

        //         $user_sub_data= Transactions::where('stripe_subscription_id', $sub_id)->first();

        //         $sub_table_id = $user_sub_data->id;
        //         $user_id   = $user_sub_data->user_id;
        //         $user_type = $user_sub_data->user_type;
        //         $user_name = $user_sub_data->user_name;
        //         $user_email = $user_sub_data->user_email;
        //         $other_user_id = $user_sub_data->other_user_id;
        //         $other_user_type = $user_sub_data->other_user_type;

        //         $sub_master_data = [
        //             'user_id' => $user_id,
        //             'user_type' => $user_type,
        //             'user_name' => $user_name,
        //             'user_email' => $user_email,
        //             'other_user_id' => $other_user_id ? $other_user_id : '',
        //             'other_user_type' => $other_user_type ? $other_user_type : '',
        //             'payment_method' => "stripe",
        //             'stripe_subscription_id' => $sub_id,
        //             'stripe_customer_id' => $customer_id,
        //             'stripe_plan_id' => $plan_id,
        //             'plan_amount' => $plan_amount,
        //             'plan_amount_currency' => $plan_amount_currency,
        //             'plan_interval' => $plan_interval,
        //             'plan_interval_count' => $plan_interval_count,
        //             'transaction_datetime' => $sub_convert_date,
        //             'sub_created' => date('Y-m-d H:i:s', $sub_created),
        //             'plan_period_start' => $sub_convert_date,
        //             'plan_period_end' => $plan_convert_date,
        //             'status' => $status,
        //             'updated_at' => date('Y-m-d h:i:s')
        //         ];

        //         $query = DB::table('bookings')->insert($sub_master_data);

        //         if ($status == 'active') {
        //             $update_status = [
        //                 'plan_period_start' => $sub_convert_date,
        //                 'plan_period_end' => $plan_convert_date,
        //                 'updated_at' => date('Y-m-d h:i:s'),
        //             ];

        //             $update_time = Transactions::where('id', '=',  $sub_table_id)->update($update_status);
        //         }else{
        //             $update_sub_data = ['active_subscription_id' => '1'];

        //             if ($user_type == 'singleton') {
        //                $update = Singleton::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
        //             } else {
        //                 $update = ParentsModel::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
        //             }

        //             if ($other_user_id) {
        //                 $other_user_ids  = explode(',',$other_user_id);
        //                 if ($other_user_type == 'singleton') {
        //                     foreach ($other_user_ids as $id) {
        //                         Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
        //                     }
        //                 } else {
        //                     foreach ($other_user_ids as $id) {
        //                         ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
        //                     }
        //                 }
        //             }

        //             if($update)
        //             {
        //                 $inactive = ['status' => 'inactive'];
        //                 Transactions::where('id', '=',  $sub_table_id)->update($inactive);
        //             }
        //         }
        //         break;
        //     case 'invoice.created':
        //         $invoice = $event->data->object;
        //     case 'invoice.deleted':
        //         $invoice = $event->data->object;
        //     case 'invoice.finalization_failed':
        //         $invoice = $event->data->object;
        //     case 'invoice.finalized':
        //         $invoice = $event->data->object;
        //     case 'invoice.paid':
        //         $invoice = $event->data->object;
        //         generateInvoicePdf($invoice);
        //         break;
        //     case 'invoice.sent':
        //         $invoice = $event->data->object;
        //     case 'invoice.upcoming':
        //         $invoice = $event->data->object;
        //     case 'invoice.updated':
        //         $invoice = $event->data->object;
        //     case 'checkout.session.async_payment_succeeded':
        //         $invoice = $event->data->object;
        //     case 'checkout.session.async_payment_failed':
        //         $invoice = $event->data->object;
        //     case 'checkout.session.completed':
        //         $invoice = $event->data->object;
        //     case 'checkout.session.expired':
        //         $invoice = $event->data->object;
        //     case 'payment_intent.succeeded':
        //         $paymentIntent = $event->data->object;
        //     case 'payment_method.attached':
        //         $paymentMethod = $event->data->object;
        //     case 'payment_intent.created':
        //         $paymentIntent = $event->data->object;
        //         case 'checkout.session.expired':
        //             $invoice = $event->data->object;
        //     // ... handle other event types
        //     default:
        //         echo 'Received unknown event type ' . $event->type;
        // }

        switch ($event->type) {
            case 'charge.failed':
                $paymentMethod = $event->data->object;
                $charge_id = $paymentMethod->id;
                $object = $paymentMethod->object;
                $customer_id = $paymentMethod->customer;
                $balance_transaction = $paymentMethod->balance_transaction;
                $amount_captured     = $paymentMethod->amount_captured;
                $name     = $paymentMethod->billing_details->name;
                $currency = $paymentMethod->currency;
                $charge_time     = $paymentMethod->created;
                $sub_convert_date = date('Y-m-d H:i:s', $charge_time);
                $description     = $paymentMethod->description;
                $invoice = $paymentMethod->invoice;
                $paid_status = $paymentMethod->paid;
                $ayment_intent = $paymentMethod->payment_intent;
                $payment_method = $paymentMethod->payment_method;
                $card_brand = $paymentMethod->payment_method_details->card->brand;
                $country = $paymentMethod->payment_method_details->card->country;
                $exp_month = $paymentMethod->payment_method_details->card->exp_month;
                $exp_year = $paymentMethod->payment_method_details->card->exp_year;
                $funding = $paymentMethod->payment_method_details->card->funding;
                $last4 = $paymentMethod->payment_method_details->card->last4;
                $network = $paymentMethod->payment_method_details->card->network;
                $card_type = $paymentMethod->payment_method_details->type;
                $paid_status = $paymentMethod->paid;
                $status = $paymentMethod->status;
                $seller_message = $paymentMethod->outcome['seller_message'];
                $charge_data = [
                    'charge_id' => $charge_id,
                    'object' => $object,
                    'charge_customer_id' => $customer_id,
                    'balance_transaction' => $balance_transaction,
                    'plan_amount' => $amount_captured,
                    'payer_email' => $name,
                    'plan_amount_currency' => $currency,
                    'charge_create' => $sub_convert_date,
                    'charge_currency' => $currency,
                    'charge_description' => $description,
                    'charge_invoice' => $invoice,
                    'seller_message' => $seller_message,
                    'payment_intent' => $ayment_intent,
                    'payment_method' => $payment_method,
                    'paid_status' => $paid_status,
                    'charge_country' => $country,
                    'exp_month' => $exp_month,
                    'funding' => $funding,
                    'last4' => $last4,
                    'network' => $network,
                    'type'=> $card_type,
                    'status'=> $status,
                    'updated_at' => date('Y-m-d h:i:s')
                ];

                $query = Charges::insert($charge_data);


                if($status == 'failed')
                {
                    $user_sub_data= Transactions::where('stripe_customer_id', $customer_id)->first();

                    $user_id   = $user_sub_data->user_id;
                    $user_type = $user_sub_data->user_type;
                    $user_name = $user_sub_data->user_name;
                    $user_email = $user_sub_data->user_email;
                    $other_user_id = $user_sub_data->other_user_id;
                    $other_user_type = $user_sub_data->other_user_type; 
                    $sub_table_id = $user_sub_data->id;

                    $update_sub_data = ['active_subscription_id' => '1'];

                    if ($user_type == 'singleton') {
                        $update = Singleton::where([['id','=',$user_id],['stripe_id','=', $customer_id],['status','=','Unblocked']])->update($update_sub_data);
                    } else {
                        $update = ParentsModel::where([['id','=',$user_id],['stripe_id','=', $customer_id],['status','=','Unblocked']])->update($update_sub_data);
                    }

                    if ($other_user_id) {
                        $other_user_ids  = explode(',',$other_user_id);
                        if ($other_user_type == 'singleton') {
                            foreach ($other_user_ids as $id) {
                                Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                            }
                        } else {
                            foreach ($other_user_ids as $id) {
                                ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                            }
                        }
                    }

                    if($update)
                    {
                        $inactive = ['status' => 'inactive'];
                        Transactions::where('id', '=',  $sub_table_id)->update($inactive);
                    }
                }
                // echo json_encode($charge_data);die();
                break;
            case 'charge.succeeded':
                $paymentMethod = $event->data->object;
                $charge_id = $paymentMethod->id;
                $object = $paymentMethod->object;
                $customer_id = $paymentMethod->customer;
                $balance_transaction = $paymentMethod->balance_transaction;
                $amount_captured     = $paymentMethod->amount_captured;
                $name     = $paymentMethod->billing_details->name;
                $currency = $paymentMethod->currency;
                $charge_time     = $paymentMethod->created;
                $sub_convert_date = date('Y-m-d H:i:s', $charge_time);
                $description     = $paymentMethod->description;
                $invoice = $paymentMethod->invoice;
                $paid_status = $paymentMethod->paid;
                $ayment_intent = $paymentMethod->payment_intent;
                $payment_method = $paymentMethod->payment_method;
                $card_brand = $paymentMethod->payment_method_details->card->brand;
                $country = $paymentMethod->payment_method_details->card->country;
                $exp_month = $paymentMethod->payment_method_details->card->exp_month;
                $exp_year = $paymentMethod->payment_method_details->card->exp_year;
                $funding = $paymentMethod->payment_method_details->card->funding;
                $last4 = $paymentMethod->payment_method_details->card->last4;
                $network = $paymentMethod->payment_method_details->card->network;
                $card_type = $paymentMethod->payment_method_details->type;
                $paid_status = $paymentMethod->paid;
                $status = $paymentMethod->status;
                $seller_message = $paymentMethod->outcome['seller_message'];

                if ($status == 'succeeded') {
                    $charge_data = [
                        'charge_id' => $charge_id,
                        'object' => $object,
                        'charge_customer_id' => $customer_id,
                        'balance_transaction' => $balance_transaction,
                        'plan_amount' => $amount_captured,
                        'payer_email' => $name,
                        'plan_amount_currency' => $currency,
                        'charge_create' => $sub_convert_date,
                        'charge_currency' => $currency,
                        'charge_description' => $description,
                        'charge_invoice' => $invoice,
                        'seller_message' => $seller_message,
                        'payment_intent' => $ayment_intent,
                        'payment_method' => $payment_method,
                        'paid_status' => $paid_status,
                        'charge_country' => $country,
                        'exp_month' => $exp_month,
                        'funding' => $funding,
                        'last4' => $last4,
                        'network' => $network,
                        'type'=> $card_type,
                        'status'=> $status,
                        'created_at' => date('Y-m-d h:i:s')
                    ];
                    $query = Charges::insert($charge_data);
                }
                break;
            case 'customer.subscription.updated':
                $paymentMethod = $event->data->object;
                $sub_id = $paymentMethod->id;
                $status = $paymentMethod->status;
                $des_obj = $paymentMethod->object;
                $sub_created = $paymentMethod->created;
                $sub_period_start = $paymentMethod->current_period_start;
                $plan_period_end = $paymentMethod->current_period_end;
                $sub_convert_date = date('Y-m-d H:i:s', $sub_period_start);
                $plan_convert_date = date('Y-m-d H:i:s', $plan_period_end);
                $customer_id = $paymentMethod->customer;
                $plan_id = $paymentMethod->plan->id;
                $plan_amount = $paymentMethod->plan->amount;
                $plan_amount_currency = $paymentMethod->plan->currency;
                $plan_interval = $paymentMethod->plan->interval;
                $plan_interval_count = $paymentMethod->plan->interval_count;

                $user_sub_data= Transactions::where('stripe_subscription_id', $sub_id)->first();

                $sub_table_id = $user_sub_data->id;
                $user_id   = $user_sub_data->user_id;
                $user_type = $user_sub_data->user_type;
                $user_name = $user_sub_data->user_name;
                $user_email = $user_sub_data->user_email;
                $other_user_id = $user_sub_data->other_user_id;
                $other_user_type = $user_sub_data->other_user_type;
                $active_subscription_id = $user_sub_data->active_subscription_id;

                $sub_master_data = [
                    'user_id' => $user_id,
                    'user_type' => $user_type,
                    'user_name' => $user_name,
                    'user_email' => $user_email,
                    'other_user_id' => $other_user_id ? $other_user_id : '',
                    'other_user_type' => $other_user_type ? $other_user_type : '',
                    'payment_method' => "stripe",
                    'active_subscription_id' => $active_subscription_id,
                    'stripe_subscription_id' => $sub_id,
                    'stripe_customer_id' => $customer_id,
                    'stripe_plan_id' => $plan_id,
                    'plan_amount' => $plan_amount,
                    'plan_amount_currency' => $plan_amount_currency,
                    'plan_interval' => $plan_interval,
                    'plan_interval_count' => $plan_interval_count,
                    'transaction_datetime' => $sub_convert_date,
                    'sub_created' => date('Y-m-d H:i:s', $sub_created),
                    'plan_period_start' => $sub_convert_date,
                    'plan_period_end' => $plan_convert_date,
                    'status' => $status,
                    'updated_at' => date('Y-m-d h:i:s')
                ];

                $query = DB::table('bookings')->insert($sub_master_data);

                if ($status == 'active') {
                    $update_status = [
                        'plan_period_start' => $sub_convert_date,
                        'plan_period_end' => $plan_convert_date,
                        'updated_at' => date('Y-m-d h:i:s'),
                    ];

                    $update_time = Transactions::where('id', '=',  $sub_table_id)->update($update_status);
                }else{
                    $update_sub_data = ['active_subscription_id' => '1'];

                    if ($user_type == 'singleton') {
                       $update = Singleton::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                    } else {
                        $update = ParentsModel::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                    }

                    if ($other_user_id) {
                        $other_user_ids  = explode(',',$other_user_id);
                        if ($other_user_type == 'singleton') {
                            foreach ($other_user_ids as $id) {
                                Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                            }
                        } else {
                            foreach ($other_user_ids as $id) {
                                ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                            }
                        }
                    }

                    if($update)
                    {
                        $inactive = ['status' => 'inactive'];
                        Transactions::where('id', '=',  $sub_table_id)->update($inactive);
                    }
                }
                break;
            case 'payment_intent.succeeded':
                $paymentIntent = $event->data->object;
            case 'payment_intent.canceled':
                $paymentIntent = $event->data->object;
            case 'payment_intent.created':
                $paymentIntent = $event->data->object;
            case 'invoice.paid':
                $invoice = $event->data->object;
                generateInvoicePdf($invoice);
                break;
            case 'account.updated':
                $account = $event->data->object;
            case 'charge.captured':
                $charge = $event->data->object;
            case 'charge.expired':
                $charge = $event->data->object;
            case 'charge.pending':
                $charge = $event->data->object;
            case 'charge.refunded':
                $charge = $event->data->object;    
            case 'charge.updated':
                $charge = $event->data->object;
            case 'checkout.session.completed':
                $session = $event->data->object;
            case 'checkout.session.expired':
                $session = $event->data->object;
            case 'customer.created':
                $customer = $event->data->object;
            case 'customer.deleted':
                $customer = $event->data->object;
            case 'customer.updated':
                $customer = $event->data->object;
            case 'customer.subscription.created':
                $subscription = $event->data->object;
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
            case 'customer.subscription.paused':
                $subscription = $event->data->object;
            case 'customer.subscription.resumed':
                $subscription = $event->data->object;
            case 'invoice.created':
                $invoice = $event->data->object;
            case 'invoice.deleted':
                $invoice = $event->data->object;
            case 'invoice.finalization_failed':
                $invoice = $event->data->object;
            case 'invoice.finalized':
                $invoice = $event->data->object;
            case 'invoice.sent':
                $invoice = $event->data->object;
            case 'invoice.updated':
                $invoice = $event->data->object;
            case 'payment_intent.processing':
                $paymentIntent = $event->data->object;
            case 'payment_intent.requires_action':
                $paymentIntent = $event->data->object;
            case 'payment_method.attached':
                $paymentMethod = $event->data->object;
            case 'payment_method.detached':
                $paymentMethod = $event->data->object;
            case 'payment_method.updated':
                $paymentMethod = $event->data->object;
            case 'refund.created':
                $refund = $event->data->object;
            case 'refund.updated':
                $refund = $event->data->object;
            case 'subscription_schedule.canceled':
                $subscriptionSchedule = $event->data->object;
            case 'subscription_schedule.completed':
                $subscriptionSchedule = $event->data->object;
            case 'subscription_schedule.created':
                $subscriptionSchedule = $event->data->object;
            case 'subscription_schedule.expiring':
                $subscriptionSchedule = $event->data->object;
            case 'subscription_schedule.updated':
                $subscriptionSchedule = $event->data->object;
            // ... handle other event types
            default:
              echo 'Received unknown event type ' . $event->type;
          }

        http_response_code(200);
    }
}
