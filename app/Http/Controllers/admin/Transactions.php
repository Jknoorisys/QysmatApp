<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Transactions as ModelsTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Transactions extends Controller
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

    public function index(Request $request)
    {
        $data['to_date']                  = $request->to_date ? $request->to_date : '';
        $data['from_date']                = $request->from_date ? $request->from_date : '';
        $data['admin']                    = $this->admin;
        $data['previous_title']           = __("msg.Dashboard");
        $data['url']                      = route('dashboard');
        $data['title']                    = __("msg.Manage Transactions");
        if(!empty($data['from_date']) && !empty($data['to_date'])){
            $data['records']              = ModelsTransactions::whereDate('transaction_datetime', '>=', $data['from_date'])
                                                ->whereDate('transaction_datetime', '<=', $data['to_date'])
                                                ->paginate(10);
        }else{
            $data['records']               = ModelsTransactions::paginate(10);
        }

        $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
        $data['content']             = view('transactions.transactions_list', $data);
        return view('layouts.main',$data);
    }

    public function viewTransaction(Request $request)
    {
        $id     = $request->id;
        if(!empty($id)){
            $data['details']             = ModelsTransactions::where('transactions.id',$id)->first();
            $data['admin']               = $this->admin;
            $data['previous_title']      = __("msg.Manage Transactions");
            $data['url']                 = route('transactions');
            $data['title']               = __("msg.Transaction Details");
            $data['notifications']       = $this->admin->unreadNotifications->where('user_type','=','admin');
            $data['content']             = view('transactions.transaction_details', $data);
            return view('layouts/main', $data);
        }else {
            return back()->with('fail', __('msg.Somthing Went Wrong, Please Try Again...'));
        }
    }
}
