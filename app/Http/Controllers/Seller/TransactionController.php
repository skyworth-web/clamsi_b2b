<?php

namespace App\Http\Controllers\seller;

use App\Http\Controllers\Admin\SellerController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function wallet_transactions()
    {
        return view('seller.pages.tables.wallet_transactions');
    }

    public function wallet_transactions_list(SellerController $sellerController)
    {
        $user_id = auth()->user()->id;
        $role_id = Auth::user()->role_id;
        $res = $sellerController->wallet_transactions_list($user_id, $role_id);
        return $res;
    }
}
