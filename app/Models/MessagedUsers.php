<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessagedUsers extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'singleton_id',
        'messaged_user_id',
        'messaged_user_type',
        'messaged_user_singleton_id',
        'conversation',
        'status',
        'created_at',
        'updated_at'
    ];
}
