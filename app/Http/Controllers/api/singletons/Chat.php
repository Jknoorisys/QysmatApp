<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\ChatRequest as ModelsChatRequest;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\RecievedMatches;
use App\Models\ReferredMatches;
use App\Models\Singleton;
use App\Models\ReportedUsers as ModelsReportedUsers;
use App\Models\UnMatches;
use App\Notifications\AcceptChatRequest;
use App\Notifications\ChatRequest;
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

        try {
            $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->messaged_user_id], ['blocked_user_type', '=', 'singleton']])->first();
            if (!empty($block)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.blocked'),
                ],400);
            }

            $report = ModelsReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->messaged_user_id], ['reported_user_type', '=', 'singleton']])->first();
            if (!empty($report)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.reported'),
                ],400);
            }

            $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->messaged_user_id]])->first();
            if (!empty($unMatch)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.un-matched'),
                ],400);
            }

            $not_in_list1 = MyMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id]])->first();
            $not_in_list2 = ReferredMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['referred_match_id', '=', $request->messaged_user_id]])->first();
            $not_in_list3 = RecievedMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['recieved_match_id', '=', $request->messaged_user_id]])->first();

            if (empty($not_in_list1) && empty($not_in_list2) && empty($not_in_list3)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.failure'),
                ],400);
            }

            $chat = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['matched_id', '!=', $request->messaged_user_id]])->first();
            $chat1 = ReferredMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['referred_match_id', '!=', $request->messaged_user_id]])->first();
            $chat2 = RecievedMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['recieved_match_id', '!=', $request->messaged_user_id]])->first();
            if (!empty($chat) || !empty($chat1) || !empty($chat2)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.invalid'),
                ],400);
            }

            $not_accepted = ModelsChatRequest ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['requested_user_id', '=', $request->messaged_user_id], ['status', '!=', 'accepted']])->first();
            
            if (!empty($not_accepted)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.not-accepted'),
                ],400);
            }

            $message                     = new ChatHistory();
            $message->user_id            = $request->login_id ? $request->login_id : '';
            $message->user_type          = $request->user_type ? $request->user_type : '';
            $message->messaged_user_id   = $request->messaged_user_id ? $request->messaged_user_id : '';
            $message->messaged_user_type = $request->messaged_user_type ? $request->messaged_user_type : '';
            $message->message            = $request->message ? $request->message : '';
            $messaged                    = $message->save();

            if (!empty($messaged)) {
                $title = __('msg.New Message');
                $reciever = Singleton::where([['id', '=', $request->messaged_user_id], ['status', '=', 'Unblocked']])->first();
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

                MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id]])->update(['chat_in_progress' => '1', 'updated_at' => date('Y-m-d H:i:s')]);
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.send-message.success'),
                    'data'      => $message
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.failure'),
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

        try {
            $list = ChatHistory::leftJoin('singletons', 'chat_histories.messaged_user_id', '=', 'singletons.id')
                                ->where([['chat_histories.user_id', '=', $request->login_id],['chat_histories.user_type', '=', $request->user_type]])
                                ->select('chat_histories.messaged_user_id','singletons.*','chat_histories.user_id')
                                ->orderBy('chat_histories.id', 'desc')
                                ->distinct()
                                ->get();

            foreach ($list as $key => $value) {
                $last_message = ChatHistory::where([['chat_histories.user_id', '=', $value->user_id],['chat_histories.user_type', '=', $request->user_type],['chat_histories.messaged_user_id', '=', $value->messaged_user_id],['chat_histories.messaged_user_type', '=', 'singleton']])
                                        ->orWhere([['chat_histories.user_id', '=', $value->messaged_user_id],['chat_histories.user_type', '=', 'singleton'],['chat_histories.messaged_user_id', '=', $value->user_id],['chat_histories.messaged_user_type', '=', $request->user_type]])                        
                                        ->select('chat_histories.message')
                                        ->orderBy('chat_histories.id', 'desc')
                                        ->first();

                $list[$key]->last_message = $last_message->message;
            }

            if(!$list->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.messaged-users.success'),
                    'data'      => $list,
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.messaged-users.failure'),
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
                Rule::in(['singleton']),
            ],
            'messaged_user_id'  => 'required||numeric',
            'messaged_user_type' => [
                'required' ,
                Rule::in(['singleton']),
            ],
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
            $total = ChatHistory::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type]])
                                ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', $request->user_type]])
                                ->count();

            $chat = ChatHistory::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type]])
                                ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', $request->user_type]])
                                ->orderBy('id', 'desc')
                                ->offset(($page_number - 1) * $per_page)
                                ->limit($per_page)
                                ->get();

            if(!$chat->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.chat-history.success'),
                    'data'      => $chat,
                    'total'     => $total
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.chat-history.failure'),
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

        try {
            $close = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id], ['chat_in_progress', '=', '1']])->update(['chat_in_progress' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
            if($close){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.close-chat.success'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.close-chat.failure'),
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
            ]
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->messaged_user_id], ['blocked_user_type', '=', 'singleton']])->first();
            if (!empty($block)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.blocked'),
                ],400);
            }

            $report = ModelsReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->messaged_user_id], ['reported_user_type', '=', 'singleton']])->first();
            if (!empty($report)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.reported'),
                ],400);
            }

            $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->messaged_user_id]])->first();
            if (!empty($unMatch)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.un-matched'),
                ],400);
            }

            $not_in_list1 = MyMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id]])->first();
            $not_in_list2 = ReferredMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['referred_match_id', '=', $request->messaged_user_id]])->first();
            $not_in_list3 = RecievedMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['recieved_match_id', '=', $request->messaged_user_id]])->first();

            if (empty($not_in_list1) && empty($not_in_list2) && empty($not_in_list3)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.start-chat.failure'),
                ],400);
            }

            $chat = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['matched_id', '!=', $request->messaged_user_id]])->first();
            $chat1 = ReferredMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['referred_match_id', '!=', $request->messaged_user_id]])->first();
            $chat2 = RecievedMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['chat_in_progress', '=', '1'],['recieved_match_id', '!=', $request->messaged_user_id]])->first();
            if (!empty($chat) || !empty($chat1) || !empty($chat2)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.invalid'),
                ],400);
            }

            $data = [
                'user_id' => $request->login_id,
                'user_type' => $request->user_type,
                'requested_user_id' => $request->messaged_user_id
            ];

            $insert = DB::table('chat_requests')->insert($data);
            if ($insert) {
                $user = Singleton::where([['id','=', $request->messaged_user_id],['status','!=','Deleted']])->first();
                $singleton = Singleton::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();
                $user->notify(new ChatRequest($singleton, $user->user_type));

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.start-chat.success'),
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.start-chat.failure'),
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

    public function inviteParent(Request $request)
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

        try {
            $linked = ParentChild::where([['singleton_id','=',$request->login_id],['status','=','Linked']])->first();
            if (!empty($linked)) {
                $invite = new ReferredMatches();
                $invite->user_id = $linked->parent_id ? $linked->parent_id : '';
                $invite->user_type = 'parent';
                $invite->singleton_id = $request->login_id ? $request->login_id : '';
                $invite->referred_match_id = $request->messaged_user_id ? $request->messaged_user_id : '';
                $send = $invite->save();

                if ($send) {

                    $user = ParentsModel::where([['id','=',$linked->parent_id],['status','!=','Deleted']])->first();
                    $singleton = Singleton::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();
                    $user->notify(new ReferNotification($singleton, $user->user_type, $request->login_id));


                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.invitation.success'),
                        'data'      => $invite
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.invitation.failure'),
                    ],400);
                }

            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.invitation.invalid'),
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

    public function acceptChatRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'  => 'required||numeric',
            'requested_user_id'  => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $requested = DB::table('chat_requests')->where([['user_id', '=', $request->requested_user_id], ['user_type', '=', 'singleton'], ['requested_user_id', '=', $request->login_id]])->first();
            if (!empty($requested)) {
                if ($requested->status != 'accepted') {
                    $update = DB::table('chat_requests')->where([['user_id', '=', $request->requested_user_id], ['user_type', '=', 'singleton'], ['requested_user_id', '=', $request->login_id]])->update(['status' => 'accepted']);

                   if ($update) {
                        $user = Singleton::where([['id','=', $request->requested_user_id],['status','!=','Deleted']])->first();
                        $singleton = Singleton::where([['id','=', $request->login_id],['status','=','Unblocked']])->first();
                        $user->notify(new AcceptChatRequest($singleton, $user->user_type));

                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.singletons.accept-chat-request.success'),
                        ],200);
                   } else {
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.singletons.accept-chat-request.failure'),
                        ],400);
                   }
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.accept-chat-request.accepted'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.accept-chat-request.invalid'),
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
