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

    public function suggestions(Request $request)
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
        }

        $category = ModelsCategories::where('singleton_id',$request->login_id)->first();

        if (!empty($category)) {
            $gender = $category->gender ? $category->gender : '';
            $profession = $category->profession ? $category->profession : '';
            $location = $category->location ? $category->location : '';
            $height = $category->height ? $category->height : '';
            $islamic_sect = $category->islamic_sect ? $category->islamic_sect : '';
            $age = $category->age_range ? explode('-',$category->age_range) : '';
            $min_age = $age ? $age[0] : '' ;
            $max_age = $age ? $age[1] : '';

            $this->db = DB::table('singletons');

            if(!empty($profession)){
                $this->db->where('profession','=',$profession);
            }

            if(!empty($location)){
                $this->db->where('location','=',$location);
            }

            if(!empty($height)){
                $this->db->where('height','=',$height);
            }

            if(!empty($islamic_sect)){
                $this->db->where('islamic_sect','=',$islamic_sect);
            }

            if(!empty($min_age) && !empty($max_age)){
                $this->db->where('age','>=',$min_age);
                $this->db->where('age','<=',$max_age);
            }

            $this->db->where('id','!=',$request->login_id);
            $this->db->where('status','=','Unblocked');
            $this->db->where('is_verified','=','verified');
            $this->db->where('gender','=',$gender);
            $suggestion = $this->db->get();
            if(!$suggestion->isEmpty()){
                $users = [];
            foreach ($suggestion as $m) {
                $singleton_id = $m->id;
                $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['un_matched_id', '=', $singleton_id]])->first();
                $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['matched_id', '=', $singleton_id]])->first();

                if (empty($block) && empty($report) && empty($unMatch) && empty($Match)) {
                    $users[] = $m;
                }
            }

            if(!empty($users)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.Suggestions Based on Singleton Categories Fetched Successfully!'),
                    'data'      => $users
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.No Suggestions Found!'),
                ],400);
            }
                // return response()->json([
                //     'status'    => 'success',
                //     'message'   => __('msg.Suggestions Based on Singleton Categories Fetched Successfully!'),
                //     'data'    => $suggestion
                // ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.No Suggestions Found!'),
                ],400);
            }
        } else {
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
            ],400);
        }

    }
}
