<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OrderBankTransfers extends Model
{
   
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'order_id',
        'attachments',
        'status',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
