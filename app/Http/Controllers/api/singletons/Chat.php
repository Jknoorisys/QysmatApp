<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\ReportedUsers as ModelsReportedUsers;
use App\Models\UnMatches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Chat extends Controller
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
            'login_id'  => 'required||numeric',
            'user_type' => [
                'required' ,
                Rule::in(['singleton']),
            ],
            'messaged_user_id'  => 'required||numeric',
            'messaged_user_type' => [
                'required' ,
                Rule::in(['singleton']),
            ],
            'message'   => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->messaged_user_id], ['blocked_user_type', '=', 'singleton']])->first();
        if (!empty($block)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Blocked this User!'),
            ],400);
        }

        $report = ModelsReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->messaged_user_id], ['reported_user_type', '=', 'singleton']])->first();
        if (!empty($report)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Reported this User!'),
            ],400);
        }

        $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->messaged_user_id]])->first();
        if (!empty($unMatch)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Un-Matched this User!'),
            ],400);
        }

        $chat = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['matched_id', '!=', $request->messaged_user_id]])->first();
        if (!empty($chat)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have an Un-Closed Chat...'),
            ],400);
        }

        $message                     = new ChatHistory();
        $message->user_id            = $request->login_id;
        $message->user_type          = $request->user_type;
        $message->messaged_user_id   = $request->messaged_user_id;
        $message->messaged_user_type = $request->messaged_user_type;
        $message->message            = $request->message;
        $messaged                    = $message->save();

        if (!empty($messaged)) {
            MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id]])->update(['chat_in_progress' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Message Sent!'),
                'data'      => $message
            ],200);
        } else {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
            ],400);
        }

    }

    public function messagedUsers(Request $request)
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

        $list = ChatHistory::where([['chat_histories.user_id', '=', $request->login_id],['chat_histories.user_type', '=', $request->user_type]])
                                ->join('singletons', 'chat_histories.messaged_user_id', '=', 'singletons.id')
                                ->select('chat_histories.messaged_user_id','singletons.*')
                                ->distinct()
                                ->get();
        if(!$list->isEmpty()){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Messaged Users List Fetched Successfully!'),
                'data'      => $list
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
            ],400);
        }
    }

    public function chatHistory(Request $request)
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
            'messaged_user_id'  => 'required||numeric',
            'messaged_user_type' => [
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

        $chat = ChatHistory::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type]])->get();

        if(!$chat->isEmpty()){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Messaged Users List Fetched Successfully!'),
                'data'      => $chat
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
            ],400);
        }
    }

    public function closeChat(Request $request)
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
            'messaged_user_id'  => 'required||numeric',
            'messaged_user_type' => [
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

        $close = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id], ['chat_in_progress', '=', '1']])->update(['chat_in_progress' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
        if($close){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Conversation Ended!'),
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
            ],400);
        }
    }
}
