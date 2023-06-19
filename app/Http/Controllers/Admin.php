<?php

namespace App\Http\Controllers;

use App\Models\Admin as AdminModel;
use App\Models\ParentsModel;
use App\Models\PasswordReset;
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
    
    public function storeToken(Request $request)
    {
        $admin = AdminModel::find(1);
        $data = ['device_token' => $request->token];
        AdminModel::where('id', '=', 1)->update($data);
        return response()->json(['Token successfully stored.']);
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
            'password' => 'required|min:5'
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
            'password' => 'required',
            'cnfm_password' => 'required'
        ]);
        
        if ($request->user_type == 'singleton') {
            $password =  Singleton :: where([['id', '=', $request->id], ['email', '=', $request->email]])->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $password =  ParentsModel :: where([['id', '=', $request->id], ['email', '=', $request->email]])->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
        }
        if($password)
        {
            PasswordReset::where('email','=',$request->email)->delete();
            $data['msg'] = __('msg.Password Changed!');
            return view('reset_password_fail', $data);
        }else{
            $data['msg'] = __('msg.Please Try Again....');
            return view('reset_password_fail', $data);
        }
    }
}
