<?php

namespace App\Http\Controllers\api\delete_account;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DeletedUsers as ModelsDeletedUsers;
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\File;
use App\Models\BlockList;
use App\Models\ChatHistory;
use App\Models\InstantMatchRequest;
use App\Models\LastSwipe;
use App\Models\Matches;
use App\Models\MessagedUsers;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\RecievedMatches;
use App\Models\ReferredMatches;
use App\Models\ReportedUsers;
use App\Models\ResetProfileSearch as ModelsResetProfileSearch;
use App\Models\Singleton;
use App\Models\Transactions;
use App\Models\UnMatches;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Stripe\Stripe;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class DeleteUser extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userFound($_POST['login_id'], $_POST['user_type']);
        }
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'      => 'required||numeric',
            'reason_type'   => 'required',
            'reason'        => 'required',
            'user_type'     => [
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

            if($request->user_type == 'singleton'){
                $userExists = Singleton::find($request->login_id);
            }else{
                $userExists = ParentsModel::find($request->login_id);
            }

            $user = new ModelsDeletedUsers();
            $user->user_id           = $request->login_id;
            $user->user_type         = $request->user_type;
            $user->user_name         = $userExists->name;
            $user->reason_type       = $request->reason_type;
            $user->reason            = $request->reason;
            $user_details = $user->save();

            if($user_details){
                if($request->user_type == 'singleton'){
                    $user = Singleton::find($request->login_id);
                    $active_subscription_id = $user ? $user->active_subscription_id : '';
                    $delete =  Singleton :: whereId($request->login_id)->delete();
                }else{
                    $user = ParentsModel::find($request->login_id);
                    $active_subscription_id = $user ? $user->active_subscription_id : '';
                    $delete =  ParentsModel :: whereId($request->login_id)->delete();
                }
                
                if ($delete) {
                    $login_id = $request->login_id;
                    $user_type = $request->user_type;
                    $transaction = DB::table('transactions')
                                            ->where([['user_id', '=', $login_id],['user_type', '=', $user_type]])
                                            ->orWhere(function($query) use ($login_id, $user_type){
                                                $query->whereRaw("FIND_IN_SET($login_id, other_user_id)")
                                                    ->where('other_user_type', '=', $user_type);
                                            })
                                            ->first();
                    
                    if ($transaction) {
                        $stripe = Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
                        $subscription = \Stripe\Subscription::update(
                            $transaction->subscription_id,
                            [
                                'cancel_at_period_end' => true,
                            ]
                        );

                        if ($subscription) {
                            Transactions::where('id','=', $transaction->id)->update(['subs_status' => $subscription->status, 'updated_at' => date('Y-m-d H:i:s')]);
                            $update_sub_data = [
                                'customer_id'            => '',
                                'active_subscription_id' => 1,
                                'stripe_plan_id'         => '',
                                'subscription_item_id'   => ''
                            ];
        
                            if ($active_subscription_id == 3 && $transaction->other_user_id) {
                                $other_user_ids = $transaction->other_user_id ? explode(',',$transaction->other_user_id) : null;
                                if ($transaction->other_user_type == 'singleton') {
                                    foreach ($other_user_ids as $id) {
                                        Singleton::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                                    }
                                } elseif ($transaction->other_user_type == 'parent') {
                                    foreach ($other_user_ids as $id) {
                                        ParentsModel::where([['id','=',$id],['status','=','Unblocked']])->update($update_sub_data);
                                    }
                                }
                            }
                        }
                    }

                    if($request->user_type == 'singleton'){
                        $link = ParentChild::where([['singleton_id', '=', $request->login_id]])->delete();
                    }else{
                        $link = ParentChild::where([['parent_id', '=', $request->login_id]])->delete();
                        if($link){
                            Singleton::where('parent_id', '=', $request->login_id)->update(['parent_id' => '', 'updated_at' => date('Y-m-d H:i:s')]);
                        }
                    }
                    $match = MyMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                    $unmatch = UnMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                    $refer = ReferredMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                    $received = RecievedMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();

                    $requests = InstantMatchRequest::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                    $block = BlockList::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                    $report = ReportedUsers::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                    $chat = MessagedUsers::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
                    $chat = ChatHistory::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])
                                        ->orWhere([['messaged_user_id','=',$request->login_id],['messaged_user_type','=',$request->user_type]])->delete();
                    $swipe = LastSwipe::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();

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

                                Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'matched']])
                                            ->orWhere([['match_id','=',$request->login_id],['user_type','=','singleton'], ['match_type', '=', 'matched']])
                                            ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no'],['status' => 'available']);
                                Singleton::where('id', '=', $request->login_id)->orWhere('id', '=', $un_matched_id)->update(['chat_status' => 'available']);
                            }
                        }
                    } else {
                        $mutual = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'matched']])
                                            ->orWhere([['matched_parent_id','=',$request->login_id],['user_type','=','parent'], ['match_type', '=', 'matched']])
                                            ->update(['match_type' => 'liked', 'queue' => 0, 'is_rematched' => 'no']);
                        $liked = Matches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'liked']])
                                            ->orWhere([['user_id','=',$request->login_id],['user_type','=',$request->user_type], ['match_type', '=', 'un-matched']])
                                            ->delete();
                    }

                    $admin = Admin::find(1);

                    $details = [
                        'title' => __('msg.Account Deleted'),
                        'msg'   => __('msg.has Deleted His/Her Account.'),
                    ];

                    $admin->notify(new AdminNotification($user, 'admin', 0, $details));

                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.delete-account.success'),
                        'data'      => $user
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.delete-account.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.delete-account.invalid'),
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
