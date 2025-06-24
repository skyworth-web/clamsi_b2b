<?php

namespace App\Http\Controllers\Delivery_boy;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{

    public function index()
    {
        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
        $deliveryBoyId = Auth::id();

        $user_res = fetchDetails('users', ['id' => $deliveryBoyId], ['balance', 'bonus']);
        $bonus = $user_res[0]->bonus;
        $balance = $user_res[0]->balance;

        return view('delivery_boy.pages.forms.home', compact('currency', 'deliveryBoyId','bonus', 'balance'));
    }
}
