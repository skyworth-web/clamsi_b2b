<?php

namespace App\Livewire\Orders;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class Listing extends Component
{
    public $orderStatus = "";

    public function render()
    {
        $store_id = session('store_id');
        $user = Auth::user();
        // dd($user);
        if ($user != null) {

            $user_orders = $this->getOrders($user->id, $store_id);
            $orders = collect($user_orders['order_data']);
            $page = request()->get('page', 1);
            if (isset($page)) {
                $perPage = 8;
                $paginator = new LengthAwarePaginator(
                    $orders->forPage((int)$page, (int)$perPage),
                    $user_orders['total'],
                    (int)$perPage,
                    (int)$page,
                    ['path' => url()->current()]
                );
            }
            $user_orders['order_data'] = $paginator->items();
            $user_orders['links'] = $paginator->links();
            // dd($user_orders);
            return view('livewire.' . config('constants.theme') . '.orders.listing', [
                'user_info' => $user,
                'user_orders' => $user_orders,
            ])->title("Orders |");
        } else {
            abort(404);
        }
    }

    public function getOrders($userId, $store_id)
    {
        if (empty($this->orderStatus)) {
            $data = fetchOrders(user_id: $userId, sort: "o.id", order: 'DESC', store_id: $store_id);
            return $data;
        } else {
            $order_status = explode(',', $this->orderStatus);
            $orderDetails = fetchOrders(user_id: $userId, sort: "o.id", order: 'DESC', status: $order_status, store_id: $store_id);

            return $orderDetails;
        }
    }

    public function filterOrders($status)
    {
        $this->orderStatus = $status;
    }
}
