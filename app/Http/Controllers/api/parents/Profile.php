<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\ReVerifyRequests;
use App\Models\Singleton;
use App\Notifications\AccountLinkedNotification;
use App\Notifications\RequestAccessNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Profile extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id'])) {
            $user = ParentsModel::find($_POST['login_id']);
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
            if ($request->is_me == 'yes') {
                $profile = ParentsModel::where([['id','=',$request->login_id], ['status','=','Unblocked']])->first();
                if (!empty($profile) && $profile->is_verified != 'pending') {
                    $user = ParentsModel::where([['id','=',$request->login_id], ['status','=','Unblocked'], ['is_email_verified','=','verified']])->first();
                } else {
                    $old_user = ParentsModel::where([['id','=',$request->login_id]])->first();
                    $user = ReVerifyRequests::where([['user_id','=',$request->login_id], ['user_type','=','parent'], ['status','!=','verified']])
                    ->first(['user_id as id','user_type','name','email','mobile','profile_pic','relation_with_singleton','nationality','country_code','nationality_code','ethnic_origin','islamic_sect','location','lat','long','live_photo','id_proof','status as is_verified']);
                    
                    if ($old_user) {
                        $user->profile_pic = ($user->profile_pic == '' || empty($user->profile_pic)) ? $old_user->profile_pic : $user->profile_pic;
                        $user->live_photo = ($user->live_photo == '' || empty($user->live_photo)) ? $old_user->live_photo : $user->live_photo;
                        $user->id_proof = ($user->id_proof == '' || empty($user->id_proof)) ? $old_user->id_proof : $user->photo1;
                    }
                }
            } else {
                $user = ParentsModel::where([['id','=',$request->login_id], ['status','=','unblocked'], ['is_email_verified','=','verified']])->first();
            }
            
            if(!empty($user)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.get-profile.success'),
                    'data'      => $user
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.get-profile.failure'),
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
    //         'relation_with_singleton' => 'required',
    //         'profile_pic'       => 'image||mimes:jpeg,png,jpg,svg||max:5000',
    //         'nationality'       => 'required',
    //         'country_code'      => 'required',
    //         'ethnic_origin'     => 'required',
    //         'islamic_sect'      => 'required',
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
    //         $user = ParentsModel::find($request->login_id);
    //         if(!empty($user)){
    //             $user->name          = $request->name ? $request->name : '';
    //             $user->email         = $request->email ? $request->email : '';
    //             $user->mobile        = $request->mobile ? $request->mobile : '';
    //             $user->nationality   = $request->nationality ? $request->nationality : '';
    //             $user->country_code  = $request->country_code ? $request->country_code : '';
    //             $user->ethnic_origin = $request->ethnic_origin ? $request->ethnic_origin : '';
    //             $user->islamic_sect  = $request->islamic_sect ? $request->islamic_sect : '';
    //             $user->location      = $request->location ? $request->location : '';
    //             $user->lat           = $request->lat ? $request->lat : '';
    //             $user->long          = $request->long ? $request->long : '';
    //             $user->relation_with_singleton          = $request->relation_with_singleton ? $request->relation_with_singleton : '';

    //             $file = $request->file('profile_pic');
    //             if ($file) {
    //                 $extension = $file->getClientOriginalExtension();
    //                 $filename = time().'.'.$extension;
    //                 $file->move('assets/uploads/parent-photos/', $filename);
    //                 $user->profile_pic = 'assets/uploads/parent-photos/'.$filename  ;
    //             }

    //             $file1 = $request->file('live_photo');
    //             if ($file1) {
    //                 $extension = $file1->getClientOriginalExtension();
    //                 $filename = time().'.'.$extension;
    //                 $file1->move('assets/uploads/parent-live-photos/', $filename);
    //                 $user->live_photo = 'assets/uploads/parent-live-photos/'.$filename;
    //             }

    //             $file2 = $request->file('id_proof');
    //             if ($file2) {
    //                 $extension = $file2->getClientOriginalExtension();
    //                 $filename = time().'.'.$extension;
    //                 $file2->move('assets/uploads/parent-id-proofs/', $filename);
    //                 $user->id_proof = 'assets/uploads/parent-id-proofs/'.$filename;
    //             }

    //         $userDetails =  $user->save();
    //         if($userDetails){
    //                 return response()->json([
    //                     'status'    => 'success',
    //                     'message'   => __('msg.parents.update-profile.success'),
    //                     'data'      => $user
    //                 ],200);
    //         }else{
    //                 return response()->json([
    //                     'status'    => 'failed',
    //                     'message'   => __('msg.parents.update-profile.failure'),
    //                 ],400);
    //         }
    //         }else{
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.parents.update-profile.invalid'),
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
            'email'             => ['required', 'email'],
            // 'mobile'            => 'required||unique:singletons||unique:parents',
            'mobile'            => 'required',
            'relation_with_singleton' => 'required',
            'profile_pic'       => 'image||mimes:jpeg,png,jpg,svg||max:5000',
            'nationality'       => 'required',
            'country_code'      => 'required',
            'nationality_code'  => 'required',
            'ethnic_origin'     => 'required',
            'islamic_sect'      => 'required',
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
            $user = ParentsModel::find($request->login_id);
            if(!empty($user)){
                $file = $request->file('profile_pic');
                if ($file) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file->move('assets/uploads/parent-photos/', $filename);
                    $profile_pic = 'assets/uploads/parent-photos/'.$filename  ;
                }

                $file1 = $request->file('live_photo');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file1->move('assets/uploads/parent-live-photos/', $filename);
                    $live_photo = 'assets/uploads/parent-live-photos/'.$filename;
                }

                $file2 = $request->file('id_proof');
                if ($file2) {
                    $extension = $file2->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file2->move('assets/uploads/parent-id-proofs/', $filename);
                    $id_proof = 'assets/uploads/parent-id-proofs/'.$filename;
                }

                $userDetails = ReVerifyRequests::updateOrInsert(
                    ['user_id' => $request->login_id, 'user_type' => 'parent'],
                    [
                        'user_id'                   => $request->login_id, 
                        'user_type'                 => 'parent',
                        'name'                      => $request->name ? $request->name : '',
                        'email'                     => $request->email ? $request->email : '',
                        'mobile'                    => $request->mobile ? $request->mobile : '',
                        'nationality'               => $request->nationality ? $request->nationality : '',
                        'country_code'              => $request->country_code ? $request->country_code : '',
                        'nationality_code'          => $request->nationality_code ? $request->nationality_code : '',
                        'ethnic_origin'             => $request->ethnic_origin ? $request->ethnic_origin : '',
                        'ethnic_origin'             => $request->ethnic_origin ? $request->ethnic_origin : '',
                        'islamic_sect'              => $request->islamic_sect ? $request->islamic_sect : '',
                        'location'                  => $request->location ? $request->location : '',
                        'lat'                       => $request->lat ? $request->lat : '',
                        'long'                      => $request->long ? $request->long : '',
                        'relation_with_singleton'   => $request->relation_with_singleton ? $request->relation_with_singleton : '',
                        'profile_pic'               => $request->file('profile_pic') ? $profile_pic : '',
                        'live_photo'                => $request->file('live_photo') ? $live_photo : '',
                        'id_proof'                  => $request->file('id_proof') ? $id_proof : '',
                        'status'                    => 'pending'
                    ]
                );
                
                if($userDetails){
                    ParentsModel::where('id', '=', $request->login_id)->update(['is_verified' => 'pending']);

                    $userData = [
                        'user_id'                   => $request->login_id, 
                        'user_type'                 => 'parent',
                        'name'                      => $request->name ? $request->name : $user->name,
                        'email'                     => $request->email ? $request->email : $user->email,
                        'mobile'                    => $request->mobile ? $request->mobile : $user->mobile,
                        'nationality'               => $request->nationality ? $request->nationality : $user->nationality,
                        'country_code'              => $request->country_code ? $request->country_code : $user->country_code,
                        'ethnic_origin'             => $request->ethnic_origin ? $request->ethnic_origin : $user->ethnic_origin,
                        'islamic_sect'              => $request->islamic_sect ? $request->islamic_sect : $user->islamic_sect,
                        'location'                  => $request->location ? $request->location : $user->location,
                        'lat'                       => $request->lat ? $request->lat : $user->lat,
                        'long'                      => $request->long ? $request->long : $user->long,
                        'relation_with_singleton'   => $request->relation_with_singleton ? $request->relation_with_singleton : $user->relation_with_singleton,
                        'profile_pic'               => $request->file('profile_pic') ? $profile_pic : $request->profile_pic,
                        'live_photo'                => $request->file('live_photo') ? $live_photo : $user->live_photo,
                        'id_proof'                  => $request->file('id_proof') ? $id_proof : $user->id_proof,
                        'is_verified'               => 'pending'
                    ];

                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.update-profile.success'),
                        'data'      => $userData
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.parents.update-profile.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.update-profile.invalid'),
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

    public function searchChild(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
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
            $search =  $request->search ? $request->search : '';

            $children = $search ? Singleton::where([['status', '=' ,'Unblocked'],['is_verified', '=' ,'verified'], ['name', 'LIKE', "%$search%"]])->orWhere([['status', '=' ,'Unblocked'],['is_verified', '=' ,'verified'], ['email', 'LIKE', "%$search%"]])->orderBy('name')->get() : Singleton::where([['status', '=' ,'Unblocked'],['is_verified', '=' ,'verified']])->orderBy('name')->get();

            if(!$children->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.search-child.success'),
                    'data'    => $children
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.search-child.failure'),
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

    public function sendAccessRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'      => 'required||numeric',
            'singleton_id'  => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = Singleton::where([['id','=',$request->singleton_id],['status','!=','Deleted']])->first();

            if (!empty($user) && !empty($user->parent_id)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.access-request.invalid'),
                ],400);
            }

            $access_code = random_int(100000, 999999);
            $accessRequest = ParentChild::updateOrCreate(
                ['parent_id' => $request->login_id, 'singleton_id' => $request->singleton_id],
                [
                    'parent_id'    => $request->login_id ? $request->login_id : '',
                    'singleton_id' => $request->singleton_id ? $request->singleton_id : '',
                    'access_code'  => $access_code ? $access_code : '',
                    'status'       => 'Unlinked',
                    'created_at'   => date('Y-m-d H:i:s'),
                ]
            );

            if($accessRequest){

                $parent = ParentsModel::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();
                $user->notify(new RequestAccessNotification($parent, $user->user_type, 0, $access_code));

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.access-request.success'),
                    'data'    => $accessRequest
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.access-request.failure'),
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

    public function verifyAccessRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'      => 'required||numeric',
            'singleton_id'  => 'required||numeric',
            'access_code'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $accessRequest = ParentChild::where([['singleton_id','=',$request->singleton_id],['access_code','=',$request->access_code]])->first();

            if(!empty($accessRequest)){
                ParentChild::where([['singleton_id', '=', $accessRequest->singleton_id],['access_code','=',$request->access_code]])->update(['parent_id' => $request->login_id,'status' => 'Linked']);
                Singleton::where('id','=',$accessRequest->singleton_id)->update(['parent_id' => $request->login_id]);

                $user = Singleton::where([['id','=',$request->singleton_id],['status','!=','Deleted']])->first();
                $parent = ParentsModel::where([['id','=',$request->login_id],['status','=','Unblocked']])->first();
                $user->notify(new AccountLinkedNotification($parent, $user->user_type, 0));
                $parent->notify(new AccountLinkedNotification($user, $parent->user_type, $accessRequest->singleton_id));

                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.verify-access-request.success'),
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.verify-access-request.failure'),
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

    public function getLinkedProfiles(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'      => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $profiles = DB::table('parent_children')
                        ->where([['parent_children.parent_id','=',$request->login_id], ['parent_children.status', '=', 'Linked']])
                        ->join('singletons', 'singletons.id', '=', 'parent_children.singleton_id')
                        ->select('singletons.*')
                        ->get();

            if(!$profiles->isEmpty()){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.get-linked-profiles.success'),
                    'data'      => $profiles
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.get-linked-profiles.failure'),
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

    public function getChildProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'      => 'required||numeric',
            'singleton_id'  => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $profiles = DB::table('parent_children')
                        ->where('parent_children.parent_id','=',$request->login_id)
                        ->where('singletons.id','=',$request->singleton_id)
                        ->where('parent_children.status','=', 'Linked')
                        ->join('singletons', 'singletons.id', '=', 'parent_children.singleton_id')
                        ->select('singletons.*')
                        ->first();

            if(!empty($profiles)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.get-child-profile.success'),
                    'data'      => $profiles
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.get-child-profile.failure'),
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
            $update = ParentsModel::where('id', '=', $request->login_id)->update($data);
            if ($update) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.parents.update-location.success'),
                    'data'      => $data,
                ],200);
            } else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.parents.update-location.failure'),
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
