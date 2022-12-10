<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
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

class Matches extends Controller
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
            'login_id'  => 'required||numeric',
            'user_type' => [
                'required' ,
                Rule::in(['parent']),
            ],
            'singleton_id'    => 'required||numeric',
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
            $userExists = Singleton::find($request->un_matched_id);

            if(empty($userExists) || $userExists->staus == 'Deleted' || $userExists->staus == 'Blocked'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.un-match.invalid'),
                ],400);
            }

            $matchExists = MyMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['singleton_id', '=', $request->singleton_id],['matched_id', '=', $request->un_matched_id]])->first();

            if(!empty($matchExists)){

                $user = new UnMatches();
                $user->un_matched_id             = $request->un_matched_id;
                $user->singleton_id              = $request->singleton_id;
                $user->user_id                   = $request->login_id;
                $user->user_type                 = $request->user_type;
                $user_details                    = $user->save();

                if($user_details){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.un-match.success'),
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.un-match.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.un-match.not-found'),
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
            $match = DB::table('my_matches')
                            ->where([['my_matches.user_id', '=', $request->login_id], ['my_matches.user_type', '=', $request->user_type], ['my_matches.singleton_id', '=', $request->singleton_id]])
                            ->join('singletons', 'my_matches.matched_id', '=', 'singletons.id')
                            ->get(['my_matches.user_id','my_matches.user_type','singletons.*']);

            if(!$match->isEmpty()){
                $users = [];
                foreach ($match as $m) {
                    $singleton_id = $m->id;
                    $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
                    $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
                    $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['un_matched_id', '=', $singleton_id], ['singleton_id', '=', $request->singleton_id]])->first();
                    $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['matched_id', '=', $singleton_id], ['singleton_id', '=', $request->singleton_id]])->first();

                    if (empty($block) && empty($report) && empty($unMatch) && empty($Match)) {
                        $users[] = $m;
                    }
                }

                if(!empty($users)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.match.success'),
                        'data'      => $users
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.match.invalid'),
                    ],400);
                    }
            }else{
                return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.parents.match.failure'),
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
            $match = DB::table('recieved_matches')
                    ->where([['recieved_matches.user_id', '=', $request->login_id], ['recieved_matches.user_type', '=', $request->user_type], ['recieved_matches.singleton_id', '=', $request->singleton_id]])
                    ->join('singletons','recieved_matches.recieved_match_id','=','singletons.id')
                    ->get(['recieved_matches.user_id','recieved_matches.user_type','recieved_matches.singleton_id','singletons.*']);

            if(!$match->isEmpty()){
                $users = [];
                foreach ($match as $m) {
                    $singleton_id = $m->id;
                    $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
                    $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
                    $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['un_matched_id', '=', $singleton_id], ['singleton_id', '=', $request->singleton_id]])->first();
                    $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['matched_id', '=', $singleton_id], ['singleton_id', '=', $request->singleton_id]])->first();

                    if (empty($block) && empty($report) && empty($unMatch) && empty($Match)) {
                        $users[] = $m;
                    }
                }

                if(!empty($users)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.received-match.success'),
                        'data'      => $users
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.received-match.invalid'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.received-match.failure'),
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
            $match = DB::table('referred_matches')
                    ->where([['referred_matches.user_id', '=', $request->login_id], ['referred_matches.user_type', '=', $request->user_type], ['referred_matches.singleton_id', '=', $request->singleton_id]])
                    ->join('singletons', 'referred_matches.referred_match_id', '=', 'singletons.id')
                    ->get(['referred_matches.user_id','referred_matches.user_type','referred_matches.singleton_id','singletons.*']);
                    
            if(!$match->isEmpty()){
                $users = [];
                foreach ($match as $m) {
                    $singleton_id = $m->id;
                    $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
                    $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton'], ['singleton_id', '=', $request->singleton_id]])->first();
                    $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['un_matched_id', '=', $singleton_id], ['singleton_id', '=', $request->singleton_id]])->first();
                    $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'parent'], ['matched_id', '=', $singleton_id], ['singleton_id', '=', $request->singleton_id]])->first();

                    if (empty($block) && empty($report) && empty($unMatch) && empty($Match)) {
                        $users[] = $m;
                    }
                }

                if(!empty($users)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.referred-match.success'),
                        'data'      => $users
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.referred-match.invalid'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.referred-match.failure'),
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
