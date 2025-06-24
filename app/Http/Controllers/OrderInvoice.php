<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;

class OrderInvoice extends Controller
{
    public function index(Request $request)
    {
        $store_id = session('store_id');
        $order_id = $request->segment(2);
        $user = Auth::user();

        $user_orders = fetchOrders($order_id, $user->id, '', '', '', '', 'o.id', 'DESC', '', '', '', '', '', '', '', '', '', '', $store_id);
        if (empty($user_orders['order_data'])) {
            abort(404);
        }
        $currency_symbol = $user_orders['order_data'][0]->order_payment_currency_code ?? null;
        return view('components.OrderInvoice',[
            'order_details' => $user_orders,
            'currency_symbol' => $currency_symbol,
        ]);
    }

    public function generatInvoicePDF($id)
    {
        $res = getOrderDetails(['o.id' => $id]);
        $seller_ids = array_values(array_unique(array_column($res, "seller_id")));
        $seller_user_ids = [];
        $promo_code = [];
        $items = [];

        foreach ($seller_ids as $id) {
            $seller_user_ids[] = Seller::where('id', $id)->value('user_id');
        }

        if (!empty($res)) {

            if (!empty($res[0]->promo_code_id)) {
                $promo_code = fetchDetails('promo_codes', ['id' => trim($res[0]->promo_code_id)]);
            }

            foreach ($res as $row) {
                $temp['product_id'] = $row->product_id;
                $temp['seller_id'] = $row->seller_id;
                $temp['product_variant_id'] = $row->product_variant_id;
                $temp['pname'] = $row->pname;
                $temp['quantity'] = $row->quantity;
                $temp['discounted_price'] = $row->discounted_price;
                $temp['tax_percent'] = $row->tax_percent;
                $temp['tax_amount'] = $row->tax_amount;
                $temp['price'] = $row->price;
                $temp['product_special_price'] = $row->product_special_price;
                $temp['product_price'] = $row->product_price;
                $temp['delivery_boy'] = $row->delivery_boy;
                $temp['mobile_number'] = $row->mobile_number;
                $temp['active_status'] = $row->oi_active_status;
                $temp['hsn_code'] = $row->hsn_code ?? '';
                $temp['is_prices_inclusive_tax'] = $row->is_prices_inclusive_tax;
                array_push($items, $temp);
            }
        }

        $item1 = InvoiceItem::make('Service 1')->pricePerUnit(2);
        $sellers = [
            'seller_ids' => $seller_ids,
            'seller_user_ids' => $seller_user_ids,
            'mobile_number' => $res[0]->mobile_number,
        ];

        $customer = new Buyer([
            'name' => $res[0]->uname,
            'custom_fields' => [
                'address' => $res[0]->address,
                'order_id' => $res[0]->id,
                'date_added' => $res[0]->created_at,
                'store_id' => $res[0]->store_id,
                'payment_method' => $res[0]->payment_method,
                'discount' => $res[0]->discount,
                'promo_code' => $promo_code[0]->promo_code ?? '',
                'promo_code_discount' => $promo_code[0]->discount ?? '',
                'promo_code_discount_type' => $promo_code[0]->discount_type ?? '',
            ],
        ]);

        $client = new Party([
            'custom_fields' => $sellers,
        ]);

        $invoice = Invoice::make()
            ->buyer($customer)
            ->seller($client)
            ->setCustomData($items)
            ->addItem($item1)
            ->template('invoice');

        return $invoice->stream();
    }
}
