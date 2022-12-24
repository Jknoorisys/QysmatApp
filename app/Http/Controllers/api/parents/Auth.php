<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\ParentsModel;
use App\Models\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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
            'email'         => ['required', 'email', 'unique:parents', 'unique:singletons'],
            'password'      => ['required', 'min:5', 'max:20'],
            'device_type' => [
                'required',
                Rule::in(['android','ios']),
            ],
            'fcm_token'     => 'required',
            'device_token'  => 'required',
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
            $email_otp = random_int(100000, 999999);
            $parent = ParentsModel::create([
                'name'          => $request->name ? $request->name : '',
                'email'         => $request->email ? $request->email : '',
                'user_type'     => 'Parent',
                'email_otp'     => $email_otp ? $email_otp : '',
                'device_type'   => $request->device_type ? $request->device_type : '',
                'fcm_token'     => $request->fcm_token ? $request->fcm_token : '',
                'device_token'  => $request->device_token ? $request->device_token : '',
                'password' => Hash::make($request->password),
            ]);

            if($parent){
                $user = ParentsModel::where('email','=',$request->email)->first();
                $data = ['salutation' => __('msg.Hi'),'name'=> $user->name,'otp'=> $user->email_otp, 'msg'=> __('msg.We are pleased that you have registered with us. Please Verify your OTP!'), 'otp_msg'=> __('msg.Your OTP is')];
                $user =  ['to'=> $user->email];
                Mail::send('mail', $data, function ($message) use ($user) {
                    $message->to($user['to']);
                    $message->subject(__('msg.Email Verification'));
                });
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.register.success'),
                    'data'    => $parent
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.register.failure'),
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
            'email'         => ['required', 'email', 'unique:parents', 'unique:singletons'],
            'password'      => ['required', 'min:5', 'max:20'],
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
            $email_otp = random_int(100000, 999999);
            $parent = ParentsModel::create([
                'name'          => $request->name ? $request->name : '',
                'email'         => $request->email ? $request->email : '',
                'user_type'     => 'Parent',
                'email_otp'     => $email_otp ? $email_otp : '',
                'device_type'   => $request->device_type ? $request->device_type : '',
                'fcm_token'     => $request->fcm_token ? $request->fcm_token : '',
                'device_token'  => $request->device_token ? $request->device_token : '',
                'is_social'     => $request->is_social ? $request->is_social : '',
                'social_type'   => $request->social_type ? $request->social_type : '',
                'social_id'     => $request->social_id ? $request->social_id : '',
                'password' => Hash::make($request->password),
            ]);

            if($parent){
                $user = ParentsModel::where('email','=',$request->email)->first();
                $data = ['salutation' => __('msg.Hi'),'name'=> $user->name,'otp'=> $user->email_otp, 'msg'=> __('msg.We are pleased that you have registered with us. Please Verify your OTP!'), 'otp_msg'=> __('msg.Your OTP is')];
                $user =  ['to'=> $user->email];
                Mail::send('mail', $data, function ($message) use ($user) {
                    $message->to($user['to']);
                    $message->subject(__('msg.Email Verification'));
                });
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.register.success'),
                    'data'    => $parent
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.register.failure'),
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
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = ParentsModel::where([['id','=',$request->user_id],['status','=','unblocked']])->first();
            if(!empty($user)){
                if($user->email_otp == $request->otp){
                    $verified =  ParentsModel :: whereId($request->user_id)->update(['is_email_verified' => 'verified', 'email_verified_at' => date('Y-m-d H:i:s')]);
                    if($verified){
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.parents.validate-email.success'),
                            'data'      => $user
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.parents.validate-email.failure'),
                        ],400);
                    }
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.validate-email.invalid'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.validate-email.not-found'),
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
            $user = ParentsModel::where([['email','=',$request->email],['status','=','Unblocked']])->first();
            if(!empty($user)){
                $email_otp = random_int(100000, 999999);
                $singleton =  ParentsModel :: where('email','=',$request->email)->update(['email_otp' => $email_otp, 'updated_at' => date('Y-m-d H:i:s')]);
                if($singleton){
                    $user = ParentsModel::where('email','=',$request->email)->first();
                    $data = ['salutation' => __('msg.Hi'),'name'=> $user->name,'otp'=> $user->email_otp, 'msg'=> __('msg.We are pleased that you have registered with us. Please Verify your OTP!'), 'otp_msg'=> __('msg.Your OTP is')];
                    $user =  ['to'=> $user->email];
                    Mail::send('mail', $data, function ($message) use ($user) {
                        $message->to($user['to']);
                        $message->subject(__('msg.Email Verification'));
                    });
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.resend-otp.success'),
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.resend-otp.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.resend-otp.invalid'),
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
            $user = ParentsModel::where([['email','=',$request->email],['status','=','Unblocked']])->first();

            if(!empty($user)){
                $token  = Str::random(40);
                $domain = URL::to('/');
                $url    = $domain.'/api/parent/reset-password?token='.$token;

                $password_reset = PasswordReset::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => date('Y-m-d H:i:s'),
                    ]
                );

                if ($password_reset) {
                    $data = ['salutation' => __('msg.Hi'), 'name'=> $user->name,'url'=> $url, 'msg'=> __('msg.I am pleased that you have registered with us. Please Click on Below link to Reset Your Password!'), 'url_msg'=> __('msg.Click Here to Reset Password!')];
                    $user =  ['to'=> $user->email];
                    Mail::send('reset_password_mail', $data, function ($message) use ($user) {
                        $message->to($user['to']);
                        $message->subject(__('msg.Forget Password'));
                    });
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.forget-pass.success'),
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.forget-pass.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.forget-pass.invalid'),
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
            $resetData = PasswordReset::where('token',$request->token)->first();
            if (!empty($resetData)) {
                $user = ParentsModel::where([['email','=',$resetData->email],['status','=','Unblocked']])->first();
                if(!empty($user)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.forget-pass-link.success'),
                        'data'      => $user,
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.forget-pass-link.invalid'),
                    ],400);
                }
            }else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.forget-pass-link.failure'),
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
            $user = ParentsModel::where([['id','=',$request->user_id],['status','=','Unblocked']])->first();
            if(!empty($user)){
                $verified =  ParentsModel :: where('id','=',$request->user_id)->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
                if($verified){
                    PasswordReset::where('email','=',$user->email)->delete();
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.set-new-pass.success'),
                        'data'      => $user
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.set-new-pass.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.set-new-pass.invalid'),
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
            $user = ParentsModel::where([['id','=',$request->user_id],['status','=','unblocked']])->first();
            if(!empty($user)){
                if($user->email_otp == $request->otp){
                    $verified =  ParentsModel :: where('id','=',$request->user_id)->update(['password' => Hash::make($request->password), 'updated_at' => date('Y-m-d H:i:s')]);
                    if($verified){
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.parents.validate-forget-pass.success'),
                            'data'      => $user
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.parents.validate-forget-pass.failure'),
                        ],400);
                    }
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.validate-forget-pass.invalid'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.validate-forget-pass.not-found'),
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

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = ParentsModel::where([['email','=',$request->email],['is_social','=',$request->is_social],['social_type','=',$request->social_type],['status','=','unblocked']])->first();
            if(!empty($user)){
                // if($user->is_email_verified == 'verified'){
                    if($request->social_id == $user->social_id){
                        ParentsModel::where('email','=',$request->email)->update(['device_type' => $request->device_type, 'device_token' => $request->device_token, 'fcm_token' => $request->fcm_token]);
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.parents.login.success'),
                            'data'      => $user
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __("msg.parents.login.not-found"),
                        ],400);
                    }
                // }else{
                //     return response()->json([
                //         'status'    => 'failed',
                //         'message'   => __("msg.parents.login.failure"),
                //     ],400);
                // }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.login.not-found'),
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

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = ParentsModel::where([['email','=',$request->email],['status','=','unblocked']])->first();
            if(!empty($user)){
                // if($user->is_email_verified == 'verified'){
                    if(Hash::check($request->password, $user->password)){
                        ParentsModel::where('email','=',$request->email)->update(['device_type' => $request->device_type, 'device_token' => $request->device_token, 'fcm_token' => $request->fcm_token]);
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.parents.login.success'),
                            'data'      => $user
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __("msg.parents.login.invalid"),
                        ],400);
                    }
                // }else{
                //     return response()->json([
                //         'status'    => 'failed',
                //         'message'   => __("msg.parents.login.failure"),
                //     ],400);
                // }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.login.not-found'),
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
