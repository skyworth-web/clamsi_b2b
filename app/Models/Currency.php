<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Currency extends Model
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $fillable = [
        'name',
        'code',
        'symbol',
        'exchange_rate',
    ];

    public static function update_exchange_rate_from_api($api_rates, $base)
    {
        // fetch default currency

        $default_currency = Currency::where('is_default', 1)->first();

        // update exchange rate if default currency is usd

        if (strtoupper($default_currency->code) == $base) {
            foreach ($api_rates as $currency_code => $exchange_rate) {
                $currency_code = strtoupper($currency_code);

                $currency = Currency::where('code', $currency_code)->first();

                if ($currency) {
                    $currency->update(['exchange_rate' => $exchange_rate]);
                }
            }
        }
    }
}
