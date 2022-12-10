<?php

namespace App\Http\Controllers\api\contact_us;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\ContactUs as ModelsContactUs;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Notifications\AdminNotification;
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
            userFound($_POST['login_id'], $_POST['user_type']);
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

        try {
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

                $admin = Admin::find(1);

                if($request->user_type == 'singleton'){
                    $user = Singleton::find($request->login_id);
                }else{
                    $user = ParentsModel::find($request->login_id);
                }

                $details = [
                    'title' => __('msg.New Query Message'),
                    'msg'   => __('msg.has Contacted You.'),
                ];

                $admin->notify(new AdminNotification($user, 'admin', 0, $details));

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.contact-us.success'),
                    'data'      => $form
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.contact-us.failure'),
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
