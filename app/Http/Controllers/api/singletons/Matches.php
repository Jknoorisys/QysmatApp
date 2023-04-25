<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\Matches as ModelsMatches;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\PremiumFeatures;
use App\Models\RecievedMatches;
use App\Models\ReferredMatches;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\UnMatches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Matches extends Controller
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
            'un_matched_id'   => 'required||numeric',
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
            $un_matched_id = $request->un_matched_id;
            $userExists = Singleton::find($request->un_matched_id);

            if(empty($userExists) || $userExists->staus == 'Deleted' || $userExists->staus == 'Blocked'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.un-match.invalid'),
                ],400);
            }

            $matchExists = MyMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['singleton_id', '=', '0'],['matched_id', '=', $request->un_matched_id]])->first();
            $receievdMatchExists = RecievedMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['singleton_id', '=', '0'],['recieved_match_id', '=', $request->un_matched_id]])->first();
            $referredMatchExists = ReferredMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['singleton_id', '=', '0'],['referred_match_id', '=', $request->un_matched_id]])->first();
            $matched = ModelsMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->un_matched_id], ['match_type', '=', 'matched']])
                                        ->orWhere([['user_id', '=', $request->un_matched_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['match_type', '=', 'matched']])
                                        ->first();

            if(!empty($matchExists) || !empty($receievdMatchExists) || !empty($referredMatchExists) || !empty($matched)){
                if (!empty($matched)) {
                    ModelsMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->un_matched_id], ['match_type', '=', 'matched']])
                                    ->orWhere([['user_id', '=', $request->un_matched_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['match_type', '=', 'matched']])
                                    ->update(['match_type' => 'un-matched', 'updated_at' => date('Y-m-d H:i:s'), 'status' => 'available']);

                    $queue = ModelsMatches::
                                            leftjoin('singletons', function($join) use ($singleton_id) {
                                                $join->on('singletons.id','=','matches.match_id')
                                                    ->where('matches.match_id','!=',$singleton_id);
                                                $join->orOn('singletons.id','=','matches.user_id')
                                                    ->where('matches.user_id','!=',$singleton_id);
                                            })
                                            ->where('singletons.chat_status', '=','available')
                                            // ->where([['matches.user_id', '=', $request->login_id], ['matches.user_type', '=', $request->user_type], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available']])
                                            // ->orWhere([['matches.match_id', '=', $request->login_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available']])
                                            ->where(function($query) use ($singleton_id){
                                                $query->where([['matches.user_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']])
                                                      ->orWhere([['matches.match_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']]);
                                            })
                                            ->orderBy('matches.queue')->first(['matches.*']);

                   if (!empty($queue)) {
                        ModelsMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_type', '=', 'hold'], ['queue', '=', $queue->queue]])
                                    ->orWhere([['match_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold'], ['queue', '=', $queue->queue]])
                                    ->update(['match_type' => 'matched', 'queue' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
                        
                        $notify = ModelsMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_type', '=', 'matched']])
                        ->orWhere([['match_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                        ->first();

                        if (!empty($notify)) {
                            // send congratulations fcm notification
                            $user1 = Singleton::where([['id', '=', $notify->user_id],['user_type', '=', 'singleton']])->first();
                            $user2 = Singleton::where([['id', '=', $notify->match_id],['user_type', '=', 'singleton']])->first();

                            if (isset($user) && !empty($user)) {
                                $title = __('msg.Profile Matched');
                                $body = __('msg.Congratulations Your Profile is Matched!');
                                $token = $user->fcm_token;
                                $data = array(
                                    'notType' => "profile_matched",
                                    'user1_id' => $user1 ? $user1->id : '',
                                    'user1_name' => $user1 ?  $user1->name : '',
                                    'user1_profile' => $user1 ?  $user1->photo1 : '',
                                    'user2_id' => $user2 ? $user2->id : '',
                                    'user2_name' => $user2 ? $user2->name : '',
                                    'user2_profile' => $user2 ? $user2->photo1 : '',
                                );
                                $result = sendFCMNotifications($token, $title, $body, $data);
                            }
                        }
                   }

                   $other_queue = ModelsMatches::leftjoin('singletons', function($join) use ($un_matched_id) {
                                                $join->on('singletons.id','=','matches.match_id')
                                                    ->where('matches.match_id','!=',$un_matched_id);
                                                $join->orOn('singletons.id','=','matches.user_id')
                                                    ->where('matches.user_id','!=',$un_matched_id);
                                            })
                                            ->where('singletons.chat_status', '=','available')
                                            // ->where([['matches.user_id', '=', $request->un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available']])
                                            // ->orWhere([['matches.match_id', '=', $request->un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available']])
                                            ->where(function($query) use ($un_matched_id){
                                                $query->where([['matches.user_id', '=', $un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']])
                                                      ->orWhere([['matches.match_id', '=', $un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']]);
                                            })
                                            ->orderBy('matches.queue')->first(['matches.*']);

                    if (!empty($other_queue)) {
                        ModelsMatches::where([['user_id', '=', $request->un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold'], ['queue', '=', $other_queue->queue]])
                                        ->orWhere([['match_id', '=', $request->un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold'], ['queue', '=', $other_queue->queue]])
                                        ->update(['match_type' => 'matched','queue' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

                        $notify = ModelsMatches::where([['user_id', '=', $request->un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                        ->orWhere([['match_id', '=', $request->un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                        ->first();

                        if (!empty($notify)) {
                            // send congratulations fcm notification
                            $user1 = Singleton::where([['id', '=', $notify->user_id],['user_type', '=', 'singleton']])->first();
                            $user2 = Singleton::where([['id', '=', $notify->match_id],['user_type', '=', 'singleton']])->first();

                            if (isset($user1) && !empty($user1) && isset($user2) && !empty($user2)) {
                                $title = __('msg.Profile Matched');
                                $body = __('msg.Congratulations Your Profile is Matched!');
                                $token1 = $user1->fcm_token;
                                $token2 = $user2->fcm_token;
                                $data = array(
                                    'notType' => "profile_matched",
                                    'user1_id' => $user1 ? $user1->id : '',
                                    'user1_name' => $user1 ?  $user1->name : '',
                                    'user1_profile' => $user1 ?  $user1->photo1 : '',
                                    'user2_id' => $user2 ? $user2->id : '',
                                    'user2_name' => $user2 ? $user2->name : '',
                                    'user2_profile' => $user2 ? $user2->photo1 : '',
                                );
                                sendFCMNotifications($token1, $title, $body, $data);
                                sendFCMNotifications($token2, $title, $body, $data);
                            }
                        }
                    }
                }

                if (!empty($matchExists)) {
                    MyMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['singleton_id', '=', '0'],['matched_id', '=', $request->un_matched_id]])->delete();
                }

                if (!empty($receievdMatchExists)) {
                    RecievedMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['singleton_id', '=', '0'],['recieved_match_id', '=', $request->un_matched_id]])->delete();
                }

                if (!empty($referredMatchExists)) {
                    ReferredMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['singleton_id', '=', '0'],['referred_match_id', '=', $request->un_matched_id]])->delete();
                }

                $user = new UnMatches();
                $user->un_matched_id             = $request->un_matched_id;
                $user->user_id                   = $request->login_id;
                $user->user_type                 = $request->user_type;
                $user_details                    = $user->save();

                if($user_details){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.un-match.success'),
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.un-match.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.un-match.not-found'),
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

    public function myMatches(Request $request)
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
            $total = DB::table('my_matches')
                        ->where([['my_matches.user_id', '=', $request->login_id], ['my_matches.user_type', '=', $request->user_type]])
                        ->join('singletons', 'my_matches.matched_id', '=', 'singletons.id')
                        ->count();

            $match = DB::table('my_matches')
                        ->where([['my_matches.user_id', '=', $request->login_id], ['my_matches.user_type', '=', $request->user_type]])
                        ->join('singletons', 'my_matches.matched_id', '=', 'singletons.id')
                        ->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get(['my_matches.user_id','my_matches.user_type','singletons.*']);

            if(!$match->isEmpty()){
                $users = [];
                foreach ($match as $m) {
                    $singleton_id = $m->id;
                    $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                    $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                    $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $singleton_id]])->first();

                    if (empty($block) && empty($report)) {
                        // $users[] = $m;
                        $m->visibility = 'enabled';
                    }else{
                        $m->visibility = 'disabled';
                    }

                    if (empty($unMatch)) {
                        $users[] = $m;
                    }

                }

                if(!empty($users)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.match.success'),
                        'data'      => $users,
                        'total'     => $total
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.match.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.match.failure'),
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

    public function RecievedMatches(Request $request)
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
            $total = DB::table('recieved_matches')
                        ->where([['recieved_matches.user_id', '=', $request->login_id], ['recieved_matches.user_type', '=', $request->user_type]])
                        ->join('singletons','recieved_matches.recieved_match_id','=','singletons.id')
                        ->count();

            $match = DB::table('recieved_matches')
                        ->where([['recieved_matches.user_id', '=', $request->login_id], ['recieved_matches.user_type', '=', $request->user_type]])
                        ->join('singletons','recieved_matches.recieved_match_id','=','singletons.id')
                        ->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get(['recieved_matches.user_id','recieved_matches.user_type','recieved_matches.singleton_id','singletons.*']);
            if(!$match->isEmpty()){
                $users = [];
                foreach ($match as $m) {
                    $singleton_id = $m->id;
                    $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                    $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                    $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $singleton_id]])->first();

                    if (empty($block) && empty($report)) {
                        // $users[] = $m;
                        $m->visibility = 'enabled';
                    }else{
                        $m->visibility = 'disabled';
                    }

                    if (empty($unMatch)) {
                        $users[] = $m;
                    }
                }

                if(!empty($users)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.received-match.success'),
                        'data'      => $users,
                        'total'     => $total
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.received-match.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.received-match.failure'),
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

    public function RefferedMatches(Request $request)
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
            $total = DB::table('referred_matches')
                        ->where([['referred_matches.user_id', '=', $request->login_id], ['referred_matches.user_type', '=', $request->user_type]])
                        ->join('singletons', 'referred_matches.referred_match_id', '=', 'singletons.id')
                        ->count();
                        
            $match = DB::table('referred_matches')
                        ->where([['referred_matches.user_id', '=', $request->login_id], ['referred_matches.user_type', '=', $request->user_type]])
                        ->join('singletons', 'referred_matches.referred_match_id', '=', 'singletons.id')
                        ->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get(['referred_matches.user_id','referred_matches.user_type','referred_matches.singleton_id','singletons.*']);
            if(!$match->isEmpty()){
                $users = [];
                foreach ($match as $m) {
                    $singleton_id = $m->id;
                    $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                    $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                    $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $singleton_id]])->first();

                    if (empty($block) && empty($report)) {
                        // $users[] = $m;
                        $m->visibility = 'enabled';
                    }else{
                        $m->visibility = 'disabled';
                    }

                    if (empty($unMatch)) {
                        $users[] = $m;
                    }
                }

                if(!empty($users)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.referred-match.success'),
                        'data'      => $users,
                        'total'     => $total
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.referred-match.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.referred-match.invalid'),
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

    public function MutualMatches(Request $request)
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
            $singleton_id = $request->login_id;
            $page_number = $request->input(key:'page_number', default:1);
            $total = DB::table('matches')
                        // ->where([['matches.user_id', '=', $request->login_id], ['matches.user_type', '=', $request->user_type]])
                        // ->orWhere([['matches.match_id', '=', $request->login_id], ['matches.user_type', '=', 'singleton']])
                        ->where(function($query) use ($singleton_id) {
                            $query->where([['matches.user_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton']])
                                  ->orWhere([['matches.match_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton']]);
                        })
                        ->where(function($query) {
                            $query->where('match_type', '=', 'matched')
                                  ->orWhere('match_type', '=', 'un-matched')
                                  ->orWhere('match_type', '=', 're-matched');
                        })
                        ->count();

            $match = DB::table('matches')
                        // ->where([['matches.user_id', '=', $request->login_id], ['matches.user_type', '=', $request->user_type]])
                        // ->orWhere([['matches.match_id', '=', $request->login_id], ['matches.user_type', '=', 'singleton']])
                        ->where(function($query) use ($singleton_id) {
                            $query->where([['matches.user_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton']])
                                  ->orWhere([['matches.match_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton']]);
                        })
                        ->where(function($query) {
                            $query->where('match_type', '=', 'matched')
                                  ->orWhere('match_type', '=', 'un-matched')
                                  ->orWhere('match_type', '=', 're-matched');
                        })
                        // ->join('singletons', 'matches.match_id', '=', 'singletons.id')
                        ->leftjoin('singletons', function($join) use ($singleton_id) {
                            $join->on('singletons.id','=','matches.match_id')
                                 ->where('matches.match_id','!=',$singleton_id);
                            $join->orOn('singletons.id','=','matches.user_id')
                                 ->where('matches.user_id','!=',$singleton_id);
                        })
                        ->offset(($page_number - 1) * $per_page)
                        ->limit($per_page)
                        ->get(['matches.user_id','matches.user_type','matches.match_type','matches.is_rematched','singletons.*']);

            if(!$match->isEmpty()){
                // $users = [];
                foreach ($match as $m) {
                    $singleton_ids = $m->id;
                    $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $singleton_ids], ['blocked_user_type', '=', 'singleton']])->first();
                    $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $singleton_ids], ['reported_user_type', '=', 'singleton']])->first();
                    // $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $singleton_ids]])->first();
                    // if (empty($block) && empty($report) && empty($unMatch)) {
                    //     $users[] = $m;
                    // }

                    if (empty($block) && empty($report)) {
                        // $users[] = $m;
                        $m->visibility = 'enabled';
                    }else{
                        $m->visibility = 'disabled';
                    }

                    // empty($block) ? $m->blocked = '' : 'Blocked';
                    // empty($report) ? $m->Reported = '' : 'Reported';
                    // empty($unMatch) ? $m->Unmatched = '' : 'Unmatched';
                }

                // if(!empty($users)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.match.success'),
                        'data'      => $match,
                        'total'     => $total
                    ],200);
                // }else{
                //     return response()->json([
                //         'status'    => 'failed',
                //         'message'   => __('msg.singletons.match.failure'),
                //     ],400);
                // }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.match.invalid'),
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

    public function reMatch(Request $request)
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
            're_matched_id'   => 'required||numeric',
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
            $re_matched_id = $request->re_matched_id;
            $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            $featureStatus = PremiumFeatures::whereId(1)->first();
            if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.re-match.premium'),
                ],400);
            }

            $userExists = Singleton::find($request->re_matched_id);
            if(empty($userExists) || $userExists->staus == 'Deleted' || $userExists->staus == 'Blocked'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.re-match.invalid'),
                ],400);
            }

            $rematched = ModelsMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->re_matched_id]])
                                        ->orWhere([['user_id', '=', $request->re_matched_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id]])
                                        ->first();

            if(!empty($rematched) && $rematched->is_rematched == 'yes'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.re-match.rematched'),
                ],400);
            }

            $blocked = BlockList::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->re_matched_id], ['blocked_user_type', '=', 'singleton']])
                                        ->orWhere([['user_id', '=', $request->re_matched_id], ['user_type', '=', 'singleton'], ['blocked_user_id', '=', $request->login_id], ['blocked_user_type', '=', 'singleton']])
                                        ->first();

            if(!empty($blocked)){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.re-match.failure'),
                ],400);
            }

            $reported = ReportedUsers::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->re_matched_id], ['reported_user_type', '=', 'singleton']])
                                        ->orWhere([['user_id', '=', $request->re_matched_id], ['user_type', '=', 'singleton'], ['reported_user_id', '=', $request->login_id], ['reported_user_type', '=', 'singleton']])
                                        ->first();

            if(!empty($reported)){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.re-match.failure'),
                ],400);
            }

            $unmatched = ModelsMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->re_matched_id], ['match_type', '=', 'un-matched'], ['is_rematched', '=', 'no']])
                                        ->orWhere([['user_id', '=', $request->re_matched_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['match_type', '=', 'un-matched'], ['is_rematched', '=', 'no']])
                                        ->first();

            if(!empty($unmatched)){
               
                $matched = ModelsMatches::where([['matches.user_id', '=', $request->re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'matched'], ['matches.status', '=', 'available']])
                                        ->orWhere([['matches.match_id', '=', $request->re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'matched'], ['matches.status', '=', 'available']])
                                        ->first(['matches.*']);

                $queue = ModelsMatches::leftjoin('singletons', function($join) use ($re_matched_id) {
                                            $join->on('singletons.id','=','matches.match_id')
                                                ->where('matches.match_id','!=',$re_matched_id);
                                            $join->orOn('singletons.id','=','matches.user_id')
                                                ->where('matches.user_id','!=',$re_matched_id);
                                        })
                                        ->where('singletons.chat_status', '=','available')
                                        ->where(function($query) use ($re_matched_id){
                                            $query->where([['matches.user_id', '=', $re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']])
                                                  ->orWhere([['matches.match_id', '=', $re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']]);
                                        })
                                        ->orderBy('matches.queue', 'DESC')->first(['matches.*']);

                $other_queue = ModelsMatches::
                                        leftjoin('singletons', function($join) use ($singleton_id) {
                                            $join->on('singletons.id','=','matches.match_id')
                                                ->where('matches.match_id','!=',$singleton_id);
                                            $join->orOn('singletons.id','=','matches.user_id')
                                                ->where('matches.user_id','!=',$singleton_id);
                                        })
                                        ->where('singletons.chat_status', '=','available')
                                        ->where(function($query) use ($singleton_id){
                                            $query->where([['matches.user_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']])
                                                  ->orWhere([['matches.match_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']]);
                                        })
                                        ->orderBy('matches.queue', 'DESC')->first(['matches.*']);

                // $queue = ModelsMatches::leftJoin('singletons','matches.match_id','=','singletons.id')->where('singletons.chat_status', '=','available')
                //                         ->where([['matches.user_id', '=', $request->re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.status', '=', 'available']])
                //                         ->orWhere([['matches.match_id', '=', $request->re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.status', '=', 'available']])
                //                         ->orderBy('matches.queue', 'DESC')->first();

                // $other_queue =  ModelsMatches::leftJoin('singletons','matches.match_id','=','singletons.id')->where('singletons.chat_status', '=','available')
                //                                 ->where([['matches.user_id', '=', $request->login_id], ['matches.user_type', '=', $request->user_type], ['matches.status', '=', 'available']])
                //                                 ->orWhere([['matches.match_id', '=', $request->login_id], ['matches.user_type', '=', 'singleton'], ['matches.status', '=', 'available']])
                //                                 ->orderBy('matches.queue', 'DESC')->first();

                if (empty($matched)) {
                    $notify = ModelsMatches::where([['matches.user_id', '=', $request->re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'matched']])
                    ->orWhere([['matches.match_id', '=', $request->re_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'matched']])
                    ->first(['matches.*']);

                    if (!empty($notify)) {
                        // send congratulations fcm notification
                        $user1 = Singleton::where([['id', '=', $notify->user_id],['user_type', '=', 'singleton']])->first();
                        $user2 = Singleton::where([['id', '=', $notify->match_id],['user_type', '=', 'singleton']])->first();

                        if (isset($user1) && !empty($user1) && isset($user2) && !empty($user2)) {
                            $title = __('msg.Profile Matched');
                            $body = __('msg.Congratulations Your Profile is Matched!');
                            $token1 = $user1->fcm_token;
                            $token2 = $user2->fcm_token;
                            $data = array(
                                'notType' => "profile_matched",
                                'user1_id' => $user1 ? $user1->id : '',
                                'user1_name' => $user1 ?  $user1->name : '',
                                'user1_profile' => $user1 ?  $user1->photo1 : '',
                                'user2_id' => $user2 ? $user2->id : '',
                                'user2_name' => $user2 ? $user2->name : '',
                                'user2_profile' => $user2 ? $user2->photo1 : '',
                            );
                           sendFCMNotifications($token1, $title, $body, $data);
                           sendFCMNotifications($token2, $title, $body, $data);
                        }
                    }

                    $queue_no = 0;
                    $match_type = 'matched';
                    
                } elseif (!empty($queue) && !empty($matched)) {
                    $queue_no = $queue ? $queue->queue+1 : 0;
                    $match_type = 'hold';
                } else{
                    $queue_no =  $other_queue ?  $other_queue->queue +1 : 0;
                    $match_type = 'hold';
                }

                
                $re_matched = ModelsMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->re_matched_id], ['match_type', '=', 'un-matched']])
                                ->orWhere([['user_id', '=', $request->re_matched_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['match_type', '=', 'un-matched']])
                                ->update(['match_type' => $match_type, 'queue' => $queue_no, 'is_rematched' => 'yes', 'updated_at' => date('Y-m-d H:i:s'), 'status' => 'available']);
                
                
                if($re_matched){
                    UnMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->re_matched_id]])
                                ->orWhere([['user_id', '=', $request->re_matched_id], ['user_type', '=', 'singleton'], ['un_matched_id', '=', $request->login_id]])
                                ->delete();


                    $myMatch = MyMatches::updateOrInsert(
                        ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->re_matched_id],
                        ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->re_matched_id]
                    );  
                    // $myMatch              = new MyMatches();
                    // $myMatch->user_id     = $request->login_id ? $request->login_id : '';
                    // $myMatch->user_type   = $request->user_type ? $request->user_type : '';
                    // $myMatch->matched_id  = $request->re_matched_id ? $request->re_matched_id : '';
                    // $myMatch->save();
    
                    if ($myMatch){
                        $recieved = RecievedMatches::updateOrInsert(
                            ['user_id' => $request->re_matched_id, 'user_type' => 'singleton', 'recieved_match_id' => $request->login_id],
                            ['user_id' => $request->re_matched_id, 'user_type' => 'singleton', 'recieved_match_id' => $request->login_id]
                        ); 
                        // $recieved = new RecievedMatches();
                        // $recieved->user_id = $request->re_matched_id ? $request->re_matched_id : '';
                        // $recieved->user_type = 'singleton';
                        // $recieved->recieved_match_id = $request->login_id ? $request->login_id : '';
                        // $recieved->save();
                    }
                    
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.re-match.success'),
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.re-match.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.re-match.not-found'),
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
