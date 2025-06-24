<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;

class Customers extends Controller
{
    public function getUserTransactionList(UserController $userController, Request $request)
    {
        $user_id = Auth::user()->id ?? null;
        $transaction_type = 'wallet';
        $res = $userController->transactions_list($user_id, '', $transaction_type);
        return $res;
    }
    public function wallet_withdrawal_request(UserController $userController, Request $request)
    {
        $user_id = Auth::user()->id ?? null;
        $transaction_type = 'wallet';
        $res = $userController->wallet_withdrawal_request($user_id);
        return $res;
    }

    public function get_transaction(UserController $userController, Request $request)
    {
        $user_id = Auth::user()->id ?? null;
        $transaction_type = 'transaction';
        $res = $userController->transactions_list($user_id, null, $transaction_type);
        return $res;
    }

    public function notifications(NotificationsController $notifications)
    {
        $user_id = Auth::user()->id ?? null;
        $res = $notifications->get_notifications($user_id);
        return $res;
    }
}
