<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Notifications\SendNotification;

class Notifications extends Controller
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

    public function index()
    {
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Dashboard");
        $data['url']                 = route('dashboard');
        $data['title']               = __("msg.Manage Notifications");
        $data['records']             = $this->admin->notifications()->where('user_type','=','admin')->paginate(10);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('notifications.notifications_list', $data);
        return view('layouts.main',$data);
    }

    public function sendNotification(Request $request)
    {
        $singletons = Singleton::where('status', '=', 'Unblocked')->get();
        $parent     = ParentsModel::where('status', '=', 'Unblocked')->get();
        $message    = $request->message;
        foreach ($singletons as $user) {
            $user->notify(new SendNotification ($message,$user->user_type) );
        }
        foreach ($parent as $user) {
            $user->notify(new SendNotification ($message,$user->user_type) );
        }
        return redirect()->to('notifications')->with('success', __('msg.Notification Sent!'));
    }
}
