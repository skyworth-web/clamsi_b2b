<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Address extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'user_id ',
        'name',
        'type',
        'mobile',
        'alternate_mobile',
        'address',
        'landmark',
        'area_id',
        'city_id',
        'city',
        'area',
        'pincode',
        'system_pincode',
        'country_code',
        'state',
        'country',
        'latitude',
        'longitude',
        'is_default',
    ];
}
