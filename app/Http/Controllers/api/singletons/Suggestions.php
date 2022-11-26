<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\Categories as ModelsCategories;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\UnMatches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\Console\Completion\Suggestion;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Suggestions extends Controller
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

            if (empty($user) || $user->is_verified != 'verified') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.Profile not Verified, Please Try After Some Time...'),
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

        $category = ModelsCategories::where([['status','=','Active'],['singleton_id','=',$request->login_id]])->first();

        if(!empty($category)){
            return response()->json([
                'status'    => 'success',
                'message'   => __('msg.Singleton Category Details Fetched Successfully!'),
                'data'      => $category
            ],200);
        }else{
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Singleton Category Details Not Found!'),
            ],400);
        }
    }

    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
            'gender'     => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        $categoryExists = ModelsCategories::where('singleton_id',$request->login_id)->first();

        if (!empty($categoryExists)) {
            $category = ModelsCategories::where('singleton_id',$request->login_id)->first();
            $category->gender        = $request->gender ? $request->gender : '';
            $category->age_range     = $request->age_range ? $request->age_range : '';
            $category->profession    = $request->profession ? $request->profession : '';
            $category->location      = $request->location ? $request->location : '';
            $category->height        = $request->height ? $request->height : '';
            $category->islamic_sect  = $request->islamic_sect ? $request->islamic_sect : '';

            $category_details = $category->save();

            if($category_details){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Singleton Category Details Updated Successfully!'),
                    'data'    => $category
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                ],400);
            }
        } else {
            $category = new ModelsCategories();
            $category->singleton_id  = $request->login_id ? $request->login_id : '';
            $category->gender        = $request->gender ? $request->gender : '';
            $category->age_range     = $request->age_range ? $request->age_range : '';
            $category->profession    = $request->profession ? $request->profession : '';
            $category->location      = $request->location ? $request->location : '';
            $category->height        = $request->height ? $request->height : '';
            $category->islamic_sect  = $request->islamic_sect ? $request->islamic_sect : '';

            $category_details = $category->save();

            if($category_details){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Singleton Category Details Added Successfully!'),
                    'data'    => $category
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                ],400);
            }
        }
    }

}
