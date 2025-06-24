<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SellerCommission extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'seller_id',
        'store_id',
        'category_id',
        'commission',
    ];
}
