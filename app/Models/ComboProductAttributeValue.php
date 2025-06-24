<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ComboProductAttribute;

class ComboProductAttributeValue extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
        'combo_product_attribute_id',
        'value',
        'status',
    ];
    public function attribute()
    {
        return $this->belongsTo(ComboProductAttribute::class);
    }
}
