<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\Categories as ModelsCategories;
use App\Models\Counters;
use App\Models\Matches;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ReportedUsers;
use App\Models\Singleton;
use App\Models\SwipedUpUsers;
use App\Models\UnMatches;
use Carbon\Carbon;
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
    private $db;
    
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id'])) {
            $user = Singleton::find($_POST['login_id']);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-found'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }

            if (empty($user) || $user->is_verified != 'verified') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.helper.not-verified'),
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
            $category = ModelsCategories::where([['status','=','Active'],['singleton_id','=',$request->login_id]])->first();

            if(!empty($category)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.singletons.get-category.success'),
                    'data'      => $category
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.get-category.failure'),
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

    public function addCategory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'   => 'required||numeric',
            // 'gender'     => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $categoryExists = ModelsCategories::where('singleton_id',$request->login_id)->first();

            if (!empty($categoryExists)) {
                $user = Singleton::where('id',$request->login_id)->first();
                $category = ModelsCategories::where('singleton_id',$request->login_id)->first();
                $category->gender        = $user->gender == 'Male' ? 'Female' : 'Male';
                $category->age_range     = $request->age_range ? $request->age_range : '';
                $category->profession    = $request->profession ? $request->profession : '';
                $category->location      = $request->location ? $request->location : '';
                $category->height        = $request->height ? $request->height : '';
                $category->islamic_sect  = $request->islamic_sect ? $request->islamic_sect : '';

                $category_details = $category->save();

                if($category_details){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.singletons.update-category.success'),
                        'data'    => $category
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.update-category.failure'),
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
                        'message'   => __('msg.singletons.add-category.success'),
                        'data'    => $category
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.add-category.failure'),
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

        try {
            $linked = ParentChild::where('singleton_id','=', $request->login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.get-suggestions.not-linked'),
                ],400);
            }

            $category = ModelsCategories::where('singleton_id',$request->login_id)->first();

            if (!empty($category)) {
                // $gender = $category->gender ? $category->gender : '';
                $user = Singleton::where('id',$request->login_id)->first();
                $gender = $category->gender ? $category->gender : '';
                $profession = $category->profession ? $category->profession : '';
                $location = $category->location ? $category->location : '';
                // $height = $category->height ? $category->height : '';
                $islamic_sect = $category->islamic_sect ? $category->islamic_sect : '';
                $age = $category->age_range ? explode('-',$category->age_range) : '';
                $min_age = $age ? $age[0] : '' ;
                $max_age = $age ? $age[1] : '';

                $height = $category->height ? explode('-',$category->height) : '';
                $min_height = $height ? $height[0] : '' ;
                $max_height = $height ? $height[1] : '';

                $this->db = DB::table('singletons');

                if(!empty($profession)){
                    $this->db->where('profession','LIKE',"%$profession%");
                }

                if(!empty($location)){
                    $this->db->where('location','LIKE',"%$location%");
                }

                if(!empty($min_height) && !empty($max_height)){
                    $this->db->whereBetween('height', [$min_height, $max_height]);
                }

                if(!empty($islamic_sect)){
                    $this->db->where('islamic_sect','=',$islamic_sect);
                }

                if(!empty($min_age) && !empty($max_age)){
                    if ($max_age == 'above') {
                        $this->db->where('age','>=', $min_age);
                    }else{
                        $this->db->whereBetween('age', [$min_age, $max_age]);
                    }
                }

                $this->db->where('id','!=',$request->login_id);
                $this->db->where('status','=','Unblocked');
                $this->db->where('is_verified','=','verified');
                $this->db->where('gender','=', $gender);
                $this->db->where('parent_id', '!=', $linked->parent_id);
                $suggestion = $this->db->get();

                if(!$suggestion->isEmpty()){
                    $users = [];
                    $count = 0;
                    foreach ($suggestion as $m) {
                        $singleton_id = $m->id;
                        $swiped_up = SwipedUpUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['swiped_user_id', '=', $singleton_id]])->first();
                        $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
                        $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
                        $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['un_matched_id', '=', $singleton_id]])->first();
                        $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['matched_id', '=', $singleton_id]])->first();
                        // $not_linked = ParentChild ::where([['singleton_id','=', $singleton_id], ['status', '=', 'Unlinked']])->first();
                        $not_linked = Singleton ::where([['id','=', $singleton_id], ['parent_id', '=', '']])->orWhere([['id','=', $singleton_id], ['parent_id', '=', '0']])->first();

                        $mutual = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $singleton_id],['match_type', '!=', 'liked']])
                                            ->orWhere([['user_id', '=', $singleton_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id],['match_type', '!=', 'liked']])
                                            ->first();
                        if (empty($block) && empty($report) && empty($unMatch) && empty($Match) && empty($not_linked) && empty($mutual) && empty($swiped_up)) {
                            $users[] = $m;
                            $count = $count + 1;
                        }
                    }

                    $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                    if (!empty($premium) && $premium->active_subscription_id == '1') {
                        $user_counter = Counters::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->first();
                        if(!empty($user_counter)){
                            if($user_counter->date != date('Y-m-d')){
                                Counters::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->update(
                                    [
                                        'counter' => ($count <= $user_counter->counter || $count <= 5) ? 0 : $user_counter->counter + 5,
                                        'date' => date('Y-m-d'),
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]
                                );
                            }
                        }else{
                            Counters::insert(
                                [
                                    'user_id' => $request->login_id,
                                    'user_type' => 'singleton',
                                    'counter' => 0,
                                    'date' => date('Y-m-d'),
                                    'created_at' => date('Y-m-d H:i:s')
                                ]
                            );
                        }

                        $slice = $user_counter ? $user_counter->counter : 0;
                        $users = array_slice($users, $slice, 5, false);
                    }

                    if(!empty($users)){
                        return response()->json([
                            'status'    => 'success',
                            'message'   => __('msg.singletons.get-suggestions.success'),
                            'data'      => $users
                        ],200);
                    }else{
                        return response()->json([
                            'status'    => 'failed',
                            'message'   => __('msg.singletons.get-suggestions.failure'),
                        ],400);
                    }

                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.singletons.get-suggestions.failure'),
                    ],400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.singletons.get-suggestions.invalid'),
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
