<?php

namespace App\Livewire\Orders;

use App\Models\OrderItems;
use Livewire\Component;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Details extends Component
{
    public function render(Request $request)
    {
        $store_id = session('store_id');
        $order_id = $request->segment(2);
        $user = Auth::user();

        $user_orders = fetchOrders(order_id: $order_id, user_id: $user->id, store_id: $store_id);
        if (count($user_orders['order_data']) < 1) {
            abort(404);
        }
        $user_orders_transaction_data = json_decode(json_encode($user_orders['order_data']), true);
        foreach ($user_orders_transaction_data as &$user_order) {
            foreach ($user_order['order_items'] as &$user_order_item) {
                $order_item_id = $user_order_item['id'];

                // Assuming you have a Transaction model
                $transaction = Transaction::where('order_item_id', $order_item_id)->first();

                if ($transaction) {
                    // If a transaction is found, add it to the order item data
                    $user_order_item['transaction'] = $transaction->toArray();
                } else {
                    // If no transaction is found, you can set a default value or handle it as needed
                    $user_order_item['transaction'] = null;
                }
            }
        }

        $currency_id = $user_orders['order_data'][0]->order_payment_currency_id ?? null;
        $currency_symbol = "";
        if ($currency_id != null) {
            $currency = fetchDetails('currencies', ['id' => $currency_id]);
            $currency_symbol = $currency[0]->symbol;
        }
        // dd($user_orders_transaction_data);
        return view('livewire.' . config('constants.theme') . '.orders.details', [
            'user_orders' => $user_orders,
            'order_transaction' => $user_orders_transaction_data,
            'currency_symbol' => $currency_symbol,
        ])->title("Orders Detail |");
    }

    public function update_order_item_status(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_status' => 'required',
            'order_item_id' => 'required',
        ]);
        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
                'code' => 102,
            ];
            return response()->json($response);
        }
        $order_item = OrderItems::find($request['order_item_id']);
        if ($request['order_status'] == 'cancelled') {
            update_order_item($order_item['id'], $request['order_status'], 1);

            updateStock($order_item['product_variant_id'], $order_item['quantity'], 'plus');
            process_refund($order_item['id'], $request['order_status']);
            $response = [
                'error' => false,
                'message' => 'Order Item Status Updated Successfully',
            ];
            return response()->json($response);
        }
        if ($request['order_status'] == 'returned') {
            $res = validateOrderStatus($request['order_item_id'], $request['order_status'],  'order_items', '', true);

            if ($res['error']) {
                $response['error'] = (isset($res['return_request_flag'])) ? false : true;
                $response['message'] = $res['message'];
                $response['data'] = $res['data'];
                print_r(json_encode($response));
                return false;
            }
            $request['order_status'] = 'return_request_pending';
            if (updateOrder(['status' => $request['order_status']], ['id' => $order_item['id']], true)) {
                updateOrder(['active_status' => $request['order_status']], ['id' => $order_item['id']], false);
                $response = [
                    'error' => false,
                    'message' => 'Order Status Updated Successfully',
                ];
                return response()->json($response);
            }
        }
    }
}
