<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Area extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'city_id',
        'zipcode_id',
        'minimum_free_delivery_order_amount',
        'delivery_charges',
    ];

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function zipcode()
    {
        return $this->belongsTo(Zipcode::class, 'zipcode_id');
    }
}
