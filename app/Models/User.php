<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasPermissions;
use Spatie\Permission\Traits\HasRoles;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;



class User extends Authenticatable implements HasMedia
{
    use InteractsWithMedia;

    use HasApiTokens, HasFactory, Notifiable, HasPermissions, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    
    protected $fillable = [
        'username',
        'role_id',
        'active',
        'password',
        'address',
        'mobile',
        'email',
        'latitude',
        'longitude',
        'image',
        'fcm_id',
        'front_licence_image',
        'back_licence_image',
        'status',
        'balance',
        'bonus_type',
        'bonus',
        'serviceable_zones',
        'disk',
        'city',
        'pincode',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function seller_data()
    {
        return $this->hasOne(Seller::class, 'user_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'seller_id');
    }

    public function registerMediaCollections(): void
    {
        $media_storage_settings = fetchDetails('storage_types', ['is_default' => 1], '*');
        $mediaStorageType = isset($media_storage_settings) && !empty($media_storage_settings) ? $media_storage_settings[0]->name : 'public';
        if ($mediaStorageType === 's3') {
            $this->addMediaCollection('user_image')->useDisk('s3');
        } else {
            $this->addMediaCollection('user_image')->useDisk('public');
        }
    }
}
