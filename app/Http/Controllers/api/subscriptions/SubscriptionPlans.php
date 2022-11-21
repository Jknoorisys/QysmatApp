<?php

namespace App\Http\Controllers\api\subscriptions;

use App\Http\Controllers\Controller;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\Subscriptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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

            if ($_POST['user_type'] == 'singleton') {
                $user = Singleton::find($_POST['login_id']);
                if (empty($user) || $user->status != 'Unblocked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.User Not Found!'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                if (empty($user) || $user->is_verified != 'verified') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Profile not Verified, Please Try After Some Time...'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                $linked = ParentChild::where('singleton_id','=',$_POST['login_id'])->first();
                if (empty($linked) || ($linked->status) != 'Linked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }
            } else {
                $user = ParentsModel::find($_POST['login_id']);
                if (empty($user) || $user->status != 'Unblocked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.User Not Found!'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                if (empty($user) || $user->is_verified != 'verified') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Profile not Verified, Please Try After Some Time...'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                $linked = ParentChild::where('parent_id','=',$_POST['login_id'])->first();
                if (empty($linked) || ($linked->status) != 'Linked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }
            }

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

        $page = Subscriptions::where('status','=','Active')->get();
        if(!empty($page)){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Subsription Details Fetched Successfully!'),
                'data'      => $page
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Subsription Plan Not Found!'),
            ],400);
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

        if ($request->user_type == 'singleton') {
            $page = Singleton::where([['subscriptions.status','=','Active'],['singletons.id',$request->login_id]])->join('subscriptions','subscriptions.id','=','active_subscription_id')->select('subscriptions.*','singletons.id as singleton_id')->first();
        } else {
            $page = ParentsModel::where([['subscriptions.status','=','Active'],['parents.id',$request->login_id]])->join('subscriptions','subscriptions.id','=','active_subscription_id')->select('subscriptions.*','parents.id as singleton_id')->first();
        }

        if(!empty($page)){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Subsription Details Fetched Successfully!'),
                'data'      => $page
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Subsription Plan Not Found!'),
            ],400);
        }
    }
}
