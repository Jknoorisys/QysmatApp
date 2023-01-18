<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallHistory extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'caller_id',
        'caller_type',
        'receiver_id',
        'receiver_type',
        'call_type',
        'status',
        'created_at',
        'updated_at'
    ];
}
