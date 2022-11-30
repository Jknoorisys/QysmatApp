<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ContactDetails as ModelsContactDetails;
use Illuminate\Http\Request;

class ContactDetails extends Controller
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
        $data['title']               = __("msg.Manage Contact Details");
        $data['records']             =  ModelsContactDetails::where('status', '!=' ,'Deleted')->paginate(10);
        $data['notifications']       = $this->admin->notifications->where('user_type','=','admin');
        $data['content']             = view('contact_details.contact_details_list', $data);
        return view('layouts.main',$data);
    }

    public function addContact()
    {
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Contact Details");
        $data['url']                 = route('contact_details');
        $data['title']               = __("msg.Add Contact Details");
        $data['notifications']       = $this->admin->notifications->where('user_type','=','admin');
        $data['content']             = view('contact_details.add_contact_details', $data);
        return view('layouts.main',$data);
    }

    public function addContactFun(Request $request)
    {
        $request->validate([
            'contact_type' => 'required',
            'details'             => 'required',
        ]);

        $contact =  new ModelsContactDetails();
        $contact->contact_type = $request->contact_type;
        $contact->details      = $request->details;
        $contact->created_at   = date('Y-m-d H:i:s');
        $add = $contact->save();
        if($add)
        {
            return redirect()->to('contact_details')->with('success', __('msg.Contact Detail Added!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function changeContactStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  ModelsContactDetails :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Inactive') {
                return back()->with('success', __('msg.Contact Detail Inactivated'));
            } else {
                return back()->with('success', __('msg.Contact Detail Activated'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function updateContact(Request $request)
    {
        $id                          = $request->id;
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Contact Details");
        $data['url']                 = route('contact_details');
        $data['title']               = __("msg.Update Contact Details");
        $data['records']             =  ModelsContactDetails::find($id);
        $data['notifications']       = $this->admin->notifications->where('user_type','=','admin');
        $data['content']             = view('contact_details.contact_details_update', $data);
        return view('layouts.main',$data);
    }

    public function updateContactFun(Request $request)
    {
        $request->validate([
            'contact_type' => 'required',
            'details'      => 'required',
        ]);

        $update =  ModelsContactDetails :: whereId($request->id)->update(['contact_type' => $request->contact_type, 'details' => $request->details, 'updated_at' => date('Y-m-d H:i:s')]);
        if($update)
        {
            return redirect()->to('contact_details')->with('success', __('msg.Contact Detail Updated!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function deleteContact(Request $request)
    {
        $id = $request->id;
        $delete =  ModelsContactDetails :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        if ($delete) {
            return back()->with('success', __('msg.Contact Detail Deleted!'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
