<?php

namespace App\Http\Controllers\api\agora;

use App\Http\Controllers\Controller;
use App\Models\BankDetails as ModelsBankDetails;
use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
// use Willywes\AgoraSDK\RtcTokenBuilder;
use Illuminate\Support\Str;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Headers: Content-Type, Access-Control");

class Call extends Controller
{
    public function  __construct()
    {
        $lang = (isset($_POST['language']) && !empty($_POST['language'])) ? $_POST['language'] : 'en';
        App::setlocale($lang);

        if (isset($_POST['login_id']) && !empty($_POST['login_id']) && isset($_POST['user_type']) && !empty($_POST['user_type'])) {
            userExist($_POST['login_id'], $_POST['user_type']);
        }
    } 

    // public function index(Request $request){
    //     $validator = Validator::make($request->all(), [
    //         'language' => [
    //             'required' ,
    //             Rule::in(['en','hi','ur','bn','ar','in','ms','tr','fa','fr','de','es']),
    //         ],
    //         'login_id'   => 'required||numeric',
    //         'user_type' => [
    //             'required' ,
    //             Rule::in(['singleton','parent']),
    //         ],
    //     ]);

    //     if($validator->fails()){
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.Validation Failed!'),
    //             'errors'    => $validator->errors()
    //         ],400);
    //     }

    //     try {

    //         if ($request->user_type == 'singleton') {
    //             $premium = Singleton::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
    //         } else {
    //             $premium = ParentsModel::where([['id', '=', $request->login_id], ['status', '=', 'Unblocked']])->first();
    //         }

    //         if ($premium->active_subscription_id == '1') {
    //             return response()->json([
    //                 'status'    => 'failed',
    //                 'message'   => __('msg.reset-profile.premium'),
    //             ],400);
    //         }

    //         // $user_id = $request->login_id;
    //         // $user_type = $request->user_type;

    //         // $appID = env('APP_ID');
    //         // $appCertificate = env('APP_CERTIFICATE');
           
    //         // $channelName = "7d72365eb983485397e3e3f9d460bdda";
    //         // $uid = 2882341273;
    //         // $uidStr = "2882341273";
    //         // $role = RtcTokenBuilder::RoleAttendee;
    //         // $expireTimeInSeconds = 3600;
    //         // $currentTimestamp = (new \DateTime("now", new \DateTimeZone('UTC')))->getTimestamp();
    //         // $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds;
    //         // $privilegeExpiredTs = $currentTimestamp + $expireTimeInSeconds; 
        
    //         // return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $channelName, $uid, $role, $privilegeExpiredTs); 
    //     } catch (\Throwable $e) {
    //         return response()->json([
    //             'status'    => 'failed',
    //             'message'   => __('msg.error'),
    //             'error'     => $e->getMessage()
    //         ],500);
    //     }
    // }

    public function index()
    {
        // $this->cors();

        $cname  =   (string) random_int(100000000, 9999999999999999);
        $token  =   $this->generateTokenForChannel($cname);

        echo json_encode(['status' => 'success', 'app_id' => 'ccd7d92514b946bc991026b785d48973', 'cname' => $cname, 'token_id' => $token]);
        exit;
    }    

    /**
     * @method generateTokenForChannelByUID()     
     * @date: 2021-11-28 15:15
     * 
     */

    private function generateTokenForChannel($cname = null, $uid = 0)
    {
    //    include('RtcTokenBuilder');
    //    require_once "RtcTokenBuilder.php";

        $appID                  =   'ccd7d92514b946bc991026b785d48973';
        $appCertificate         =   '4e5a208bb0d4480299faa2889e0f4ca5';

        $role                   =   RtcTokenBuilder::RolePublisher;
        $expireTimeInSeconds    =   3600;
        $currentTimestamp       =   (new DateTime("now", new DateTimeZone('UTC')))->getTimestamp();
        $privilegeExpiredTs     =   $currentTimestamp + $expireTimeInSeconds;

        return RtcTokenBuilder::buildTokenWithUid($appID, $appCertificate, $cname, $uid, $role, $privilegeExpiredTs);
    }    
}
