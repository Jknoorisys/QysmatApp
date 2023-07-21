<?php

namespace App\Http\Controllers\api\contact_details;

use App\Http\Controllers\Controller;
use App\Models\ContactDetails as ModelsContactDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
            // 'contact_type'   => ['required','alpha_dash'],
            'contact_type' => [
                'required' ,
                Rule::in(['email','phone','address','instagram','facebook','whatsapp','twitter','linkedin','skype','telegram']),
            ],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $page = ModelsContactDetails::where([['contact_type','=',$request->contact_type], ['status','=','Active']])->first();
            if(!empty($page)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.contact-details.success'),
                    'data'      => $page
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.contact-details.failure'),
                ],400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }
}
