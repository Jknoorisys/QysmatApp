<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\InstantMatchRequest;
use App\Models\Matches;
use App\Models\MessagedUsers;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\PremiumFeatures;
use App\Models\RecievedMatches;
use App\Models\ReferredMatches;
use App\Models\Singleton;
use App\Models\ReportedUsers as ModelsReportedUsers;
use App\Models\UnMatches;
use App\Notifications\InstantMatchNotification;
use App\Notifications\ReferNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class InstantMatch extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userExist($_POST['login_id'], $_POST['user_type']);
        }
    }

    public function sendInstantRequest(Request $request)
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
            'requested_id'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {

            $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            $featureStatus = PremiumFeatures::whereId(1)->first();
            if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.premium'),
                ],400);
            }

            $userExists = Singleton::find($request->requested_id);
            if(empty($userExists) || $userExists->status == 'Deleted' || $userExists->status == 'Blocked'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.invalid'),
                ],400);
            }

            if(empty($userExists) || $userExists->parent_id == 0){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.not-linked'),
                ],400);
            }

            // $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['matched_id', '=', $request->requested_id]])->first();
            // if(empty($Match)){
            //     return response()->json([
            //         'status'    => 'failed',
            //         'message'   => __('msg.singletons.send-request.match-list'),
            //     ],400);
            // }

            $metched = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_type', '=', 'matched']])
                                    ->orWhere([['match_id', '=', $request->login_id],['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                                    ->first();
            if (!empty($metched)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.matched'),
                ],400);
            }

            $formsSubmitted = InstantMatchRequest::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type]])
                    ->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()])
                    ->count();

            if ($formsSubmitted >= 3) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.limit'),
                ],400);
            }           

            $sender = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            $reciever = Singleton::where([['id', '=', $request->requested_id], ['status', '=', 'Unblocked']])->first();

            $from = InstantMatchRequest::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type], ['requested_parent_id', '=', $userExists->parent_id], ['requested_id', '=', $request->requested_id], ['request_type', '=', 'pending']])->first();
            if (!empty($from)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.duplicate'),
                ],400);
            }

            // $data = [
            //     'user_id' => $request->login_id,
            //     'user_type' => $request->user_type,
            //     'requested_id' => $request->requested_id,
            //     'requested_parent_id' => $userExists->parent_id,
            //     'request_type' => 'pending',
            //     'created_at' => Carbon::now(),
            // ];

            // $requests = InstantMatchRequest::insert($data);

            $requests = InstantMatchRequest::updateOrInsert(
                ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'requested_id' => $request->requested_id, 'requested_parent_id' => $userExists->parent_id],
                ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'requested_id' => $request->requested_id, 'requested_parent_id' => $userExists->parent_id, 'request_type' => 'pending']
            );

            if($requests){
                $title = __('msg.Instant Match Request');
                $body = __('msg.You have a Instant Match Request from').' '.$sender->name;

                if (isset($reciever) && !empty($reciever) && $reciever->chat_status != 'busy') {
                    $token = $reciever->fcm_token;
                    $data = array(
                        'notType' => "instant_request",
                        'sender_name' => $sender->name,
                        'sender_pic'=> $sender->photo1,
                        'sender_id'=> $sender->id,
                        'reciever_id'=> $reciever->id
                    );

                    $result = sendFCMNotifications($token, $title, $body, $data);
                }

                $reciever->notify(new InstantMatchNotification($sender, $reciever->user_type, 0));
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.send-request.success'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.failure'),
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

    public function changeRequestStatus(Request $request)
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
            'swiped_user_id'   => 'required||numeric',
            'status'          => [
                'required' ,
                Rule::in(['matched','un-matched','rejected']),
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
            $status = $request->status;

            $parent = Singleton::where([['id', '=', $request->swiped_user_id], ['status','=', 'Unblocked']])->first();
            if (empty($parent) || ($parent->parent_id == 0)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.change-request-status.not-linked'),
                ],400);
            }

            $requests = InstantMatchRequest::where([['requested_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['request_type', '=', 'pending']])->first();
            if (empty($requests)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.change-request-status.invalid'),
                ],400);
            }

            if ($status == 'rejected') {
                $update = InstantMatchRequest::where([['requested_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['request_type', '=', 'pending']])
                                    ->update(['request_type' => 'rejected', 'updated_at' => Carbon::now()]);
            }elseif ($status == 'un-matched') {
                $update = InstantMatchRequest::where([['requested_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['request_type', '=', 'pending']])
                                    ->update(['request_type' => 'un-matched', 'updated_at' => Carbon::now()]);
                if ($update) {
                    Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id]])
                                ->orWhere([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id]])
                                ->delete();

                    UnMatches::insert([
                        'user_id'       => $request->login_id,
                        'user_type'     => $request->user_type,
                        'un_matched_id' => $request->swiped_user_id,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }elseif ($status == 'matched') {
                $update = InstantMatchRequest::where([['requested_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['request_type', '=', 'pending']])
                                                ->update(['request_type' => 'matched', 'updated_at' => Carbon::now()]);
                if ($update) {
                    $mutual = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id]])
                                    ->orWhere([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id]])
                                    ->first();
                                    
                    if (!empty($mutual)) {
                        // $busy = Matches::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'],['status', 'busy']])->first();
                        $matched = Matches::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'],['match_type', 'matched']])
                                            ->orWhere([['match_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                                            ->first();

                        if (!empty($matched)) {
                            $hold = Matches::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'],['match_type', 'hold'], ['match_id', '=', $request->login_id]])
                                            ->orWhere([['user_id', '=', $request->login_id],['match_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold']])
                                            ->first();
                            if (empty($hold)) {
                                $queue_no = Matches::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton']])
                                    ->orderBy('queue','desc')
                                    ->first();
                                $queue = $queue_no->queue + 1;
                                $match_type = 'hold';
                            } else{
                                $queue = $hold->queue;
                                $match_type = 'hold';
                            }
                        }else{
                            $queue = 0;
                            $match_type = 'matched';

                            // send congratulations fcm notification
                            $user = Singleton::where([['id','=',$request->swiped_user_id],['status','!=','Deleted']])->first();
                            $singleton = Singleton::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();

                            if (isset($user) && !empty($user) && isset($singleton) && !empty($singleton)) {
                                $title = __('msg.Profile Matched');
                                $body = __('msg.Congratulations It’s a Match!');
                                $token = $user->fcm_token;
                                $data = array(
                                    'notType' => "profile_matched",
                                    'user1_id' => $user->id,
                                    'user1_name' => $user->name,
                                    'user1_profile' => $user->photo1,
                                    'user2_id' => $singleton->id,
                                    'user2_name' => $singleton->name,
                                    'user2_profile' => $singleton->photo1,
                                );
                                sendFCMNotifications($token, $title, $body, $data);

                                $token1 = $singleton->fcm_token;
                                $data1 = array(
                                    'notType' => "profile_matched",
                                    'user1_id' => $singleton->id,
                                    'user1_name' => $singleton->name,
                                    'user1_profile' => $singleton->photo1,
                                    'user2_id' => $user->id,
                                    'user2_name' => $user->name,
                                    'user2_profile' => $user->photo1,
                                );
                                sendFCMNotifications($token1, $title, $body, $data1);
                            }
                        }

                        Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no']])
                                ->orWhere([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['is_rematched', '=', 'no']])
                                ->update(['match_type' => $match_type, 'is_reset' => 'no', 'queue' => $queue, 'updated_at' => date('Y-m-d H:i:s')]);
                    }else{
                        $data = [
                            'user_id' => $request->login_id,
                            'user_type' => $request->user_type,
                            'match_id' => $request->swiped_user_id,
                            'matched_parent_id' => $parent->parent_id,
                            'created_at' => date('Y-m-d H:i:s')
                        ];
                        DB::table('matches')->insert($data);
                    }

                    $right = MyMatches::updateOrInsert(
                        ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->swiped_user_id],
                        ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->swiped_user_id]
                    ); 

                    if ($right){
                        $recieved = RecievedMatches::updateOrInsert(
                            ['user_id' => $request->swiped_user_id, 'user_type' => 'singleton', 'recieved_match_id' => $request->login_id],
                            ['user_id' => $request->swiped_user_id, 'user_type' => 'singleton', 'recieved_match_id' => $request->login_id]
                        );
                    }
                }
            }

            if ($update) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.change-request-status.success'),
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.change-request-status.failure'),
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

    public function requestList(Request $request)
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
            $requests = InstantMatchRequest::leftJoin('singletons', function($join) {
                    $join->on('singletons.id', '=', 'instant_match_requests.user_id')
                        ->where('instant_match_requests.user_type', '=', 'singleton');
                    })
                    ->where([['instant_match_requests.requested_id', '=', $request->login_id], ['instant_match_requests.user_type', '=', $request->user_type], ['instant_match_requests.request_type', '=', 'pending']])
                    ->get(['instant_match_requests.id as request_id', 'singletons.*']);

            $matched = Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                                ->orWhere([['match_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])->get();

            if(!$requests->isEmpty() && $matched->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.requests-list.success'),
                    'data'      => $requests
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.requests-list.failure'),
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
