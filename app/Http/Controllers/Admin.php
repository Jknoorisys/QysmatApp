<?php

namespace App\Http\Controllers;

use App\Models\Admin as AdminModel;
use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

class Admin extends Controller
{
    public function  __construct()
    {
        # code...
    }

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
}
