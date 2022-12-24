<?php

namespace App\Http\Controllers\api\parents;

use App\Http\Controllers\Controller;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Notifications\AccountLinkedNotification;
use App\Notifications\RequestAccessNotification;
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
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.User Not Found!'),
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
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = ParentsModel::where([['id','=',$request->login_id], ['status','=','unblocked'], ['is_email_verified','=','verified']])->first();
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
            'mobile'            => 'required||unique:singletons||unique:parents',
            'profile_pic'       => 'required||image||mimes:jpeg,png,jpg,svg||max:5000',
            'nationality'       => 'required',
            'ethnic_origin'     => 'required',
            'islamic_sect'      => 'required',
            'location'          => 'required',
            'lat'               => 'required',
            'long'              => 'required',
            'live_photo'        => 'required',
            'id_proof'          => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $user = ParentsModel::find($request->login_id);
            if(!empty($user)){
                $user->name          = $request->name ? $request->name : '';
                $user->email         = $request->email ? $request->email : '';
                $user->mobile        = $request->mobile ? $request->mobile : '';
                $user->nationality   = $request->nationality ? $request->nationality : '';
                $user->ethnic_origin = $request->ethnic_origin ? $request->ethnic_origin : '';
                $user->islamic_sect  = $request->islamic_sect ? $request->islamic_sect : '';
                $user->location      = $request->location ? $request->location : '';
                $user->lat           = $request->lat ? $request->lat : '';
                $user->long          = $request->long ? $request->long : '';

                $file = $request->file('profile_pic');
                if ($file) {
                    $extension = $file->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file->move('assets/uploads/parent-photos/', $filename);
                    $user->profile_pic = 'assets/uploads/parent-photos/'.$filename;
                }

                $file1 = $request->file('live_photo');
                if ($file1) {
                    $extension = $file1->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file1->move('assets/uploads/parent-live-photos/', $filename);
                    $user->live_photo = 'assets/uploads/parent-live-photos/'.$filename;
                }

                $file2 = $request->file('id_proof');
                if ($file2) {
                    $extension = $file2->getClientOriginalExtension();
                    $filename = time().'.'.$extension;
                    $file2->move('assets/uploads/parent-id-proofs/', $filename);
                    $user->id_proof = 'assets/uploads/parent-id-proofs/'.$filename;
                }

            $userDetails =  $user->save();
            if($userDetails){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.parents.update-profile.success'),
                        'data'      => $user
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

                $user = Singleton::where([['id','=',$request->singleton_id],['status','!=','Deleted']])->first();
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
            $accessRequest = ParentChild::where([['parent_id','=',$request->login_id],['singleton_id','=',$request->singleton_id],['access_code','=',$request->access_code]])->first();

            if(!empty($accessRequest)){
                ParentChild::where([['parent_id', '=', $accessRequest->parent_id],['singleton_id', '=', $accessRequest->singleton_id],['access_code','=',$request->access_code]])->update(['status' => 'Linked']);
                Singleton::where('id','=',$accessRequest->singleton_id)->update(['parent_id' => $accessRequest->parent_id]);

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
                        ->where('parent_children.parent_id','=',$request->login_id)
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
}
