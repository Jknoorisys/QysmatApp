<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ChatHistory;
use App\Models\MyMatches;
use App\Models\ParentsModel;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\Transactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class Dashboard extends Controller
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
        $data['title']               = "no_breadcrumb";
        $data['main-title']          = __("msg.Dashboard");
        $data['admin']               = $this->admin;
        $data['singletons']          = Singleton::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->count();
        $data['parents']             = ParentsModel::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->count();

        $data['active']              = Singleton::where([['status', '=' ,'Unblocked'],['is_email_verified', '=' ,'verified']])->count() + ParentsModel::where([['status', '=' ,'Unblocked'],['is_email_verified', '=' ,'verified']])->count();
        $data['blocked']             = Singleton::where([['status', '=' ,'Blocked'],['is_email_verified', '=' ,'verified']])->count() + ParentsModel::where([['status', '=' ,'Blocked'],['is_email_verified', '=' ,'verified']])->count();
        $data['deleted']             = Singleton::where([['status', '=' ,'Deleted'],['is_email_verified', '=' ,'verified']])->count() + ParentsModel::where([['status', '=' ,'Deleted'],['is_email_verified', '=' ,'verified']])->count();

        $data['reported']            = ReportedUsers::count();
        $data['matches']             = MyMatches::count();

        $data['conversations']       = ChatHistory::count();

        $data['records']             = Singleton::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->latest()->take(5)->get();
        $data['parents_records']     = ParentsModel::where([['status', '!=' ,'Deleted'], ['is_email_verified', '=' ,'verified']])->latest()->take(5)->get();

        $data['revenue']             = Transactions::where('payment_status','=','SUCCESS')->avg('paid_amount');

        $data['content']             = view('admin.dashboard', $data);
        return view('layouts.main', $data);
    }

    public function changePassword()
    {
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Dashboard");
        $data['url']                 = route('dashboard');
        $data['title']               = __("msg.Change Password");;
        $data['content']             = view('admin.change_password', $data);
        return view('layouts.main',$data);
    }

    public function changePasswordFun(Request $request)
    {
        $request->validate([
            'old_password' => 'required|min:5|max:12',
            'new_password' => 'required|min:5|max:12',
            'cnfm_password' => 'required|min:5|max:12'
        ]);

        if (!Hash::check($request->old_password, $this->admin->password)) {
            return back()->with('fail', __('msg.Old Password Do Not Match!'));
        } else {
           $password =  Admin :: whereId($this->admin_id)->update(['password' => Hash::make($request->new_password), 'updated_at' => date('Y-m-d H:i:s')]);
           if($password)
           {
            return redirect()->to('dashboard')->with('success', __('msg.Password Changed!'));
           }else{
            return back()->with('fail', __('msg.Please Try Again....'));
           }
        }
    }

}
