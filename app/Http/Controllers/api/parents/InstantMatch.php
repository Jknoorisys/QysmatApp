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
use App\Notifications\InstantMatchNotification;
use App\Notifications\MutualMatchNotification;
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

            // $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['matched_id', '=', $request->requested_id], ['singleton_id', '=', $request->singleton_id]])->first();
            // if(empty($Match)){
            //     return response()->json([
            //         'status'    => 'failed',
            //         'message'   => __('msg.parents.send-request.match-list'),
            //     ],400);
            // }

            $formsSubmitted = InstantMatchRequest::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type]])
                    ->whereBetween('created_at', [Carbon::now()->subWeek(), Carbon::now()])
                    ->count();

            if ($formsSubmitted >= 3) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.limit'),
                ],400);
            }
          
            $sender = Singleton::where([['id', '=', $request->singleton_id], ['status', '=', 'Unblocked']])->first();
            $reciever = ParentsModel::where([['id', '=', $userExists->parent_id], ['status', '=', 'Unblocked']])->first();

            $form = InstantMatchRequest::where([['user_id', '=', $request->login_id],['user_type', '=', $request->user_type],['singleton_id', '=', $request->singleton_id], ['requested_parent_id', '=', $userExists->parent_id], ['requested_id', '=', $request->requested_id]])->whereIn('request_type', ['pending', 'hold'])->first();
            if (!empty($form)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.send-request.duplicate'),
                ],400);
            }

            $remetched = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['singleton_id', '=', $request->singleton_id], ['match_id', '=', $request->requested_id], ['is_rematched', '=', 'yes']])
                        ->orWhere([['match_id', '=', $request->singleton_id],['user_type', '=', 'parent'], ['singleton_id', '=', $request->requested_id], ['user_id', '=', $userExists->parent_id], ['is_rematched', '=', 'yes']])
                        ->first();
            if (!empty($remetched)) {
                return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.parents.re-match.rematched'),
                ],400);
            }

            $requests = InstantMatchRequest::updateOrInsert(
                ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id, 'requested_id' => $request->requested_id, 'requested_parent_id' => $userExists->parent_id],
                ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id, 'requested_id' => $request->requested_id, 'requested_parent_id' => $userExists->parent_id, 'request_type' => 'pending']
            );

            if($requests){
                $title = __('msg.Instant Match Request');
                $body = __('msg.You have a Instant Match Request from').' '.$premium->name;

                if (isset($reciever) && !empty($reciever)) {
                    $token = $reciever->fcm_token;
                    $data = array(
                        'notType' => "instant_request",
                        'sender_name' => $sender->name,
                        'sender_pic'=> $sender->photo1,
                        'sender_id'=> $sender->id,
                        'sender_blur_image' => ($sender->gender == 'Female' ? $sender->is_blurred : 'no'),
                        'reciever_id'=> $reciever->id
                    );

                    $result = sendFCMNotifications($token, $title, $body, $data);;
                }

                $reciever->notify(new InstantMatchNotification($premium, $reciever->user_type, $request->requested_id));
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
                Rule::in(['matched','un-matched','rejected','maybe']),
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

            // if (empty($parent) || ($parent->is_verified != 'verified')) {
            //     return response()->json([
            //         'status'    => 'failed',
            //         'message'   => __('msg.parents.change-request-status.not-verified'),
            //     ],400);
            // }

            $requests = InstantMatchRequest::where([['user_id', '=', $parent->parent_id], ['singleton_id', '=', $request->swiped_user_id],['requested_parent_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['requested_id', '=', $request->singleton_id], ['request_type', '=', 'pending']])->first();
            if (empty($requests)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.change-request-status.invalid'),
                ],400);
            }

            if ($status == 'rejected') {
                $update = InstantMatchRequest::where([['id', '=', $requests->id], ['request_type', '=', 'pending']])
                                    ->update(['request_type' => 'rejected', 'updated_at' => Carbon::now()]);
            }elseif ($status == 'maybe') {
                $update = InstantMatchRequest::where([['id', '=', $requests->id], ['request_type', '=', 'pending']])
                                    ->update(['request_type' => 'hold', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()]);
            }elseif ($status == 'un-matched') {
                $update = InstantMatchRequest::where([['id', '=', $requests->id], ['request_type', '=', 'pending']])
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

                    $parent2 = ParentsModel::whereId($request->login_id)->first();
                    $parent1 = ParentsModel::whereId($parent->parent_id)->first();

                    $user2 = Singleton::whereId($request->singleton_id)->first();
                    $user1 = Singleton::whereId($request->swiped_user_id)->first();
            
                    if (!empty($mutual)) {
                        Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id], ['is_rematched', '=', 'no']])
                                ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no']])
                                ->update(['match_type' => 'matched', 'matched_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')]);

                        // send congratulations fcm notification
                        if (isset($user1) && !empty($user1) && isset($user2) && !empty($user2)) {

                            // database notification
                            $msg = __('msg.Congratulations! Your child got a new match with');
                            $parent2->notify(new MutualMatchNotification($parent1, $parent2->user_type, $user2->id, ($msg.' '.$user1->name)));
                            $parent1->notify(new MutualMatchNotification($parent2, $parent1->user_type, $user1->id, ($msg.' '.$user2->name)));

                            $title = __('msg.Profile Matched');
                            $body = __('msg.Congratulations It’s a Match!');
                            $token = $parent1->fcm_token;
                            $data = array(
                                'notType' => "profile_matched",
                                'user1_id' => $user1->id,
                                'user1_name' => $user1->name,
                                'user1_profile' => $user1->photo1,
                                'user1_blur_image' => ($user1->gender == 'Female' ? ($mutual->match_type == 'matched' ? $mutual->blur_image : $user1->is_blurred) : 'no'),
                                'user2_id' => $user2->id,
                                'user2_name' => $user2->name,
                                'user2_profile' => $user2->photo1,
                                'user2_blur_image' => ($user2->gender == 'Female' ? ($mutual->match_type == 'matched' ? $mutual->blur_image : $user2->is_blurred) : 'no'),
                            );
                            sendFCMNotifications($token, $title, $body, $data);

                            $token1 = $parent2->fcm_token;
                            $data1 = array(
                                'notType' => "profile_matched",
                                'user1_id' => $user2->id,
                                'user1_name' => $user2->name,
                                'user1_profile' => $user2->photo1,
                                'user1_blur_image' => ($user2->gender == 'Female' ? ($mutual->match_type == 'matched' ? $mutual->blur_image : $user2->is_blurred) : 'no'),
                                'user2_id' => $user1->id,
                                'user2_name' => $user1->name,
                                'user2_profile' => $user1->photo1,
                                'user2_blur_image' => ($user1->gender == 'Female' ? ($mutual->match_type == 'matched' ? $mutual->blur_image : $user1->is_blurred) : 'no'),
                            );
                            sendFCMNotifications($token1, $title, $body, $data1);
                        }
                    }else{
                        $data = [
                            'user_id'           => $request->login_id,
                            'user_type'         => $request->user_type,
                            'match_id'          => $request->swiped_user_id,
                            'singleton_id'      => $request->singleton_id,
                            'matched_parent_id' => $parent->parent_id,
                            'match_type'        => 'matched',
                            'blur_image'        => $user1->gender == 'Female' ? $user1->is_blurred : $user2->is_blurred,
                            'matched_at'        => date('Y-m-d H:i:s'),
                            'created_at'        => date('Y-m-d H:i:s')
                        ];
    
                        DB::table('matches')->insert($data);
                    }
                }

                $right = MyMatches::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->swiped_user_id, 'singleton_id' => $request->singleton_id],
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->swiped_user_id, 'singleton_id' => $request->singleton_id]
                ); 

                // $right               = new MyMatches();
                // $right->user_id      = $request->login_id ? $request->login_id : '';
                // $right->user_type    = $request->user_type ? $request->user_type : '';
                // $right->singleton_id = $request->singleton_id ? $request->singleton_id : '';
                // $right->matched_id   = $request->swiped_user_id ? $request->swiped_user_id : '';
                // $right->save();

                if ($right){
                    $recieved = RecievedMatches::updateOrInsert(
                        ['user_id' => $parent->parent_id, 'user_type' => 'parent', 'recieved_match_id' => $request->singleton_id, 'singleton_id' => $request->swiped_user_id],
                        ['user_id' => $parent->parent_id, 'user_type' => 'parent', 'recieved_match_id' => $request->singleton_id, 'singleton_id' => $request->swiped_user_id]
                    );

                    // $recieved = new RecievedMatches();
                    // $recieved->user_id = $parent->parent_id ? $parent->parent_id : '';
                    // $recieved->user_type = 'parent';
                    // $recieved->singleton_id = $request->swiped_user_id ? $request->swiped_user_id : '';
                    // $recieved->recieved_match_id = $request->singleton_id ? $request->singleton_id : '';
                    // $recieved->save();
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
                $loggedInUserChild = Singleton::find($request->singleton_id);
                foreach ($requests as $instantRequest) {
                    $singleton = Singleton::find($instantRequest->singleton_id);
                    $instantRequest->is_singleton_blurred_photos = $loggedInUserChild->is_blurred;
                    if ($singleton->gender == 'Female') {
                        $instantRequest->blur_image =  $singleton->is_blurred;
                        $instantRequest->is_blurred =  $singleton->is_blurred;
                    } else{
                        $instantRequest->blur_image = 'no';
                        $instantRequest->is_blurred =  'no';
                    }
                }
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
