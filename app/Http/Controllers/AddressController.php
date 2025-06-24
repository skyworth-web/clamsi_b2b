<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function getAddress($user_id = null, $id = null, $fetch_latest = false, $is_default = false)
    {
        $query = DB::table('addresses as addr')
            ->select('addr.*')
            ->where(function ($query) use ($user_id, $id) {
                if ($user_id !== null) {
                    $query->where('user_id', $user_id);
                }
                if ($id !== null) {
                    $query->where('addr.id', $id);
                }
            })
            ->groupBy('addr.id')
            ->orderByDesc('addr.id');

        if ($fetch_latest) {
            $query->limit(1);
        }

        if ($is_default) {
            $query->where('is_default', true);
        }


        $addresses = $query->get();
        $addresses = $addresses->map(function ($address) {
            $zipcode = $address->pincode ?? null;

            $zipcode_id = fetchDetails('zipcodes', ['zipcode' => $zipcode], 'id');

            $zipcode_id = isset($zipcode_id) && !empty($zipcode_id) ? $zipcode_id[0]->id : '';
            $minimum_free_delivery_order_amount = fetchDetails('zipcodes', ['id' => $zipcode_id], ['minimum_free_delivery_order_amount', 'delivery_charges']);
            $address->minimum_free_delivery_order_amount = optional($minimum_free_delivery_order_amount)->minimum_free_delivery_order_amount ?? 0;
            $address->delivery_charges = optional($minimum_free_delivery_order_amount)->delivery_charges ?? 0;
            return $address;
        });

        return $addresses->toArray();
    }
}
