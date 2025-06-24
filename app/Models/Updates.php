<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Updates extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'version',
    ];

    public $timestamps = false;

    // Define default ordering
    public function scopeLatestById($query)
    {
        return $query->orderBy('id', 'desc');
    }
}
