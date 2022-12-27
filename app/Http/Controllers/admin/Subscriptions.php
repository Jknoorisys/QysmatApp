<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Subscriptions as ModelsSubscriptions;
use Illuminate\Http\Request;

class Subscriptions extends Controller
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
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Dashboard");
        $data['url']                 = route('dashboard');
        $data['title']               = __("msg.Manage Subscriptions");
        $data['records']             =  ModelsSubscriptions::paginate(10);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');

        $features = [];
        foreach ($data['records'] as $page) {
            if ($page->subscription_type == 'Basic') {
                $features = [__("msg.Only 5 Profile Views per day"), __("msg.Unrestricted profile search criteria")];
            }else {
                $features = [__("msg.Unlimited swipes per day"), __("msg.Send instant message  (3 per week)"), __("msg.In-app telephone and video calls"), __("msg.Refer profiles to friends and family"), __("msg.Undo last swipe"), __("msg.Reset profile search and start again once a month")];
            }
            $page->features= !empty($features) ? $features : "";
        }

        $data['content']             = view('subscriptions.subscriptions_list', $data);
        return view('layouts.main',$data);
    }

    public function changeSubscriptionStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  ModelsSubscriptions :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Inactive') {
                return back()->with('success', __('msg.Subscription Inactivated'));
            } else {
                return back()->with('success', __('msg.Subscription Activated'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function updatePrice(Request $request)
    {
        $id                          = $request->id;
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Subscriptions");
        $data['url']                 = route('subscriptions');
        $data['title']               = __("msg.Update Subscription Price");
        $data['records']             =  ModelsSubscriptions::find($id);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('subscriptions.subscriptions_update', $data);
        return view('layouts.main',$data);
    }

    public function updatePriceFun(Request $request)
    {
        $request->validate([
            'subscription_type' => 'required',
            'price'             => 'required',
        ]);

        $update =  ModelsSubscriptions :: whereId($request->id)->update(['subscription_type' => $request->subscription_type, 'price' => $request->price, 'updated_at' => date('Y-m-d H:i:s')]);
        if($update)
        {
            return redirect()->to('subscriptions')->with('success', __('msg.Subscription Price Updated!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }
}
