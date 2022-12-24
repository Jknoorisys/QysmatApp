<?php

namespace App\Http\Controllers\api\delete_account;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\DeletedUsers as ModelsDeletedUsers;
use App\Models\ParentsModel;
use App\Models\Singleton;
use App\Notifications\AdminNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class DeleteUser extends Controller
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
            'login_id'      => 'required||numeric',
            'reason_type'   => 'required',
            'reason'        => 'required',
            'user_type'     => [
                                    'required' ,
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
                $userExists = Singleton::find($request->login_id);
            }else{
                $userExists = ParentsModel::find($request->login_id);
            }

            $user = new ModelsDeletedUsers();
            $user->user_id           = $request->login_id;
            $user->user_type         = $request->user_type;
            $user->user_name         = $userExists->name;
            $user->reason_type       = $request->reason_type;
            $user->reason            = $request->reason;
            $user_details = $user->save();

            if($user_details){
                if($request->user_type == 'singleton'){
                    $delete =  Singleton :: whereId($request->login_id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
                    // $delete =  Singleton :: whereId($request->login_id)->delete();
                }else{
                    $delete =  ParentsModel :: whereId($request->login_id)->update(['status' => 'Deleted', 'updated_at' => date('Y-m-d H:i:s')]);
                    // $delete =  ParentsModel :: whereId($request->login_id)->delete();
                }

                if ($delete) {

                    $admin = Admin::find(1);

                    if($request->user_type == 'singleton'){
                        $user = Singleton::find($request->login_id);
                        // if (File::exists($user->photo1)) {
                        //     File::delete($user->photo1);
                        // }

                        // if (File::exists($user->photo2)) {
                        //     File::delete($user->photo2);
                        // }

                        // if (File::exists($user->photo3)) {
                        //     File::delete($user->photo3);
                        // }

                        // if (File::exists($user->photo4)) {
                        //     File::delete($user->photo4);
                        // }

                        // if (File::exists($user->photo5)) {
                        //     File::delete($user->photo5);
                        // }

                        // if (File::exists($user->live_photo)) {
                        //     File::delete($user->live_photo);
                        // }

                        // if (File::exists($user->id_proof)) {
                        //     File::delete($user->id_proof);
                        // }
                    }else{
                        $user = ParentsModel::find($request->login_id);
                        // if (File::exists($user->profile_pic)) {
                        //     File::delete($user->profile_pic);
                        // }

                        // if (File::exists($user->live_photo)) {
                        //     File::delete($user->live_photo);
                        // }

                        // if (File::exists($user->id_proof)) {
                        //     File::delete($user->id_proof);
                        // }
                    }

                    $details = [
                        'title' => __('msg.Account Deleted'),
                        'msg'   => __('msg.has Deleted His/Her Account.'),
                    ];

                    $admin->notify(new AdminNotification($user, 'admin', 0, $details));

                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.delete-account.success'),
                        'data'      => $user
                    ],200);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.delete-account.failure'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.delete-account.invalid'),
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
