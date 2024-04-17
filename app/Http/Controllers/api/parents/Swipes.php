<?php

namespace App\Http\Controllers\api\parents;

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
use App\Notifications\MutualMatchNotification;
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

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type']) && isset($_POST['singleton_id']) && !empty($_POST['singleton_id'])) {
            parentExist($_POST['login_id'], $_POST['user_type'], $_POST['singleton_id']);
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
                Rule::in(['parent']),
            ],
            'swiped_user_id'   => 'required||numeric',
            'singleton_id'   => 'required||numeric',
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
            $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->swiped_user_id], ['blocked_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
            if (!empty($block)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.swips.blocked'),
                ],400);
            }

            $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->swiped_user_id], ['reported_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
            if (!empty($report)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.swips.reported'),
                ],400);
            }

            $parent = Singleton::where([['id', '=', $request->swiped_user_id], ['status','=', 'Unblocked'], ['is_verified', '=', 'verified']])->first();
            if (empty($parent) || ($parent->parent_id == 0)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.swips.not-linked'),
                ],400);
            }

            $rematchRequest = RematchRequests::where([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['is_rematched', '=', 'no'], ['singleton_id', '=', $request->swiped_user_id]])
                                                ->orWhere([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no'], ['singleton_id', '=', $request->singleton_id]])->first();

            if ($request->swipe == 'right') {
                $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->first();
                if (!empty($unMatch)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.swips.un-matched'),
                    ],400);
                }

                $mutual = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])
                                    ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->swiped_user_id]])
                                    ->first();

                                    
                $parent2 = ParentsModel::whereId($request->login_id)->first();
                $parent1 = ParentsModel::whereId($parent->parent_id)->first();

                $user2 = Singleton::whereId($request->singleton_id)->first();
                $user1 = Singleton::whereId($request->swiped_user_id)->first();

                if (!empty($mutual)) {
                    Matches::where([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no']])
                            ->orWhere([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id], ['is_rematched', '=', 'no']])
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
                            'user1_blur_image' => $user1 ? ($user1->gender == 'Female' ? $mutual->blur_image : 'no') : '',
                            'user2_id' => $user2->id,
                            'user2_name' => $user2->name,
                            'user2_profile' => $user2->photo1,
                            'user2_blur_image' => $user2 ? ($user2->gender == 'Female' ? $mutual->blur_image : 'no') : '',
                        );
                        sendFCMNotifications($token, $title, $body, $data);

                        $token1 = $parent2->fcm_token;
                        $data1 = array(
                            'notType' => "profile_matched",
                            'user1_id' => $user2->id,
                            'user1_name' => $user2->name,
                            'user1_profile' => $user2->photo1,
                            'user1_blur_image' => $user2 ? ($user2->gender == 'Female' ? $mutual->blur_image : 'no') : '',
                            'user2_id' => $user1->id,
                            'user2_name' => $user1->name,
                            'user2_profile' => $user1->photo1,
                            'user2_blur_image' => $user1 ? ($user1->gender == 'Female' ? $mutual->blur_image : 'no') : '',
                        );
                        sendFCMNotifications($token1, $title, $body, $data1);
                    }
                }else{
                    $data = [
                        'user_id' => $request->login_id,
                        'user_type' => $request->user_type,
                        'match_id' => $request->swiped_user_id,
                        'singleton_id' => $request->singleton_id,
                        'matched_parent_id' => $parent->parent_id,
                        'blur_image' => $user1->gender == 'Female' ? $user1->is_blurred : $user2->is_blurred,
                        'created_at' => date('Y-m-d H:i:s')
                    ];

                    DB::table('matches')->insert($data);
                }

                // $right               = new MyMatches();
                // $right->user_id      = $request->login_id ? $request->login_id : '';
                // $right->user_type    = $request->user_type ? $request->user_type : '';
                // $right->singleton_id = $request->singleton_id ? $request->singleton_id : '';
                // $right->matched_id   = $request->swiped_user_id ? $request->swiped_user_id : '';
                // $right->save();

                $right = MyMatches::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id , 'matched_id' => $request->swiped_user_id],
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id , 'matched_id' => $request->swiped_user_id]
                );

                if ($right){

                    $recieved = RecievedMatches::updateOrInsert(
                        ['user_id' => $parent->parent_id, 'user_type' => 'parent', 'singleton_id' => $request->swiped_user_id, 'recieved_match_id' => $request->singleton_id],
                        ['user_id' => $parent->parent_id, 'user_type' => 'parent', 'singleton_id' => $request->swiped_user_id, 'recieved_match_id' => $request->singleton_id]
                    ); 

                    // $recieved = new RecievedMatches();
                    // $recieved->user_id = $parent->parent_id ? $parent->parent_id : '';
                    // $recieved->user_type = 'parent';
                    // $recieved->singleton_id = $request->swiped_user_id ? $request->swiped_user_id : '';
                    // $recieved->recieved_match_id = $request->singleton_id ? $request->singleton_id : '';
                    // $recieved->save();
                }

                $user = ParentsModel::where([['id','=',$parent->parent_id],['status','!=','Deleted']])->first();
                $parent = ParentsModel::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();
                if (isset($user) && !empty($user)) {
                    $title = __('msg.Match Request!');
                    $message = __('msg.You have a Match Request!');
                    $fcm_regid[] = $user->fcm_token;
                    $notification = array(
                        'title'         => $title,
                        'message'       => $message,
                        'click_action'  => 'FLUTTER_NOTIFICATION_CLICK',
                        'date'          => date('Y-m-d H:i'),
                        'type'          => 'verification',
                        'response'      => ''
                    );
                    $result = sendFCMNotification($notification, $fcm_regid, 'verification');

                    // $body = __('msg.You have a New Match Request!');
                    // $token = $user->fcm_token;
                    // $data = array(
                    //     'notType' => "match_request",
                    // );
                    // $result = sendFCMNotifications($token, $title, $body, $data);
                }

                $user->notify(new MatchNotification($parent, $user->user_type, $request->swiped_user_id));

                $swipe = LastSwipe::updateOrCreate(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                    [
                        'user_id'           => $request->login_id ? $request->login_id : '',
                        'user_type'         => $request->user_type ? $request->user_type : '',
                        'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                        'singleton_id'      => $request->singleton_id ? $request->singleton_id : '',
                        'swipe'             => 'right',
                    ]
                );

                if (!empty($rematchRequest)) {
                    $matched_table_id = $rematchRequest->matched_table_id;
                    $data = ['is_rematched' => 'yes', 'updated_at' => Carbon::now()];

                    // $rematchRequest = RematchRequests::where([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['is_rematched', '=', 'no'], ['singleton_id', '=', $request->swiped_user_id]])
                    //                                     ->orWhere([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no'], ['singleton_id', '=', $request->singleton_id]])->update($data);

                    $rematchRequest = RematchRequests::where('id', '=', $rematchRequest->id)->update($data);
                    Matches::where('id', '=', $matched_table_id)->update($data);
                }
            }elseif ($request->swipe == 'left') {
                // $left                 = new UnMatches();
                // $left->user_id        = $request->login_id ? $request->login_id : '';
                // $left->user_type      = $request->user_type ? $request->user_type : '';
                // $left->singleton_id   = $request->singleton_id ? $request->singleton_id : '';
                // $left->un_matched_id  = $request->swiped_user_id ? $request->swiped_user_id : '';
                // $left->save();

                $left   =  UnMatches::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id, 'un_matched_id' => $request->swiped_user_id],
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id, 'un_matched_id' => $request->swiped_user_id]
                );

                $swipe = LastSwipe::updateOrCreate(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                    [
                        'user_id'           => $request->login_id ? $request->login_id : '',
                        'user_type'         => $request->user_type ? $request->user_type : '',
                        'singleton_id'      => $request->singleton_id ? $request->singleton_id : '',
                        'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                        'swipe'             => 'left',
                    ]
                );

                if (!empty($rematchRequest)) {
                    $matched_table_id = $rematchRequest->matched_table_id;
                    $data = ['is_rematched' => 'yes', 'updated_at' => Carbon::now()];

                    // $rematchRequest = RematchRequests::where([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['is_rematched', '=', 'no'], ['singleton_id', '=', $request->swiped_user_id]])
                    //                                     ->orWhere([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->swiped_user_id], ['is_rematched', '=', 'no'], ['singleton_id', '=', $request->singleton_id]])->update($data);

                    $rematchRequest = RematchRequests::where('id', '=', $rematchRequest->id)->update($data);
                    Matches::where('id', '=', $matched_table_id)->update($data);
                }
            }elseif ($request->swipe == 'up') {
                $swiped_data = [
                    'user_id' => $request->login_id ? $request->login_id : '',
                    'user_type'  => $request->user_type ? $request->user_type : '',
                    'singleton_id'      => $request->singleton_id ? $request->singleton_id : '',
                    'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                    'created_at'    => Carbon::now(),
                ];
                SwipedUpUsers::insert($swiped_data);
                $swipe = LastSwipe::updateOrCreate(
                    ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                    [
                        'user_id'           => $request->login_id ? $request->login_id : '',
                        'user_type'         => $request->user_type ? $request->user_type : '',
                        'singleton_id'      => $request->singleton_id ? $request->singleton_id : '',
                        'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                        'swipe'             => 'up',
                    ]
                );
            }elseif ($request->swipe == 'down') {
                $premium = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                $featureStatus = PremiumFeatures::whereId(1)->first();
                if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.swips.premium'),
                    ],400);
                }
                
                $last_swipe = LastSwipe::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['singleton_id', '=', $request->singleton_id]])->first();
                if(!empty($last_swipe)){
                    if ($last_swipe->swipe == 'right') {
                        MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['matched_id', '=', $last_swipe->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->delete();
                        
                        $match = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $last_swipe->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])
                                            ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $last_swipe->swiped_user_id]])
                                            ->first();
                        
                        if (!empty($match) && $match->match_type == 'liked') {
                            Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $last_swipe->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])
                                    ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $last_swipe->swiped_user_id]])
                                    ->delete();
                        }elseif (!empty($match) && $match->match_type == 'matched') {
                            Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $last_swipe->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])
                                    ->orWhere([['user_id', '=', $parent->parent_id], ['user_type', '=', 'parent'], ['match_id', '=', $request->singleton_id], ['singleton_id', '=', $last_swipe->swiped_user_id]])
                                    ->update(['match_type' => 'liked', 'matched_at' =>  Null, 'updated_at' => date('Y-m-d H:i:s')]);
                        }

                        $swipe = LastSwipe::updateOrCreate(
                            ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                            [
                                'user_id'           => $request->login_id ? $request->login_id : '',
                                'user_type'         => $request->user_type ? $request->user_type : '',
                                'singleton_id'      => $request->singleton_id ? $request->singleton_id : '',
                                'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                                'swipe'             => '',
                            ]
                        );
                    }elseif ($last_swipe->swipe == 'left') {
                        UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $last_swipe->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->delete();
                        $swipe = LastSwipe::updateOrCreate(
                            ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                            [
                                'user_id'           => $request->login_id ? $request->login_id : '',
                                'user_type'         => $request->user_type ? $request->user_type : '',
                                'singleton_id'      => $request->singleton_id ? $request->singleton_id : '',
                                'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                                'swipe'             => '',
                            ]
                        );
                    }elseif ($last_swipe->swipe == 'up') {
                        SwipedUpUsers::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['swiped_user_id', '=', $last_swipe->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->delete();
                        $swipe = LastSwipe::updateOrCreate(
                            ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                            [
                                'user_id'           => $request->login_id ? $request->login_id : '',
                                'user_type'         => $request->user_type ? $request->user_type : '',
                                'singleton_id'      => $request->singleton_id ? $request->singleton_id : '',
                                'swiped_user_id'    => $request->swiped_user_id ? $request->swiped_user_id : '',
                                'swipe'             => '',
                            ]
                        );
                    } else {
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.parents.swips.down'),
                        ],400);
                    }
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.swips.invalid'),
                    ],400);
                }
            }

            if(!empty($swipe)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.swips.success').$request->swipe,
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.swips.failure'),
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
