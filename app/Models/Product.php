<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'store_id',
        'name',
        'short_description',
        'slug',
        'type',
        'tax',
        'category_id',
        'seller_id',
        'made_in',
        'brand',
        'indicator',
        'image',
        'total_allowed_quantity',
        'minimum_order_quantity',
        'quantity_step_size',
        'warranty_period',
        'guarantee_period',
        'other_images',
        'video_type',
        'video',
        'tags',
        'status',
        'description',
        'extra_description',
        'deliverable_type',
        'deliverable_zones',
        'hsn_code',
        'pickup_location',
        'stock_type',
        'sku',
        'stock',
        'availability',
        'is_returnable',
        'is_cancelable',
        'is_attachment_required',
        'cancelable_till',
        'download_allowed',
        'download_type',
        'download_link',
        'cod_allowed',
        'is_prices_inclusive_tax',
        'product_identity',
        'row_order',
        'rating',
        'no_of_ratings',
        'minimum_free_delivery_order_qty',
        'delivery_charges',
    ];

    public function category()
    {
        return $this->hasMany(Product_variants::class);
    }
    public function sellerData()
    {
        return $this->belongsTo(Seller::class, 'seller_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function productVariants()
    {
        return $this->hasMany(Product_variants::class, 'product_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax');
    }

    public function productAttributes()
    {
        return $this->hasMany(Product_attributes::class, 'product_id');
    }
    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }
}
