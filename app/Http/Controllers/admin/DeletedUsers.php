<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DeletedUsers as ModelsDeletedUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeletedUsers extends Controller
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
        $data['title']                    = __("msg.Manage Deleted Users");
        $data['records']                  = ModelsDeletedUsers::paginate(10);

        // $query                            = DB::raw("(CASE WHEN deleted_users.user_type='Singleton' THEN (singletons.name) ELSE (parents.name) END) as user_name");
        // $data['records']                  = DB::table('deleted_users')
        //                                     ->join('singletons', 'singletons.id', '=', 'deleted_users.user_id')
        //                                     ->join('parents', 'parents.id', '=', 'deleted_users.user_id')
        //                                     ->select('deleted_users.*',$query)
        //                                     ->paginate(10);

        $data['content']                   = view('deleted_users.deleted_users_list', $data);
        return view('layouts.main',$data);
    }
}
