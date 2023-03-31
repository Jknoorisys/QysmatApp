<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Faqs as ModelsFaqs;
use Illuminate\Http\Request;

class FAQS extends Controller
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
        $data['title']               = __("msg.Manage FAQs");
        $data['records']             =  ModelsFaqs::where('status', '!=' ,'Deleted')->get();
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('faqs.faq_list', $data);
        return view('layouts.main',$data);
    }

    public function addFAQ()
    {
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage FAQs");
        $data['url']                 = route('faqs');
        $data['title']               = __("msg.Add FAQ");
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('faqs.add_faq', $data);
        return view('layouts.main',$data);
    }

    public function addFAQFun(Request $request)
    {
        $request->validate([
            'question'   => 'required',
            'answer'     => 'required',
        ]);

        $faq =  new ModelsFaqs();
        $faq->question      = $request->question ? $request->question : '';
        $faq->answer        = $request->answer ? $request->answer : '';
        $faq->created_at    = date('Y-m-d H:i:s');
        $add = $faq->save();
        if($add)
        {
            return redirect()->to('faqs')->with('success', __('msg.FAQ Added!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function changeFAQStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  ModelsFaqs :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Inactive') {
                return back()->with('success', __('msg.FAQ Inactivated'));
            } else {
                return back()->with('success', __('msg.FAQ Activated'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function updateFAQ(Request $request)
    {
        $id                          = $request->id;
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage FAQs");
        $data['url']                 = route('faqs');
        $data['title']               = __("msg.Update FAQ");
        $data['records']             =  ModelsFaqs::find($id);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('faqs.update_faq', $data);
        return view('layouts.main',$data);
    }

    public function updateFAQFun(Request $request)
    {
        $request->validate([
            'question'   => 'required',
            'answer'     => 'required',
        ]);

        $update =  ModelsFaqs :: whereId($request->id)->update(['question' => $request->question,'answer' => $request->answer, 'updated_at' => date('Y-m-d H:i:s')]);
        if($update)
        {
            return redirect()->to('faqs')->with('success', __('msg.FAQ Updated!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function deleteFAQ(Request $request)
    {
        $id = $request->id;
        $delete =  ModelsFaqs :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        if ($delete) {
            return back()->with('success', __('msg.FAQ Deleted!'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
