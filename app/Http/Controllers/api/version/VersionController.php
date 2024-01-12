<?php

namespace App\Http\Controllers\api\version;

use App\Http\Controllers\Controller;
use App\Models\AppVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VersionController extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);
    }

    function index(Request $request) {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'version'   => 'required',
            'platform'  => [
                'required' ,
                Rule::in(['android','ios']),
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
            $version = AppVersion::where('platform', $request->platform)->first();
            if(!empty($version)){
                if($request->version < $version->version){
                    return response()->json([
                        'status'    => 'success',
                        'message'   => __('msg.version.update'),
                        'data'      => $version
                    ], 200);
                }else{
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.version.latest'),
                    ], 400);
                }
            } else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.version.failure'),
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
