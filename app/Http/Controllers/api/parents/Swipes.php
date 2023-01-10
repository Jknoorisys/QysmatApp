<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\LastSwipe;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\RecievedMatches;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\UnMatches;
use App\Notifications\MatchNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

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

            if ($request->swipe == 'right') {
                $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->first();
                if (!empty($unMatch)) {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.swips.un-matched'),
                    ],400);
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

                $user = ParentsModel::where([['id','=',$parent->parent_id],['status','!=','Deleted']])->first();
                $parent = ParentsModel::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();
                if (isset($user) && !empty($user)) {
                    $title = __('msg.New Message');
                    $message = __('msg.You hav a New Match Request!');
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
            }elseif ($request->swipe == 'left') {
                $left                 = new UnMatches();
                $left->user_id        = $request->login_id ? $request->login_id : '';
                $left->user_type      = $request->user_type ? $request->user_type : '';
                $left->singleton_id   = $request->singleton_id ? $request->singleton_id : '';
                $left->un_matched_id  = $request->swiped_user_id ? $request->swiped_user_id : '';
                $left->save();
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
            }elseif ($request->swipe == 'up') {
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
                if ($premium->active_subscription_id != '1') {
                    $last_swipe = LastSwipe::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['singleton_id', '=', $request->singleton_id]])->first();
                    if(!empty($last_swipe)){
                        if ($last_swipe->swipe == 'right') {
                            MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['matched_id', '=', $last_swipe->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->delete();
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
                                'message'   => __('msg.error'),
                            ],400);
                        }
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.parents.swips.invalid'),
                        ],400);
                    }
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.swips.premium'),
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
