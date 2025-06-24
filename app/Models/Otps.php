<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otps extends Model
{
    use HasFactory;
    protected $fillable = [
        'otp',
        'mobile',
        'varified',
    ];
}
