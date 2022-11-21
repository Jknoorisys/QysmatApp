<?php

namespace App\Http\Controllers\api\contact_us;

use App\Http\Controllers\Controller;
use App\Models\ContactUs as ModelsContactUs;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ContactUs extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {

            if ($_POST['user_type'] == 'singleton') {
                $user = Singleton::find($_POST['login_id']);
                if (empty($user)) {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.User Not Found!'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }
            } else {
                $user = ParentsModel::find($_POST['login_id']);
                if (empty($user)) {
                    $response = [
                        'status'    => 'failed',
                        'message'   => __('msg.User Not Found!'),
                        'status_code' => 403
                    ];
                    echo json_encode($response);die();
                }
            }

        }
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
            'user_type' => [
                'required' ,
                Rule::in(['singleton','parent']),
            ],
            'title' => 'required',
            'description' => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        if($request->user_type == 'singleton'){
            $userExists = Singleton::find($request->login_id);
        }else{
            $userExists = ParentsModel::find($request->login_id);
        }

        $form = new ModelsContactUs();
        $form->user_id      = $request->login_id;
        $form->user_type    = $request->user_type;
        $form->user_name    = $userExists->name;
        $form->title        = $request->title;
        $form->description  = $request->description;
        $formDetails = $form->save();

        if(!empty($formDetails)){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Contact Us Form Submitted Successfully!'),
                'data'      => $form
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
            ],400);
        }
    }
}
