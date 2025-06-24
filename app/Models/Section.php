<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Section extends Model
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'sections';

    protected $fillable = [
        'store_id',
        'title',
        'short_description',
        'style',
        'product_ids',
        'row_order',
        'categories',
        'product_type',
        'banner_image',
        'background_color',
        'header_style',
    ];
}
