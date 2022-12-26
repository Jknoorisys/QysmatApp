<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\Notifications as ModelsNotifications;
use App\Models\ParentsModel;
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

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type']) && isset($_POST['singleton_id']) && !empty($_POST['singleton_id'])) {
            userFound($_POST['login_id'], $_POST['user_type'], $_POST['singleton_id']);
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
                Rule::in(['parent']),
            ],
            'singleton_id'  => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = ParentsModel::where([['id', '=', $request->login_id],['user_type', '=', $request->user_type]])->first();
            $notifications = $user->notifications->where('user_type', '=', $request->user_type)->where('singleton_id', '=', $request->singleton_id);

            if(!$notifications->isEmpty()){
                foreach ($notifications as $notify) {
                    $notification[] = $notify->data;
                }

                if ($notification) {
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.notifications.success'),
                        'data'      => $notification
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.notifications.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.notifications.failure'),
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
}
