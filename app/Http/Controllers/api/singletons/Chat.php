<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\Matches;
use App\Models\MessagedUsers;
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

            // $not_in_list1 = MyMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id]])->first();
            // $not_in_list2 = ReferredMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['referred_match_id', '=', $request->messaged_user_id]])->first();
            // $not_in_list3 = RecievedMatches ::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['recieved_match_id', '=', $request->messaged_user_id]])->first();
            $not_in_list4 = Matches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['match_id', '=', $request->messaged_user_id],['match_type', '=', 'matched']])
                                    ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['match_id', '=', $request->login_id],['match_type', '=', 'matched']])
                                    ->first();

            if (empty($not_in_list4)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.failure'),
                ],400);
            }

            // $chat1 = Singleton::where([['id', '=', $request->login_id],['user_type', '=', $request->user_type],['status', '=', 'Unblocked'],['chat_status', '=', 'busy']])
            //                    ->orWhere([['id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['status', '=', 'Unblocked'],['chat_status', '=', 'busy']])
            //                    ->first();
            $chat = Matches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['match_id', '!=', $request->messaged_user_id],['status', '=', 'busy']])
                               ->orWhere([['user_id', '!=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['match_id', '=', $request->login_id],['status', '=', 'busy']])
                               ->first();

            if (!empty($chat)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-message.invalid'),
                ],400);
            }

            $conversation = MessagedUsers::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type]])
                                            ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', $request->user_type]])
                                            ->first();
           
            if (!empty($conversation)) {
                $sender = MessagedUsers:: where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['messaged_user_type', '=', $request->messaged_user_type]])->first();
                if (empty($sender)) {
                    $data = [
                        'conversation' => 'yes',
                        'updated_at'   => date('Y-m-d H:i:s')
                    ];
    
                    $reply = MessagedUsers::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type]])
                                            ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', $request->user_type]])
                                            ->update($data);
                    if ($reply) {
                        $busy = Singleton::where([['id', '=', $request->login_id],['user_type', '=', $request->user_type],['status', '=', 'Unblocked'],['chat_status', '=', 'busy']])
                                       ->orWhere([['id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['status', '=', 'Unblocked'],['chat_status', '=', 'busy']])
                                       ->first();
    
                        if (empty($busy)) {
                            Singleton::where([['id', '=', $request->login_id],['user_type', '=', $request->user_type],['status', '=', 'Unblocked']])
                                        ->orWhere([['id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['status', '=', 'Unblocked']])
                                        ->update(['chat_status' => 'busy']);
    
                            Matches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['match_id', '=', $request->messaged_user_id],['match_type', '=', 'matched']])
                                    ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['match_id', '=', $request->login_id],['match_type', '=', 'matched']])
                                    ->update([
                                        'status' => 'busy',
                                        'updated_at'   => date('Y-m-d H:i:s')
                                    ]);
                        }
                    }
                }
            } else {
                $data = [
                    'user_id' => $request->login_id,
                    'user_type' => $request->user_type,
                    'messaged_user_id' => $request->messaged_user_id,
                    'messaged_user_type' => $request->messaged_user_type,
                ];
                MessagedUsers::insert($data);
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

                    // $body = $request->message;
                    // $token = $reciever->fcm_token;
                    // $data = array(
                    //     'notType' => "chat",
                    // );
                    // $result = sendFCMNotifications($token, $title, $body, $data);
                }

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
            $singleton_id = $request->login_id;
            // $list = ChatHistory::leftjoin('singletons', function($join) use ($singleton_id) {
            //                         $join->on('singletons.id','=','chat_histories.messaged_user_id')
            //                             ->where('chat_histories.messaged_user_id','!=',$singleton_id);
            //                         $join->orOn('singletons.id','=','chat_histories.user_id')
            //                             ->where('chat_histories.user_id','!=',$singleton_id);
            //                     })
            //                     ->where([['chat_histories.user_id', '=', $request->login_id],['chat_histories.user_type', '=', $request->user_type]])
            //                     ->orWhere([['chat_histories.messaged_user_id', '=', $request->login_id],['chat_histories.user_type', '=', 'singleton']])
            //                     ->select('chat_histories.messaged_user_id','singletons.*','chat_histories.user_id')
            //                     ->orderBy('chat_histories.id', 'desc')
            //                     ->distinct()
            //                     ->get();
            
            $list = MessagedUsers::leftjoin('singletons', function($join) use ($singleton_id) {
                                        $join->on('singletons.id','=','messaged_users.messaged_user_id')
                                            ->where('messaged_users.messaged_user_id','!=',$singleton_id);
                                        $join->orOn('singletons.id','=','messaged_users.user_id')
                                            ->where('messaged_users.user_id','!=',$singleton_id);
                                    })
                                    ->where([['messaged_users.user_id', '=', $request->login_id],['messaged_users.user_type', '=', $request->user_type]])
                                    ->orWhere([['messaged_users.messaged_user_id', '=', $request->login_id],['messaged_users.messaged_user_type', '=', $request->user_type]])
                                    ->select('messaged_users.messaged_user_id','singletons.*','messaged_users.user_id')
                                    ->orderBy('messaged_users.id', 'desc')
                                    ->get(); 

            foreach ($list as $key => $value) {
                $block = BlockList::where([['user_id','=', $value->user_id],['user_type', '=', $value->user_type],['blocked_user_id', '=', $value->messaged_user_id],['blocked_user_type', '=', 'singleton']])->first();
                $report = ModelsReportedUsers::where([['user_id','=', $value->user_id],['user_type', '=', $value->user_type],['reported_user_id', '=', $value->messaged_user_id],['reported_user_type', '=', 'singleton']])->first();
                $unMatch = UnMatches::where([['user_id','=', $value->user_id],['user_type', '=', $value->user_type],['un_matched_id', '=', $value->messaged_user_id]])->first();

                $last_message = ChatHistory::where([['chat_histories.user_id', '=', $value->user_id],['chat_histories.user_type', '=', $request->user_type],['chat_histories.messaged_user_id', '=', $value->messaged_user_id],['chat_histories.messaged_user_type', '=', 'singleton']])
                                        ->orWhere([['chat_histories.user_id', '=', $value->messaged_user_id],['chat_histories.user_type', '=', 'singleton'],['chat_histories.messaged_user_id', '=', $value->user_id],['chat_histories.messaged_user_type', '=', $request->user_type]])                        
                                        ->select('chat_histories.message')
                                        ->orderBy('chat_histories.id', 'desc')
                                        ->first();

                $list[$key]->last_message = $last_message->message;
                
                if (!empty($block) || !empty($report) || !empty($unMatch)) {
                    $list[$key]->chat_status = 'disabled';
                }else{
                    $list[$key]->chat_status = 'enabled';
                }
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
                    'total'     => $total,
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
            // $close = MyMatches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['matched_id', '=', $request->messaged_user_id], ['chat_in_progress', '=', '1']])->update(['chat_in_progress' => '0', 'updated_at' => date('Y-m-d H:i:s')]);
            $close = Singleton::where([['id', '=', $request->login_id],['user_type', '=', $request->user_type],['status', '=', 'Unblocked']])
                                ->orWhere([['id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['status', '=', 'Unblocked']])            
                                ->update(['chat_status' => 'available']);

                        // Singleton::where([['id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['status', '=', 'Unblocked']])
                        // ->update(['chat_status' => 'available']);
                        
            if($close){
                Matches::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['match_id', '=', $request->messaged_user_id],['match_type', '=', 'matched']])
                                ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['match_id', '=', $request->login_id],['match_type', '=', 'matched']])
                                ->update([
                                    'status' => 'available',
                                    'updated_at'   => date('Y-m-d H:i:s')
                                ]);
                $data = [
                    'conversation' => 'no',
                    'updated_at'   => date('Y-m-d H:i:s')
                ];

                MessagedUsers::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['messaged_user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type]])
                                        ->orWhere([['user_id', '=', $request->messaged_user_id],['user_type', '=', $request->messaged_user_type],['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', $request->user_type]])
                                        ->update($data);

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
            $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->messaged_user_id], ['blocked_user_type', '=', 'singleton']])->first();
            if (!empty($block)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.blocked'),
                ],400);
            }

            $report = ModelsReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->messaged_user_id], ['reported_user_type', '=', 'singleton']])->first();
            if (!empty($report)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.reported'),
                ],400);
            }

            $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->messaged_user_id]])->first();
            if (!empty($unMatch)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.un-matched'),
                ],400);
            }

            $linked = ParentChild::where([['singleton_id','=',$request->login_id],['status','=','Linked']])->first();

            if (!empty($linked)) {
                $block = BlockList ::where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['singleton_id', '=', $request->login_id], ['blocked_user_id', '=', $request->messaged_user_id], ['blocked_user_type', '=', 'singleton']])->first();
                if (!empty($block)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.invitation.blocked'),
                    ],400);
                }

                $report = ModelsReportedUsers ::where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['singleton_id', '=', $request->login_id], ['reported_user_id', '=', $request->messaged_user_id], ['reported_user_type', '=', 'singleton']])->first();
                if (!empty($report)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.invitation.reported'),
                    ],400);
                }

                $unMatch = UnMatches ::where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['singleton_id', '=', $request->login_id], ['un_matched_id', '=', $request->messaged_user_id]])->first();
                if (!empty($unMatch)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.invitation.un-matched'),
                    ],400);
                }

                $refer = ReferredMatches ::where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['singleton_id', '=', $request->login_id],['referred_match_id', '=', $request->messaged_user_id]])->first();
                if (!empty($refer)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.swips.invite'),
                    ],400);
                }

                $invite = new ReferredMatches();
                $invite->user_id = $linked->parent_id ? $linked->parent_id : '';
                $invite->user_type = 'parent';
                $invite->singleton_id = $request->login_id ? $request->login_id : '';
                $invite->referred_match_id = $request->messaged_user_id ? $request->messaged_user_id : '';

                $sent = DB::table('referred_matches')->where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['singleton_id', '=', $request->login_id], ['referred_match_id', '=', $request->messaged_user_id]])->first();
                if (!empty($sent)) {
                    $send = DB::table('referred_matches')->where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['singleton_id', '=', $request->login_id], ['referred_match_id', '=', $request->messaged_user_id]])->update(['updated_at' => date('Y-m-d H:i:s')]);
                }else{
                    $invite->created_at  = date('Y-m-d H:i:s');
                    $send = $invite->save();
                }

                if ($send) {
                    DB::table('my_matches')->updateORInsert(
                        ['user_id' => $linked->parent_id, 'user_type' => 'parent', 'singleton_id' => $request->login_id, 'matched_id' => $request->messaged_user_id],
                        [
                            'user_id' => $linked->parent_id, 
                            'user_type' => 'parent', 
                            'singleton_id' => $request->login_id, 
                            'matched_id' => $request->messaged_user_id
                        ]
                    );

                    $parent = Singleton:: where([['id', '=', $request->messaged_user_id], ['status','=', 'Unblocked'], ['is_verified', '=', 'verified']])->first();
                    $mutual = Matches  :: where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->messaged_user_id], ['singleton_id', '=', $request->login_id]])
                                        ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->login_id], ['singleton_id', '=', $request->messaged_user_id]])
                                        ->first();

                    if (!empty($mutual)) {
                        // Matches::where([['user_id', '=', $linked->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->messaged_user_id], ['singleton_id', '=', $request->login_id], ['is_rematched', '=', 'no']])
                        //         ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->login_id], ['singleton_id', '=', $request->messaged_user_id], ['is_rematched', '=', 'no']])
                        //         ->update(['match_type' => 'matched', 'updated_at' => date('Y-m-d H:i:s')]);
                        Matches::where([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->login_id], ['singleton_id', '=', $request->messaged_user_id], ['is_rematched', '=', 'no'], ['match_type', '=', 'liked']])
                                ->update(['match_type' => 'matched', 'updated_at' => date('Y-m-d H:i:s')]);

                        // send congratulations fcm notification
                        $parent2 = ParentsModel::whereId($linked->parent_id)->first();
                        $parent1 = ParentsModel::whereId($parent->parent_id)->first();

                        $user2 = Singleton::whereId($request->login_id)->first();
                        $user1 = Singleton::whereId($request->messaged_user_id)->first();

                        if (isset($user1) && !empty($user1) && isset($user2) && !empty($user2)) {
                            $title = __('msg.Profile Matched');
                            $body = __('msg.Congratulations It’s a Match!');
                            $token = $parent1->fcm_token;
                            $token1 = $parent2->fcm_token;
                            $data = array(
                                'notType' => "profile_matched",
                                'user1_id' => $user1->id,
                                'user1_name' => $user1->name,
                                'user1_profile' => $user1->photo1,
                                'user2_id' => $user2->id,
                                'user2_name' => $user2->name,
                                'user2_profile' => $user2->photo1,
                            );
                            sendFCMNotifications($token, $title, $body, $data);
                            sendFCMNotifications($token1, $title, $body, $data);
                        }
                    }else{
                        $data = [
                            'user_id' => $linked->parent_id,
                            'user_type' => 'parent',
                            'match_id' => $request->messaged_user_id,
                            'singleton_id' => $request->login_id,
                            'matched_parent_id' => $parent->parent_id,
                            'created_at' => date('Y-m-d H:i:s')
                        ];

                        DB::table('matches')->insert($data);
                    }

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
}
