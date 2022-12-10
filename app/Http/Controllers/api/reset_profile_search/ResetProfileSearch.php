<?php

namespace App\Http\Controllers\api\reset_profile_search;

use App\Http\Controllers\Controller;
use App\Models\BlockList;
use App\Models\MyMatches;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\RecievedMatches;
use App\Models\ReferredMatches;
use App\Models\Singleton;
use App\Models\UnMatches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class ResetProfileSearch extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userExist($_POST['login_id'], $_POST['user_type']);
        }
    }

    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'language' => [
                'required' ,
                Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
            ],
            'login_id'  => 'required||numeric',
            'user_type' => [
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
            $match = MyMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $unmatch = UnMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $refer = ReferredMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();
            $received = RecievedMatches::where([['user_id','=',$request->login_id],['user_type','=',$request->user_type]])->delete();

            if($match || $unmatch || $refer || $received){
                return response()->json([
                    'status'    => 'success',
                    'message'   => __('msg.reset-profile.success'),
                ],200);
            }else {
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.reset-profile.failure'),
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
