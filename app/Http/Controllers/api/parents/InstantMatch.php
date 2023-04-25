<?php

namespace App\Http\Controllers\api\parents;

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
use App\Notifications\AcceptChatRequest;
use App\Notifications\ChatRequest;
use App\Notifications\ReferNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class InstantMatch extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type']) && isset($_POST['singleton_id']) && !empty($_POST['singleton_id'])) {
            parentExist($_POST['login_id'], $_POST['user_type'], $_POST['singleton_id']);
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
                Rule::in(['parent']),
            ],
            'singleton_id'    => 'required||numeric',
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

            $premium = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            $featureStatus = PremiumFeatures::whereId(1)->first();
            if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.premium'),
                ],400);
            }

            $userExists = Singleton::find($request->requested_id);
            if(empty($userExists) || $userExists->status == 'Deleted' || $userExists->status == 'Blocked'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.invalid'),
                ],400);
            }

            if(empty($userExists) || $userExists->parent_id == 0){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.not-linked'),
                ],400);
            }

            $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['matched_id', '=', $request->requested_id], ['singleton_id', '=', $request->singleton_id]])->first();
            if(empty($Match)){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.match-list'),
                ],400);
            }

            $formsSubmitted = InstantMatchRequest::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type]])
                    ->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()])
                    ->count();

            if ($formsSubmitted >= 3) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.limit'),
                ],400);
            }
            

            $data = [
                'user_id' => $request->login_id,
                'user_type' => $request->user_type,
                'singleton_id' => $request->singleton_id,
                'requested_parent_id' => $userExists->parent_id,
                'requested_id' => $request->requested_id,
                'created_at' => Carbon::now(),
            ];

            $sender = Singleton::where([['id', '=', $request->singleton_id], ['status', '=', 'Unblocked']])->first();
            $reciever = ParentsModel::where([['id', '=', $userExists->parent_id], ['status', '=', 'Unblocked']])->first();

            $from = InstantMatchRequest::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['singleton_id', '=', $request->singleton_id], ['requested_parent_id', '=', $userExists->parent_id], ['requested_id', '=', $request->requested_id], ['request_type', '=', 'pending']])->first();
            if (!empty($from)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.duplicate'),
                ],400);
            }

            $requests = InstantMatchRequest::insert($data);

            if($requests){
                $title = __('msg.Instant Match Request');
                $body = __('msg.You have a Instant Match Request from').' '.$premium->name;

                if (isset($reciever) && !empty($reciever)) {
                    $token = $reciever->fcm_token;
                    $data = array(
                        'notType' => "instant_request",
                        'sender_name' => $sender->name,
                        'sender_pic'=> $sender->photo1,
                        'sender_id'=> $sender->id
                    );

                    $result = sendFCMNotifications($token, $title, $body, $data);;
                }
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.send-request.success'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.failure'),
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
                Rule::in(['parent']),
            ],
            'singleton_id'    => 'required||numeric',
            'swiped_user_id'    => 'required||numeric',
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

            $parent = Singleton::where([['id', '=', $request->swiped_user_id], ['status','=', 'Unblocked'], ['is_verified', '=', 'verified']])->first();
            if (empty($parent) || ($parent->parent_id == 0)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.change-request-status.not-linked'),
                ],400);
            }

            $requests = InstantMatchRequest::where([['requested_parent_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['requested_id', '=', $request->singleton_id], ['request_type', '=', 'pending']])->first();
            if (empty($requests)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.change-request-status.invalid'),
                ],400);
            }

            if ($status == 'rejected') {
                $update = InstantMatchRequest::where([['requested_parent_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['requested_id', '=', $request->singleton_id], ['request_type', '=', 'pending']])
                                    ->update(['request_type' => 'rejected', 'updated_at' => Carbon::now()]);
            }elseif ($status == 'un-matched') {
                $update = InstantMatchRequest::where([['requested_parent_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['requested_id', '=', $request->singleton_id], ['request_type', '=', 'pending']])
                                    ->update(['request_type' => 'un-matched', 'updated_at' => Carbon::now()]);
                if ($update) {
                    Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])
                                    ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->swiped_user_id]])
                                    ->delete();

                    UnMatches::insert([
                        'user_id'       => $request->login_id,
                        'user_type'     => $request->user_type,
                        'singleton_id'  => $request->singleton_id,
                        'un_matched_id' => $request->swiped_user_id,
                        'created_at'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }elseif ($status == 'matched') {
                $update = InstantMatchRequest::where([['requested_parent_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['requested_id', '=', $request->singleton_id], ['request_type', '=', 'pending']])
                                    ->update(['request_type' => 'matched', 'updated_at' => Carbon::now()]);
                if ($update) {
                    $mutual = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])
                                    ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->swiped_user_id]])
                                    ->first();
                    if (!empty($mutual)) {
                        Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id], ['is_rematched', '=', 'no']])
                                ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no']])
                                ->update(['match_type' => 'matched', 'updated_at' => date('Y-m-d H:i:s')]);

                        // send congratulations fcm notification
                        $parent1 = ParentsModel::whereId($request->login_id)->first();
                        $parent2 = ParentsModel::whereId($parent->parent_id)->first();

                        $user1 = Singleton::whereId($request->singleton_id)->first();
                        $user2 = Singleton::whereId($request->swiped_user_id)->first();

                        if (isset($user1) && !empty($user1) && isset($user2) && !empty($user2)) {
                            $title = __('msg.Profile Matched');
                            $body = __('msg.Congratulations Your Child Profile is Matched!');
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
                            'user_id'           => $request->login_id,
                            'user_type'         => $request->user_type,
                            'match_id'          => $request->swiped_user_id,
                            'singleton_id'      => $request->singleton_id,
                            'matched_parent_id' => $parent->parent_id,
                            'match_type'        => 'matched',
                            'created_at'        => date('Y-m-d H:i:s')
                        ];
    
                        DB::table('matches')->insert($data);
                    }
                }

                $right               = new MyMatches();
                $right->user_id      = $request->login_id ? $request->login_id : '';
                $right->user_type    = $request->user_type ? $request->user_type : '';
                $right->singleton_id = $request->singleton_id ? $request->singleton_id : '';
                $right->matched_id   = $request->swiped_user_id ? $request->swiped_user_id : '';
                $right->save();

                if ($right){
                    $recieved = new RecievedMatches();
                    $recieved->user_id = $parent->parent_id ? $parent->parent_id : '';
                    $recieved->user_type = 'parent';
                    $recieved->singleton_id = $request->swiped_user_id ? $request->swiped_user_id : '';
                    $recieved->recieved_match_id = $request->singleton_id ? $request->singleton_id : '';
                    $recieved->save();
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
                Rule::in(['parent']),
            ],
            'singleton_id'    => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $requests = InstantMatchRequest::leftJoin('parents', function($join) {
                                            $join->on('parents.id', '=', 'instant_match_requests.user_id')
                                                ->where('instant_match_requests.user_type', '=', 'parent');
                                            })    
                                            ->where([['instant_match_requests.requested_parent_id', '=', $request->login_id], ['instant_match_requests.user_type', '=', $request->user_type], ['instant_match_requests.requested_id', '=', $request->singleton_id], ['instant_match_requests.request_type', '=', 'pending']])
                                            ->get(['instant_match_requests.id as request_id','instant_match_requests.singleton_id', 'parents.*']);

            if(!$requests->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.requests-list.success'),
                    'data'      => $requests
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.requests-list.failure'),
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
