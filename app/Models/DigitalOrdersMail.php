<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DigitalOrdersMail extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'order_id',
        'order_item_id',
        'subject',
        'message',
        'file_url',
    ];
}
