<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'booking_id',
        'user_id',
        'user_type',
        'user_name',
        'other_user_id',
        'paid_by',
        'paid_amount',
        'currency_code',
        'payment_type',
        'transaction_id',
        'subscription_type',
        'transaction_datetime',
        'payment_status',
        'created_at',
        'updated_at'
    ];
}
