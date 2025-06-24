<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComboProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'short_description',
        'description',
        'seller_id',
        'product_type',
        'product_ids',
        'tax',
        'tags',
        'pro_input_tax',
        'is_prices_inclusive_tax',
        'price',
        'special_price',
        'deliverable_type',
        'deliverable_zones',
        'pickup_location',
        'cod_allowed',
        'is_returnable',
        'is_cancelable',
        'is_attachment_required',
        'cancelable_till',
        'image',
        'other_images',
        'attribute',
        'attribute_value_ids',
        'simple_stock_management_status',
        'sku',
        'stock',
        'availability',
        'status',
        'store_id',
        'slug',
        'selected_products',
        'breadth',
        'length',
        'height',
        'weight',
        'has_similar_product',
        'similar_product_ids',
        'total_allowed_quantity',
        'minimum_order_quantity',
        'quantity_step_size',
    ];
}
