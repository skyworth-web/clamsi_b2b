<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Product_attributes extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'product_id',
        'attribute_value_ids'
    ];
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
