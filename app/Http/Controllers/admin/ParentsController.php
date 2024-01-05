<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DeletedUsers;
use App\Models\ParentsModel;
use App\Models\PremiumFeatures;
use App\Models\Singleton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentsController extends Controller
{
    private $admin_id;
    private $admin;
    
    public function  __construct()
    {
        $this->middleware(function ($request, $next) {
            if(Session()->get('loginId') == false && empty(Session()->get('loginId'))) {
                return redirect()->to('/')->with('warning', __('msg.Please Login First!'));
            }else {
                $this->admin_id = Session()->get('loginId');
                $this->admin = Admin::where('id', '=', $this->admin_id)->first();
            }
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $search =  $request->search ? $request->search : '';
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Dashboard");
        $data['url']                 = route('dashboard');
        $data['title']               = __("msg.Manage Parents");
        $data['records']             = $search ? ParentsModel::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->Where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orderBy('name')->get() : ParentsModel::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->orderBy('name')->get();
        $data['search']              =  $request->search;
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('parents.parents_list', $data);
        return view('layouts.main',$data);
    }

    public function viewParent(Request $request)
    {
        $id     = $request->id;
        if(!empty($id)){
            $data['details']             = DB::table('parents')
                                                ->join('subscriptions','subscriptions.id','=','parents.active_subscription_id')
                                                ->where('parents.id',$id)
                                                ->select('parents.*','subscriptions.subscription_type','subscriptions.price','subscriptions.currency')
                                                ->first();

            $featureStatus = PremiumFeatures::whereId(1)->first();
            if ((!empty($featureStatus) && $featureStatus->status == 'inactive')) {
                $data['details']->price = 'Free';
                $features = [__("msg.Unlimited swipes per day"), __("msg.Send instant match request (3 per week)"), __("msg.In-app telephone and video calls"), __("msg.Refer profiles to friends and family"), __("msg.Undo last swipe"), __("msg.Reset profile search and start again once a month")];
            }else{
                if ($data['details']->subscription_type == 'Basic') {
                    $data['details']->price = 'Free';
                    $features = [__("msg.Only 5 Profile Views per day"), __("msg.Unrestricted profile search criteria")];
                }else {
                    $features = [__("msg.Unlimited swipes per day"), __("msg.Send instant match request (3 per week)"), __("msg.In-app telephone and video calls"), __("msg.Refer profiles to friends and family"), __("msg.Undo last swipe"), __("msg.Reset profile search and start again once a month")];
                }            
            }

            $data['details']->features= !empty($features) ? $features : "";
            
            $data['reverify']            = DB::table('re_verify_requests')->where([['user_id', '=', $id],['user_type', '=', 'parent'],['status', '=', 'pending']])->first();
            $data['admin']               = $this->admin;
            $data['previous_title']      = __("msg.Manage Parents");
            $data['url']                 = route('parents');
            $data['title']               = __("msg.Parent Details");
            $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
            $data['content']             = view('parents.parent_details', $data);
            return view('layouts/main', $data);
        }else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function verifyParent(Request $request)
    {
        $id = $request->id;
        $is_verified = $request->is_verified;
        $verified =  ParentsModel :: whereId($id)->update(['is_verified' => $is_verified, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($verified) {
            if ($is_verified == 'verified') {
                $reciever = ParentsModel::where('id', '=', $id)->first();

                $reVerify = DB::table('re_verify_requests')->where([['user_id', '=', $id],['user_type', '=', 'parent']])->first();
                if (!empty($reVerify)) {

                    $data = [
                        'name'                      => $reVerify->name ? $reVerify->name : $reciever->name,
                        'lname'                     => $reVerify->lname ? $reVerify->lname : $reciever->lname,
                        // 'email'                     => $reVerify->email ? $reVerify->email : $reciever->email,
                        'mobile'                    => $reVerify->mobile ? $reVerify->mobile : $reciever->mobile,
                        'nationality'               => $reVerify->nationality ? $reVerify->nationality : $reciever->nationality,
                        'country_code'              => $reVerify->country_code ? $reVerify->country_code : $reciever->country_code,
                        'nationality_code'          => $reVerify->nationality_code ? $reVerify->nationality_code : $reciever->nationality_code,
                        'ethnic_origin'             => $reVerify->ethnic_origin ? $reVerify->ethnic_origin : $reciever->ethnic_origin,
                        'islamic_sect'              => $reVerify->islamic_sect ? $reVerify->islamic_sect : $reciever->islamic_sect,
                        'location'                  => $reVerify->location ? $reVerify->location : $reciever->location,
                        'lat'                       => $reVerify->lat ? $reVerify->lat : $reciever->lat,
                        'long'                      => $reVerify->long ? $reVerify->long : $reciever->long,
                        'relation_with_singleton'   => $reVerify->relation_with_singleton ? $reVerify->relation_with_singleton : $reciever->relation_with_singleton,
                        'profile_pic'               => $reVerify->profile_pic ? $reVerify->profile_pic : $reciever->profile_pic,
                        'live_photo'                => $reVerify->live_photo ? $reVerify->live_photo : $reciever->live_photo,
                        'id_proof'                  => $reVerify->id_proof ? $reVerify->id_proof : $reciever->id_proof,
                    ];

                    ParentsModel :: whereId($id)->update($data);
                    DB::table('re_verify_requests')->where([['id', '=', $reVerify->id],['user_id', '=', $id],['user_type', '=', 'parent']])->update(['status' => $is_verified]);
                }

                if (isset($reciever) && !empty($reciever)) {
                    $title = __('msg.Profile Verified');
                    $message = __('msg.Your Profile is Verified by Admin');
                   
                    $fcm_regid[] = $reciever->fcm_token;
                    $notification = array(
                        'title'         => $title,
                        'message'       => $message,
                        'click_action'  => 'FLUTTER_NOTIFICATION_CLICK',
                        'date'          => date('Y-m-d H:i'),
                        'type'          => 'verification',
                        'response'      => ''
                    );
                    $result = sendFCMNotification($notification, $fcm_regid, 'verification');

                    // $body = __('msg.Your Profile is Verified by Admin');
                    // $token = $reciever->fcm_token;
                    // $data = array(
                    //     'notType' => "profile_verified",
                    // );
                    // $result = sendFCMNotifications($token, $title, $body, $data);
                }
                return redirect()->to('parents')->with('success', __('msg.Parents Profile Verified Successfully'));
            } else {
                $reciever = ParentsModel::where('id', '=', $id)->first();
                if (isset($reciever) && !empty($reciever)) {
                    $title = __('msg.Profile Rejected');
                    $message = __('msg.Your Profile is Rejected by Admin');
                   
                    $fcm_regid[] = $reciever->fcm_token;
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
                DB::table('re_verify_requests')->where([['user_id', '=', $id],['user_type', '=', 'parent']])->update(['status' => $is_verified]);
                return redirect()->to('parents')->with('success', __('msg.Parents Profile Rejected Successfully'));
            }
        } else {
            return redirect()->to('parents')->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function changeParentStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  ParentsModel :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Blocked') {
                return back()->with('success', __('msg.Parent Blocked'));
            } else {
                return back()->with('success', __('msg.Parent Unblocked'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function deleteParent(Request $request)
    {
        $id = $request->id;
        $user = ParentsModel::find($id);
        $user_type = 'parent';
        $active_subscription_id = $user ? $user->active_subscription_id : '';
        $data = [
            'user_id'     => $id,
            'user_type'   => $user_type,
            'user_name'   => $user->name,
            'reason_type' => 'Admin',
            'reason'      => 'Admin',
        ];
        $insert = DeletedUsers::insert($data);
        // $delete =  ParentsModel :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        $delete =  ParentsModel :: whereId($id)->delete();
        if ($delete) {
            $deleteAccount = deleteAccountDetails($id,$user_type,$active_subscription_id);
            return back()->with('success', __('msg.Parent Deleted'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
