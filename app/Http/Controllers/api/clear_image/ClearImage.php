<?php

namespace App\Http\Controllers\api\clear_image;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Matches;
use App\Models\Singleton;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;


class ClearImage extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userExist($_POST['login_id'], $_POST['user_type']);
        }
    }

    public function clearImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'  => 'required||numeric',
            'user_type' => [
                'required' ,
                Rule::in(['parent','singleton']),
            ],
            'singleton_id' => 'required_if:user_type,parent|numeric',
            'match_id'   => 'required||numeric',
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $userExists = Singleton::find($request->match_id);
            if(empty($userExists) || $userExists->status == 'Deleted' || $userExists->status == 'Blocked'){
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.clear-image.invalid'),
                ],400);
            }

            if ($request->user_type == 'singleton') {
                $matched = Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->match_id], ['match_type', '=', 'matched']])
                                        ->orWhere([['user_id', '=', $request->match_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['match_type', '=', 'matched']])
                                        ->first();
            } else {
                $matched = Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->match_id], ['match_type', '=', 'matched'],['singleton_id', '=', $request->singleton_id]])
                ->orWhere([['user_id', '=', $userExists->parent_id], ['user_type', '=', 'parent'],['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->match_id], ['match_type', '=', 'matched']])
                ->first();    
            }

            if (empty($matched)) {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.clear-image.not-found'),
                ],400);
            }

            if ($matched->blur_image == 'no') {
                if ($request->user_type == 'singleton') {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.clear-image.already-clear-singleton'),
                    ], 400);
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.clear-image.already-clear-parent'),
                    ], 400);
                }
            }

            if ($request->user_type == 'singleton') {
                $clearImage = Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->match_id], ['match_type', '=', 'matched']])
                                        ->orWhere([['user_id', '=', $request->match_id], ['user_type', '=', 'singleton'], ['match_id', '=', $request->login_id], ['match_type', '=', 'matched']])
                                        ->update(['blur_image' => 'no']);
            } else {
                $clearImage = Matches::where([['user_id', '=', $request->login_id], ['user_type', '=', $request->user_type], ['match_id', '=', $request->match_id], ['match_type', '=', 'matched'],['singleton_id', '=', $request->singleton_id]])
                ->orWhere([['user_id', '=', $userExists->parent_id], ['user_type', '=', 'parent'],['match_id', '=', $request->singleton_id], ['singleton_id', '=', $request->match_id], ['match_type', '=', 'matched']])
                ->update(['blur_image' => 'no']);
            }

            if ($clearImage) {
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.clear-image.success'),
                ],200);
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.clear-image.failed'),
                ],500);
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
