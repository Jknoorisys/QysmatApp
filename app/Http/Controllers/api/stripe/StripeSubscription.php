<?php

namespace App\Http\Controllers\api\stripe;

use App\Http\Controllers\Controller;

use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Subscription;
use Stripe\Stripe;

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
            userExist($_POST['login_id'], $_POST['user_type']);
        }

        // $this->stripe = new \Stripe\StripeClient(
        //     'sk_test_51MNsy9EHSmvHTsA3gQfJiCToJMt4riPCdLw0avWQ6JG2SJbAMORJBz3vFV802mSD8OncvxoM1gVYiPmS24u6CgIE00IAIhN4Hw'
        // );
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
            'stripe_plan_id'   => 'required',
            'plan_id' => [
                'required' ,
                Rule::in(['1','2','3']),
            ],
            // 'transaction_id' => 'required',
            // 'payment_type'   => [
            //         'required' ,
            //         Rule::in(['stripe','in-app']),
            //     ],
            'other_user_id'   => ['required_if:plan_id,3'],
            'other_user_type' => [
                'required_if:plan_id,3' ,
                Rule::in(['singleton','parent']),
            ],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
            if ($request->user_type == 'singleton') {
                $user = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            } else {
                $user = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            }
            
            $plan = Subscriptions::where([['stripe_plan_id', '=', $request->stripe_plan_id], ['id', '=', $request->plan_id], ['status', '=', 'Active']])->first();
            if (!empty($plan)) {
                $stripe = new \Stripe\StripeClient(
                    'sk_test_51MNsy9EHSmvHTsA3gQfJiCToJMt4riPCdLw0avWQ6JG2SJbAMORJBz3vFV802mSD8OncvxoM1gVYiPmS24u6CgIE00IAIhN4Hw'
                );

                $token = $stripe->tokens->create([
                    'account' => [
                        'business_type'  => 'individual',
                        'tos_shown_and_accepted' => true,
                    ],
                ]);

                return $token;
                
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.No Such Plan Found!, Please try again...'),
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
}
