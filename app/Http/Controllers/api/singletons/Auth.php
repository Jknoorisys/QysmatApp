<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Categories;
use App\Models\ParentsModel;
use App\Models\PasswordReset;
use App\Models\Singleton;
use App\Notifications\AdminNotification;
use GrahamCampbell\ResultType\Success;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\ContactDetails as ModelsContactDetails;
use App\Models\ParentChild;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Auth extends Controller
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
                'required',
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'name'          => ['required', 'string', 'min:3', 'max:255'],
            'lname'         => ['required', 'string', 'min:3', 'max:255'],
            'email'         => ['required', 'email', 'unique:parents', 'unique:singletons'],
            'password'      => ['required', 'min:5', 'max:20'],
            'device_type' => [
                'required',
                Rule::in(['android','ios']),
            ],
            'user_type' => [
                'required',
                Rule::in(['singleton','parent']),
            ],
            'fcm_token'     => 'required',
            'device_token'  => 'required',
        ]);

        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            // if($key == 'email')
                $key = 'error_message';
                $errors[$key] = is_array($value) ? implode(',', $value) : $value;
        }

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => $errors['error_message'] ? $errors['error_message'] : __('msg.Validation Failed!'),
                // 'errors'    => $validator->errors()
            ],400);
        }

        $verify = detect_disposable_email($request->email);

        if ($verify == 0) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Invalid Email...'),
            ],400);
        }

        try {
            $email_otp = random_int(100000, 999999);


            if($request->user_type == 'singleton'){
                $userDetails = Singleton::create([
                    'name'          => $request->name ? $request->name : '',
                    'lname'         => $request->lname ? $request->lname : '',
                    'email'         => $request->email ? $request->email : '',
                    'user_type'     => $request->user_type ? $request->user_type : '',
                    'email_otp'     => $email_otp ? $email_otp : '',
                    'device_type'   => $request->device_type ? $request->device_type : '',
                    'fcm_token'     => $request->fcm_token ? $request->fcm_token : '',
                    'device_token'  => $request->device_token ? $request->device_token : '',
                    'password'      => Hash::make($request->password),
                ]);
                $user = Singleton::where('email','=',$request->email)->first();
            }else{
                $userDetails = ParentsModel::create([
                    'name'          => $request->name ? $request->name : '',
                    'lname'         => $request->lname ? $request->lname : '',
                    'email'         => $request->email ? $request->email : '',
                    'user_type'     => $request->user_type ? $request->user_type : '',
                    'email_otp'     => $email_otp ? $email_otp : '',
                    'device_type'   => $request->device_type ? $request->device_type : '',
                    'fcm_token'     => $request->fcm_token ? $request->fcm_token : '',
                    'device_token'  => $request->device_token ? $request->device_token : '',
                    'password'      => Hash::make($request->password),
                ]);
                $user = ParentsModel::where('email','=',$request->email)->first();
            }

            if($userDetails){
                $data = ['salutation' => __('msg.Hi'),'name'=> $user->name.' '.$user->lname,'otp'=> $user->email_otp, 'msg'=> __('msg.Let’s get you Registered with us!'), 'otp_msg'=> __('msg.Your One time Password to Complete your Registrations is')];
                $user =  ['to'=> $user->email];
                Mail::send('email_template', $data, function ($message) use ($user) {
                    $message->to($user['to']);
                    $message->subject(__('msg.Email Verification'));
                });

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.register.success'),
                    'data'    => $userDetails
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.register.failure'),
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

    public function socialRegistration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'name'          => ['required', 'string', 'min:3', 'max:255'],
            'lname'         => ['required', 'string', 'min:3', 'max:255'],
            'email'         => ['required', 'email', 'unique:parents', 'unique:singletons'],
            'user_type' => [
                'required',
                Rule::in(['singleton','parent']),
            ],
            // 'password'      => ['required', 'min:5', 'max:20'],
            'device_type' => [
                'required',
                Rule::in(['android','ios']),
            ],
            'fcm_token'     => 'required',
            'device_token'  => 'required',
            'is_social'     => ['required', Rule::in(['0','1'])],
            'social_type'   => [
                'required',
                Rule::in(['google','facebook','apple']),
            ],
            'social_id'     => 'required',
        ]);

        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            // if($key == 'email')
                $key = 'error_message';
                $errors[$key] = is_array($value) ? implode(',', $value) : $value;
        }

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => $errors['error_message'] ? $errors['error_message'] : __('msg.Validation Failed!'),
                // 'errors'    => $validator->errors()
            ],400);
        }

        $verify = detect_disposable_email($request->email);

        if ($verify == 0) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Invalid Email...'),
            ],400);
        }

        try {
            $email_otp = random_int(100000, 999999);


            if($request->user_type == 'singleton'){
                $userDetails = Singleton::create([
                    'name'          => $request->name ? $request->name : '',
                    'lname'         => $request->lname ? $request->lname : '',
                    'email'         => $request->email ? $request->email : '',
                    'user_type'     => $request->user_type ? $request->user_type : '',
                    'email_otp'     => $email_otp ? $email_otp : '',
                    'device_type'   => $request->device_type ? $request->device_type : '',
                    'fcm_token'     => $request->fcm_token ? $request->fcm_token : '',
                    'device_token'  => $request->device_token ? $request->device_token : '',
                    'is_social'     => $request->is_social ? $request->is_social : '',
                    'social_type'   => $request->social_type ? $request->social_type : '',
                    'social_id'     => $request->social_id ? $request->social_id : '',
                    // 'password' => Hash::make($request->password),
                ]);
                $user = Singleton::where('email','=',$request->email)->first();
            }else{
                $userDetails = ParentsModel::create([
                    'name'          => $request->name ? $request->name : '',
                    'lname'         => $request->lname ? $request->lname : '',
                    'email'         => $request->email ? $request->email : '',
                    'user_type'     => $request->user_type ? $request->user_type : '',
                    'email_otp'     => $email_otp ? $email_otp : '',
                    'device_type'   => $request->device_type ? $request->device_type : '',
                    'fcm_token'     => $request->fcm_token ? $request->fcm_token : '',
                    'device_token'  => $request->device_token ? $request->device_token : '',
                    'is_social'     => $request->is_social ? $request->is_social : '',
                    'social_type'   => $request->social_type ? $request->social_type : '',
                    'social_id'     => $request->social_id ? $request->social_id : '',
                    // 'password' => Hash::make($request->password),
                ]);
                $user = ParentsModel::where('email','=',$request->email)->first();
            }

            if($userDetails){
                $data = ['salutation' => __('msg.Hi'),'name'=> $user->name.' '.$user->lname,'otp'=> $user->email_otp, 'msg'=> __('msg.Let’s get you Registered with us!'), 'otp_msg'=> __('msg.Your One time Password to Complete your Registrations is')];

                $user =  ['to'=> $user->email];
                Mail::send('email_template', $data, function ($message) use ($user) {
                    $message->to($user['to']);
                    $message->subject(__('msg.Email Verification'));
                });
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.register.success'),
                    'data'    => $userDetails
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.register.failure'),
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

    public function validateEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'otp'       => ['required', 'numeric'],
            'user_id'   => 'required||numeric',
            'user_type' => [
                'required',
                Rule::in(['singleton','parent']),
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
            if($request->user_type == 'singleton'){
                $user = Singleton::where([['id','=',$request->user_id],['status','=','Unblocked']])->first();
            }else{
                $user = ParentsModel::where([['id','=',$request->user_id],['status','=','Unblocked']])->first();
            }

            if(!empty($user)){
                if($user->email_otp == $request->otp){

                    if($request->user_type == 'singleton'){
                        $verified =  Singleton :: whereId($request->user_id)->update(['is_email_verified' => 'verified', 'updated_at' => date('Y-m-d H:i:s')]);
                    }else{
                        $verified =  ParentsModel :: whereId($request->user_id)->update(['is_email_verified' => 'verified', 'updated_at' => date('Y-m-d H:i:s')]);
                    }
                    
                    if($verified){

                        $admin = Admin::find(1);

                        if($request->user_type == 'singleton'){
                            $user = Singleton::where([['id','=',$request->user_id],['status','=','Unblocked']])->first();

                            $accessCode = random_int(100000,999999);

                            $codeExists = ParentChild::where('access_code', '=', $accessCode)->first();
                            if (!empty($codeExists)) {
                                $accessCode = random_int(100000,999999);
                            }
                                                                                                                                                                                                                                                                                                                                                                                             
                            $accessRequest = ParentChild::updateOrCreate(
                                ['singleton_id' => $user->id],
                                [
                                    'singleton_id' => $user->id ? $user->id : '',
                                    'access_code'  => $accessCode ? $accessCode : '',
                                    'status'       => 'Unlinked',
                                    'created_at'   => date('Y-m-d H:i:s'),
                                ]
                            );
                            $data = ['salutation' => __('msg.Hi'),'name'=> $user->name,'otp'=> $accessCode, 'msg'=> __('msg.Let’s get Connected!'), 'otp_msg'=> __('msg.Your Access Code to get Connected with your Parent/Guardian is')];
                            // $user =  ['to'=> $user->email];
                            Mail::send('email_template', $data, function ($message) use ($user) {
                                $message->to($user->email);
                                $message->subject(__('msg.Access Request Code'));
                            });
                        }else{
                            $user = ParentsModel::where([['id','=',$request->user_id],['status','=','Unblocked']])->first();
                        }

                        $details = [
                            'title' => __('msg.New Registration'),
                            'msg'   => __('msg.has Registered.'),
                        ];
                        $admin->notify(new AdminNotification($user, 'admin', 0, $details));

                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.singletons.validate-email.success'),
                            'data'      => $user
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.singletons.validate-email.failure'),
                        ],400);
                    }
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.validate-email.invalid'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.validate-email.not-found'),
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

    public function resendRegisterOTP(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'email'     => ['required', 'email'],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $verify = detect_disposable_email($request->email);

        if ($verify == 0) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Invalid Email...'),
            ],400);
        }

        try {
            $user = Singleton::where([['email','=',$request->email],['status','=','Unblocked']])->first();
            if(!empty($user)){
                $email_otp = random_int(100000, 999999);
                $singleton =  Singleton :: where('email','=',$request->email)->update(['email_otp' => $email_otp, 'updated_at' => date('Y-m-d H:i:s')]);
                if($singleton){
                    $user = Singleton::where('email','=',$request->email)->first();
                    // $data = ['salutation' => __('msg.Hi'),'name'=> $user->name,'otp'=> $user->email_otp, 'msg'=> __('msg.We are pleased that you have registered with us. Please Verify your OTP!'), 'otp_msg'=> __('msg.Your OTP is')];
                    $data = ['salutation' => __('msg.Hi'),'name'=> $user->name,'otp'=> $user->email_otp, 'msg'=> __('msg.Let’s get you Registered with us!'), 'otp_msg'=> __('msg.Your One time Password to Complete your Registrations is')];

                    $user =  ['to'=> $user->email];
                    Mail::send('email_template', $data, function ($message) use ($user) {
                        $message->to($user['to']);
                        $message->subject(__('msg.Email Verification'));
                    });
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.resend-otp.success'),
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.resend-otp.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.resend-otp.invalid'),
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

    public function socialLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'email'     => ['required', 'email'],
            // 'password'  => ['required', 'min:5', 'max:20'],
            'device_type' => [
                'required',
                Rule::in(['android','ios']),
            ],
            'fcm_token'     => 'required',
            'device_token'  => 'required',
            'is_social'     => ['required', Rule::in(['0','1'])],
            'social_type'   => [
                'required',
                Rule::in(['google','facebook','apple']),
            ],
            'social_id'     => 'required',
        ]);

        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            // if($key == 'email')
                $key = 'error_message';
                $errors[$key] = is_array($value) ? implode(',', $value) : $value;
        }

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => $errors['error_message'] ? $errors['error_message'] : __('msg.Validation Failed!'),
                // 'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = Singleton::where([['email','=',$request->email],['social_type','=',$request->social_type],['is_social','=',$request->is_social]])->first();

            if (empty($user) || $user->status == 'Deleted') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                ],400);
            }

            if (empty($user) || $user->status == 'Blocked') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
                ],400);
            }
    
            if(!empty($user)){
                if($request->social_id == $user->social_id){
                    if ($user->mobile == '') {
                        $user->register_profile = 0;
                    }else {
                        $user->register_profile = 1;
                    }
        
                    if ($user->photo1 == '' && $user->photo2 == '' && $user->photo3 == '' && $user->photo4 == '' && $user->photo5 == '') {
                        $user->photo_uploaded = 0;
                    }else {
                        $user->photo_uploaded = 1;
                    }
        
                    $category = Categories::where([['user_id',$user->id],['user_type', '=', 'singleton']])->first();
                    if (empty($category)) {
                        $user->category_exists = 0;
                    }else{
                        $user->category_exists = 1;
                    }

                    if($user->is_email_verified == 'verified'){
                        Singleton::where('email','=',$request->email)->update(['device_type' => $request->device_type, 'device_token' => $request->device_token, 'fcm_token' => $request->fcm_token]);
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.singletons.login.success'),
                            'data'      => $user
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __("msg.singletons.login.failure"),
                            'data'      => $user
                        ],200);
                    }
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __("msg.singletons.login.not-social"),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.login.not-found'),
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

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'email'     => ['required', 'email'],
            'password'  => ['required', 'min:5', 'max:20'],
            'device_type' => [
                'required',
                Rule::in(['android','ios']),
            ],
            'fcm_token'     => 'required',
            'device_token'  => 'required',
        ]);

        $errors = [];
        foreach ($validator->errors()->messages() as $key => $value) {
            // if($key == 'email')
                $key = 'error_message';
                $errors[$key] = is_array($value) ? implode(',', $value) : $value;
        }

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => $errors['error_message'] ? $errors['error_message'] : __('msg.Validation Failed!'),
                // 'errors'    => $validator->errors()
            ],400);
        }

        $user = Singleton::where('email','=',$request->email)->first();

        if (empty($user) || $user->status == 'Deleted') {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.helper.not-found'),
            ],400);
        }
        
        if (empty($user) || $user->status == 'Blocked') {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.helper.blocked'),
            ],400);
        }

        if(!empty($user)){
            if(Hash::check($request->password, $user->password)){
                if ($user->mobile == '') {
                    $user->register_profile = 0;
                }else {
                    $user->register_profile = 1;
                }
    
                if ($user->photo1 == '' || $user->photo2 == '' || $user->photo3 == '' || $user->photo4 == '' || $user->photo5 == '') {
                    $user->photo_uploaded = 0;
                }else {
                    $user->photo_uploaded = 1;
                }
    
                $category = Categories::where([['user_id',$user->id],['user_type', '=', 'singleton']])->first();
                if (empty($category)) {
                    $user->category_exists = 0;
                }else{
                    $user->category_exists = 1;
                }
                
                if($user->is_email_verified == 'verified'){
                    Singleton::where('email','=',$request->email)->update(['device_type' => $request->device_type, 'device_token' => $request->device_token, 'fcm_token' => $request->fcm_token]);
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.login.success'),
                        'data'      => $user
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __("msg.singletons.login.failure"),
                        'data'      => $user
                    ],200);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __("msg.singletons.login.invalid"),
                ],400);
            }
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.singletons.login.not-found'),
            ],400);
        }
    }

    public function forgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'email'     => ['required', 'email'],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $verify = detect_disposable_email($request->email);

        if ($verify == 0) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Invalid Email...'),
            ],400);
        }

        try {
            $user = Singleton::where([['email','=',$request->email],['status','=','Unblocked']])->first();

            if(!empty($user)){
                $token  = Str::random(40);
                $domain = URL::to('/');
                $url    = $domain.'/api/singleton/reset-password?token='.$token;

                $password_reset = PasswordReset::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]
                );

                if ($password_reset) {
                    $data = ['salutation' => __('msg.Hi'), 'name'=> $user->name.' '.$user->lname,'url'=> $url, 'msg'=> __('msg.Need to reset your password?'), 'url_msg'=> __('msg.No problem! Just click on the button below and you’ll be on yor way.')];
                    $user =  ['to'=> $user->email];
                    Mail::send('reset_password_mail', $data, function ($message) use ($user) {
                        $message->to($user['to']);
                        $message->subject(__('msg.Forget Password'));
                    });
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.forget-pass.success'),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.forget-pass.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.forget-pass.invalid'),
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

    public function ResetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'language' => [
            //     'required',
            //     Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            // ],
            'token'     => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $resetData = DB::table('password_resets')->where('token', '=', $request->token)->first();
            if (!empty($resetData)) {
                $user = Singleton::where([['email','=',$resetData->email],['status','=','Unblocked']])->first();
                if(!empty($user)){
                    $data['user'] = $user;
                    return view('reset_password', $data);
                }else{
                    $data['msg'] = __('msg.Unable to Reset Password! Please Try Again...');
                    return view('reset_password_fail', $data);
                }
            }else {
                $data['msg'] = __('msg.Reset Password Link Expired! Please Try Again...');
                return view('reset_password_fail', $data);
            }
        } catch (\Throwable $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function setNewPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'password'  => ['required', 'min:5', 'max:20'],
            'user_id'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = Singleton::where([['id','=',$request->user_id],['status','=','Unblocked']])->first();
            if(!empty($user)){
                $verified =  Singleton :: where('id','=',$request->user_id)->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
                if($verified){
                    PasswordReset::where('email','=',$user->email)->delete();
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.set-new-pass.success'),
                        'data'      => $user
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.set-new-pass.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.set-new-pass.invalid'),
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

    public function validateForgetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'otp'       => ['required', 'numeric'],
            'password'  => ['required', 'min:5', 'max:20'],
            'user_id'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = Singleton::where([['id','=',$request->user_id],['status','=','Unblocked']])->first();
            if(!empty($user)){
                if($user->email_otp == $request->otp){
                    $verified =  Singleton :: where('id','=',$request->user_id)->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
                    if($verified){
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.singletons.validate-forget-pass.success'),
                            'data'      => $user
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.singletons.validate-forget-pass.failure'),
                        ],400);
                    }
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.validate-forget-pass.invalid'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.validate-forget-pass.not-found'),
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
