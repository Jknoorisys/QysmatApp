<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\ParentChild;
use App\Models\Singleton;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
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
            $user = Singleton::find($_POST['login_id']);
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

        $user = Singleton::where([['id','=',$request->login_id], ['status','=','unblocked'], ['is_email_verified','=','verified']])->first();
        if(!empty($user)){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Profile Details Fetched Successfully!'),
                'data'      => $user
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.User Not Found!'),
            ],400);
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
            'dob'               => 'required',
            'gender'            => 'required',
            'height'            => 'required',
            'profession'        => 'required',
            'nationality'       => 'required',
            'ethnic_origin'     => 'required',
            'islamic_sect'      => 'required',
            'short_intro'       => 'required',
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
        $age = Carbon::parse($request->dob)->age;
        $user = Singleton::find($request->login_id);
        if(!empty($user)){
            $user->name          = $request->name;
            $user->email         = $request->email;
            $user->mobile        = $request->mobile;
            $user->dob           = $request->dob;
            $user->gender        = $request->gender;
            $user->age           = $age;
            $user->height        = $request->height;
            $user->profession    = $request->profession;
            $user->nationality   = $request->nationality;
            $user->ethnic_origin = $request->ethnic_origin;
            $user->islamic_sect  = $request->islamic_sect;
            $user->short_intro   = $request->short_intro;
            $user->location      = $request->location;
            $user->lat           = $request->lat;
            $user->long          = $request->long;

            $file1 = $request->file('live_photo');
            if ($file1) {
                $extension = $file1->getClientOriginalExtension();
                $filename = time().'.'.$extension;
                $file1->move('assets/uploads/singleton-live-photos/', $filename);
                $user->live_photo = 'assets/uploads/singleton-live-photos/'.$filename;
            }

            $file2 = $request->file('id_proof');
            if ($file2) {
                $extension = $file2->getClientOriginalExtension();
                $filename = time().'.'.$extension;
                $file2->move('assets/uploads/singleton-id-proofs/', $filename);
                $user->id_proof = 'assets/uploads/singleton-id-proofs/'.$filename;
            }

           $userDetails =  $user->save();
           if($userDetails){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Profile Details Updated Successfully!'),
                    'data'      => $user
                ],200);
           }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...!'),
                ],400);
           }
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.User Not Found!'),
            ],400);
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
            'photo1'     => 'required_without_all:photo2,photo3,photo4,photo5',
            'photo2'     => 'required_without_all:photo1,photo3,photo4,photo5',
            'photo3'     => 'required_without_all:photo2,photo1,photo4,photo5',
            'photo4'     => 'required_without_all:photo2,photo3,photo1,photo5',
            'photo5'     => 'required_without_all:photo2,photo3,photo4,photo1',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $user = Singleton::find($request->login_id);
        if(!empty($user)){
            $file1 = $request->file('photo1');
            if ($file1) {
                $extension = $file1->getClientOriginalExtension();
                $filename1 = time().'1.'.$extension;
                $file1->move('assets/uploads/singleton-photos/', $filename1);
                $user->photo1 = 'assets/uploads/singleton-photos/'.$filename1;
            }

            $file2 = $request->file('photo2');
            if ($file2) {
                $extension = $file2->getClientOriginalExtension();
                $filename2 = time().'2.'.$extension;
                $file2->move('assets/uploads/singleton-photos/', $filename2);
                $user->photo2 = 'assets/uploads/singleton-photos/'.$filename2;
            }

            $file3 = $request->file('photo3');
            if ($file3) {
                $extension = $file3->getClientOriginalExtension();
                $filename3 = time().'3.'.$extension;
                $file3->move('assets/uploads/singleton-photos/', $filename3);
                $user->photo3 = 'assets/uploads/singleton-photos/'.$filename3;
            }

            $file4 = $request->file('photo4');
            if ($file4) {
                $extension = $file4->getClientOriginalExtension();
                $filename4 = time().'4.'.$extension;
                $file4->move('assets/uploads/singleton-photos/', $filename4);
                $user->photo4 = 'assets/uploads/singleton-photos/'.$filename4;
            }

            $file5 = $request->file('photo5');
            if ($file5) {
                $extension = $file5->getClientOriginalExtension();
                $filename5 = time().'5.'.$extension;
                $file5->move('assets/uploads/singleton-photos/', $filename5);
                $user->photo5 = 'assets/uploads/singleton-photos/'.$filename5;
            }

            $userDetails =  $user->save();
            if($userDetails){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.Profile Details Updated Successfully!'),
                        'data'      => $user
                    ],200);
            }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.Somthing Went Wrong, Please Try Again...!'),
                    ],400);
            }
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.User Not Found!'),
            ],400);
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

        $linked = ParentChild::where('singleton_id','=', $request->login_id)->first();
        if (empty($linked) || ($linked->status) != 'Linked') {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
            ],400);
        }else{
            $parent = ParentChild::where('singleton_id','=', $request->login_id)->join('parents', 'parent_children.parent_id','=','parents.id')->first(['parent_children.access_code', 'parents.*']);
            if(!empty($parent)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Parent Access Request Details Fetched Successfully!'),
                    'data'      => $parent
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...!'),
                ],400);
            }
        }
    }
}
