<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DeletedUsers as ModelsDeletedUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeletedUsers extends Controller
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

    public function index()
    {
        $data['admin']                    = $this->admin;
        $data['previous_title']           = __("msg.Dashboard");
        $data['url']                      = route('dashboard');
        $data['title']                    = __("msg.Manage Deleted Users");
        $data['records']                  = ModelsDeletedUsers::paginate(10);
        $data['notifications']            = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']                  = view('deleted_users.deleted_users_list', $data);
        return view('layouts.main',$data);
    }
}
