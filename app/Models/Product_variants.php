<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Product;

class Product_variants extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'product_id',
        'price',
        'special_price',
        'weight',
        'height',
        'breadth',
        'length',
        'sku',
        'stock',
        'availability',
        'images',
        'attribute_value_ids',
        'status',
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }

    public function attributes()
    {
        return $this->belongsToMany(Attribute::class, 'variant_attribute_values', 'variant_id', 'attribute_id');
    }

    public function getStockTypeAttribute()
    {
        return $this->product ? $this->product->stock_type : null;
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }
}