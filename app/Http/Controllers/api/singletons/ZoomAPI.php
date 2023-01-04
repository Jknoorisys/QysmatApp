<?php

namespace App\Http\Controllers\api\singletons;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Singleton;
use Exception;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");


class ZoomAPI extends Controller
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
            'login_id'   => 'required||numeric',
            'user_type' => [
                'required' ,
                Rule::in(['singleton']),
            ],
            'called_user_id'   => 'required||numeric',
            'called_user_type' => [
                'required' ,
                Rule::in(['singleton']),
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
            $singleton = Singleton::where([['status','=','Unblocked'],['id','=',$request->login_id],['user_type','=', $request->user_type]])->first();
            if(!empty($singleton)){
                $password =  Str::random(10);
                $random_email = ltrim(substr($singleton->id, 2)) . str_replace(" ", "_", strtolower($singleton->name)) . '_' . time() . '@' . env('DOMAIN_NAME');

                $meeting_time = date('d-m-Y H:i:s');
                $start_time = date('Y-m-d H:i:s', strtotime($meeting_time));
                $meeting_start_time = gmdate('Y-m-d\TH:i:s', strtotime($start_time)) . 'Z';

                $UserInfo['url']            =  env('API_URL') . "/users";
                $UserInfo['method'] 		= 'POST';
                $UserInfo['fields']         =   [
                    "action"            =>  'custCreate',
                    "user_info"    		=>  [
                        "email"         =>     $random_email,
                        "first_name"    =>     $singleton->name,
                        "last_name"     =>     '',
                        "password"      =>     $password,
                        "type"     		=>     1,

                    ],
                    "feature"			=> [
                        "zoom_phone"    =>  true
                    ]
                ];

                $User = SendRequest($UserInfo);
                return $User;exit;

                if (!empty($User) && !empty($User->id)) {

                    // get zoom token
                    $token['url']            =  env('API_URL') . "/users/" . $User->id . '/token';
                    $token['method'] 		 = 'GET';
                    $token['fields']         =  [];

                    $z_token = SendRequest($token);

                    // get zoom access token

                    $zak_token['url']            =   env('API_URL') . "/users/" . $User->id . '/token?type=zak';
                    $zak_token['method'] 		 =  'GET';
                    $zak_token['fields']         =  [];

                    $token_zak = SendRequest($zak_token);

                    // create zoom meeting
                    $meeting_data['url'] 			= env('API_URL') . "/users/" . $User->id . "/meetings";
                    $meeting_data['method'] 		= 'POST';
                    $meeting_data['fields']         =   [
                        "agenda"           	 =>  '',
                        "default_password"   =>  false,
                        "duration"   		 =>  2,
                        "password"   		 =>  $password,
                        "pre_schedule"   	 =>  false,
                        "schedule_for"   	 =>  $User->email,

                        "settings"    		 =>  [
                            "email"             =>     $User->email,
                            "first_name"        =>     $User->first_name,
                            "last_name"         =>     '',
                            "password"          =>     $password,
                            "type"     		    =>     1,

                            "host_video"        =>   true,
                            "participant_video" =>   true,
                            "audio"			    =>   "both",
                            "approval_type"     =>   0,
                            "join_before_host"  =>   true,
                            "mute_upon_entry"   =>   true,
                            "watermark"			=>   false,
                            "use_pmi"			=>   false,
                            "enforce_login"		=>   false,
                            "waiting_room"		=>   false,
                            "alternative_hosts"	=>   "",
                        ],
                        "start_time"		   => $meeting_start_time,
                        "timezone"			   => "Europe/London",
                        "topic"			   	   => '',
                        "type"			   	   => 1,
                    ];

                    $meeting = SendRequest($meeting_data);
                    $data   = array();
                    if (isset($meeting) && !empty($meeting)) {
                        $data['meeting_id'] 	  =  $meeting->id;
                        $data['join_url']		  =  $meeting->join_url;
                        $data['meeting_topic'] 	  =  $meeting->topic;
                        $data['meeting_password'] =  $meeting->password;
                        $data['start_url'] 		  =  $meeting->start_url;
                        $data['host_id']  		  =  $User->id;
                        $data['host_email']       =  $User->email;
                        $data['token']       	  =  $z_token->token;
                        $data['zak_token']        =  $token_zak->token;
                    }
                    return $data;
                } else {
                    return response()->json([
                        'status'    => 'failed',
                        'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                    ],400);
                }
            }else{
                return response()->json([
                    'status'    => 'failed',
                    'message'   => __('msg.Somthing Went Wrong, Please Try Again...'),
                ],400);
            }
        } catch (Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'messgae' => __('msg.Somthing Went Wrong, Please Try Again...'),
            ], 500);
        }
    }
}
