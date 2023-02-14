<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscriptions extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'subscription_type',
        'price',
        'currency',
        'stripe_plan_id',
        'status',
        'created_at',
        'updated_at'
    ];

    protected $hidden = [
        'stripe_plan_id',
        'created_at',
        'updated_at'
    ];
}
