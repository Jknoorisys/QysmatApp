<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\WebPages as ModelsWebPages;
use Illuminate\Http\Request;

class WebPages extends Controller
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
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Dashboard");
        $data['url']                 = route('dashboard');
        $data['title']               = __("msg.Manage Web Pages");
        $data['records']             =  ModelsWebPages::where('status', '!=' ,'Deleted')->paginate(10);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('web_pages.web_pages_list', $data);
        return view('layouts.main',$data);
    }

    public function addWebPage()
    {
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Web Pages");
        $data['url']                 = route('web_pages');
        $data['title']               = __("msg.Add Web Page");
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('web_pages.add_web_pages', $data);
        return view('layouts.main',$data);
    }

    public function addWebPageFun(Request $request)
    {
        $request->validate([
            'page_name'   => 'required',
            'page_title'  => 'required',
            'description' => 'required',
        ]);

        $page =  new ModelsWebPages();
        $page->page_name = $request->page_name;
        $page->page_title = $request->page_title;
        $page->short_description = $request->short_description ? $request->short_description : '';
        $page->description      = $request->description ? $request->description : '';
        $page->created_at   = date('Y-m-d H:i:s');
        $add = $page->save();
        if($add)
        {
            return redirect()->to('web_pages')->with('success', __('msg.Web Page Added!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function changeWebPageStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  ModelsWebPages :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Inactive') {
                return back()->with('success', __('msg.Web Page Inactivated'));
            } else {
                return back()->with('success', __('msg.Web Page Activated'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function updateWebPage(Request $request)
    {
        $id                          = $request->id;
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Web Pages");
        $data['url']                 = route('web_pages');
        $data['title']               = __("msg.Update Page");
        $data['records']             =  ModelsWebPages::find($id);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('web_pages.web_pages_update', $data);
        return view('layouts.main',$data);
    }

    public function updateWebPageFun(Request $request)
    {
        $request->validate([
            'page_name'   => 'required',
            'page_title'  => 'required',
            'description' => 'required',
        ]);

        $update =  ModelsWebPages :: whereId($request->id)->update(['page_name' => $request->page_name,'page_title' => $request->page_title,'short_description' => $request->short_description ? $request->short_description : '', 'description' => $request->description ? $request->description : '', 'updated_at' => date('Y-m-d H:i:s')]);
        if($update)
        {
            return redirect()->to('web_pages')->with('success', __('msg.Web Page Updated!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function deleteWebPage(Request $request)
    {
        $id = $request->id;
        $delete =  ModelsWebPages :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        if ($delete) {
            return back()->with('success', __('msg.Web Page Deleted!'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
