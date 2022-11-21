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
        // $query1                            = DB::raw("(CASE WHEN reported_users.user_type='Singleton' THEN (singletons.name) ELSE (parents.name) END) as user_name");
        // $query2                            = DB::raw("(CASE WHEN reported_users.user_type='Singleton' THEN (singletons.status) ELSE (parents.status) END) as user_status");
        // $data['records']                   = DB::table('reported_users')
        //                                     ->join('singletons', 'singletons.id', '=', 'reported_users.user_id')
        //                                     ->join('parents', 'parents.id', '=', 'reported_users.user_id')
        //                                     ->select('reported_users.*',$query1, $query2)
        //                                     ->paginate(10);
        $data['content']             = view('reported_users.reported_users_list', $data);
        return view('layouts.main',$data);
    }
}
