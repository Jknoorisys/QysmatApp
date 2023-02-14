<?php

namespace App\Http\Controllers\api\agora;

use App\Http\Controllers\Controller;
use App\Models\BankDetails as ModelsBankDetails;
use App\Models\BlockList;
use App\Models\CallHistory;
use App\Models\Matches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\UnMatches;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
// use Willywes\AgoraSDK\RtcTokenBuilder;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Call extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    } 

    // public function index(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'language' => [
    //             'required' ,
    //             Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
    //         ],
    //         'login_id'   => 'required||numeric',
    //         'user_type' => [
    //             'required' ,
    //             Rule::in(['singleton','parent']),
    //         ],
    //     ]);

    //     if($validator->fails()){
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.Validation Failed!'),
    //             'errors'    => $validator->errors()
    //         ],400);
    //     }

    //     try {

    //         if ($request->user_type == 'singleton') {
    //             $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
    //         } else {
    //             $premium = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
    //         }

    //         if ($premium->active_subscription_id == '1') {
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.reset-profile.premium'),
    //             ],400);
    //         }

    //         // $user_id = $request->login_id;
    //         // $user_type = $request->user_type;

    //         // $appID = env('APP_ID');
    //         // $appCertificate = env('APP_CERTIFICATE');
           
    //         // $channelName = "7d72365eb983485397e3e3f9d460bdda";
    //         // $uid = 2882341273;
    //         // $uidStr = "2882341273";
    //         // $role = RtcTokenBuilder::RoleAttendee;
    //         // $expireTimeInSeconds = 3600;
    //         // $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
    //         // $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
    //         // $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds; 
        
    //         // return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs); 
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.error'),
    //             'error'     => $e->getMessage()
    //         ],500);
    //     }
    // }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'caller_id'   => 'required||numeric',
            'caller_user_type' => [
                'required' ,
                Rule::in(['singleton','parent']),
            ],
            'receiver_id'   => 'required||numeric',
            'receiver_user_type' => [
                'required' ,
                Rule::in(['singleton','parent']),
            ],
            'singleton_id' => [
                'required_if:caller_user_type,parent',
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

            if ($request->caller_user_type == 'singleton') {
                $premium = Singleton::where([['id', '=', $request->caller_id], ['status', '=', 'Unblocked']])->first();
                $sender_pic = $premium ? $premium->photo1 : '';
            } else {
                $premium = ParentsModel::where([['id', '=', $request->caller_id], ['status', '=', 'Unblocked']])->first();
                $sender_pic = $premium ? $premium->profile_pic : '';
            }

            if ($premium->active_subscription_id == '1') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.reset-profile.premium'),
                ],400);
            }

            if ($request->receiver_user_type == 'singleton') {
                $reciever = Singleton::where([['id', '=', $request->receiver_id], ['status', '=', 'Unblocked']])->first();
            } else {
                $reciever = ParentsModel::where([['id', '=', $request->receiver_id], ['status', '=', 'Unblocked']])->first();
            }

            $cname  =   (string) random_int(100000000, 9999999999999999);
            // $cname  =   'QysmatApp';
            $token  =   $this->generateTokenForChannel($cname);

            if ($token) {
                $title = __('msg.Call');
                $body = __('msg.You have a Call from').' '.$premium->name;

                if (isset($reciever) && !empty($reciever)) {
                    $token = $reciever->fcm_token;
                    $data = array(
                        'notType' => "video_call" || "audio_call",
                        'from_user_name' => $premium->name,
                        'from_user_pic' => $sender_pic,
                        'from_user_id' => $premium->id,
                        'to_user_id' => $reciever->id,
                        'to_user_type' => $reciever->user_type,
                        'channel_name' => $cname,
                        'token' => $token,
                    );

                    sendFCMNotifications($token, $title, $body, $data);
                }

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.agora.success'),
                    'channel_name' => $cname, 
                    'token' => $token
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.agora.failure'),
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

    private function generateTokenForChannel($cname = null, $uid = 0)
    {
        $appID                  =   env('APP_ID');
        $appCertificate         =   env('APP_CERTIFICATE');

        $role                   =   RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds    =   3600;
        $currentTimestamp       =   (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs     =   $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $cname, $uid, $role, $privilegeExpiredTs);
    }   
    
    public function callHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'caller_id'   => 'required||numeric',
            'caller_user_type' => [
                'required' ,
                Rule::in(['singleton','parent']),
            ],
            'receiver_id'   => 'required||numeric',
            'receiver_user_type' => [
                'required' ,
                Rule::in(['singleton','parent']),
            ],
            'call_type' => [
                'required' ,
                Rule::in(['audio','video']),
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
            $data = [
                'caller_id' => $request->caller_id,
                'caller_type' => $request->caller_user_type,
                'receiver_id' => $request->receiver_id,
                'receiver_type' => $request->receiver_user_type,
                'call_type' => $request->call_type,
                'created_at' => date('Y-m-d H:i:s')
            ];

            $insert = CallHistory::insert($data);
            if ($insert) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.agora.create.success'),
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.agora.create.failure'),
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
