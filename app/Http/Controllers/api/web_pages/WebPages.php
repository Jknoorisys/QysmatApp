<?php

namespace App\Http\Controllers\api\web_pages;

use App\Http\Controllers\Controller;
use App\Models\ContactDetails;
use App\Models\Faqs;
use App\Models\StaticPages as ModelsStaticPages;
use App\Models\WebPages as ModelsWebPages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class WebPages extends Controller
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

            'page_name' => [
                'required' ,
                Rule::in(['about_us','privacy_policy','terms_and_conditions','cookies','faqs','download_links','app_details']),
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
                $page = Faqs::where('status','=','Active')->get();
                if(!$page->isEmpty()){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.web-pages.success'),
                        'data'      => $page
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.web-pages.failure'),
                    ],400);
                }
            } elseif ($request->page_name == 'download_links' || $request->page_name == 'app_details') {
                $page = ModelsWebPages::where([['page_name','=',$request->page_name], ['status','=','Active']])->get();
                if(!$page->isEmpty()){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.web-pages.success'),
                        'data'      => $page
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.web-pages.failure'),
                    ],400);
                }
            } else {
                $page = ModelsWebPages::where([['page_name','=',$request->page_name], ['status','=','Active']])->first();
                if(!empty($page)){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.web-pages.success'),
                        'data'      => $page
                    ],200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.web-pages.failure'),
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
}
