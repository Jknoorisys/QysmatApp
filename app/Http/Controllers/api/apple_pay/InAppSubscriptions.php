<?php

namespace App\Http\Controllers\api\apple_pay;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BankDetails;
use App\Models\Charges;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Models\Subscriptions;
use App\Models\Transactions;
use App\Notifications\AdminNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class InAppSubscriptions extends Controller
{
    public function updateSubscription(Request $request)
    {
        return $request->all();
    }
}
