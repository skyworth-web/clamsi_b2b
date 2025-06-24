<?php

namespace App\Models;

use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PickupLocation extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'seller_id',
        'pickup_location',
        'name',
        'email',
        'phone',
        'city',
        'country',
        'state',
        'pincode',
        'address',
        'address2',
        'longitude',
        'latitude',
        'status',
    ];
}
