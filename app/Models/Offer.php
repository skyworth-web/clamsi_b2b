<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Offer extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'type',
        'store_id',
        'title',
        'type_id',
        'link',
        'image',
        'banner_image',
        'min_discount',
        'max_discount',
    ];
}
