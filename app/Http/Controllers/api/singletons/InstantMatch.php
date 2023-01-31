<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\ChatRequest as ModelsChatRequest;
use App\Models\InstantMatchRequest;
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
            if ($premium->active_subscription_id == '1') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.premium'),
                ],400);
            }

            $userExists = Singleton::find($request->requested_id);
            if(empty($userExists) || $userExists->staus == 'Deleted' || $userExists->staus == 'Blocked'){
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

            $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['matched_id', '=', $request->requested_id]])->first();
            if(empty($Match)){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.send-request.match-list'),
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
            

            $data = [
                'user_id' => $request->login_id,
                'user_type' => $request->user_type,
                'requested_id' => $request->requested_id,
                'requested_parent_id' => $userExists->parent_id,
                'created_at' => Carbon::now(),
            ];

            $sender = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            $reciever = Singleton::where([['id', '=', $request->requested_id], ['status', '=', 'Unblocked']])->first();

            $request = InstantMatchRequest::insert($data);

            if($request){
                $title = __('msg.Instant Match Request');
                $body = __('msg.You have a Instant Match Request from').' '.$sender->name;

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
            'requested_id'   => 'required||numeric',
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
            //code...
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
            // $requests = InstantMatchRequest::where([['requested_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['request_type', '=', 'pending']])->get();

            $requests = InstantMatchRequest::leftJoin('singletons', function($join) {
                    $join->on('singletons.id', '=', 'instant_match_requests.user_id')
                        ->where('instant_match_requests.user_type', '=', 'singleton');
                    })
                    ->where([['instant_match_requests.requested_id', '=', $request->login_id], ['instant_match_requests.user_type', '=', $request->user_type], ['instant_match_requests.request_type', '=', 'pending']])
                    ->get();

            if(!$requests->isEmpty()){
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
