<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\Notifications as ModelsNotifications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Notifications extends Controller
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
            'login_id'  => 'required||numeric',
            'user_type' => [
                'required' ,
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

        $notifications = ModelsNotifications::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['status','=','unread']])->get();

        if(!$notifications->isEmpty()){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Notifications List Fetched Successfully!'),
                'data'      => $notifications
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.No Notification Found!'),
            ],400);
        }
    }
}
