<?php

namespace App\Http\Controllers\api\agora;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\CallHistory;
use App\Models\ParentsModel;
use App\Models\PremiumFeatures;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\UnMatches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Call extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    } 

    // public function index(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'language' => [
    //             'required' ,
    //             Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
    //         ],
    //         'caller_id'   => 'required||numeric',
    //         'caller_user_type' => [
    //             'required' ,
    //             Rule::in(['singleton','parent']),
    //         ],
    //         'receiver_id'   => 'required||numeric',
    //         'receiver_user_type' => [
    //             'required' ,
    //             Rule::in(['singleton','parent']),
    //         ],
    //         'call_type' => [
    //             'required' ,
    //             Rule::in(['audio','video']),
    //         ],
    //         'channel_name' => 'required',
    //         'singleton_id' => [
    //             'required_if:caller_user_type,parent',
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

    //         if ($request->caller_user_type == 'singleton') {
    //             $premium = Singleton::where([['id', '=', $request->caller_id], ['status', '=', 'Unblocked']])->first();
    //             $sender_pic = $premium ? $premium->photo1 : '';
    //         } else {
    //             $premium = ParentsModel::where([['id', '=', $request->caller_id], ['status', '=', 'Unblocked']])->first();
    //             $sender_pic = $premium ? $premium->profile_pic : '';
    //         }

    //         $featureStatus = PremiumFeatures::whereId(1)->first();
    //         if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.agora.premium'),
    //             ],400);
    //         }

    //         if ($request->receiver_user_type == 'singleton') {
    //             $reciever = Singleton::where([['id', '=', $request->receiver_id], ['status', '=', 'Unblocked']])->first();
    //         } else {
    //             $reciever = ParentsModel::where([['id', '=', $request->receiver_id], ['status', '=', 'Unblocked']])->first();
    //         }

    //         $block = BlockList ::where([['user_id', '=', $request->caller_id], ['user_type', '=', $request->caller_user_type], ['blocked_user_id', '=', $request->receiver_id], ['blocked_user_type', '=', $request->receiver_user_type]])
    //                             ->orWhere([['user_id', '=', $request->receiver_id], ['user_type', '=', $request->receiver_user_type], ['blocked_user_id', '=', $request->caller_id], ['blocked_user_type', '=', $request->caller_user_type]])->first();

    //         $report = ReportedUsers ::where([['user_id', '=', $request->caller_id], ['user_type', '=', $request->caller_user_type], ['reported_user_id', '=', $request->receiver_id], ['reported_user_type', '=', $request->receiver_user_type]])
    //                                 ->orWhere([['user_id', '=', $request->receiver_id], ['user_type', '=', $request->receiver_user_type], ['reported_user_id', '=', $request->caller_id], ['reported_user_type', '=', $request->caller_user_type]])->first();

    //         $unMatch = UnMatches ::where([['user_id', '=', $request->caller_id], ['user_type', '=', $request->caller_user_type], ['un_matched_id', '=', $request->receiver_id]])
    //                             ->orWhere([['user_id', '=', $request->receiver_id], ['user_type', '=', $request->receiver_user_type], ['un_matched_id', '=', $request->caller_id]])->first();

    //         if (!empty($block) || !empty($report) || !empty($unMatch)) {
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.agora.invalid'),
    //             ],400);
    //         }

    //         $channelName = $request->channel_name;
    //         $agora  =   GetToken($request->caller_id, $channelName);

    //         if ($agora) {
    //             $title = $premium->name;
    //             $body = __('msg.Incoming').' '.$request->call_type.' '. __('msg.Call');

    //             if (isset($reciever) && !empty($reciever)) {
    //                 $token = $reciever->fcm_token;
    //                 $data = array(
    //                     'notType'        => $request->call_type,
    //                     'from_user_name' => $premium->name,
    //                     'from_user_pic'  => $sender_pic,
    //                     'from_user_id'   => $premium->id,
    //                     'to_user_id'     => $reciever->id,
    //                     'to_user_type'   => $reciever->user_type,
    //                     'channel_name'   => $agora['channel'],
    //                     'token'          => $agora['token'],
    //                 );

    //                 sendFCMNotifications($token, $title, $body, $data);
    //             }

    //             return response()->json([
    //                 'status'    => 'success',
    //                 'message'   => __('msg.agora.success'),
    //                 'channel_name' => $agora['channel'], 
    //                 'token' => $agora['token']
    //             ],200);
    //         }else{
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.agora.failure'),
    //             ],400);
    //         }
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
            'call_type' => [
                'required' ,
                Rule::in(['audio','video']),
            ],
            'channel_name' => 'required',
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

            $channelName = $request->channel_name;
            $agora  =   GetToken($request->login_id, $channelName);

            if ($agora) {
                $title = $premium->name;
                $body = __('msg.Incoming').' '.$request->call_type.' '. __('msg.Call');

                if (isset($reciever) && !empty($reciever)) {
                    $token = $reciever->fcm_token;
                    $data = array(
                        'notType'        => $request->call_type,
                        'from_user_name' => $premium->name,
                        'from_user_pic'  => $sender_pic,
                        'from_user_id'   => $premium->id,
                        'to_user_id'     => $reciever->id,
                        'to_user_type'   => $reciever->user_type,
                        'channel_name'   => $agora['channel'],
                        'token'          => $agora['token'],
                    );

                    sendFCMNotifications($token, $title, $body, $data);
                }

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.agora.success'),
                    'channel_name' => $agora['channel'], 
                    'token' => $agora['token']
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
            'call_status' => [
                'required' ,
                Rule::in(['incoming','accepted','rejected']),
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
                'status'    => $request->call_status,
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

    public function rejectCall(Request $request)
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

            if ($request->caller_user_type == 'singleton') {
                $premium = Singleton::where([['id', '=', $request->caller_id], ['status', '=', 'Unblocked']])->first();
                $sender_pic = $premium ? $premium->photo1 : '';
            } else {
                $premium = ParentsModel::where([['id', '=', $request->caller_id], ['status', '=', 'Unblocked']])->first();
                $sender_pic = $premium ? $premium->profile_pic : '';
            }

            $featureStatus = PremiumFeatures::whereId(1)->first();
            if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
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

            $title = $premium->name;
            $body = __('msg.Call Rejected');

            if (isset($reciever) && !empty($reciever)) {
                $token = $reciever->fcm_token;
                $data = array(
                    'notType'        => 'canceled',
                    'from_user_name' => $premium->name,
                    'from_user_id'   => $premium->id,
                    'from_user_pic'  => $sender_pic,
                    'to_user_id'     => $reciever->id,
                    'to_user_type'   => $reciever->user_type,
                );

                sendFCMNotifications($token, $title, $body, $data);
            }

            $data = [
                'caller_id' => $request->caller_id,
                'caller_type' => $request->caller_user_type,
                'receiver_id' => $request->receiver_id,
                'receiver_type' => $request->receiver_user_type,
                'call_type' => $request->call_type,
                'status'    => 'rejected',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $insert = CallHistory::insert($data);
            
            if ($insert) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.agora.reject.success'),
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.agora.reject.failure'),
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
