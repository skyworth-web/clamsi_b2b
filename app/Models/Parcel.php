<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcel extends Model
{
    use HasFactory;
    protected $fillable = [
        'order_id',
        'delivery_boy_id',
        'name',
        'type',
        'status',
        'active_status',
        'otp',
        'delivery_charge',
    ];
}
