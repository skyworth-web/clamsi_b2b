<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Zipcode extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'zipcode',
        'city_id',
        'minimum_free_delivery_order_amount',
        'delivery_charges',
    ];

    public function cities()
    {
        return $this->hasMany(cities::class);
    }
}
