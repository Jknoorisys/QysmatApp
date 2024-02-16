<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class Singleton extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'lname',
        'email',
        'mobile',
        'password',
        'user_type',
        'email_otp',
        'is_email_verified',
        'photo1',
        'photo2',
        'photo3',
        'photo4',
        'photo5',
        'dob',
        'age',
        'gender',
        'marital_status',
        'height',
        'height_converted',
        'profession',
        'nationality',
        'country_code',
        'nationality_code',
        'ethnic_origin',
        'islamic_sect',
        'short_intro',
        'location',
        'lat',
        'long',
        'live_photo',
        'id_proof',
        'active_subscription_id',
        'stripe_plan_id',
        'customer_id',
        'subscription_item_id',
        'mobile_otp',
        'device_id',
        'device_type',
        'fcm_token'  ,
        'device_token',
        'is_social',
        'social_type',
        'social_id',
        'is_verified',
        'is_blurred',
        'chat_status',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_otp',
        'stripe_plan_id',
        'subscription_item_id',
        'customer_id',
        'social_id',
        'status',
        'created_at',
        'updated_at',
    ];
}
