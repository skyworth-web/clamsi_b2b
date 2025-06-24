<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Transaction extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'transaction_type',
        'user_id',
        'order_id',
        'order_item_id',
        'type',
        'txn_id',
        'payu_txn_id',
        'amount',
        'status',
        'currency_code',
        'payer_email',
        'message',
        'transaction_date',
        'is_refund',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
