<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute_values extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'value',
        'attribute_id',
        'swatche_type',
        'swatche_value',
        'status'
    ];

    public function attribute()
    {
        return $this->belongsTo(Attribute::class);
    }
}
