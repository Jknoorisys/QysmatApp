<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppVersion extends Model
{
    use HasFactory;
    protected $fillable = [
        'version',
        'platform',
        'url',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
