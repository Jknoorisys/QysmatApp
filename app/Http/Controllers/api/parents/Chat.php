<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\ReferredMatches;
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
        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type']) && isset($_POST['singleton_id']) && !empty($_POST['singleton_id'])) {
            parentExist($_POST['login_id'], $_POST['user_type'], $_POST['singleton_id']);
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
            'singleton_id'       => 'required||numeric',
            'messaged_user_id'   => 'required||numeric',
            'messaged_user_type' => [
                'required' ,
                Rule::in(['parent']),
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

        $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->swiped_user_id], ['blocked_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
        if (!empty($block)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Blocked this User!'),
            ],400);
        }

        $report = ModelsReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->swiped_user_id], ['reported_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
        if (!empty($report)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Reported this User!'),
            ],400);
        }

        $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->first();
        if (!empty($unMatch)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Un-Matched this User!'),
            ],400);
        }

        $chat = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['matched_id', '!=', $request->messaged_user_id],['singleton_id', '=', $request->singleton_id]])->first();
        if (!empty($chat)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have an Un-Closed Chat...'),
            ],400);
        }

        $message                     = new ChatHistory();
        $message->user_id            = $request->login_id;
        $message->user_type          = $request->user_type;
        $message->singleton_id       = $request->singleton_id;
        $message->messaged_user_id   = $request->messaged_user_id;
        $message->messaged_user_type = $request->messaged_user_type;
        $message->message            = $request->message;
        $messaged                    = $message->save();

        if (!empty($messaged)) {
            MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id],['singleton_id', '=', $request->singleton_id]])->update(['chat_in_progress' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
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
                Rule::in(['parent']),
            ],
            'singleton_id'       => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $list = ChatHistory::where([['chat_histories.user_id', '=', $request->login_id],['chat_histories.user_type', '=', $request->user_type],['chat_histories.singleton_id', '=', $request->singleton_id]])
                                ->join('parents', 'chat_histories.messaged_user_id', '=', 'parents.id')
                                ->select('chat_histories.messaged_user_id','parents.*')
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
                Rule::in(['parent']),
            ],
            'singleton_id'       => 'required||numeric',
            'messaged_user_id'   => 'required||numeric',
            'messaged_user_type' => [
                'required' ,
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

        $chat = ChatHistory::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type],['singleton_id', '=', $request->singleton_id]])->get();

        if(!$chat->isEmpty()){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Message List Fetched Successfully!'),
                'data'      => $chat
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.No Message Found!'),
            ],400);
        }
    }

    public function startChat(Request $request)
    {
        # code...
    }

    public function inviteChild(Request $request)
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
            'singleton_id'       => 'required||numeric',
            'messaged_user_id'   => 'required||numeric',
            'messaged_user_singleton_id'   => 'required||numeric',
            'messaged_user_type' => [
                'required' ,
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

        $linked = ParentChild::where([['parent_id','=',$request->login_id],['singleton_id','=',$request->singleton_id],['status','=','Linked']])->first();
        if (!empty($linked)) {
            $invite = new ReferredMatches();
            $invite->user_id = $linked->singleton_id;
            $invite->user_type = 'singleton';
            $invite->singleton_id = $request->singleton_id;
            $invite->referred_match_id = $request->messaged_user_singleton_id;
            $send = $invite->save();

            if ($send) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Invitation Sent...'),
                    'data'      => $invite
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                ],400);
            }

        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
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
}
