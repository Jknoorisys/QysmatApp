<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\LastSwipe;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
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

class Swipes extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {

            if ($_POST['user_type'] == 'singleton') {
                $user = Singleton::find($_POST['login_id']);
                if (empty($user) || $user->status != 'Unblocked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.User Not Found!'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                if (empty($user) || $user->is_verified != 'verified') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Profile not Verified, Please Try After Some Time...'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                $linked = ParentChild::where('singleton_id','=',$_POST['login_id'])->first();
                if (empty($linked) || ($linked->status) != 'Linked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }
            } else {
                $user = ParentsModel::find($_POST['login_id']);
                if (empty($user) || $user->status != 'Unblocked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.User Not Found!'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                if (empty($user) || $user->is_verified != 'verified') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Profile not Verified, Please Try After Some Time...'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }

                $linked = ParentChild::where('parent_id','=',$_POST['login_id'])->first();
                if (empty($linked) || ($linked->status) != 'Linked') {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }
            }

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

        $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $request->swiped_user_id], ['blocked_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
        if (!empty($block)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Blocked this User!'),
            ],400);
        }

        $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $request->swiped_user_id], ['reported_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
        if (!empty($report)) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.You have Reported this User!'),
            ],400);
        }

        // $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->first();
        // if (!empty($unMatch)) {
        //     return response()->json([
        //         'status'    => 'failed',
        //         'message'   => __('msg.You have Un-Matched this User!'),
        //     ],400);
        // }

        if ($request->swipe == 'right') {
            $right               = new MyMatches();
            $right->user_id      = $request->login_id;
            $right->user_type    = $request->user_type;
            $right->singleton_id = $request->singleton_id;
            $right->matched_id   = $request->swiped_user_id;
            $right->save();
            $swipe = LastSwipe::updateOrCreate(
                ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'swiped_user_id'    => $request->swiped_user_id, 'singleton_id'    => $request->singleton_id],
                [
                    'user_id'           => $request->login_id,
                    'user_type'         => $request->user_type,
                    'swiped_user_id'    => $request->swiped_user_id,
                    'singleton_id'      => $request->singleton_id,
                    'swipe'             => 'right',
                ]
            );
        }elseif ($request->swipe == 'left') {
            $left                 = new UnMatches();
            $left->user_id        = $request->login_id;
            $left->user_type      = $request->user_type;
            $left->singleton_id   = $request->singleton_id;
            $left->un_matched_id  = $request->swiped_user_id;
            $left->save();
            $swipe = LastSwipe::updateOrCreate(
                ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'swiped_user_id'    => $request->swiped_user_id, 'singleton_id'    => $request->singleton_id],
                [
                    'user_id'           => $request->login_id,
                    'user_type'         => $request->user_type,
                    'singleton_id'      => $request->singleton_id,
                    'swiped_user_id'    => $request->swiped_user_id,
                    'swipe'             => 'left',
                ]
            );
        }elseif ($request->swipe == 'up') {
            $swipe = LastSwipe::updateOrCreate(
                ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'swiped_user_id'    => $request->swiped_user_id, 'singleton_id'    => $request->singleton_id],
                [
                    'user_id'           => $request->login_id,
                    'user_type'         => $request->user_type,
                    'singleton_id'      => $request->singleton_id,
                    'swiped_user_id'    => $request->swiped_user_id,
                    'swipe'             => 'up',
                ]
            );
        }else {
            $last_swipe = LastSwipe::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['swiped_user_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->first();
            if(!empty($last_swipe)){
                if ($last_swipe->swipe == 'right') {
                    MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['matched_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->delete();
                    $swipe = LastSwipe::updateOrCreate(
                        ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                        [
                            'user_id'           => $request->login_id,
                            'user_type'         => $request->user_type,
                            'singleton_id'      => $request->singleton_id,
                            'swiped_user_id'    => $request->swiped_user_id,
                            'swipe'             => '',
                        ]
                    );
                }elseif ($last_swipe->swipe == 'left') {
                    UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $request->swiped_user_id], ['singleton_id', '=', $request->singleton_id]])->delete();
                    $swipe = LastSwipe::updateOrCreate(
                        ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                        [
                            'user_id'           => $request->login_id,
                            'user_type'         => $request->user_type,
                            'singleton_id'    => $request->singleton_id,
                            'swiped_user_id'    => $request->swiped_user_id,
                            'swipe'             => '',
                        ]
                    );
                }elseif ($last_swipe->swipe == 'up') {
                    $swipe = LastSwipe::updateOrCreate(
                        ['user_id' => $request->login_id, 'user_type' => $request->user_type, 'singleton_id' => $request->singleton_id],
                        [
                            'user_id'           => $request->login_id,
                            'user_type'         => $request->user_type,
                            'singleton_id'      => $request->singleton_id,
                            'swiped_user_id'    => $request->swiped_user_id,
                            'swipe'             => '',
                        ]
                    );
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                ],400);
            }
        }

        if(!empty($swipe)){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Swiped ').$request->swipe,
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Swipe Failed!'),
            ],400);
        }
    }
}
