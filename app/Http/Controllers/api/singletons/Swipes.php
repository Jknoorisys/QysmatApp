<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\LastSwipe;
use App\Models\Matches;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\PremiumFeatures;
use App\Models\RecievedMatches;
use App\Models\RematchRequests;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\SwipedUpUsers;
use App\Models\UnMatches;
use App\Notifications\MatchNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Swipes extends Controller
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
            'login_id'       => 'required||numeric',
            'user_type' => [
                'required' ,
                Rule::in(['singleton']),
            ],
            'swiped_user_id'   => 'required||numeric',
            'swipe' => [
                'required' ,
                Rule::in(['left','right','up','down']),
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
            $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->swiped_user_id], ['blocked_user_type', '=', 'singleton']])->first();
            if (!empty($block)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.blocked'),
                ],400);
            }

            $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->swiped_user_id], ['reported_user_type', '=', 'singleton']])->first();
            if (!empty($report)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.reported'),
                ],400);
            }

            $parent = Singleton::where([['id', '=', $request->swiped_user_id], ['status','=', 'Unblocked'], ['is_verified', '=', 'verified']])->first();
            if (empty($parent) || ($parent->parent_id == 0)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.not-linked'),
                ],400);
            }

            $rematchRequest = RematchRequests::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['is_rematched', '=', 'no']])
                                                ->orWhere([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no']])->first();
            if ($request->swipe == 'right') {

                $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->swiped_user_id]])->first();
                if (!empty($unMatch)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.swips.un-matched'),
                    ],400);
                }

                $metched = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_type', '=', 'matched']])
                                    ->orWhere([['match_id', '=', $request->login_id],['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                                    ->first();
                if (!empty($metched)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.swips.matched'),
                    ],400);
                }

                $mutual = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id]])
                                    ->orWhere([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id]])
                                    ->first();

                if (!empty($mutual)) {
                    // $busy = Matches::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'],['status', 'busy']])->first();
                    $matched = Matches::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'],['match_type', 'matched']])
                                        ->orWhere([['match_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'matched']])
                                        ->first();
                    if (!empty($matched)) {
                        $queue_no = Matches::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton']])
                                ->orderBy('queue','desc')
                                ->first();
                        $queue = $queue_no ? $queue_no->queue + 1 : 0;
                        $match_type = 'hold';
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
                            ->update(['match_type' => $match_type, 'queue' => $queue, 'is_reset' => 'no', 'updated_at' => date('Y-m-d H:i:s')]);
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

                // $right              = new MyMatches();
                // $right->user_id     = $request->login_id ? $request->login_id : '';
                // $right->user_type   = $request->user_type ? $request->user_type : '';
                // $right->matched_id  = $request->swiped_user_id ? $request->swiped_user_id : '';
                // $right->save();

                $right = MyMatches::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->swiped_user_id],
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'matched_id' => $request->swiped_user_id]
                );

                if ($right){
                    $right = MyMatches::updateOrInsert(
                        ['user_id' => $request->swiped_user_id, 'user_type' => 'singleton', 'recieved_match_id' => $request->login_id],
                        ['user_id' => $request->swiped_user_id, 'user_type' => 'singleton', 'recieved_match_id' => $request->login_id]
                    );

                    // $recieved = new RecievedMatches();
                    // $recieved->user_id = $request->swiped_user_id ? $request->swiped_user_id : '';
                    // $recieved->user_type = 'singleton';
                    // $recieved->recieved_match_id = $request->login_id ? $request->login_id : '';
                    // $recieved->save();
                }

                if (!empty($rematchRequest)) {
                    $matched_table_id = $rematchRequest->matched_table_id;
                    $data = ['is_rematched' => 'yes', 'updated_at' => Carbon::now()];
                    $rematchRequest = RematchRequests::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['is_rematched', '=', 'no']])
                                                        ->orWhere([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no']])->update($data);
                    Matches::where('id', '=', $matched_table_id)->update($data);
                }

                $swipe = LastSwipe::updateOrCreate(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type],
                    [
                        'user_id'           => $request->login_id ? $request->login_id : '',
                        'user_type'         => $request->user_type ? $request->user_type : '',
                        'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                        'swipe'             => 'right',
                    ]
                );
            }elseif ($request->swipe == 'left') {
                // $left                 = new UnMatches();
                // $left->user_id        = $request->login_id ? $request->login_id : '';
                // $left->user_type      = $request->user_type ? $request->user_type : '';
                // $left->un_matched_id  = $request->swiped_user_id ? $request->swiped_user_id : '';
                // $left->save();

                $left   =  UnMatches::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'un_matched_id' => $request->swiped_user_id],
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'un_matched_id' => $request->swiped_user_id]
                );

                UnMatches::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'un_matched_id' => $request->swiped_user_id],
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'un_matched_id' => $request->swiped_user_id]
                );
                $swipe = LastSwipe::updateOrCreate(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type],
                    [
                        'user_id'           => $request->login_id ? $request->login_id : '',
                        'user_type'         => $request->user_type ? $request->user_type : '',
                        'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                        'swipe'             => 'left',
                    ]
                );

                if (!empty($rematchRequest)) {
                    $matched_table_id = $rematchRequest->matched_table_id;
                    $data = ['is_rematched' => 'yes', 'updated_at' => Carbon::now()];
                    $rematchRequest = RematchRequests::where([['user_id', '=', $request->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['is_rematched', '=', 'no']])
                                                        ->orWhere([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no']])->update($data);
                    Matches::where('id', '=', $matched_table_id)->update($data);
                }
            }elseif ($request->swipe == 'up') {
                $swiped_data = [
                    'user_id' => $request->login_id ? $request->login_id : '',
                    'user_type'  => $request->user_type ? $request->user_type : '',
                    'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                    'created_at'    => Carbon::now(),
                ];
                SwipedUpUsers::insert($swiped_data);
                $swipe = LastSwipe::updateOrCreate(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type],
                    [
                        'user_id'           => $request->login_id ? $request->login_id : '',
                        'user_type'         => $request->user_type ? $request->user_type : '',
                        'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                        'swipe'             => 'up',
                    ]
                );
            }elseif ($request->swipe == 'down') {
                $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                $featureStatus = PremiumFeatures::whereId(1)->first();
                if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.swips.premium'),
                    ],400);
                }

                $last_swipe = LastSwipe::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type]])->first();
                if(!empty($last_swipe)){
                    if ($last_swipe->swipe == 'right') {
                        MyMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['matched_id', '=', $last_swipe->swiped_user_id]])->delete();

                        $match = Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $last_swipe->swiped_user_id]])
                                            ->orWhere([['user_id', '=', $last_swipe->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id]])->first();
                        if (!empty($match) && $match->match_type == 'liked') {
                            Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $last_swipe->swiped_user_id]])
                                    ->orWhere([['user_id', '=', $last_swipe->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id]])
                                    ->delete();
                        }elseif (!empty($match) && ($match->match_type == 'matched' || $match->match_type == 'hold')) {
                            Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $last_swipe->swiped_user_id]])
                                        ->orWhere([['user_id', '=', $last_swipe->swiped_user_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id]])
                                        ->update(['match_type' => 'liked', 'is_reset' => 'no', 'queue' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
                        }

                        $swipe = LastSwipe::updateOrCreate(
                            ['user_id' => $request->login_id, 'user_type' => $request->user_type],
                            [
                                'user_id'           => $request->login_id ? $request->login_id : '',
                                'user_type'         => $request->user_type ? $request->user_type : '',
                                'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                                'swipe'             => '',
                            ]
                        );
                    }elseif ($last_swipe->swipe == 'left') {
                        UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $last_swipe->swiped_user_id]])->delete();
                        $swipe = LastSwipe::updateOrCreate(
                            ['user_id' => $request->login_id, 'user_type' => $request->user_type],
                            [
                                'user_id'           => $request->login_id ? $request->login_id : '',
                                'user_type'         => $request->user_type ? $request->user_type : '',
                                'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                                'swipe'             => '',
                            ]
                        );
                    }elseif ($last_swipe->swipe == 'up') {
                        SwipedUpUsers::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['swiped_user_id', '=', $last_swipe->swiped_user_id]])->delete();
                        $swipe = LastSwipe::updateOrCreate(
                            ['user_id' => $request->login_id, 'user_type' => $request->user_type],
                            [
                                'user_id'           => $request->login_id ? $request->login_id : '',
                                'user_type'         => $request->user_type ? $request->user_type : '',
                                'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                                'swipe'             => '',
                            ]
                        );
                    } else {
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.singletons.swips.down'),
                        ],400);
                    }
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.swips.invalid'),
                    ],400);
                }
            }

            if(!empty($swipe)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.swips.success').$request->swipe,
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.swips.failure'),
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
