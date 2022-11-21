<?php

namespace App\Http\Controllers\api\contact_details;

use App\Http\Controllers\Controller;
use App\Models\ContactDetails as ModelsContactDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ContactDetails extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'contact_type'   => ['required','alpha_dash'],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $page = ModelsContactDetails::where([['contact_type','=',$request->contact_type], ['status','=','Active']])->first();
        if(!empty($page)){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Contact Details Fetched Successfully!'),
                'data'      => $page
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Contact Details Not Found!'),
            ],400);
        }
    }
}
