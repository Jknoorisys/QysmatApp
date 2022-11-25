<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\StaticPages as ModelsStaticPages;
use Illuminate\Http\Request;

class StaticPages extends Controller
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
        $data['title']               = __("msg.Manage Static Pages");
        $data['records']             =  ModelsStaticPages::where('status', '!=' ,'Deleted')->paginate(10);
        $data['content']             = view('static_pages.static_pages_list', $data);
        // return $data['records'];exit;
        return view('layouts.main',$data);
    }

    public function addPage()
    {
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Static Pages");
        $data['url']                 = route('static_pages');
        $data['title']               = __("msg.Add Static Page");
        $data['content']             = view('static_pages.add_static_pages', $data);
        return view('layouts.main',$data);
    }

    public function addPageFun(Request $request)
    {
        $request->validate([
            'page_name'   => 'required',
            'page_title'  => 'required',
            'description' => 'required',
        ]);

        $page =  new ModelsStaticPages();
        $page->page_name = $request->page_name;
        $page->page_title = $request->page_title;
        $page->short_description = $request->short_description ? $request->short_description : '';
        $page->description      = $request->description ? $request->description : '';
        $page->created_at   = date('Y-m-d H:i:s');
        $add = $page->save();
        if($add)
        {
            return redirect()->to('static_pages')->with('success', __('msg.Page Added!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function changePageStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  ModelsStaticPages :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Inactive') {
                return back()->with('success', __('msg.Page Inactivated'));
            } else {
                return back()->with('success', __('msg.Page Activated'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function updatePage(Request $request)
    {
        $id                          = $request->id;
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Static Pages");
        $data['url']                 = route('static_pages');
        $data['title']               = __("msg.Update Page");
        $data['records']             =  ModelsStaticPages::find($id);
        $data['content']             = view('static_pages.static_pages_update', $data);
        // return $data['records'];exit;
        return view('layouts.main',$data);
    }

    public function updatePageFun(Request $request)
    {
        $request->validate([
            'page_name'   => 'required',
            'page_title'  => 'required',
            'description' => 'required',
        ]);

        $update =  ModelsStaticPages :: whereId($request->id)->update(['page_name' => $request->page_name,'page_title' => $request->page_title,'short_description' => $request->short_description ? $request->short_description : '', 'description' => $request->description ? $request->description : '', 'updated_at' => date('Y-m-d H:i:s')]);
        if($update)
        {
            return redirect()->to('static_pages')->with('success', __('msg.Page Updated!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function deletePage(Request $request)
    {
        $id = $request->id;
        $delete =  ModelsStaticPages :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        if ($delete) {
            return back()->with('success', __('msg.Page Deleted!'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
