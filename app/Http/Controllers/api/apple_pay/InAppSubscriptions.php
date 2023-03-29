<?php

namespace App\Http\Controllers\api\apple_pay;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BankDetails;
use App\Models\Booking;
use App\Models\Charges;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\Subscriptions;
use App\Models\Transactions;
use App\Notifications\AdminNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
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

        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'session_id'   => 'required',
            'transaction_id' => 'required',
            'product_id' => 'required',
            'amount_paid' => 'required',
            'payment_status' => 'required',
            'transaction_date' => 'required'
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
                $active_subscription_id = $payment_details->active_subscription_id;
                $subscription_id = $request->transaction_id;
                $plan_id = $request->product_id;
                $amount_paid = $request->amount_paid;
                $payment_status = $request->payment_status;
                $transaction_date = $request->transaction_date;

                $parent_premium = ($plan_id == 'com.app.qysmat.premium.parent.joint_1.subscription' || $plan_id == 'com.app.qysmat.premium.parent.joint_2.subscription' || $plan_id == 'com.app.qysmat.premium.parent.joint_3.subscription' || $plan_id == 'com.app.qysmat.premium.parent.joint_4.subscription') ? 1 : 0;
                $child_premium = $plan_id == 'com.app.qysmat.premium.child.joint.subscription' ? 1 : 0;

                if ($payment_status == 'purchased' || $payment_status == 'PURCHASED') {
                    $update_booking  =  [
                        'subscription_id' => $subscription_id,
                        'amount_paid' => $amount_paid,
                        'payment_status' => 'paid',
                        'session_status' => 'complete',
                        'updated_at'     => date('Y-m-d H:i:s'),
                    ];

                    $update = DB::table('bookings')->where('id', '=', $payment_details->id)->update($update_booking);
                    $plan = Subscriptions::where('id','=','2')->first();
                    $joint_plan = Subscriptions::where('id','=','3')->first();
                    $quantity = $other_user_ids ? count($other_user_ids) : '';

                    $sub_data = [
                        'booking_id' => $booking_id,
                        'user_id' => $user_id,
                        'user_type' => $user_type,
                        'user_name' => $user_name,
                        'user_email' => $user_email,
                        'other_user_id' => $other_user_id ? $other_user_id : '',
                        'other_user_type' => $other_user_type ? $other_user_type : '',
                        'payment_method' => 'in-app',
                        'active_subscription_id' => $active_subscription_id,
                        'subscription_id' => $subscription_id,
                        'item1_plan_id' => $plan_id,
                        'item1_unit_amount' => $child_premium == 0 ? $plan->price : $joint_plan->price,
                        'item2_unit_amount' => $parent_premium == 1 ? $joint_plan->price : '',
                        'item1_quantity' => 1,
                        'item2_quantity' => $parent_premium == 1 ? $quantity : '',
                        'amount_paid' => $amount_paid,
                        'currency' => $payment_details->currency,
                        'plan_interval' => 'month',
                        'plan_interval_count' => 1,
                        'payer_email' => $user_email,
                        'plan_period_start' => Carbon::createFromTimestamp($transaction_date / 1000),
                        'plan_period_end' => Carbon::createFromTimestamp($transaction_date / 1000),
                        'payment_status' => 'paid',
                        'subs_status' => 'active',
                        'created_at' => Carbon::now()
                    ];

                    $insert = DB::table('transactions')->insert($sub_data);

                    if($update){
                        $update_sub_data = [
                            'active_subscription_id' => $active_subscription_id,
                            'stripe_plan_id'         => $plan_id,
                            'subscription_item_id'   => $subscription_id
                        ];

                        if ($user_type == 'singleton') {
                            Singleton::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                        } else {
                            ParentsModel::where([['id','=',$user_id],['status','=','Unblocked']])->update($update_sub_data);
                        }

                        $update_sub_data1 = [
                            'active_subscription_id' => $active_subscription_id,
                            'stripe_plan_id'         => $plan_id,
                            'subscription_item_id'   => $subscription_id
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

                $update_booking  =  [
                    'active_subscription_id' => 1,
                    'payment_status'         => 'canceled',
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
                    'active_subscription_id' => 1,
                    'payment_status' => 'canceled',
                    'subs_status' => 'inactive',
                    'created_at' => date('Y-m-d h:i:s')
                ];

                $insert = DB::table('transactions')->insert($sub_data);

                if($update){
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.apple.failure'),
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
       // Get the encoded response from the request content
        $encodedResponse = $request->getContent();

        // Split the encoded response into its component parts
        $parts = explode('.', $encodedResponse);

        // Extract the header, payload, and signature
        $header = base64_decode($parts[0]);
        $payload = base64_decode($parts[1]);
        $signature = $parts[2];

        $payloadData = json_decode($payload, true);

        // Retrieve the notification type from the payload
        $notificationType = $payloadData['notificationType'];
        
        $encodedSignedTransactionInfo = explode('.', ($payloadData['data']['signedTransactionInfo']));
        $encodedSignedRenewalInfo = explode('.', $payloadData['data']['signedRenewalInfo']);
        $signedTransactionInfo = json_decode(base64_decode($encodedSignedTransactionInfo[1]), true);
        $signedRenewalInfo = json_decode(base64_decode($encodedSignedRenewalInfo[1]), true);
        $createDate = Carbon::createFromTimestamp($signedTransactionInfo['purchaseDate'] / 1000);
        $expiresDate = Carbon::createFromTimestamp($signedTransactionInfo['expiresDate'] / 1000);

        // Handle the notification based on its type
        switch ($notificationType) {
            case 'DID_FAIL_TO_RENEW':
                $transactionId = $signedTransactionInfo['transactionId'];
                $originalTransactionId = $signedTransactionInfo['originalTransactionId'];

                $paymentDetails = Transactions::where([['subscription_id', '=', $originalTransactionId],['subs_status', '=', 'active']])->first();

                if (!empty($paymentDetails)) {
                    $update_sub_data = ['active_subscription_id' => '1'];

                    if ($paymentDetails->user_type == 'singleton') {
                        $update = Singleton::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                    } else {
                        $update = ParentsModel::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                    }

                    if ($paymentDetails->other_user_id) {
                        $other_user_ids  = explode(',',$paymentDetails->other_user_id);
                        if ($paymentDetails->other_user_type == 'singleton') {
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
                        Transactions::where('id', '=',  $paymentDetails->id)->update($inactive);
                    }
                }
                break;
            case 'DID_RENEW':
                $transactionId = $signedTransactionInfo['transactionId'];
                $originalTransactionId = $signedTransactionInfo['originalTransactionId'];
                $status = $signedTransactionInfo['inAppOwnershipType'];

                $paymentDetails = Transactions::where([['subscription_id', '=', $originalTransactionId],['subs_status', '=', 'active']])->first();
                $productId = $signedTransactionInfo['productId'];
                $parent_premium = ($productId == 'com.app.qysmat.premium.parent.joint_1.subscription' || $productId == 'com.app.qysmat.premium.parent.joint_2.subscription' || $productId == 'com.app.qysmat.premium.parent.joint_3.subscription' || $productId == 'com.app.qysmat.premium.parent.joint_4.subscription') ? 1 : 0;
                $child_premium = $productId == 'com.app.qysmat.premium.child.joint.subscription' ? 1 : 0;

                if (!empty($paymentDetails)) {

                    $booking = Booking::where('subscription_id', '=', $originalTransactionId)->first();
                    $booking_data = [
                        'payment_method' => $paymentDetails->payment_method,
                        'user_id' => $paymentDetails->user_id,
                        'user_type' => $paymentDetails->user_type,
                        'user_name' => $paymentDetails->user_name,
                        'user_email' => $paymentDetails->user_email,
                        'other_user_id' => $paymentDetails->other_user_id ? $paymentDetails->other_user_id : '',
                        'other_user_type' => $paymentDetails->other_user_type ? $paymentDetails->other_user_type : '',
                        'active_subscription_id' => $paymentDetails->active_subscription_id,
                        'currency' => $paymentDetails->currency,
                        'amount_paid' => $paymentDetails->amount_paid,
                        'subscription_id' => $originalTransactionId,
                        'payment_status' => 'paid',
                        'session_status' => 'complete',
                        'created_at' => Carbon::now()
                    ];

                    if (empty($booking)) {
                        $booking_id = Booking::insertGetId($booking_data);
                    }else{
                        $booking_id = $booking->id;
                    }

                    if ($status == 'PURCHASED' || $status == 'purchased') {
                        $trxn_data = [
                            'booking_id' => $booking_id,
                            'subscription_id' => $originalTransactionId,
                            'plan_period_start' => $createDate,
                            'plan_period_end' => $expiresDate,
                            'updated_at' => Carbon::now()
                        ];
    
                        Transactions::where('subscription_id', '=', $originalTransactionId)->update($trxn_data);

                        $premium = Subscriptions::where('id', '=', '2')->first();
                        $joint = Subscriptions::where('id', '=', '3')->first();

                        // send invoice
                        $data = [
                            'name' => $paymentDetails->user_name ? $paymentDetails->user_name : '',
                            'email' => $paymentDetails->user_email ? $paymentDetails->user_email : '',
                            'phone' =>  '',
                            'invoice_number' => (string)rand(10000, 50000),
                            'amount_paid' => $paymentDetails->amount_paid,
                            'currency' => $paymentDetails->currency ? $paymentDetails->currency : '',
                            'period_start' => $signedTransactionInfo['purchaseDate'] / 1000,
                            'period_end' => $signedTransactionInfo['expiresDate'] / 1000,
                            'subtotal' => $paymentDetails->amount_paid,
                            'total' => $paymentDetails->amount_paid,
                            'item1_name' => $child_premium == 0 ? $premium->subscription_type : $joint->subscription_type,
                            'item1_unit_price' => $paymentDetails->item1_unit_amount ? $paymentDetails->item1_unit_amount * 100 : '',
                            'item1_quantity' => $paymentDetails->item1_quantity,
                            'item2_name' => $parent_premium == 1 ? $joint->subscription_type : '',
                            'item2_unit_price' => $paymentDetails->item2_unit_amount ? $paymentDetails->item2_unit_amount * 100 : '',
                            'item2_quantity' => $paymentDetails->item2_quantity,
                            'item2' => $parent_premium == 1 ? 2 : 1,
                        ];

                        $pdf = Pdf::loadView('invoice', $data);
                        $pdf_name = 'invoice_'.time().'.pdf';
                        $path = Storage::put('invoices/'.$pdf_name,$pdf->output());
                        $invoice_url = ('storage/app/invoices/'.$pdf_name);
                        Transactions::where('subscription_id', '=', $originalTransactionId)->update(['invoice_url' => $invoice_url]);
                        $email = $paymentDetails->user_email;
                        $data1 = ['salutation' => __('msg.Dear'),'name'=> $paymentDetails->user_name, 'msg'=> __('msg.This email serves to confirm the successful setup of your subscription with Us.'), 'msg1'=> __('msg.We are delighted to welcome you as a valued subscriber and are confident that you will enjoy the benefits of Premium Services.'),'msg2' => __('msg.Thank you for your trust!')];
                
                        Mail::send('invoice_email', $data1, function ($message) use ($pdf_name, $email, $pdf) {
                            $message->to($email)->subject('Invoice');
                            $message->attachData($pdf->output(), $pdf_name, ['as' => $pdf_name, 'mime' => 'application/pdf']);
                        });

                    } else {
                        $update_sub_data = ['active_subscription_id' => '1'];

                        if ($paymentDetails->user_type == 'singleton') {
                            $update = Singleton::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                        } else {
                            $update = ParentsModel::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                        }

                        if ($paymentDetails->other_user_id) {
                            $other_user_ids  = explode(',',$paymentDetails->other_user_id);
                            if ($paymentDetails->other_user_type == 'singleton') {
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
                            Transactions::where('id', '=',  $paymentDetails->id)->update($inactive);
                        }
                    }
                }
                break;
            case 'EXPIRED':
                $transactionId = $signedTransactionInfo['transactionId'];
                $originalTransactionId = $signedTransactionInfo['originalTransactionId'];

                $paymentDetails = Transactions::where([['subscription_id', '=', $originalTransactionId],['subs_status', '=', 'active']])->first();

                if (!empty($paymentDetails)) {
                    $update_sub_data = ['active_subscription_id' => '1'];

                    if ($paymentDetails->user_type == 'singleton') {
                        $update = Singleton::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                    } else {
                        $update = ParentsModel::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                    }

                    if ($paymentDetails->other_user_id) {
                        $other_user_ids  = explode(',',$paymentDetails->other_user_id);
                        if ($paymentDetails->other_user_type == 'singleton') {
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
                        Transactions::where('id', '=',  $paymentDetails->id)->update($inactive);
                    }
                }
                break;
            case 'SUBSCRIBED':
                $transactionId = $signedTransactionInfo['transactionId'];
                $originalTransactionId = $signedTransactionInfo['originalTransactionId'];
                $status = $signedTransactionInfo['inAppOwnershipType'];

                $paymentDetails = Transactions::where([['subscription_id', '=', $originalTransactionId],['subs_status', '=', 'active']])->first();
                $productId = $signedTransactionInfo['productId'];
                $parent_premium = ($productId == 'com.app.qysmat.premium.parent.joint_1.subscription' || $productId == 'com.app.qysmat.premium.parent.joint_2.subscription' || $productId == 'com.app.qysmat.premium.parent.joint_3.subscription' || $productId == 'com.app.qysmat.premium.parent.joint_4.subscription') ? 1 : 0;
                $child_premium = $productId == 'com.app.qysmat.premium.child.joint.subscription' ? 1 : 0;

                if (!empty($paymentDetails)) {

                    $booking = Booking::where('subscription_id', '=', $originalTransactionId)->first();
                    $booking_data = [
                        'payment_method' => $paymentDetails->payment_method,
                        'user_id' => $paymentDetails->user_id,
                        'user_type' => $paymentDetails->user_type,
                        'user_name' => $paymentDetails->user_name,
                        'user_email' => $paymentDetails->user_email,
                        'other_user_id' => $paymentDetails->other_user_id ? $paymentDetails->other_user_id : '',
                        'other_user_type' => $paymentDetails->other_user_type ? $paymentDetails->other_user_type : '',
                        'active_subscription_id' => $paymentDetails->active_subscription_id,
                        'currency' => $paymentDetails->currency,
                        'amount_paid' => $paymentDetails->amount_paid,
                        'subscription_id' => $originalTransactionId,
                        'payment_status' => 'paid',
                        'session_status' => 'complete',
                        'created_at' => Carbon::now()
                    ];

                    if (empty($booking)) {
                        $booking_id = Booking::insertGetId($booking_data);
                    }else{
                        $booking_id = $booking->id;
                    }

                    if ($status == 'PURCHASED' || $status == 'purchased') {
                        $trxn_data = [
                            'booking_id' => $booking_id,
                            'subscription_id' => $originalTransactionId,
                            'plan_period_start' => $createDate,
                            'plan_period_end' => $expiresDate,
                            'updated_at' => Carbon::now()
                        ];
    
                        Transactions::where('subscription_id', '=', $originalTransactionId)->update($trxn_data);

                        $premium = Subscriptions::where('id', '=', '2')->first();
                        $joint = Subscriptions::where('id', '=', '3')->first();

                        // send invoice
                        $data = [
                            'name' => $paymentDetails->user_name ? $paymentDetails->user_name : '',
                            'email' => $paymentDetails->user_email ? $paymentDetails->user_email : '',
                            'phone' =>  '',
                            'invoice_number' => (string)rand(10000, 50000),
                            'amount_paid' => $paymentDetails->amount_paid,
                            'currency' => $paymentDetails->currency ? $paymentDetails->currency : '',
                            'period_start' => $signedTransactionInfo['purchaseDate'] / 1000,
                            'period_end' => $signedTransactionInfo['expiresDate'] / 1000,
                            'subtotal' => $paymentDetails->amount_paid,
                            'total' => $paymentDetails->amount_paid,
                            'item1_name' => $child_premium == 0 ? $premium->subscription_type : $joint->subscription_type,
                            'item1_unit_price' => $paymentDetails->item1_unit_amount ? $paymentDetails->item1_unit_amount * 100 : '',
                            'item1_quantity' => $paymentDetails->item1_quantity,
                            'item2_name' => $parent_premium == 1 ? $joint->subscription_type : '',
                            'item2_unit_price' => $paymentDetails->item2_unit_amount ? $paymentDetails->item2_unit_amount * 100 : '',
                            'item2_quantity' => $paymentDetails->item2_quantity,
                            'item2' => $parent_premium == 1 ? 2 : 1,
                        ];

                        $pdf = Pdf::loadView('invoice', $data);
                        $pdf_name = 'invoice_'.time().'.pdf';
                        $path = Storage::put('invoices/'.$pdf_name,$pdf->output());
                        $invoice_url = ('storage/app/invoices/'.$pdf_name);
                        Transactions::where('subscription_id', '=', $originalTransactionId)->update(['invoice_url' => $invoice_url]);
                        $email = $paymentDetails->user_email;
                        $data1 = ['salutation' => __('msg.Dear'),'name'=> $paymentDetails->user_name, 'msg'=> __('msg.This email serves to confirm the successful setup of your subscription with Us.'), 'msg1'=> __('msg.We are delighted to welcome you as a valued subscriber and are confident that you will enjoy the benefits of Premium Services.'),'msg2' => __('msg.Thank you for your trust!')];
                
                        Mail::send('invoice_email', $data1, function ($message) use ($pdf_name, $email, $pdf) {
                            $message->to($email)->subject('Invoice');
                            $message->attachData($pdf->output(), $pdf_name, ['as' => $pdf_name, 'mime' => 'application/pdf']);
                        });

                    } else {
                        $update_sub_data = ['active_subscription_id' => '1'];

                        if ($paymentDetails->user_type == 'singleton') {
                            $update = Singleton::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                        } else {
                            $update = ParentsModel::where([['id','=',$paymentDetails->user_id],['status','=','Unblocked']])->update($update_sub_data);
                        }

                        if ($paymentDetails->other_user_id) {
                            $other_user_ids  = explode(',',$paymentDetails->other_user_id);
                            if ($paymentDetails->other_user_type == 'singleton') {
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
                            Transactions::where('id', '=',  $paymentDetails->id)->update($inactive);
                        }
                    }
                }
                break;
            default:
                // Handle unknown notification type
                break;
        }

        return response('Webhook received', 200);
    }
}
