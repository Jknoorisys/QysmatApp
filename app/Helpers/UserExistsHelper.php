<?php

use App\Models\ParentChild;
use App\Models\ParentsModel;
use App\Models\Singleton;

    function userExist($login_id, $user_type)
    {
        if ($user_type == 'singleton') {
            $user = Singleton::find($login_id);
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

            $linked = ParentChild::where('singleton_id','=',$login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        } elseif ($user_type == 'parent') {
            $user = ParentsModel::find($login_id);
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

            $linked = ParentChild::where('parent_id','=',$login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.Your Profile is No Linked with Your Children, Please search and Link.'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        }
    }

    function userFound($login_id, $user_type)
    {
        if ($user_type == 'singleton') {
            $user = Singleton::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.User Not Found!'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        } elseif ($user_type == 'parent') {
            $user = ParentsModel::find($login_id);
            if (empty($user) || $user->status != 'Unblocked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.User Not Found!'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        }
    }

    function parentExist($login_id, $user_type, $singleton_id)
    {
        if ($user_type == 'singleton') {
            $user = Singleton::find($login_id);
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

            $linked = ParentChild::where('singleton_id','=',$login_id)->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.Your Profile is No Linked with Your Parent/Guardian, Please ask Him/Her to Send Access Request.'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        } elseif ($user_type == 'parent') {
            $user = ParentsModel::find($login_id);
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

            $linked = ParentChild::where([['parent_id','=',$login_id], ['singleton_id','=',$singleton_id]])->first();
            if (empty($linked) || ($linked->status) != 'Linked') {
                $response = [
                    'status'    => 'failed',
                    'message'   => __('msg.Your Profile is No Linked with Your Children, Please search and Link.'),
                    'status_code' => 403
                ];
                echo json_encode($response);die();
            }
        }
    }

    function userBlocked($blocked_user_id, $blocked_user_type)
    {
        # code...
    }
?>
