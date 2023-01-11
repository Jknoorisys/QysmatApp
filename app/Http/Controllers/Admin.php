<?php

namespace App\Http\Controllers;

use App\Models\Admin as AdminModel;
use App\Models\ParentsModel;
use App\Models\Singleton;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class Admin extends Controller
{
    public function index()
    {
        if (Session()->get('is_logged_in') == 1) {
            return redirect()->to('dashboard');
        }
        return view('admin.login');
    }

    public function setLanguage($lang)
    {
        if (array_key_exists($lang, Config::get('languages'))) {
            Session()->put('applocale', $lang);
        }

        return redirect()->back();
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:5|max:12'
        ]);

        $admin = AdminModel::where('email', '=', $request->email)->first();
        if ($admin) {
            if (Hash::check($request->password, $admin->password)) {
                $request->session()->put('loginId', $admin->id);
                $request->session()->put('is_logged_in' , 1);
                return redirect()->to('dashboard');
            } else {
                return back()->with('fail', __('msg.Invalid Password'));
            }
        } else {
            return back()->with('fail', __('msg.User Not Found'));
        }
    }

    public function logout()
    {
        if (Session()->has('loginId')) {
            Session()->pull('loginId');
            Session()->pull('is_logged_in');
        }
        return redirect()->to('/');
    }

    public function setUserNewPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|min:5|max:12',
            'cnfm_password' => 'required|min:5|max:12'
        ]);

        if ($request->password != $request->cnfm_password) {
            return back()->with('fail', __('msg.Confirm Password Do Not Match!'));
        } else {
            if ($request->user_type == 'singleton') {
                $password =  Singleton :: where([['id', '=', $request->id], ['email', '=', $request->email]])->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
            } else {
                $password =  ParentsModel :: where([['id', '=', $request->id], ['email', '=', $request->email]])->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
            }

           if($password)
           {
            return back()->with('success', __('msg.Password Changed!'));
           }else{
            return back()->with('fail', __('msg.Please Try Again....'));
           }
        }
    }
}
