<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ReportedUsers as ModelsReportedUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportedUsers extends Controller
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
        $data['admin']                    = $this->admin;
        $data['previous_title']           = __("msg.Dashboard");
        $data['url']                      = route('dashboard');
        $data['title']                    = __("msg.Manage Reported Users");
        $data['records']                  = ModelsReportedUsers::paginate(10);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('reported_users.reported_users_list', $data);
        return view('layouts.main',$data);
    }
}
