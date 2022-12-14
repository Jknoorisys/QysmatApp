<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\ReportedUsers as ModelsReportedUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class BlockOrReportUser extends Controller
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
            'blocked_user_id'   => 'required||numeric',
            'blocked_user_type' => [
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
            if($request->blocked_user_type == 'singleton'){
                $userExists = Singleton::find($request->blocked_user_id);
            }else{
                $userExists = ParentsModel::find($request->blocked_user_id);
            }

            if(empty($userExists) || $userExists->staus == 'Deleted' || $userExists->staus == 'Blocked'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.block.invalid'),
                ],400);
            }

            $user = new BlockList();
            $user->blocked_user_id           = $request->blocked_user_id ? $request->blocked_user_id : '';
            $user->blocked_user_type         = $request->blocked_user_type ? $request->blocked_user_type : '';
            $user->user_id                   = $request->login_id ? $request->login_id : '';
            $user->user_type                 = $request->user_type ? $request->user_type : '';
            $user_details                    = $user->save();

            if($user_details){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.block.success'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.block.failure'),
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

    public function reportUser(Request $request)
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
            'reported_user_id'   => 'required||numeric',
            'reported_user_type' => [
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
            if($request->reported_user_type == 'singleton'){
                $userExists = Singleton::find($request->reported_user_id);
            }else{
                $userExists = ParentsModel::find($request->reported_user_id);
            }

            if(empty($userExists) || $userExists->staus == 'Deleted'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.report.invalid'),
                ],400);
            }

            $user = new ModelsReportedUsers();
            $user->user_id           = $request->login_id ? $request->login_id : '';
            $user->user_type         = $request->user_type ? $request->user_type : '';
            $user->reported_user_name         = $userExists->name ? $userExists->name : '';
            $user->reported_user_id  = $request->reported_user_id ? $request->reported_user_id : '';
            $user->reported_user_type = $request->reported_user_type ? $request->reported_user_type : '';
            $user_details = $user->save();

            if($user_details){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.report.success'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.report.failure'),
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
