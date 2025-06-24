<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    public function orderItems()
    {
        return $this->hasMany(OrderItems::class);
    }
    public function orderCharges()
    {
        return $this->hasMany(OrderCharges::class);
    }
    public function orderBankTransfers()
    {
        return $this->hasMany(OrderBankTransfers::class);
    }
}
