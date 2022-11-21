<?php

namespace App\Http\Controllers\api\bank_details;

use App\Http\Controllers\Controller;
use App\Models\BankDetails as ModelsBankDetails;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class BankDetails extends Controller
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

        $account = ModelsBankDetails::where([['status','=','Active'],['user_id','=',$request->login_id],['user_type','=', $request->user_type]])->get();
        if(!$account->isEmpty()){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Bank Account Details Fetched Successfully!'),
                'data'      => $account
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Bank Account Details Not Found!'),
            ],400);
        }
    }

    public function addCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'user_type' => [
                'required' ,
                Rule::in(['singleton','parent']),
            ],
            'login_id'              => 'required||numeric',
            'card_holder_name'      => 'required',
            'bank_name'             => 'required',
            'card_number'           => 'required||unique:bank_details',
            'month_year'            => 'required',
            'cvv'                   => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $account = new ModelsBankDetails();
        $account->user_id           = $request->login_id;
        $account->user_type         = $request->user_type;
        $account->card_holder_name  = $request->card_holder_name;
        $account->bank_name         = $request->bank_name;
        $account->card_number       = $request->card_number;
        $account->month_year        = $request->month_year;
        $account->cvv               = $request->cvv;

        $account_details = $account->save();

        if($account_details){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Bank Account Details Added Successfully!'),
                'data'    => $account
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
            ],400);
        }
    }

    public function deleteCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
            'method_id'   => 'required||numeric',
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

        $account = ModelsBankDetails::where([['id','=',$request->method_id],['status','=','Active'],['user_id','=',$request->login_id],['user_type','=', $request->user_type]])->delete();
        if($account){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Bank Account Details Deleted Successfully!'),
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Bank Account Details Not Found!'),
            ],400);
        }
    }
}
