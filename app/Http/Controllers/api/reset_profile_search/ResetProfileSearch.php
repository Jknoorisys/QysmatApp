<?php

namespace App\Http\Controllers\api\reset_profile_search;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\InstantMatchRequest;
use App\Models\LastSwipe;
use App\Models\Matches;
use App\Models\MessagedUsers;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\PremiumFeatures;
use App\Models\RecievedMatches;
use App\Models\ReferredMatches;
use App\Models\ReportedUsers;
use App\Models\ResetProfileSearch as ModelsResetProfileSearch;
use App\Models\Singleton;
use App\Models\SwipedUpUsers;
use App\Models\UnMatches;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ResetProfileSearch extends Controller
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
                Rule::in(['singleton','parent']),
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

            if ($request->user_type == 'singleton') {
                $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            } else {
                $premium = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
            }

            $featureStatus = PremiumFeatures::whereId(1)->first();
            if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.reset-profile.premium'),
                ],400);
            }

            $formsSubmitted = ModelsResetProfileSearch::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type]])
                    ->whereBetween('created_at', [Carbon::now()->subMonth(), Carbon::now()])
                    ->count();

            // if ($formsSubmitted >= 1) {
            //     return response()->json([
            //         'status'    => 'failed',
            //         'message'   => __('msg.reset-profile.reset'),
            //     ],400);
            // }

            $premium->notifications()->where('user_type', '=', $request->user_type)->delete();

            $match = MyMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $unmatch = UnMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $refer = ReferredMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $received = RecievedMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();

            $requests = InstantMatchRequest::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $block = BlockList::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $report = ReportedUsers::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $chat = MessagedUsers::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])
                                    ->orWhere([['messaged_user_id','=',$request->login_id],['messaged_user_type','=',$request->user_type]])->delete();
            $chat = ChatHistory::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])
                                ->orWhere([['messaged_user_id','=',$request->login_id],['messaged_user_type','=',$request->user_type]])->delete();
            $swipe = LastSwipe::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();

            $swipedup = SwipedUpUsers::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();


            if ($request->user_type == 'singleton') {
                $mutual = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'hold']])
                                    ->orWhere([['match_id','=',$request->login_id],['user_type','=','singleton'], ['match_type', '=', 'hold']])
                                    ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no']);
                $liked = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'liked']])
                                ->orWhere([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'un-matched']])
                                ->delete();


                $matched = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'matched']])
                                    ->orWhere([['match_id','=',$request->login_id],['user_type','=','singleton'], ['match_type', '=', 'matched']])
                                    ->first();

                if (!empty($matched)) {
                    $matched->match_id != $request->login_id ? $un_matched_id = $matched->match_id : $un_matched_id = $matched->user_id;

                    $other_queue = Matches::leftjoin('singletons', function($join) use ($un_matched_id) {
                                                $join->on('singletons.id','=','matches.match_id')
                                                    ->where('matches.match_id','!=',$un_matched_id);
                                                $join->orOn('singletons.id','=','matches.user_id')
                                                    ->where('matches.user_id','!=',$un_matched_id);
                                            })
                                            ->where('singletons.chat_status', '=','available')
                                            ->where(function($query) use ($un_matched_id){
                                                $query->where([['matches.user_id', '=', $un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']])
                                                      ->orWhere([['matches.match_id', '=', $un_matched_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'hold'], ['matches.status', '=', 'available'], ['is_rematched', '=', 'no']]);
                                            })
                                            ->orderBy('matches.queue')->first(['matches.*']);

                    if (!empty($other_queue)) {
                        Matches::where([['user_id', '=', $un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold'], ['queue', '=', $other_queue->queue]])
                                        ->orWhere([['match_id', '=', $un_matched_id], ['user_type', '=', 'singleton'], ['match_type', '=', 'hold'], ['queue', '=', $other_queue->queue]])
                                        ->update(['match_type' => 'matched','queue' => 0, 'updated_at' => date('Y-m-d H:i:s')]);

                        // Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'matched']])
                        //             ->orWhere([['match_id','=',$request->login_id],['user_type','=','singleton'], ['match_type', '=', 'matched']])
                        //             ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no'],['status' => 'available']);
                        Singleton::where('id', '=', $un_matched_id)->update(['chat_status' => 'available']);
                    }

                    $match = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'matched']])
                                    ->orWhere([['match_id','=',$request->login_id],['user_type','=','singleton'], ['match_type', '=', 'matched']])
                                    ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no', 'status' => 'available']);

                                    Singleton::where('id', '=', $request->login_id)->update(['chat_status' => 'available']);
                }
                Singleton::where('id', '=', $request->login_id)->update(['chat_status' => 'available']);

            } else {
                $mutual = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'matched']])
                                    ->orWhere([['matched_parent_id','=',$request->login_id],['user_type','=','parent'], ['match_type', '=', 'matched']])
                                    ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no']);
                $liked = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'liked']])
                                    ->orWhere([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'un-matched']])
                                    ->delete();
            }

            if($premium){
                // $delete = UnMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                // if ($delete) {
                    ModelsResetProfileSearch::insert(['user_id' => $request->login_id, 'user_type' => $request->user_type, 'created_at' => Carbon::now()]);
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.reset-profile.success'),
                    ],200);
                // } else {
                //     return response()->json([
                //         'status'    => 'failed',
                //         'message'   => __('msg.reset-profile.failure'),
                //     ],400);
                // }
            }else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.reset-profile.failure'),
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
