<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Notification extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'send_to',
        'type',
        'title',
        'message',
        'type_id',
        'link',
        'users_id',
        'image',
    ];
}
