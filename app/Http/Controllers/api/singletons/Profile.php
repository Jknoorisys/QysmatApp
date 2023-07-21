<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Categories;
use App\Models\ChatHistory;
use App\Models\Matches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\ReVerifyRequests;
use App\Models\Singleton;
use App\Notifications\AdminNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class Profile extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id'])) {
            $user = Singleton::find($_POST['login_id']);
            if (empty($user)) {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
            
            if (empty($user) || $user->status == 'Blocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.blocked'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->status == 'Deleted') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
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
            'is_me'     => [
                'required' ,
                Rule::in(['yes','no']),
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
            // $user = Singleton::where([['id','=',$request->login_id], ['status','=','Unblocked'], ['is_email_verified','=','verified']])->first();
            // if ($user->parent_id && $user->parent_id != 0) {
            //     $parent = ParentsModel::where('id','=',$user->parent_id)->first();
            //     $user->parent_name = $parent ? $parent->name : '';
            //     $user->parent_profile = $parent ? $parent->profile_pic : '';
            // }

            if ($request->is_me == 'yes') {
                $profile = Singleton::where([['id','=',$request->login_id], ['status','=','Unblocked']])->first();
                if (!empty($profile) && $profile->is_verified != 'pending') {
                    $user = Singleton::where([['id','=',$request->login_id], ['status','=','Unblocked'], ['is_email_verified','=','verified']])->first();
                    if ($user->parent_id && $user->parent_id != 0) {
                        $parent = ParentsModel::where('id','=',$user->parent_id)->first();
                        $user->parent_name = $parent ? $parent->name : '';
                        $user->parent_profile = $parent ? $parent->profile_pic : '';
                    }
                } else {
                    $old_user = Singleton::where([['id','=',$request->login_id], ['status','=','Unblocked']])->first();

                    $user = DB::table('re_verify_requests as sc')
                                ->where([['sc.user_id','=',$request->login_id], ['sc.user_type','=','singleton'], ['sc.status','!=','verified']])
                                ->leftJoin('singletons', function ($join) {
                                    $join->on('sc.user_id', '=', 'singletons.id')
                                        ->where('sc.user_type', '=', 'singleton');
                                })
                                ->first(['sc.user_id as id','sc.user_type','singletons.parent_id','sc.name','sc.lname','sc.email','sc.mobile','sc.photo1','sc.photo2','sc.photo3','sc.photo4','sc.photo5','sc.dob','sc.gender','sc.marital_status','sc.age','sc.height','sc.profession','sc.short_intro','sc.nationality','sc.country_code','sc.nationality_code','sc.ethnic_origin','sc.islamic_sect','sc.location','sc.lat','sc.long','sc.live_photo','sc.id_proof','sc.status as is_verified']);
                                
                    if ($old_user) {
                        $user->photo1 = ($user->photo1 == '' || empty($user->photo1)) ? $old_user->photo1 : $user->photo1;
                        $user->photo2 = ($user->photo2 == '' || empty($user->photo2)) ? $old_user->photo2 : $user->photo2;
                        $user->photo3 = ($user->photo3 == '' || empty($user->photo3)) ? $old_user->photo3 : $user->photo3;
                        $user->photo4 = ($user->photo4 == '' || empty($user->photo4)) ? $old_user->photo4 : $user->photo4;
                        $user->photo5 = ($user->photo5 == '' || empty($user->photo5)) ? $old_user->photo5 : $user->photo5;
                        $user->live_photo = ($user->live_photo == '' || empty($user->live_photo)) ? $old_user->live_photo : $user->live_photo;
                        $user->id_proof = ($user->id_proof == '' || empty($user->id_proof)) ? $old_user->id_proof : $user->id_proof;
                    }

                    if ($user->parent_id && $user->parent_id != 0) {
                        $parent = ParentsModel::where('id','=',$user->parent_id)->first();
                        $user->parent_name = $parent ? $parent->name : '';
                        $user->parent_profile = $parent ? $parent->profile_pic : '';
                    }
                }
            } else {
                $user = Singleton::where([['id','=',$request->login_id], ['status','=','Unblocked']])->first();
                if ($user->parent_id && $user->parent_id != 0) {
                    $parent = ParentsModel::where('id','=',$user->parent_id)->first();
                    $user->parent_name = $parent ? $parent->name : '';
                    $user->parent_profile = $parent ? $parent->profile_pic : '';
                }
            }
            
            if(!empty($user)){
                $unreadCounter = ChatHistory::where([['messaged_user_id', '=', $request->login_id],['messaged_user_type', '=', 'singleton']])                        
                                            ->whereNull('read_at')->count();
                $user->unread_messages = $unreadCounter;

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.get-profile.success'),
                    'data'      => $user
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.get-profile.failure'),
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

    // public function updateProfile(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'language' => [
    //             'required',
    //             Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
    //         ],
    //         'login_id'          => 'required||numeric',
    //         'name'              => ['required', 'string', 'min:3', 'max:255'],
    //         'email'             => ['required', 'email'],
    //         // 'mobile'            => 'required||unique:singletons||unique:parents',
    //         'mobile'            => 'required',
    //         'dob'               => 'required',
    //         'gender'            => 'required',
    //         'height'            => 'required',
    //         'profession'        => 'required',
    //         'nationality'       => 'required',
    //         'country_code'      => 'required',
    //         'ethnic_origin'     => 'required',
    //         'islamic_sect'      => 'required',
    //         'short_intro'       => 'required',
    //         'location'          => 'required',
    //         'lat'               => 'required',
    //         'long'              => 'required',
    //         // 'live_photo'        => 'required',
    //         // 'id_proof'          => 'required',
    //     ]);

    //     // if($validator->fails()){
    //     //     return response()->json([
    //     //         'status'    => 'failed',
    //     //         'message'   => __('msg.Validation Failed!'),
    //     //         'errors'    => $validator->errors()
    //     //     ],400);
    //     // }

    //     $errors = [];
    //     foreach ($validator->errors()->messages() as $key => $value) {
    //         // if($key == 'email')
    //             $key = 'error_message';
    //             $errors[$key] = is_array($value) ? implode(',', $value) : $value;
    //     }

    //     if($validator->fails()){
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => $errors['error_message'] ? $errors['error_message'] : __('msg.Validation Failed!'),
    //             // 'errors'    => $validator->errors()
    //         ],400);
    //     }

    //     try {
    //         $age = Carbon::parse($request->dob)->age;
    //         $user = Singleton::find($request->login_id);
    //         if(!empty($user)){
    //             $user->name          = $request->name ? $request->name : '';
    //             $user->email         = $request->email ? $request->email : '';
    //             $user->mobile        = $request->mobile ? $request->mobile : '';
    //             $user->dob           = $request->dob ? $request->dob : '';
    //             $user->gender        = $request->gender ? $request->gender : '';
    //             $user->age           = $age ? $age : '';
    //             $user->height        = $request->height ? $request->height : '';
    //             $user->profession    = $request->profession ? $request->profession : '';
    //             $user->country_code  = $request->country_code ? $request->country_code : '';
    //             $user->nationality   = $request->nationality ? $request->nationality : '';
    //             $user->ethnic_origin = $request->ethnic_origin ? $request->ethnic_origin : '';
    //             $user->islamic_sect  = $request->islamic_sect ? $request->islamic_sect : '';
    //             $user->short_intro   = $request->short_intro ? $request->short_intro : '';
    //             $user->location      = $request->location ? $request->location : '';
    //             $user->lat           = $request->lat ? $request->lat : '';
    //             $user->long          = $request->long ? $request->long : '';

    //             $file1 = $request->file('live_photo');
    //             if ($file1) {
    //                 $extension = $file1->getClientOriginalExtension();
    //                 $filename = time().'.'.$extension;
    //                 $file1->move('assets/uploads/singleton-live-photos/', $filename);
    //                 $user->live_photo = 'assets/uploads/singleton-live-photos/'.$filename;
    //             }

    //             $file2 = $request->file('id_proof');
    //             if ($file2) {
    //                 $extension = $file2->getClientOriginalExtension();
    //                 $filename = time().'.'.$extension;
    //                 $file2->move('assets/uploads/singleton-id-proofs/', $filename);
    //                 $user->id_proof = 'assets/uploads/singleton-id-proofs/'.$filename;
    //             }

    //         $userDetails =  $user->save();
    //         if($userDetails){
    //             DB::table('categories')->updateOrInsert(
    //                 ['user_id' => $request->login_id, 'user_type' => 'singleton'],
    //                 [
    //                     'user_id' => $request->login_id,
    //                     'user_type' => 'singleton',
    //                     'gender'       => $request->gender == 'Male' ? 'Female' : 'Male'
    //                 ]
    //             );
    //                 return response()->json([
    //                     'status'    => 'success',
    //                     'message'   => __('msg.singletons.update-profile.success'),
    //                     'data'      => $user
    //                 ],200);
    //         }else{
    //                 return response()->json([
    //                     'status'    => 'failed',
    //                     'message'   => __('msg.singletons.update-profile.failure'),
    //                 ],400);
    //         }
    //         }else{
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.singletons.update-profile.invalid'),
    //             ],400);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.error'),
    //             'error'     => $e->getMessage()
    //         ],500);
    //     }
    // }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required',
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'          => 'required||numeric',
            'name'              => ['required', 'string', 'min:3', 'max:255'],
            'lname'             => ['required', 'string', 'min:3', 'max:255'],
            'email'             => ['required', 'email'],
            // 'mobile'            => 'required||unique:singletons||unique:parents',
            'mobile'            => 'required',
            'dob'               => 'required',
            'gender'            => 'required',
            'marital_status'    => 'required',
            'height'            => 'required',
            'profession'        => 'required',
            'nationality'       => 'required',
            'country_code'      => 'required',
            'nationality_code'  => 'required',
            'ethnic_origin'     => 'required',
            'islamic_sect'      => 'required',
            'short_intro'       => 'required',
            'location'          => 'required',
            'lat'               => 'required',
            'long'              => 'required',
            // 'live_photo'        => 'required',
            // 'id_proof'          => 'required',
        ]);

        // if($validator->fails()){
        //     return response()->json([
        //         'status'    => 'failed',
        //         'message'   => __('msg.Validation Failed!'),
        //         'errors'    => $validator->errors()
        //     ],400);
        // }

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
            $age = Carbon::parse($request->dob)->age;
            $user = Singleton::find($request->login_id);
            if(!empty($user)){
                $file1 = $request->file('live_photo');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file1->move('assets/uploads/singleton-live-photos/', $filename);
                    $live_photo = 'assets/uploads/singleton-live-photos/'.$filename;
                }

                $file2 = $request->file('id_proof');
                if ($file2) {
                    $extension = $file2->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file2->move('assets/uploads/singleton-id-proofs/', $filename);
                    $id_proof = 'assets/uploads/singleton-id-proofs/'.$filename;
                }

                $userDetails = ReVerifyRequests::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => 'singleton'],
                    [
                        'user_id'                   => $request->login_id, 
                        'user_type'                 => 'singleton',
                        'name'                      => $request->name ? $request->name : '',
                        'lname'                     => $request->lname ? $request->lname : '',
                        'email'                     => $request->email ? $request->email : '',
                        'mobile'                    => $request->mobile ? $request->mobile : '',
                        'dob'                       => $request->dob ? $request->dob : '',
                        'gender'                    => $request->gender ? $request->gender : '',
                        'marital_status'            => $request->marital_status ? $request->marital_status : 'Never Married',
                        'age'                       => $age ? $age : '',
                        'height'                    => $request->height ? $request->height : '',
                        'profession'                => $request->profession ? $request->profession : '',
                        'nationality'               => $request->nationality ? $request->nationality : '',
                        'country_code'              => $request->country_code ? $request->country_code : '',
                        'nationality_code'          => $request->nationality_code ? $request->nationality_code : '',
                        'ethnic_origin'             => $request->ethnic_origin ? $request->ethnic_origin : '',
                        'islamic_sect'              => $request->islamic_sect ? $request->islamic_sect : '',
                        'short_intro'               => $request->short_intro ? $request->short_intro : '',
                        'location'                  => $request->location ? $request->location : '',
                        'lat'                       => $request->lat ? $request->lat : '',
                        'long'                      => $request->long ? $request->long : '',
                        'live_photo'                => $request->file('live_photo') ? $live_photo : '',
                        'id_proof'                  => $request->file('id_proof') ? $id_proof : '',
                        'status'                    => 'pending'
                    ]
                );
                
                if($userDetails){
                    Singleton::where('id', '=', $request->login_id)->update(['is_verified' => 'pending']);
                    DB::table('categories')->updateOrInsert(
                        ['user_id' => $request->login_id, 'user_type' => 'singleton'],
                        [
                            'user_id' => $request->login_id,
                            'user_type' => 'singleton',
                            'gender'       => $request->gender == 'Male' ? 'Female' : 'Male'
                        ]
                    );

                    $userData = [
                        'user_id'                   => $request->login_id, 
                        'user_type'                 => 'singleton',
                        'name'                      => $request->name ? $request->name : $user->name,
                        'email'                     => $request->email ? $request->email : $user->email,
                        'lname'                     => $request->lname ? $request->lname : $user->lname,
                        'mobile'                    => $request->mobile ? $request->mobile : $user->mobile,
                        'dob'                       => $request->dob ? $request->dob : $user->dob,
                        'gender'                    => $request->gender ? $request->gender : $user->gender,
                        'marital_status'            => $request->marital_status ? $request->marital_status : $user->marital_status,
                        'age'                       => $age ? $age : $user->age,
                        'height'                    => $request->height ? $request->height : $user->height,
                        'profession'                => $request->profession ? $request->profession : $user->profession,
                        'nationality'               => $request->nationality ? $request->nationality : $user->nationality,
                        'country_code'              => $request->country_code ? $request->country_code : $user->country_code,
                        'ethnic_origin'             => $request->ethnic_origin ? $request->ethnic_origin : $user->ethnic_origin,
                        'islamic_sect'              => $request->islamic_sect ? $request->islamic_sect : $user->islamic_sect,
                        'short_intro'               => $request->short_intro ? $request->short_intro : $user->short_intro,
                        'location'                  => $request->location ? $request->location : $user->location,
                        'lat'                       => $request->lat ? $request->lat : $user->lat,
                        'long'                      => $request->long ? $request->long : $user->long,
                        'live_photo'                => $request->file('live_photo') ? $live_photo : $user->live_photo,
                        'id_proof'                  => $request->file('id_proof') ? $id_proof : $user->id_proof,
                        'is_verified'               => 'pending'
                    ];

                    $details = [
                        'title' => __('msg.Profile Reverification Request'),
                        'msg'   => __('msg.has updated his/her Profile Details.'),
                    ];

                    $admin = Admin::find(1);
                    $admin->notify(new AdminNotification($user, 'admin', 0, $details));
                    // $send = sendWebNotification($details['title'], 'Someone '.$details['msg'])  ;

                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.update-profile.success'),
                        'data'      => $userData
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.update-profile.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.update-profile.invalid'),
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

    public function uploadPhotos(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
            // 'photo1'     => 'required_without_all:photo2,photo3,photo4,photo5||image||mimes:jpeg,png,jpg,svg||max:5000',
            // 'photo2'     => 'required_without_all:photo1,photo3,photo4,photo5||image||mimes:jpeg,png,jpg,svg||max:5000',
            // 'photo3'     => 'required_without_all:photo2,photo1,photo4,photo5||image||mimes:jpeg,png,jpg,svg||max:5000',
            // 'photo4'     => 'required_without_all:photo2,photo3,photo1,photo5||image||mimes:jpeg,png,jpg,svg||max:5000',
            // 'photo5'     => 'required_without_all:photo2,photo3,photo4,photo1||image||mimes:jpeg,png,jpg,svg||max:5000',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = Singleton::find($request->login_id);
            if(!empty($user)){
                
                $file1 = $request->file('photo1');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename1 = time().'1.'.$extension;
                    $file1->move('assets/uploads/singleton-photos/', $filename1);
                    $photo1 = 'assets/uploads/singleton-photos/'.$filename1;
                }

                $file2 = $request->file('photo2');
                if ($file2) {
                    $extension = $file2->getClientOriginalExtension();
                    $filename2 = time().'2.'.$extension;
                    $file2->move('assets/uploads/singleton-photos/', $filename2);
                    $photo2 = 'assets/uploads/singleton-photos/'.$filename2;
                }

                $file3 = $request->file('photo3');
                if ($file3) {
                    $extension = $file3->getClientOriginalExtension();
                    $filename3 = time().'3.'.$extension;
                    $file3->move('assets/uploads/singleton-photos/', $filename3);
                    $photo3 = 'assets/uploads/singleton-photos/'.$filename3;
                }

                $file4 = $request->file('photo4');
                if ($file4) {
                    $extension = $file4->getClientOriginalExtension();
                    $filename4 = time().'4.'.$extension;
                    $file4->move('assets/uploads/singleton-photos/', $filename4);
                    $photo4 = 'assets/uploads/singleton-photos/'.$filename4;
                }

                $file5 = $request->file('photo5');
                if ($file5) {
                    $extension = $file5->getClientOriginalExtension();
                    $filename5 = time().'5.'.$extension;
                    $file5->move('assets/uploads/singleton-photos/', $filename5);
                    $photo5 = 'assets/uploads/singleton-photos/'.$filename5;
                }

                $userDetails = ReVerifyRequests::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => 'singleton'],
                    [
                        'user_id'   => $request->login_id, 
                        'user_type' => 'singleton',
                        'photo1'    => $request->file('photo1') ? $photo1 : '', 
                        'photo2'    => $request->file('photo2') ? $photo2 : '',
                        'photo3'    => $request->file('photo3') ? $photo3 : '',
                        'photo4'    => $request->file('photo4') ? $photo4 : '',
                        'photo5'    => $request->file('photo5') ? $photo5 : '',
                        'status'    => 'pending'
                    ]
                );

                if($userDetails){
                    Singleton::where('id', '=', $request->login_id)->update(['is_verified' => 'pending']);
                    $user->photo1 = $request->file('photo1') ? $photo1 : $user->photo1;
                    $user->photo2 = $request->file('photo2') ? $photo2 : $user->photo2;
                    $user->photo3 = $request->file('photo3') ? $photo3 : $user->photo3;
                    $user->photo4 = $request->file('photo4') ? $photo4 : $user->photo4;
                    $user->photo5 = $request->file('photo5') ? $photo5 : $user->photo5;

                    $details = [
                        'title' => __('msg.Profile Reverification Request'),
                        'msg'   => __('msg.has updated his/her Profile Details.'),
                    ];

                    $admin = Admin::find(1);
                    $admin->notify(new AdminNotification($user, 'admin', 0, $details));
                    
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.upload-pictures.success'),
                        'data'      => $user
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.upload-pictures.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.upload-pictures.invalid'),
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

    public function getAccessDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $linked = ParentChild::where('singleton_id','=', $request->login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.access-details.invalid'),
                ],400);
            }else{
                $parent = ParentChild::where('singleton_id','=', $request->login_id)->join('parents', 'parent_children.parent_id','=','parents.id')->first(['parent_children.access_code', 'parents.*']);
                if(!empty($parent)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.access-details.success'),
                        'data'      => $parent
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.access-details.failure'),
                    ],400);
                }
            }
        } catch (\Exception $e) {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.error'),
                'error'     => $e->getMessage()
            ],500);
        }
    }

    public function chatInProgress(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $busy = Singleton::where('id', '=', $request->login_id)->first();

            if (!empty($busy) && $busy->is_verified != 'verified') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-verified'),
                ],403);
            }

            if (!empty($busy) && $busy->chat_status == 'busy') {
                $singleton_id = $request->login_id;
                $mutual = Matches ::leftjoin('singletons', function($join) use ($singleton_id) {
                                        $join->on('singletons.id','=','matches.match_id')
                                            ->where('matches.match_id','!=',$singleton_id);
                                        $join->orOn('singletons.id','=','matches.user_id')
                                            ->where('matches.user_id','!=',$singleton_id);
                                    })
                                    ->where(function($query) use ($singleton_id) {
                                        $query->where([['matches.user_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'matched'], ['matches.status','=', 'busy']])
                                              ->orWhere([['matches.match_id', '=', $singleton_id], ['matches.user_type', '=', 'singleton'], ['matches.match_type', '=', 'matched'], ['matches.status','=', 'busy']]);
                                    })
                                    ->first(['singletons.id','singletons.name','singletons.photo1']);
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.helper.busy'),
                    'data'      => $mutual ? $mutual: '',
                ],200);
            } else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.helper.available'),
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

    public function updatecurrentlocation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
            'location'   => 'required',
            'lat'        => 'required',
            'long'       => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $data = [
                'location' => $request->location,
                'lat'      => $request->lat,
                'long'     => $request->long,
                'updated_at' => Carbon::now()
            ];
            $update = Singleton::where('id', '=', $request->login_id)->update($data);
            if ($update) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.update-location.success'),
                    'data'      => $data,
                ],200);
            } else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.update-location.failure'),
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
