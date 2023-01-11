<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ParentsModel;
use App\Models\Singleton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParentsController extends Controller
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
        $data['title']               = __("msg.Manage Parents");
        $data['records']             = $search ? ParentsModel::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->Where('name', 'LIKE', "%$search%")->orWhere('email', 'LIKE', "%$search%")->orderBy('name')->paginate('10') : ParentsModel::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->orderBy('name')->paginate('10');
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

            if ($data['details']->subscription_type == 'Basic') {
                $data['details']->price = 'Free';
                $features = [__("msg.Only 5 Profile Views per day"), __("msg.Unrestricted profile search criteria")];
            }else {
                $features = [__("msg.Unlimited swipes per day"), __("msg.Send instant message  (3 per week)"), __("msg.In-app telephone and video calls"), __("msg.Refer profiles to friends and family"), __("msg.Undo last swipe"), __("msg.Reset profile search and start again once a month")];
            }
            $data['details']->features= !empty($features) ? $features : "";
            
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
                $reciever = ParentsModel::where([['id', '=', $id], ['status', '=', 'Unblocked']])->first();
                if (isset($reciever) && !empty($reciever)) {
                    $title = __('msg.New Message');
                    $message = __('msg.Your Profile is Verified by Admin Successfully!');
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
                return redirect()->to('parents')->with('success', __('msg.Parents Profile Verified Successfully'));
            } else {
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
        $delete =  ParentsModel :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        if ($delete) {
            return back()->with('success', __('msg.Parent Deleted'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
