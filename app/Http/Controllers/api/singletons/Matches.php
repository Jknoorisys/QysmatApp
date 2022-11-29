<?php

namespace App\Http\Controllers\api\singletons;

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

        $userExists = Singleton::find($request->un_matched_id);

        if(empty($userExists) || $userExists->staus == 'Deleted' || $userExists->staus == 'Blocked'){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.User, You want to Un-Match Not Found!'),
            ],400);
        }

        $matchExists = MyMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['matched_id', '=', $request->un_matched_id]])->first();

        if(!empty($matchExists)){
            MyMatches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type],['matched_id', '=', $request->un_matched_id]])->delete();
            $user = new UnMatches();
            $user->un_matched_id             = $request->un_matched_id;
            $user->user_id                   = $request->login_id;
            $user->user_type                 = $request->user_type;
            $user_details                    = $user->save();

            if($user_details){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.User Un-Matched Successfully!'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                ],400);
            }
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.User Not Found!'),
            ],400);
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
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $match = DB::table('my_matches')
                        ->where([['my_matches.user_id', '=', $request->login_id], ['my_matches.user_type', '=', $request->user_type]])
                        ->join('singletons', 'my_matches.matched_id', '=', 'singletons.id')
                        ->get(['my_matches.user_id','my_matches.user_type','singletons.*']);
                        
        if(!$match->isEmpty()){
            $users = [];
            foreach ($match as $m) {
                $singleton_id = $m->id;
                $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $singleton_id]])->first();

                if (empty($block) && empty($report) && empty($unMatch)) {
                    $users[] = $m;
                }
            }

            if(!empty($users)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Matches List Fetched Successfully!'),
                    'data'      => $users
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.No Match Found!'),
                ],400);
            }
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.No Match Found!'),
            ],400);
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
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $match = DB::table('recieved_matches')
                        ->where([['recieved_matches.user_id', '=', $request->login_id], ['recieved_matches.user_type', '=', $request->user_type]])
                        ->join('singletons','recieved_matches.recieved_match_id','=','singletons.id')
                        ->get(['recieved_matches.user_id','recieved_matches.user_type','recieved_matches.singleton_id','singletons.*']);
        if(!$match->isEmpty()){
            $users = [];
            foreach ($match as $m) {
                $singleton_id = $m->id;
                $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $singleton_id]])->first();

                if (empty($block) && empty($report) && empty($unMatch)) {
                    $users[] = $m;
                }
            }

            if(!empty($users)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Recieved Matches List Fetched Successfully!'),
                    'data'      => $users
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.No Match Found!'),
                ],400);
            }
            // return response()->json([
            //     'status'    => 'success',
            //     'message'   => __('msg.Recieved Matches List Fetched Successfully!'),
            //     'data'      => $match
            // ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.No Match Found!'),
            ],400);
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
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $match = DB::table('referred_matches')
                        ->where([['referred_matches.user_id', '=', $request->login_id], ['referred_matches.user_type', '=', $request->user_type]])
                        ->join('singletons', 'referred_matches.referred_match_id', '=', 'singletons.id')
                        ->get(['referred_matches.user_id','referred_matches.user_type','referred_matches.singleton_id','singletons.*']);
        if(!$match->isEmpty()){
            $users = [];
            foreach ($match as $m) {
                $singleton_id = $m->id;
                $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['un_matched_id', '=', $singleton_id]])->first();

                if (empty($block) && empty($report) && empty($unMatch)) {
                    $users[] = $m;
                }
            }

            if(!empty($users)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Reffered Matches List Fetched Successfully!'),
                    'data'      => $users
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.No Match Found!'),
                ],400);
            }
            // return response()->json([
            //     'status'    => 'success',
            //     'message'   => __('msg.Reffered Matches List Fetched Successfully!'),
            //     'data'      => $match
            // ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.No Match Found!'),
            ],400);
        }
    }
}
