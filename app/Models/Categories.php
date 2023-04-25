<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categories extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'user_id',
        'user_type',
        'singleton_id',
        'gender',
        'age_range',
        'profession',
        'location',
        'height',
        'islamic_sect',
        'status',
        'created_at',
        'updated_at'
    ];
}
