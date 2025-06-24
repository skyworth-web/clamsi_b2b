<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Store extends Model implements HasMedia
{

    use InteractsWithMedia;

    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'image',
        'description',
        'banner_image',
        'is_single_seller_order_system',
        'is_default_store',
        'status',
        'primary_color',
        'secondary_color',
        'hover_color',
        'active_color',
        'on_boarding_image',
        'on_boarding_video',
        'banner_image_for_most_selling_product',
        'stack_image',
        'login_image',
        'half_store_logo',
        'store_settings',
        'disk',
        'delivery_charge_type',
        'delivery_charge_amount',
        'minimum_free_delivery_amount',
        'product_deliverability_type',
    ];


    public function sellers()
    {
        return $this->belongsToMany(Seller::class);
    }

    public function registerMediaCollections(): void
    {
        $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
        $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';
        if ($mediaStorageType === 's3') {
            $this->addMediaCollection('store_images')->useDisk('s3');
        } else {
            $this->addMediaCollection('store_images')->useDisk('public');
        }
    }
}
