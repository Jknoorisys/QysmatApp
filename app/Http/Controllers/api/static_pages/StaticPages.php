<?php

namespace App\Http\Controllers\api\static_pages;

use App\Http\Controllers\Controller;
use App\Models\StaticPages as ModelsStaticPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class StaticPages extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            // 'page_name'   => ['required','alpha_dash'],
            'page_name' => [
                'required' ,
                Rule::in(['about_us','privacy_policy','terms_and_conditions','faqs']),
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
            if ($request->page_name == 'faqs') {
                $page = ModelsStaticPages::where([['page_name','=',$request->page_name], ['status','=','Active']])->get();
                if(!$page->isEmpty()){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.static-pages.success'),
                        'data'      => $page
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.static-pages.failure'),
                    ],400);
                }
            } else {
                $page = ModelsStaticPages::where([['page_name','=',$request->page_name], ['status','=','Active']])->first();
                if(!empty($page)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.static-pages.success'),
                        'data'      => $page
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.static-pages.failure'),
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

    public function getFAQs(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'page_name'   => ['required','alpha_dash'],
        ]);

        if($validator->fails()){
            return response()->json([
                'status'    => 'failed',
                'message'   => __('msg.Validation Failed!'),
                'errors'    => $validator->errors()
            ],400);
        }

        try {
            $page = ModelsStaticPages::where([['page_name','=',$request->page_name], ['status','=','Active']])->first();
            if(!empty($page)){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.static-pages.success'),
                    'data'      => $page
                ],200);
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.static-pages.failure'),
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
