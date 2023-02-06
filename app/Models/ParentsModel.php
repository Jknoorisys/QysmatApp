<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ParentsModel extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'parents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'email_otp',
        'mobile_otp',
        'device_type',
        'fcm_token'  ,
        'device_token',
        'is_social',
        'social_type',
        'social_id',
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
        'stripe_id',
        'stripe_plan_id',
        'social_id',
        'status',
        'created_at',
        'updated_at',
    ];
}
