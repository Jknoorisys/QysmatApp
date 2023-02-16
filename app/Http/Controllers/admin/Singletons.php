<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DeletedUsers;
use App\Models\Singleton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Singletons extends Controller
{
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
        $data['title']               = __("msg.Manage Singletons");
        $data['records']             = $search ? Singleton::where([['status', '!=' ,'Deleted'],['is_email_verified', '=' ,'verified']])->Where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orderBy('name')->paginate('10') : Singleton::where([['status', '!=' ,'Deleted'],['is_email_verified', '=' ,'verified']])->orderBy('name')->paginate('10');
        $data['search']              =  $request->search;
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('sigletons.sigletons_list', $data);
        return view('layouts.main',$data);
    }

    public function viewSingleton(Request $request)
    {
        $id     = $request->id;
        if(!empty($id)){
            $data['details']             = DB::table('singletons')
                                                ->join('subscriptions','subscriptions.id','=','singletons.active_subscription_id')
                                                ->where('singletons.id',$id)
                                                ->select('singletons.*','subscriptions.subscription_type','subscriptions.price','subscriptions.currency')
                                                ->first();

            if ($data['details']->subscription_type == 'Basic') {
                $data['details']->price = 'Free';
                $features = [__("msg.Only 5 Profile Views per day"), __("msg.Unrestricted profile search criteria")];
            }else {
                $features = [__("msg.Unlimited swipes per day"), __("msg.Send instant match request (3 per week)"), __("msg.In-app telephone and video calls"), __("msg.Refer profiles to friends and family"), __("msg.Undo last swipe"), __("msg.Reset profile search and start again once a month")];
            }
            $data['details']->features= !empty($features) ? $features : "";

            $data['admin']               = $this->admin;
            $data['previous_title']      = __("msg.Manage Singletons");
            $data['url']                 = route('sigletons');
            $data['title']               = __("msg.Singleton Details");
            $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
            $data['content']             = view('sigletons.singleton_details', $data);
            return view('layouts/main', $data);
        }else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function verifySingleton(Request $request)
    {
        $id = $request->id;
        $is_verified = $request->is_verified;
        $verified =  Singleton :: whereId($id)->update(['is_verified' => $is_verified, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($verified) {
            if ($is_verified == 'verified') {
                $reciever = Singleton::where([['id', '=', $id], ['status', '=', 'Unblocked']])->first();
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
                return redirect()->to('sigletons')->with('success', __('msg.Singleton Profile Verified Successfully'));
            } else {
                return redirect()->to('sigletons')->with('success', __('msg.Singleton Profile Rejected Successfully'));
            }
        } else {
            return redirect()->to('sigletons')->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function changeStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  Singleton :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Blocked') {
                return back()->with('success', __('msg.User Blocked'));
            } else {
                return back()->with('success', __('msg.User Unblocked'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function deleteSingleton(Request $request)
    {
        $id = $request->id;
        $user = Singleton::find($id);
        $user_type = 'singleton';
        $active_subscription_id = $user ? $user->active_subscription_id : '';
        $data = [
            'user_id'     => $id,
            'user_type'   => $user_type,
            'user_name'   => $user->name,
            'reason_type' => 'Admin',
            'reason'      => 'Admin',
        ];
        $insert = DeletedUsers::insert($data);
        // $delete =  Singleton :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        $delete =  Singleton :: whereId($id)->delete();
        if ($delete) {
            $deleteAccount = deleteAccountDetails($id,$user_type,$active_subscription_id);
            return back()->with('success', __('msg.User Deleted'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
