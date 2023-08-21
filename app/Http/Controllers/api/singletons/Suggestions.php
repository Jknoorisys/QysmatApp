<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\Categories as ModelsCategories;
use App\Models\Counters;
use App\Models\Matches;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\PremiumFeatures;
use App\Models\RematchRequests;
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

class Suggestions extends Controller
{
    private $db;
    
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id'])) {
            $user = Singleton::find($_POST['login_id']);
            if (empty($user) || $user->status == 'Deleted') {
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
            $category = ModelsCategories::where([['status','=','Active'],['user_id','=',$request->login_id],['user_type', '=', 'singleton']])->first();

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

        if ($request->location && !empty($request->location)) {
            $validator = Validator::make($request->all(), [
                // 'lat'    => 'required',
                // 'long'   => 'required',
                'search_by' => [
                    'required',
                    Rule::in(['radius', 'country']),
                ],
                'radius'   => ['required_if:search_by,radius'],
                'country_code'   => ['required_if:search_by,country'],
            ]);
        }

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $categoryExists = ModelsCategories::where([['user_id','=',$request->login_id],['user_type', '=', 'singleton']])->first();
            $request->height ? str_replace('.', '', $request->height) : '';
            $height = $request->height ? explode('-',$request->height) : '';
            $min_height = $height ? $height[0] : '' ;
            $min_height_converted = convertFeetToInches($min_height);
            $max_height = $height ? $height[1] : '';
            $max_height_converted = ($max_height != 'below' && $max_height != 'above') ? convertFeetToInches($max_height) : $max_height;
            $height_converted = $min_height_converted.'-'.$max_height_converted;

            if (!empty($categoryExists)) {
                $user = Singleton::where('id',$request->login_id)->first();
                $category = ModelsCategories::where([['user_id','=',$request->login_id],['user_type', '=', 'singleton']])->first();
                // $category->gender        = $user->gender == 'Male' ? 'Female' : 'Male';
                $category->age_range     = $request->age_range ? $request->age_range : '';
                // $category->profession    = $request->profession ? $request->profession : '';
                $category->location      = $request->location ? $request->location : '';
                $category->lat           = $request->lat ? $request->lat : '';
                $category->long          = $request->long ? $request->long : '';
                $category->search_by     = $request->search_by ? $request->search_by : '';
                $category->radius        = $request->radius ? $request->radius : '';
                $category->country_code  = $request->country_code ? $request->country_code : 'none';
                $category->height        = $request->height ? $request->height : '';
                $category->height_converted  = $request->height ? $height_converted : '';
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
                $category->user_id       = $request->login_id ? $request->login_id : '';
                $category->user_type     = 'singleton';
                $category->gender        = $request->gender ? $request->gender : '';
                $category->age_range     = $request->age_range ? $request->age_range : '';
                // $category->profession    = $request->profession ? $request->profession : '';
                $category->location      = $request->location ? $request->location : '';
                $category->lat           = $request->lat ? $request->lat : '';
                $category->long          = $request->long ? $request->long : '';
                $category->search_by     = $request->search_by ? $request->search_by : '';
                $category->radius        = $request->radius ? $request->radius : '';
                $category->country_code  = $request->country_code ? $request->country_code : 'none';
                $category->height        = $request->height ? $request->height : '';
                $category->height_converted = $request->height ? $height_converted : '';
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

    // public function suggestions(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'language' => [
    //             'required' ,
    //             Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
    //         ],
    //         'login_id'   => 'required||numeric',
    //     ]);

    //     if($validator->fails()){
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.Validation Failed!'),
    //             'errors'    => $validator->errors()
    //         ],400);
    //     }

    //     try {
    //         $linked = ParentChild::where('singleton_id','=', $request->login_id)->first();
    //         if (empty($linked) || ($linked->status) != 'Linked') {
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.singletons.get-suggestions.not-linked'),
    //             ],400);
    //         }

    //         $categoryLocation = ModelsCategories::where([['user_id','=', $request->login_id],['user_type', '=', 'singleton']])->first();
    //         if (!empty($categoryLocation) && !empty($categoryLocation->location)) {
    //             if ($request->lat && $request->long) {
    //                 ModelsCategories::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->update(['lat' => ($request->lat ? $request->lat : ''), 'long' => ($request->long ? $request->long : '')]);
    //             }
    //         }

    //         $category = ModelsCategories::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->first();

    //         if (!empty($category)) {
    //             $user = Singleton::where('id',$request->login_id)->first();
    //             $gender = $category->gender ? $category->gender : '';
                
    //             $location = $category->location ? $category->location : '';
    //             $latitude = $category->lat ? $category->lat : '';
    //             $longitude = $category->long ? $category->long : '';

    //             $islamic_sect = $category->islamic_sect ? $category->islamic_sect : '';
    //             $age = $category->age_range ? explode('-',$category->age_range) : '';
    //             $min_age = $age ? $age[0] : '' ;
    //             $max_age = $age ? $age[1] : '';

    //             $height = $category->height_converted ? explode('-',$category->height_converted) : '';
    //             $min_height = $height ? $height[0] : '' ;
    //             $max_height = $height ? $height[1] : '';

    //             $this->db = DB::table('singletons');

    //             if ($category->search_by != 'none') {

    //                 if ($category->search_by == 'radius') {
    //                     if ($latitude && $longitude) {
    //                         $this->db->select('*', DB::raw('(6371 * acos(cos(radians(?)) * cos(radians(`lat`)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(`lat`)))) AS distance'))
    //                         ->having('distance', '<', $category->radius)
    //                         ->orderBy('distance')
    //                         ->setBindings([$latitude, $longitude, $latitude]);
    //                     }
    //                 } else {
    //                     if ($latitude && $longitude) {
    //                         $this->db->select('*', DB::raw('(6371 * acos(cos(radians(?)) * cos(radians(`lat`)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(`lat`)))) AS distance'))
    //                         ->orderBy('distance')
    //                         ->setBindings([$latitude, $longitude, $latitude]);
    //                     }
    //                     $this->db->where('nationality_code','=',$category->country_code);
    //                 }
    //             }

    //             if(!empty($min_height) && !empty($max_height)){
    //                 if ($max_height == 'above') {
    //                     $this->db->where('height_converted','>=', $min_height);
    //                 }elseif ($max_height == 'below') {
    //                     $this->db->where('height_converted','<=', $min_height);
    //                 }else{
    //                     $this->db->whereBetween('height_converted', [$min_height, $max_height]);
    //                 }
    //             }

    //             if(!empty($islamic_sect)){
    //                 $this->db->where('islamic_sect','=',$islamic_sect);
    //             }

    //             if(!empty($min_age) && !empty($max_age)){
    //                 if ($max_age == 'above') {
    //                     $this->db->where('age','>=', $min_age);
    //                 }else{
    //                     $this->db->whereBetween('age', [$min_age, $max_age]);
    //                 }
    //             }

    //             $this->db->where('id','!=',$request->login_id);
    //             $this->db->where('status','=','Unblocked');
    //             $this->db->where('is_verified','=','verified');
    //             $this->db->where('gender','=', $gender);
    //             $this->db->where('parent_id', '!=', $linked->parent_id);
    //             $suggestion = $this->db->get();

    //             if(!$suggestion->isEmpty()){
    //                 $users = [];
    //                 $count = 0;
    //                 foreach ($suggestion as $m) {
    //                     $singleton_id = $m->id;
    //                     $swiped_up = SwipedUpUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['swiped_user_id', '=', $singleton_id]])->first();
    //                     $block = BlockList ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['blocked_user_id', '=', $singleton_id], ['blocked_user_type', '=', 'singleton']])->first();
    //                     $report = ReportedUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['reported_user_id', '=', $singleton_id], ['reported_user_type', '=', 'singleton']])->first();
    //                     $unMatch = UnMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['un_matched_id', '=', $singleton_id]])->first();
    //                     $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['matched_id', '=', $singleton_id]])->first();
    //                     // $not_linked = ParentChild ::where([['singleton_id','=', $singleton_id], ['status', '=', 'Unlinked']])->first();
    //                     $not_linked = Singleton ::where([['id','=', $singleton_id], ['parent_id', '=', '']])->orWhere([['id','=', $singleton_id], ['parent_id', '=', '0']])->first();

    //                     $mutual = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $singleton_id],['match_type', '!=', 'liked']])
    //                                         ->orWhere([['user_id', '=', $singleton_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id],['match_type', '!=', 'liked']])
    //                                         ->first();
    //                     if (empty($block) && empty($report) && empty($unMatch) && empty($Match) && empty($not_linked) && empty($mutual) && empty($swiped_up)) {
    //                         $users[] = $m;
    //                         $count = $count + 1;
    //                     }
    //                 }

    //                 $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
    //                 $featureStatus = PremiumFeatures::whereId(1)->first();
    //                 if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
    //                     $user_counter = Counters::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->first();
    //                     if(!empty($user_counter)){
    //                         if($user_counter->date != date('Y-m-d')){
    //                             Counters::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->update([
    //                                 'counter' => ($count <= $user_counter->counter || $count <= 5) ? 0 : $user_counter->counter + 5,
    //                                 'date' => date('Y-m-d'),
    //                                 'updated_at' => date('Y-m-d H:i:s')
    //                             ]);
    //                         }
    //                     }else{
    //                         Counters::insert([
    //                             'user_id' => $request->login_id,
    //                             'user_type' => 'singleton',
    //                             'counter' => 0,
    //                             'date' => date('Y-m-d'),
    //                             'created_at' => date('Y-m-d H:i:s')
    //                         ]);
    //                     }

    //                     $slice = $user_counter ? $user_counter->counter : 0;
    //                     $users = array_slice($users, $slice, 5, false);
    //                 }

    //                 if(!empty($users)){
    //                     return response()->json([
    //                         'status'    => 'success',
    //                         'message'   => __('msg.singletons.get-suggestions.success'),
    //                         'data'      => $users
    //                     ],200);
    //                 }else{
    //                     return response()->json([
    //                         'status'    => 'failed',
    //                         'message'   => __('msg.singletons.get-suggestions.failure'),
    //                     ],400);
    //                 }

    //             }else{
    //                 return response()->json([
    //                     'status'    => 'failed',
    //                     'message'   => __('msg.singletons.get-suggestions.failure'),
    //                 ],400);
    //             }
    //         } else {
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.singletons.get-suggestions.invalid'),
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

            $categoryLocation = ModelsCategories::where([['user_id','=', $request->login_id],['user_type', '=', 'singleton']])->first();
            if (!empty($categoryLocation) && !empty($categoryLocation->location)) {
                if ($request->lat && $request->long) {
                    ModelsCategories::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->update(['lat' => ($request->lat ? $request->lat : ''), 'long' => ($request->long ? $request->long : '')]);
                }
            }

            $category = ModelsCategories::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->first();

            $user = Singleton::where('id',$request->login_id)->first();

            if (!empty($category)) {
                
                $gender = $category->gender ? $category->gender : '';
                
                $location = $category->location ? $category->location : '';
                $latitude = $category->lat ? $category->lat : '';
                $longitude = $category->long ? $category->long : '';

                $islamic_sect = $category->islamic_sect ? $category->islamic_sect : '';
                $age = $category->age_range ? explode('-',$category->age_range) : '';
                $min_age = $age ? $age[0] : '' ;
                $max_age = $age ? $age[1] : '';

                $height = $category->height_converted ? explode('-',$category->height_converted) : '';
                $min_height = $height ? $height[0] : '' ;
                $max_height = $height ? $height[1] : '';

                $this->db = DB::table('singletons');

                if ($category->search_by != 'none') {

                    if ($category->search_by == 'radius') {
                        if ($latitude && $longitude) {
                            $this->db->select('*', DB::raw('(6371 * acos(cos(radians(?)) * cos(radians(`lat`)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(`lat`)))) AS distance'))
                            ->having('distance', '<', $category->radius)
                            ->orderBy('distance')
                            ->setBindings([$latitude, $longitude, $latitude]);
                        }
                    } else {
                        if ($latitude && $longitude) {
                            $this->db->select('*', DB::raw('(6371 * acos(cos(radians(?)) * cos(radians(`lat`)) * cos(radians(`long`) - radians(?)) + sin(radians(?)) * sin(radians(`lat`)))) AS distance'))
                            ->orderBy('distance')
                            ->setBindings([$latitude, $longitude, $latitude]);
                        }
                        $this->db->where('nationality_code','=',$category->country_code);
                    }
                }

                if(!empty($min_height) && !empty($max_height)){
                    if ($max_height == 'above') {
                        $this->db->where('height_converted','>=', $min_height);
                    }elseif ($max_height == 'below') {
                        $this->db->where('height_converted','<=', $min_height);
                    }else{
                        $this->db->whereBetween('height_converted', [$min_height, $max_height]);
                    }
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
                    $users1 = [];
                    $likedMeUsers = [];
                    $count = 0;
                    foreach ($suggestion as $m) {
                        $singleton_id = $m->id;

                        $swiped_up = SwipedUpUsers ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['swiped_user_id', '=', $singleton_id]])->first();

                        $block = BlockList::where(function ($query) use ($request, $singleton_id) {
                                        $query->where([
                                            ['user_id', '=', $request->login_id],
                                            ['user_type', '=', 'singleton'],
                                            ['blocked_user_id', '=', $singleton_id],
                                            ['blocked_user_type', '=', 'singleton'],
                                        ])->orWhere([
                                            ['blocked_user_id', '=', $request->login_id],
                                            ['blocked_user_type', '=', 'singleton'],
                                            ['user_id', '=', $singleton_id],
                                            ['user_type', '=', 'singleton'],
                                        ]);
                                    })->first();

                        $report = ReportedUsers::where(function ($query) use ($request, $singleton_id) {
                                        $query->where([
                                            ['user_id', '=', $request->login_id],
                                            ['user_type', '=', 'singleton'],
                                            ['reported_user_id', '=', $singleton_id],
                                            ['reported_user_type', '=', 'singleton'],
                                        ])->orWhere([
                                            ['reported_user_id', '=', $request->login_id],
                                            ['reported_user_type', '=', 'singleton'],
                                            ['user_id', '=', $singleton_id],
                                            ['user_type', '=', 'singleton'],
                                        ]);
                                    })->first();

                        $unMatch = UnMatches::where(function ($query) use ($request, $singleton_id) {
                                                $query->where([
                                                    ['user_id', '=', $request->login_id],
                                                    ['user_type', '=', 'singleton'],
                                                    ['un_matched_id', '=', $singleton_id],
                                                ])->orWhere([
                                                    ['un_matched_id', '=', $request->login_id],
                                                    ['user_type', '=', 'singleton'],
                                                    ['user_id', '=', $singleton_id],
                                                ]);
                                            })->first();

                        $Match = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton'], ['matched_id', '=', $singleton_id]])->first();

                        // $not_linked = Singleton ::where([['id','=', $singleton_id], ['parent_id', '=', '']])->orWhere([['id','=', $singleton_id], ['parent_id', '=', '0']])->first();
                        $not_linked = Singleton ::where('id','=', $singleton_id)->whereIn('parent_id', ['', '0'])->first();

                        $mutual = Matches ::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $singleton_id],['match_type', '!=', 'liked']])
                                            ->orWhere([['user_id', '=', $singleton_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id],['match_type', '!=', 'liked']])
                                            ->first();
                                        
