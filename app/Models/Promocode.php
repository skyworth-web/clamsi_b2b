<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class PromoCode extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    // protected $table = 'promo_codes';
    protected $fillable = [
        'title',
        'store_id',
        'promo_code',
        'message',
        'start_date',
        'end_date',
        'no_of_users',
        'minimum_order_amount',
        'discount',
        'discount_type',
        'max_discount_amount',
        'repeat_usage',
        'no_of_repeat_usage',
        'status',
        'is_cashback',
        'list_promocode',
        'image',
    ];
}
