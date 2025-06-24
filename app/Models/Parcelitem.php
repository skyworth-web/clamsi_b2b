<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Parcelitem extends Model
{
    protected $table = 'parcel_items';
    use HasFactory;
    protected $fillable = [
        'parcel_id',
        'order_item_id',
        'product_variant_id	',
        'unit_price',
        'quantity',
    ];
}