                        if (empty($block) && empty($report) && empty($unMatch) && empty($Match) && empty($not_linked) && empty($mutual) && empty($swiped_up)) {
                            // $liked_me = Matches::where([['matches.user_id', '=', $singleton_id],['matches.match_id', '=', $request->login_id], ['matches.user_type', '=', 'singleton'],['is_rematched', '=', 'no'],['is_reset', '=', 'no'],['match_type', '=', 'liked']])
                            //             ->join('singletons', 'matches.user_id', '=', 'singletons.id')
                            //             ->first('singletons.*');

                            $liked_me = MyMatches::where([['my_matches.user_id', '=', $singleton_id],['my_matches.matched_id', '=', $request->login_id], ['my_matches.user_type', '=', 'singleton']])
                                        ->join('singletons', 'my_matches.user_id', '=', 'singletons.id')
                                        ->first('singletons.*');

                            if (!empty($liked_me)) {
                                $likedMeUsers[] = $liked_me;
                            } else {
                                $users1[] = $m;
                                $count = $count + 1;
                            }
                        }

                    }

                    $users1 = array_merge($likedMeUsers, $users1);

                    $rematchedProfiles = RematchRequests::where([['rematch_requests.user_type', '=', 'singleton'], ['rematch_requests.match_id', '=', $request->login_id], ['rematch_requests.is_rematched', '=', 'no']])
                                                ->join('singletons', 'rematch_requests.user_id', '=', 'singletons.id')
                                                ->select('singletons.*' , DB::raw("'yes' as is_rematched"))->get();

                    $remaches = json_decode($rematchedProfiles, true);
                    $users2 = array_merge($remaches, $users1);
                    $users3 = collect($users2)->unique('id')->values()->all();

                    if (count($users3) >= 100) {
                        $users = $users3;
                    } else {
                        $blocked = BlockList::leftjoin('singletons', function($join) use ($request) {
                                        $join->on('singletons.id','=','block_lists.blocked_user_id')
                                            ->where([
                                                    ['block_lists.user_id', '=', $request->login_id],
                                                    ['block_lists.user_type', '=', 'singleton'],
                                                    ['block_lists.blocked_user_type', '=', 'singleton'],
                                                ]);
                                        $join->orOn('singletons.id','=','block_lists.user_id')
                                            ->where([
                                                    ['block_lists.blocked_user_id', '=', $request->login_id],
                                                    ['block_lists.blocked_user_type', '=', 'singleton'],
                                                    ['block_lists.user_type', '=', 'singleton'],
                                                ]);
                                    })
                                    ->get('singletons.*');

                        $reported = ReportedUsers::leftjoin('singletons', function($join) use ($request) {
                            $join->on('singletons.id','=','reported_users.reported_user_id')
                                ->where([
                                        ['reported_users.user_id', '=', $request->login_id],
                                        ['reported_users.user_type', '=', 'singleton'],
                                        ['reported_users.reported_user_type', '=', 'singleton'],
                                    ]);
                            $join->orOn('singletons.id','=','reported_users.user_id')
                                ->where([
                                        ['reported_users.reported_user_id', '=', $request->login_id],
                                        ['reported_users.reported_user_type', '=', 'singleton'],
                                        ['reported_users.user_type', '=', 'singleton'],
                                    ]);
                        })
                        ->get('singletons.*');

                        $unMatched = UnMatches::leftjoin('singletons', function($join) use ($request) {
                            $join->on('singletons.id','=','un_matches.un_matched_id')
                                ->where([
                                        ['un_matches.user_id', '=', $request->login_id],
                                        ['un_matches.user_type', '=', 'singleton'],
                                    ]);
                            $join->orOn('singletons.id','=','un_matches.user_id')
                                ->where([
                                        ['un_matches.un_matched_id', '=', $request->login_id],
                                        ['un_matches.user_type', '=', 'singleton'],
                                    ]);
                        })->get('singletons.*');

                        $Matched = MyMatches ::where([['user_id', '=', $request->login_id], ['user_type', '=', 'singleton']])->get();

                        $excludeIds = array_filter(array_merge(
                            $blocked->pluck('id')->toArray(),
                            $reported->pluck('id')->toArray(),
                            $unMatched->pluck('id')->toArray(),
                            $Matched->pluck('matched_id')->toArray(),
                        ));
                        
                        $others_liked_me = Matches::where([['matches.match_id', '=', $request->login_id], ['matches.user_type', '=', 'singleton'],['is_rematched', '=', 'no'],['is_reset', '=', 'no'],['match_type', '=', 'liked']])
                                                    ->join('singletons', 'matches.user_id', '=', 'singletons.id')
                                                    ->whereNotIn('singletons.id', $excludeIds)
                                                    ->orderBy('singletons.id')
                                                    ->get('singletons.*');

                        $randomProfiles = Singleton::inRandomOrder()
                                        ->where([
                                            ['is_verified', '=', 'verified'],
                                            ['status', '=', 'Unblocked'],
                                            ['gender', '=', $gender]
                                        ])
                                        ->whereNotIn('parent_id', ['', '0'])
                                        ->whereNotIn('id', $excludeIds)
                                        ->get();
                   
                        $users4 = array_merge($users3, json_decode($others_liked_me, true), json_decode($randomProfiles, true));
                        $users5 = collect($users4)->unique('id')->values()->all();
                        $users = $users5;
                    }
                    
                    $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
                    $featureStatus = PremiumFeatures::whereId(1)->first();
                    if ((!empty($featureStatus) && $featureStatus->status == 'active') && (!empty($premium) && $premium->active_subscription_id == '1')) {
                        $user_counter = Counters::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->first();
                        if(!empty($user_counter)){
                            if($user_counter->date != date('Y-m-d')){
                                Counters::where([['user_id', '=', $request->login_id],['user_type', '=', 'singleton']])->update([
                                    'counter' => ($count <= $user_counter->counter || $count <= 5) ? 0 : $user_counter->counter + 5,
                                    'date' => date('Y-m-d'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ]);
                            }
                        }else{
                            Counters::insert([
                                'user_id' => $request->login_id,
                                'user_type' => 'singleton',
                                'counter' => 0,
                                'date' => date('Y-m-d'),
                                'created_at' => date('Y-m-d H:i:s')
                            ]);
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
