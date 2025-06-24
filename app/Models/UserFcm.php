<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFcm extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user_fcm';
    protected $fillable = [
        'fcm_id',
    ];

}
