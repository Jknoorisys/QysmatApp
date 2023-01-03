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
use App\Notifications\ReferNotification;
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

        try {
            $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->swiped_user_id], ['blocked_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
            if (!empty($block)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-message.blocked'),
                ],400);
            }

            $report = ModelsReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->swiped_user_id], ['reported_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
            if (!empty($report)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-message.reported'),
                ],400);
            }

            $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->first();
            if (!empty($unMatch)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-message.un-matched'),
                ],400);
            }

            $chat = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['matched_id', '!=', $request->messaged_user_id],['singleton_id', '=', $request->singleton_id]])->first();
            if (!empty($chat)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-message.invalid'),
                ],400);
            }

            $message                     = new ChatHistory();
            $message->user_id            = $request->login_id ? $request->login_id : '';
            $message->user_type          = $request->user_type ? $request->user_type : '';
            $message->singleton_id       = $request->singleton_id ? $request->singleton_id : '';
            $message->messaged_user_id   = $request->messaged_user_id ? $request->messaged_user_id : '';
            $message->messaged_user_type = $request->messaged_user_type ? $request->messaged_user_type : '';
            $message->message            = $request->message ? $request->message : '';
            $messaged                    = $message->save();

            if (!empty($messaged)) {
                $title = __('msg.New Message');
                $reciever = ParentsModel::where([['id', '=', $request->messaged_user_id], ['status', '=', 'Unblocked']])->first();
                if (isset($reciever) && !empty($reciever)) {
                    $fcm_regid[] = $reciever->fcm_token;
                    $notification = array(
                        'title'         => $title,
                        'message'       => $request->message,
                        'click_action'  => 'FLUTTER_NOTIFICATION_CLICK',
                        'date'          => date('Y-m-d H:i'),
                        'type'          => 'chat',
                        'response'      => ''
                    );
                    $result = sendFCMNotification($notification, $fcm_regid, 'chat');
                }
                MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id],['singleton_id', '=', $request->singleton_id]])->update(['chat_in_progress' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.send-message.success'),
                    'data'      => $message
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-message.failure'),
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

        try {
            $list = ChatHistory::where([['chat_histories.user_id', '=', $request->login_id],['chat_histories.user_type', '=', $request->user_type],['chat_histories.singleton_id', '=', $request->singleton_id]])
                                ->join('parents', 'chat_histories.messaged_user_id', '=', 'parents.id')
                                ->select('chat_histories.messaged_user_id','parents.*')
                                ->distinct()
                                ->get();

            foreach ($list as $key => $value) {
                $last_message = ChatHistory::where([['chat_histories.user_id', '=', $request->login_id],['chat_histories.user_type', '=', $request->user_type],['chat_histories.singleton_id', '=', $request->singleton_id]])
                                            ->join('parents', 'chat_histories.messaged_user_id', '=', 'parents.id')
                                            ->select('chat_histories.message')
                                            // ->orderBy('chat_histories.created_at', 'desc')
                                            // ->latest('chat_histories.created_at')
                                            // ->first();
                                            ->get()->last();

                $list[$key]->last_message = $last_message->message;
            }

            if(!$list->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.messaged-users.success'),
                    'data'      => $list
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.messaged-users.failure'),
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
            'messaged_user_singleton_id'   => 'required||numeric',
            'page_number'  => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $per_page = 10;
            $page_number = $request->input(key:'page_number', default:1);
            $total = ChatHistory::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type],['singleton_id', '=', $request->singleton_id]])
                                    ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', $request->user_type],['singleton_id', '=', $request->messaged_user_singleton_id]])
                                    ->count();

            $chat = ChatHistory::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type],['singleton_id', '=', $request->singleton_id]])
                                    ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', $request->user_type],['singleton_id', '=', $request->messaged_user_singleton_id]])
                                    ->orderBy('id', 'desc')
                                    ->offset(($page_number - 1) * $per_page)
                                    ->limit($per_page)
                                    ->get();

            if(!$chat->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.chat-history.success'),
                    'data'      => $chat,
                    'total'     => $total
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.chat-history.failure'),
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

    public function startChat(Request $request)
    {
        # code...
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
                Rule::in(['parent']),
            ],
            'singleton_id'  => 'required||numeric',
            'messaged_user_id'  => 'required||numeric',
            'messaged_user_type' => [
                'required' ,
                Rule::in(['parent']),
            ],
            'messaged_user_singleton_id'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $close = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['singleton_id', '=', $request->singleton_id],['matched_id', '=', $request->messaged_user_singleton_id], ['chat_in_progress', '=', '1']])->update(['chat_in_progress' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
            if($close){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.close-chat.success'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.close-chat.failure'),
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

        try {
            $linked = ParentChild::where([['parent_id','=',$request->login_id],['singleton_id','=',$request->singleton_id],['status','=','Linked']])->first();
            if (!empty($linked)) {
                $invite = new ReferredMatches();
                $invite->user_id = $linked->singleton_id ? $linked->singleton_id : '';
                $invite->user_type = 'singleton';
                $invite->singleton_id = $request->singleton_id ? $request->singleton_id : '';
                $invite->referred_match_id = $request->messaged_user_singleton_id ? $request->messaged_user_singleton_id : '';
                $send = $invite->save();

                if ($send) {

                    $user = Singleton::where([['id','=',$linked->singleton_id],['status','!=','Deleted']])->first();
                    $parent = ParentsModel::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();
                    $user->notify(new ReferNotification($parent, $user->user_type, 0));

                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.invitation.success'),
                        'data'      => $invite
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.invitation.failure'),
                    ],400);
                }

            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.invitation.invalid'),
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
