<?php

namespace App\Http\Controllers\api\subscriptions;

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

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class SubscriptionPlans extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userExist($_POST['login_id'], $_POST['user_type']);
        }
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
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
            $pages = Subscriptions::where('status','=','Active')->get();
            $features = [];
            foreach ($pages as $page) {
                if ($page->subscription_type == 'Basic') {
                    $features = [__("msg.Only 5 Profile Views per day"), __("msg.Unrestricted profile search criteria")];
                }else {
                    $features = [__("msg.Unlimited swipes per day"), __("msg.Send instant message  (3 per week)"), __("msg.In-app telephone and video calls"), __("msg.Refer profiles to friends and family"), __("msg.Undo last swipe"), __("msg.Reset profile search and start again once a month")];
                }
                $page->features= !empty($features) ? $features : "";
            }

            if(!empty($pages)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.subscriptions.success'),
                    'data'      => $pages
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.subscriptions.failure'),
                ],400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function activeSubscription(Request $request)
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
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            if ($request->user_type == 'singleton') {
                $page = Singleton::where([['subscriptions.status','=','Active'],['singletons.id',$request->login_id]])->leftJoin('subscriptions','singletons.active_subscription_id','=','subscriptions.id')->select('subscriptions.*','singletons.id as singleton_id')->first();
            } else {
                $page = ParentsModel::where([['subscriptions.status','=','Active'],['parents.id',$request->login_id]])->leftJoin('subscriptions','parents.active_subscription_id','=','subscriptions.id')->select('subscriptions.*','parents.id as parent_id')->first();
            }

            if(!empty($page)){
                if ($page->subscription_type == 'Basic') {
                    $features = [__("msg.Only 5 Profile Views per day"), __("msg.Unrestricted profile search criteria")];
                }else {
                    $features = [__("msg.Unlimited swipes per day"), __("msg.Send instant message  (3 per week)"), __("msg.In-app telephone and video calls"), __("msg.Refer profiles to friends and family"), __("msg.Undo last swipe"), __("msg.Reset profile search and start again once a month")];
                }
                $page->features= !empty($features) ? $features : "";

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.subscriptions.success'),
                    'data'      => $page
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.subscriptions.failure'),
                ],400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function Subscribe(Request $request)
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
            'price'     => 'required',
            'plan_id' => [
                'required' ,
                Rule::in(['1','2','3']),
            ],
            'transaction_id' => 'required',
            'payment_type'   => [
                    'required' ,
                    Rule::in(['stripe','in-app']),
                ],
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

            if ($request->user_type == 'singleton') {
                $user = Singleton::where([['id', '=', $request->login_id],['status', '=', 'Unblocked']])->first();
            } elseif ($request->user_type == 'parent') {
                $user = ParentsModel::where([['id', '=', $request->login_id],['status', '=', 'Unblocked']])->first();
            }
            
            $data = [
                'user_id'           => $request->login_id ? $request->login_id : '',
                'user_type'         => $request->user_type ? $request->user_type : '',
                'user_name'         => $user->name ? $user->name : '',
                'other_user_id'     => $request->other_user_id ? $request->other_user_id : '',
                'other_user_type'   => $request->other_user_type ? $request->other_user_type : '',
                'paid_by'           => $user->name,
                'paid_amount'       => $request->price,
                'payment_type'      => $request->payment_type,
                'transaction_id'    => $request->transaction_id,
                'subscription_type' => $request->plan_id,
                'transaction_datetime' => date('Y-m-d h:i:s'),
            ];
            $insert = DB::table('transactions')->insert($data);
            if ($insert) {
                if ($request->plan_id == 3) {
                    if ($request->user_type == 'singleton' && $request->other_user_type == 'parent') {
                        Singleton::where([['id', '=', $request->login_id],['status', '=', 'Unblocked']])->update(['active_subscription_id' => $request->plan_id]);
                        ParentsModel::where([['id', '=', $request->other_user_id],['status', '=', 'Unblocked']])->update(['active_subscription_id' => $request->plan_id]);
                    } elseif ($request->user_type == 'parent' && $request->other_user_type == 'singleton') {
                        ParentsModel::where([['id', '=', $request->login_id],['status', '=', 'Unblocked']])->update(['active_subscription_id' => $request->plan_id]);
                        Singleton::where([['id', '=', $request->other_user_id],['status', '=', 'Unblocked']])->update(['active_subscription_id' => $request->plan_id]);
                    }
                } else{
                    if ($request->user_type == 'singleton') {
                        Singleton::where([['id', '=', $request->login_id],['status', '=', 'Unblocked']])->update(['active_subscription_id' => $request->plan_id]);
                    } elseif ($request->user_type == 'parent') {
                        ParentsModel::where([['id', '=', $request->login_id],['status', '=', 'Unblocked']])->update(['active_subscription_id' => $request->plan_id]);
                    }
                }

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.subscribe.success'),
                ],200);
            }else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.subscribe.failure'),
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
