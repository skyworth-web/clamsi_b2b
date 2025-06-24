<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ComboProductAttributeValue;

class ComboProductAttribute extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'status',
        'store_id',
    ];
    public function attribute_values()
    {
        return $this->hasMany(ComboProductAttributeValue::class);
    }
}
