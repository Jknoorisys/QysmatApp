<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Quotes as ModelsQuotes;
use Illuminate\Http\Request;

class Quotes extends Controller
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
        $data['title']               = __("msg.Manage Islamic Quotes");
        $data['records']             =  ModelsQuotes::where('status', '!=' ,'Deleted')->get();
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('quotes.quotes_list', $data);
        // return $data['records'];exit;
        return view('layouts.main',$data);
    }

    public function addQuote()
    {
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Islamic Quotes");
        $data['url']                 = route('quotes');
        $data['title']               = __("msg.Add Islamic Quotes");
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('quotes.add_quotes', $data);
        return view('layouts.main',$data);
    }

    public function addQuoteFun(Request $request)
    {
        $request->validate([
            'quote'   => 'required_without_all:image',
            'image'   => 'required_without_all:quote',
        ]);

        $quote =  new ModelsQuotes();
        $quote->quotes = $request->quote;
        $quote->created_at   = date('Y-m-d H:i:s');

        $file = $request->file('image');
        if ($request->hasFile('image')) {
            $extension = $file->getClientOriginalExtension();
            $filename = time().'1.'.$extension;
            $file->move('assets/uploads/quotes/', $filename);
            $quote->image = 'assets/uploads/quotes/'.$filename;
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }

        $add = $quote->save();
        if($add)
        {
            return redirect()->to('quotes')->with('success', __('msg.Islamic Quote Added!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function changeQuoteStatus(Request $request)
    {
        $id = $request->id;
        $status = $request->status;
        $statusChange =  ModelsQuotes :: whereId($id)->update(['status' => $status, 'updated_at' => date('Y-m-d H:i:s')]);
        if ($statusChange) {
            if ($status == 'Inactive') {
                return back()->with('success', __('msg.Islamic Quote Inactivated'));
            } else {
                return back()->with('success', __('msg.Islamic Quote Activated'));
            }

        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }

    public function updateQuote(Request $request)
    {
        $id                          = $request->id;
        $data['admin']               = $this->admin;
        $data['previous_title']      = __("msg.Manage Islamic Quotes");
        $data['url']                 = route('quotes');
        $data['title']               = __("msg.Update Islamic Quotes");
        $data['records']             =  ModelsQuotes::find($id);
        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('quotes.quotes_update', $data);
        return view('layouts.main',$data);
    }

    public function updateQuoteFun(Request $request)
    {
        $request->validate([
            'quote'   => 'required_without_all:image',
            'image'   => 'required_without_all:quote',
        ]);

        $quote =  ModelsQuotes::find($request->id);
        $quote->quotes = $request->quote;
        $quote->updated_at   = date('Y-m-d H:i:s');

        $file = $request->file('image');
        if ($request->hasFile('image')) {
            $extension = $file->getClientOriginalExtension();
            $filename = time().'1.'.$extension;
            $file->move('assets/uploads/quotes/', $filename);
            $quote->image = 'assets/uploads/quotes/'.$filename;
        }

        $update = $quote->save();

        if($update)
        {
            return redirect()->to('quotes')->with('success', __('msg.Islamic Quote Updated!'));
        }else{
            return back()->with('fail', __('msg.Please Try Again....'));
        }
    }

    public function deleteQuote(Request $request)
    {
        $id = $request->id;
        $delete =  ModelsQuotes :: whereId($id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
        if ($delete) {
            return back()->with('success', __('msg.Islamic Quote Deleted!'));
        } else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
