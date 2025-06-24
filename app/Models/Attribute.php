<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'store_id',
        'name',
        'status',
        'category_id',
    ];

    public function attribute_values()
    {
        return $this->hasMany(Attribute_values::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
