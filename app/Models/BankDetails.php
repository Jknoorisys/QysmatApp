<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankDetails extends Model
{
    use HasFactory;
    protected $fillable=[
        'id',
        'user_id',
        'user_type',
        'card_holder_name',
        'bank_name',
        'card_number',
        'month_year',
        'cvv',
        'status',
        'created_at',
        'updated_at',
    ];
}
