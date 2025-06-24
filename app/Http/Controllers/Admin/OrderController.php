<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Libraries\Shiprocket;
use App\Models\DigitalOrdersMail;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\OrderTracking;
use App\Models\Seller;
use App\Models\UserFcm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;


class OrderController extends Controller
{
    public function index(Request $request)
    {
        $store_id = getStoreId();
        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
        $user_id = $request['user_id'];
        return view('admin.pages.tables.manage_orders', compact('currency', 'user_id', 'store_id'));
    }

    public function generatInvoicePDF($id)
    {
        $res = getOrderDetails(['o.id' => $id]);
        // dd($res);
        if (empty($res)) {
            return view('admin.pages.views.no_data_found');
        }
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
                $temp['product_price'] = $row->product_price;
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
        // dd($res);
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
        // dd($temp['price']);
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

    public function order_items(Request $request)
    {

        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
        $user_id = $request['user_id'];
        return view('admin.pages.tables.manage_order_items', compact('currency', 'user_id'));
    }
    public function order_tracking()
    {
        return view('admin.pages.tables.order_tracking');
    }
    public function list()
    {
        $store_id = getStoreId();
        $search = trim(request('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit', 10);
        $sort = request('sort', 'o.id');
        $order = request('order', 'ASC');
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');

        $query = DB::table('orders as o')
            ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
            ->leftJoin('order_items as oi', 'oi.order_id', '=', 'o.id')
            ->leftJoin('product_variants as v', 'oi.product_variant_id', '=', 'v.id')
            ->leftJoin('products as p', 'p.id', '=', 'v.product_id')
            ->leftJoin('users as db', 'db.id', '=', 'oi.delivery_boy_id')
            ->leftJoin('promo_codes as pc', 'pc.id', '=', 'o.promo_code_id');

        if ($startDate && $endDate) {
            $query->whereDate('oi.created_at', '>=', $startDate)
                ->whereDate('oi.created_at', '<=', $endDate);
        }


        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->orWhere('u.username', 'like', '%' . $search . '%')
                    ->orWhere('db.username', 'like', '%' . $search . '%')
                    ->orWhere('u.email', 'like', '%' . $search . '%')
                    ->orWhere('o.id', 'like', '%' . $search . '%')
                    ->orWhere('o.mobile', 'like', '%' . $search . '%')
                    ->orWhere('o.address', 'like', '%' . $search . '%')
                    ->orWhere('o.wallet_balance', 'like', '%' . $search . '%')
                    ->orWhere('o.total', 'like', '%' . $search . '%')
                    ->orWhere('o.final_total', 'like', '%' . $search . '%')
                    ->orWhere('o.total_payable', 'like', '%' . $search . '%')
                    ->orWhere('o.payment_method', 'like', '%' . $search . '%')
                    ->orWhere('o.delivery_charge', 'like', '%' . $search . '%')
                    ->orWhere('o.delivery_time', 'like', '%' . $search . '%')
                    ->orWhere('oi.status', 'like', '%' . $search . '%')
                    ->orWhere('oi.active_status', 'like', '%' . $search . '%')
                    ->orWhere('o.created_at', 'like', '%' . $search . '%');
            });
        }
        $query->where('o.store_id', $store_id);

        if (request()->has('delivery_boy_id')) {
            $query->where('oi.delivery_boy_id', request()->input('delivery_boy_id'));
        }

        if (request()->has('seller_id') && !empty(request()->input('seller_id'))) {
            $query->where('oi.seller_id', request()->input('seller_id'));
        }

        if (request()->has('user_id') && !empty(request()->input('user_id'))) {
            $query->where('o.user_id', request()->input('user_id'));
        }

        // Filter By payment
        if (request()->has('payment_method') && !empty(request()->input('payment_method'))) {
            $query->where('payment_method', request()->input('payment_method'));
        }

        // Filter By order type
        if (request()->has('order_type') && !empty(request()->input('order_type'))) {
            if (request()->input('order_type') == 'physical_order') {
                $query->where('p.type', '!=', 'digital_product');
            } elseif (request()->input('order_type') == 'digital_order') {
                $query->where('p.type', 'digital_product');
            }
        }

        $totalCount = $query->distinct()->count('o.id');


        $userDetails = $query
            ->select('o.*', 'u.username', 'db.username as delivery_boy', 'pc.promo_code')
            ->distinct()
            ->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();


        foreach ($userDetails as $index => $row) {
            $userDetails[$index]->items = DB::table('order_items as oi')
                ->select('oi.*', 'p.name as name', 'p.id as product_id', 'p.type', 'p.download_allowed', 'u.username as uname', 'us.username as seller')
                ->leftJoin('product_variants as v', 'oi.product_variant_id', '=', 'v.id')
                ->leftJoin('products as p', 'p.id', '=', 'v.product_id')
                ->leftJoin('users as u', 'u.id', '=', 'oi.user_id')
                ->leftJoin('seller_data as sd', 'sd.id', '=', 'oi.seller_id')
                ->leftJoin('users as us', 'us.id', '=', 'sd.user_id')
                ->where('oi.order_id', $row->id)
                ->where('oi.store_id', $store_id)
                ->get()
                ->toArray();
        }



        $bulkData = array();
        $bulkData['total'] = $totalCount;
        $rows = array();
        $tempRow = array();
        $tota_amount = 0;
        $final_tota_amount = 0;
        $currency = fetchDetails('currencies', ['is_default' => 1], 'symbol')[0]->symbol;

        foreach ($userDetails as $row) {

            if (!empty($row->items)) {

                $items = $row->items;
                $items1 = '';
                $temp = '';
                $total_amt = $total_qty = 0;
                $seller = implode(",", array_values(array_unique(array_column($items, "seller"))));

                foreach ($items as $item) {

                    $product_variants = getVariantsValuesById($item->product_variant_id);
                    $variants = isset($product_variants[0]->variant_values) && !empty($product_variants[0]->variant_values) ? str_replace(',', ' | ', $product_variants[0]->variant_values) : '-';
                    $temp .= "<b>ID :</b>" . $item->id . "<b> Product Variant Id :</b> " . $item->product_variant_id . "<b> Variants :</b> " . $variants . "<b> Name : </b>" . $item->name . " <b>Price : </b>" . $item->price . " <b>QTY : </b>" . $item->quantity . " <b>Subtotal : </b>" . $item->quantity * $item->price . "<br>------<br>";
                    $total_amt += $item->sub_total;
                    $total_qty += $item->quantity;
                }

                $items1 = $temp;

                $discounted_amount = $row->total * $row->items[0]->discount / 100;
                $final_total = $row->total - $discounted_amount;
                $discount_in_rupees = $row->total - $final_total;
                $discount_in_rupees = floor($discount_in_rupees);
                $tempRow['id'] = $row->id;
                $tempRow['user_id'] = $row->user_id;
                $tempRow['name'] = $row->items[0]->uname;
                $tempRow['mobile'] = $row->mobile;
                $tempRow['delivery_charge'] = formateCurrency(formatePriceDecimal($row->delivery_charge));
                $tempRow['items'] = $items1;
                $tempRow['sellers'] = $seller;
                $tempRow['total'] = formateCurrency(formatePriceDecimal($row->total));
                $tota_amount += intval($row->total);
                $tempRow['wallet_balance'] = formateCurrency(formatePriceDecimal($row->wallet_balance));
                $tempRow['discount'] = $discount_in_rupees . '(' . $row->items[0]->discount . '%)';
                $tempRow['promo_discount'] = formateCurrency(formatePriceDecimal($row->promo_discount));
                $tempRow['promo_code'] = $row->promo_code ?? '';
                $tempRow['notes'] = $row->notes;
                $tempRow['qty'] = $total_qty;
                $tempRow['final_total'] = formateCurrency(formatePriceDecimal($row->total_payable));
                $final_total = $row->final_total - $row->wallet_balance - $row->discount;
                $tempRow['final_total'] = formateCurrency(formatePriceDecimal($final_total));
                $final_tota_amount += intval($row->final_total);
                $tempRow['deliver_by'] = $row->delivery_boy;
                $tempRow['payment_method'] = $row->payment_method;
                if (isset($row->items[0]->updated_by) && !empty($row->items[0]->updated_by)) {
                    $updated_username = fetchDetails('users', ['id' => $row->items[0]->updated_by], 'username');
                    $updated_username = isset($updated_username) && !empty($updated_username) ? $updated_username[0]->username : "";
                    $tempRow['updated_by'] = $updated_username;
                } else {
                    $tempRow['updated_by'] = '';
                }
                $tempRow['address'] = outputEscaping(str_replace('\r\n', '</br>', $row->address));
                $tempRow['delivery_date'] = $row->delivery_date;
                $tempRow['delivery_time'] = $row->delivery_time;
                $tempRow['date_added'] = Carbon::parse($row->created_at)->format('d-m-Y');
                $edit_url = route('admin.orders.edit', $row->id);
                $delete_url = route('admin.orders.destroy', $row->id);
                $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown order_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>';
                $action .= '</div>
            </div>';


                $tempRow['operate'] = $action;
                $rows[] = $tempRow;
            }
        }

        return response()->json([
            "rows" => $rows,
            "total" => $totalCount,
        ]);
    }

    public function order_item_list()
    {
        $store_id = getStoreId();
        $search = trim(request()->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = request('limit', 10);
        $sort = 'oi.id';
        $order = request('order', 'DESC');
        $startDate = request()->input('start_date');
        $endDate = request()->input('end_date');
        $deliveryBoyId = request()->input('delivery_boy_id');
        $sellerId = request()->input('seller_id');
        $userId = request()->input('user_id');
        $orderStatus = request()->input('order_status');

        $paymentMethod = request()->input('payment_method');
        $orderType = request()->input('order_type');

        // Count query
        $countQuery = DB::table('order_items as oi')
            ->leftJoin('users as u', 'u.id', '=', 'oi.delivery_boy_id')
            ->leftJoin('users as us', 'us.id', '=', 'oi.seller_id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('product_variants as v', 'oi.product_variant_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('users as un', 'o.user_id', '=', 'un.id')
            ->selectRaw('COUNT(o.id) as total');


        if ($startDate && $endDate) {
            $countQuery->whereDate('oi.created_at', '>=', $startDate)
                ->whereDate('oi.created_at', '<=', $endDate);
        }

        if ($search) {
            $countQuery->where(function ($query) use ($search) {
                $query->where('un.username', 'like', '%' . $search . '%')
                    ->orWhere('u.username', 'like', '%' . $search . '%')
                    ->orWhere('us.username', 'like', '%' . $search . '%')
                    ->orWhere('un.email', 'like', '%' . $search . '%')
                    ->orWhere('oi.id', 'LIKE', '%' . $search . '%')
                    ->orWhere('o.mobile', 'like', '%' . $search . '%')
                    ->orWhere('o.address', 'like', '%' . $search . '%')
                    ->orWhere('o.payment_method', 'like', '%' . $search . '%')
                    ->orWhere('oi.sub_total', 'LIKE', '%' . $search . '%')
                    ->orWhere('o.delivery_time', 'like', '%' . $search . '%')
                    ->orWhere('oi.active_status', 'like', '%' . $search . '%')
                    ->orWhereDate('oi.created_at', 'LIKE', '%' . $search . '%');
            });
        }
        $countQuery->where('oi.store_id', $store_id);
        if ($deliveryBoyId) {
            $countQuery->where('oi.delivery_boy_id', $deliveryBoyId);
        }

        $countQuery->where('o.is_pos_order', 0);

        if ($sellerId) {
            $countQuery->where('oi.seller_id', $sellerId)
                ->where('oi.active_status', '!=', 'awaiting');
        }

        if ($userId) {
            $countQuery->where('o.user_id', $userId);
        }

        if ($orderStatus) {
            $countQuery->where('oi.active_status', $orderStatus);
        }

        if ($paymentMethod) {
            $countQuery->where('o.payment_method', $paymentMethod);
        }

        if ($orderType === 'physical_order') {
            $countQuery->where('p.type', '!=', 'digital_product');
        }

        if ($orderType === 'digital_order') {
            $countQuery->where('p.type', 'digital_product');
        }

        $productCount = $countQuery->get()->first()->total;

        // Search query
        $searchQuery = DB::table('order_items as oi')
            ->leftJoin('users as u', 'u.id', '=', 'oi.delivery_boy_id')
            ->leftJoin('seller_data as sd', 'sd.id', '=', 'oi.seller_id')
            ->leftJoin('users as us', 'us.id', '=', 'sd.user_id')
            ->leftJoin('order_trackings as ot', 'ot.order_item_id', '=', 'oi.id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('product_variants as v', 'oi.product_variant_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('transactions as t', 't.order_item_id', '=', 'oi.id')
            ->leftJoin('users as un', 'o.user_id', '=', 'un.id')
            ->select(
                'o.id as order_id',
                'oi.id as order_item_id',
                'o.*',
                'oi.*',
                'ot.courier_agency',
                'ot.tracking_id',
                'ot.url',
                't.status as transaction_status',
                'u.username as delivery_boy',
                'un.username as username',
                'us.username as seller_name',
                'p.type',
                'p.download_allowed',
                'p.type as product_type'
            );
        $searchQuery->where('oi.store_id', $store_id);

        if ($startDate !== null && $endDate !== null) {
            $searchQuery->whereDate('oi.created_at', '>=', $startDate)
                ->whereDate('oi.created_at', '<=', $endDate);
        }

        if ($search) {
            $searchQuery->where(function ($query) use ($search) {
                $query->where('un.username', 'like', '%' . $search . '%')
                    ->orWhere('u.username', 'like', '%' . $search . '%')
                    ->orWhere('us.username', 'like', '%' . $search . '%')
                    ->orWhere('un.email', 'like', '%' . $search . '%')
                    ->orWhere('oi.id', 'LIKE', '%' . $search . '%')
                    ->orWhere('o.mobile', 'like', '%' . $search . '%')
                    ->orWhere('o.address', 'like', '%' . $search . '%')
                    ->orWhere('o.payment_method', 'like', '%' . $search . '%')
                    ->orWhere('oi.sub_total', 'LIKE', '%' . $search . '%')
                    ->orWhere('o.delivery_time', 'like', '%' . $search . '%')
                    ->orWhere('oi.active_status', 'like', '%' . $search . '%')
                    ->orWhereDate('oi.created_at', 'LIKE', '%' . $search . '%');
            });
        }

        $searchQuery->where('o.is_pos_order', 0);

        if ($deliveryBoyId) {
            $searchQuery->where('oi.delivery_boy_id', $deliveryBoyId);
        }

        if ($sellerId) {
            $searchQuery->where('oi.seller_id', $sellerId)
                ->where('oi.active_status', '!=', 'awaiting');
        }

        if ($userId) {
            $searchQuery->where('o.user_id', $userId);
        }

        if ($orderStatus) {
            $searchQuery->where('oi.active_status', $orderStatus);
        }

        if ($paymentMethod) {
            $searchQuery->where('o.payment_method', $paymentMethod);
        }

        if ($orderType === 'physical_order') {
            $searchQuery->where('p.type', '!=', 'digital_product');
        }

        if ($orderType === 'digital_order') {
            $searchQuery->where('p.type', 'digital_product');
        }

        $userDetails = $searchQuery->orderBy($sort, $order)
            ->distinct()
            ->skip($offset)
            ->take($limit)
            ->get();

        $bulkData = array();
        $bulkData['total'] = $productCount;
        $rows = array();
        $tempRow = array();
        $tota_amount = 0;
        $final_tota_amount = 0;
        $currency = fetchDetails('currencies', ['is_default' => 1], 'symbol')[0]->symbol;
        $count = 1;

        foreach ($userDetails as $row) {

            $temp = '';
            if (!empty($row->status)) {
                $status = json_decode($row->status, 1);
                foreach ($status as $st) {
                    $temp .= @$st[0] . "<br>" . " --------------- " . "</br>";
                }
            }


            if ($row->active_status == 'awaiting') {
                $active_status = '<label class="badge bg-secondary">' . ucfirst($row->active_status) . '</label>';
            }
            if ($row->active_status == 'received') {
                $active_status = '<label class="badge bg-primary">' . ucfirst($row->active_status) . '</label>';
            }
            if ($row->active_status == 'processed') {
                $active_status = '<label class="badge bg-info">' . ucfirst($row->active_status) . '</label>';
            }
            if ($row->active_status == 'shipped') {
                $active_status = '<label class="badge bg-warning">' . ucfirst($row->active_status) . '</label>';
            }
            if ($row->active_status == 'delivered') {
                $active_status = '<label class="badge bg-success">' . ucfirst($row->active_status) . '</label>';
            }
            if ($row->active_status == 'returned' || $row->active_status == 'cancelled') {
                $active_status = '<label class="badge bg-danger">' . ucfirst($row->active_status) . '</label>';
            }
            if ($row->active_status == 'return_request_decline') {
                $active_status = '<label class="badge bg-danger">Return Declined</label>';
            }
            if ($row->active_status == 'return_request_approved') {
                $active_status = '<label class="badge bg-success">Return Approved</label>';
            }
            if ($row->active_status == 'return_request_pending') {
                $active_status = '<label class="badge bg-secondary">Return Requested</label>';
            }
            if ($row->type == 'digital_product' && $row->download_allowed == 0) {
                if ($row->is_sent == 1) {
                    $mail_status = '<label class="badge bg-success">SENT </label>';
                } else if ($row->is_sent == 0) {
                    $mail_status = '<label class="badge bg-danger">NOT SENT</label>';
                } else {
                    $mail_status = '';
                }
            } else {
                $mail_status = '';
            }

            $transaction_status = '<label class="badge bg-primary">' . $row->transaction_status . '</label>';
            $status = $temp;
            $tempRow['id'] = $count;
            $tempRow['order_id'] = $row->order_id;
            $tempRow['order_item_id'] = $row->order_item_id;
            $tempRow['user_id'] = $row->user_id;
            $tempRow['seller_id'] = $row->seller_id;
            $tempRow['notes'] = (isset($row->notes) && !empty($row->notes)) ? $row->notes : "";
            $tempRow['username'] = $row->username;
            $tempRow['seller_name'] = $row->seller_name;
            $tempRow['is_credited'] = ($row->is_credited) ? '<label class="badge bg-success">Credited</label>' : '<label class="badge bg-danger">Not Credited</label>';
            $tempRow['product_name'] = $row->product_name;
            $tempRow['product_name'] .= (!empty($row->variant_name)) ? '(' . $row->variant_name . ')' : "";
            $tempRow['mobile'] = $row->mobile;
            $tempRow['sub_total'] = formateCurrency(formatePriceDecimal($row->sub_total));
            $tempRow['quantity'] = $row->quantity;
            $final_tota_amount += intval($row->sub_total);
            $tempRow['delivery_boy'] = $row->delivery_boy;
            $tempRow['payment_method'] = ucfirst($row->payment_method);
            $tempRow['delivery_boy_id'] = $row->delivery_boy_id;
            $tempRow['product_variant_id'] = $row->product_variant_id;
            $tempRow['delivery_date'] = $row->delivery_date;
            $tempRow['delivery_time'] = $row->delivery_time;
            $tempRow['courier_agency'] = (isset($row->courier_agency) && !empty($row->courier_agency)) ? $row->courier_agency : "";
            $tempRow['tracking_id'] = (isset($row->tracking_id) && !empty($row->tracking_id)) ? $row->tracking_id : "";
            $tempRow['url'] = (isset($row->url) && !empty($row->url)) ? $row->url : "";
            if (isset($row->updated_by) && !empty($row->updated_by)) {
                $updated_username = fetchDetails('users', ['id' => $row->updated_by], 'username');
                $tempRow['updated_by'] = $updated_username[0]->username;
            } else {
                $tempRow['updated_by'] = '';
            }

            $tempRow['status'] = $status;
            $tempRow['transaction_status'] = $transaction_status;
            $tempRow['active_status'] = $active_status;
            $tempRow['mail_status'] = $mail_status;
            $tempRow['date_added'] = Carbon::parse($row->created_at)->format('d-m-Y');

            $edit_url = route('admin.orders.edit', $row->order_id);
            $delete_url = route('admin.order.items.destroy', $row->order_item_id);
            $action = '<div class="dropdown bootstrap-table-dropdown">
                <a href="#" class="text-dark" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded"></i>
                </a>
                <div class="dropdown-menu table_dropdown order_items_action_dropdown" aria-labelledby="dropdownMenuButton">
                    <a class="dropdown-item dropdown_menu_items" href="' . $edit_url . '"><i class="bx bx-pencil mx-2"></i> Edit</a>
                    <a class="dropdown-item delete-data dropdown_menu_items" data-url="' . $delete_url . '"><i class="bx bx-trash mx-2"></i> Delete</a>';
            $action .= '</div>
            </div>';


            $tempRow['operate'] = $action;
            $rows[] = $tempRow;
            $count++;
        }
        return response()->json([
            "rows" => $rows,
            "total" => $productCount,
        ]);
    }
    public function get_order_tracking(Request $request)
    {

        $store_id = getStoreId();
        $search = trim($request->input('search'));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = 'order_trackings.id';
        $order = $request->input('order', 'DESC');
        $order_id = $request->input('order_id');

        $query = DB::table('order_trackings')
            ->select('*')
            ->join('orders', 'order_trackings.order_id', '=', 'orders.id')
            ->where('orders.store_id', '=', $store_id);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('order_trackings.id', '=', 'LIKE', '%' . $search . '%')
                    ->orWhere('order_trackings.order_id', '=', 'LIKE', '%' . $search . '%')
                    ->orWhere('order_trackings.tracking_id', '=', 'LIKE', '%' . $search . '%')
                    ->orWhere('order_trackings.courier_agency', '=', 'LIKE', '%' . $search . '%')
                    ->orWhere('order_trackings.order_item_id', '=', 'LIKE', '%' . $search . '%')
                    ->orWhere('order_trackings.url', '=', 'LIKE', '%' . $search . '%');
            });
        }

        if ($order_id) {
            $query->where('order_trackings.order_id', '=', $order_id);
        }

        $total = $query->count();

        $txn_search_res = $query->orderBy($sort, $order)
            ->skip($offset)
            ->take($limit)
            ->get();

        $rows = [];

        foreach ($txn_search_res as $row) {
            $edit_url = route('admin.orders.edit', $row->order_id);
            $operate = '<div class="d-flex align-items-center">
                <a href="' . $edit_url . '" class="p-2 single_action_button" title="View Order" ><i class="bx bxs-show mx-2"></i></a>
            </div>';
            $tempRow = [
                'id' => $row->id,
                'order_id' => $row->order_id,
                'order_item_id' => $row->order_item_id,
                'courier_agency' => $row->courier_agency,
                'tracking_id' => $row->tracking_id,
                'url' => $row->url,
                'date' => Carbon::parse($row->created_at)->format('d-m-Y'),
                'operate' => $operate,
            ];

            $rows[] = $tempRow;
        }

        return response()->json([
            "rows" => $rows,
            "total" => $total,
        ]);
    }


    public function edit($id)
    {
        $store_id = getStoreId();
        $res = getOrderDetails(['o.id' => $id], '', '', $store_id);
        // dd($res);
        if ($res == null || empty($res)) {
            return view('admin.pages.views.no_data_found');
        } else {
            if (isExist(['id' => $res[0]->address_id], 'addresses')) {
                $zipcode = fetchDetails('addresses', ['id' => $res[0]->address_id], 'pincode');

                if (!empty($zipcode) && ($zipcode[0]->pincode != '')) {

                    $zipcode_id = fetchDetails('zipcodes', ['zipcode' => $zipcode[0]->pincode], 'id');

                    if (!empty($zipcode_id)) {

                        $delivery_res = DB::table('users as u')
                            ->where(['u.role_id' => '3', 'u.active' => 1])
                            ->whereRaw('FIND_IN_SET(?, u.serviceable_zipcodes) != 0', [$zipcode_id[0]->id])
                            ->select('u.*')
                            ->get()
                            ->toArray();
                    } else {

                        $delivery_res = DB::table('users as u')
                            ->where(['u.role_id' => '3', 'u.active' => 1])
                            ->select('u.*')
                            ->get()
                            ->toArray();
                    }
                } else {
                    $delivery_res = DB::table('users as u')
                        ->where('u.role_id', '3')
                        ->where('u.active', 1)
                        ->select('u.*')
                        ->get()
                        ->toArray();
                }
            } else {

                $delivery_res = DB::table('users as u')
                    ->where('u.role_id', '3')
                    ->where('u.active', 1)
                    ->select('u.*')
                    ->get()
                    ->toArray();
            }


            if ($res[0]->payment_method == "bank_transfer") {
                $bank_transfer = fetchDetails('order_bank_transfers', ['order_id' => $res[0]->order_id]);
                $transaction_search_res = fetchDetails('transactions', ['order_id' => $res[0]->order_id]);
            }

            $items = $seller = [];
            foreach ($res as $row) {

                $multipleWhere = ['seller_id' => $row->seller_id, 'order_id' => $row->id];
                $orderChargeData = DB::table('order_charges')->where($multipleWhere)->get();

                $updated_username = isset($row->updated_by) && !empty($row->updated_by) && $row->updated_by != 0 ? fetchDetails('users', ['id' => $row->updated_by], 'username')[0]->username : '';
                $address_number = (isset($row->address_id) && !empty($row->address_id) && $row->address_id != 0) ? (fetchDetails('addresses', ['id' => $row->address_id], 'mobile')[0]->mobile ?? '') : '';
                $deliver_by = isset($row->delivery_boy_id) && !empty($row->delivery_boy_id) && $row->delivery_boy_id != 0 ? fetchDetails('users', ['id' => $row->delivery_boy_id], 'username')[0]->username : '';
                $temp = [
                    'id' => $row->order_item_id,
                    'item_otp' => $row->item_otp,
                    'tracking_id' => $row->tracking_id,
                    'courier_agency' => $row->courier_agency,
                    'url' => $row->url,
                    'product_id' => $row->product_id,
                    'product_variant_id' => $row->product_variant_id,
                    'product_type' => $row->type,
                    'pname' => $row->pname,
                    'quantity' => $row->quantity,
                    'is_cancelable' => $row->is_cancelable,
                    'is_attachment_required' => $row->is_attachment_required,
                    'is_returnable' => $row->is_returnable,
                    'tax_amount' => $row->tax_amount,
                    'discounted_price' => $row->discounted_price,
                    'price' => $row->price,
                    'updated_by' => $updated_username,
                    'deliver_by' => $deliver_by,
                    'active_status' => $row->oi_active_status,
                    'product_image' => $row->product_image,
                    'product_variants' => getVariantsValuesById($row->product_variant_id),
                    'pickup_location' => $row->pickup_location,
                    'seller_otp' => isset($orderChargeData[0]) ? $orderChargeData[0]->otp : '',
                    'seller_delivery_charge' => isset($orderChargeData[0]) ? $orderChargeData[0]->delivery_charge : '',
                    'seller_promo_discount' => is_numeric(isset($orderChargeData[0]) ? $orderChargeData[0]->promo_discount : 0) ? (float) $orderChargeData[0]->promo_discount : 0.0,
                    'is_sent' => $row->is_sent,
                    'seller_id' => $row->seller_id,
                    'download_allowed' => $row->download_allowed,
                    'user_email' => $row->user_email,
                    'user_profile' => getMediaImageUrl($row->user_profile, 'USER_IMG_PATH'),
                    'product_slug' => $row->product_slug,
                    'sku' => isset($row->product_sku) && !empty($row->product_sku) ? $row->product_sku : $row->sku,
                    'address_number' => $address_number,
                    'item_subtotal' => isset($row->sub_total) ? $row->sub_total : '',
                    'wallet_balance' => isset($row->wallet_balance) ? $row->wallet_balance : 0,
                ];



                array_push($items, $temp);
            }
            $order_detls = $res;
            $sellers_id = collect($res)->pluck('seller_id')->unique()->values()->all();

            foreach ($sellers_id as $id) {
                $query = DB::table('seller_store as ss')
                    ->select(
                        'ss.store_name',
                        'ss.logo as shop_logo',
                        'ss.user_id as user_id',
                        'u.mobile as seller_mobile',
                        'u.email as seller_email',
                        'u.city as seller_city',
                        'u.username as seller_name',
                        'u.pincode as seller_pincode',
                    )
                    ->leftJoin('users as u', 'u.id', '=', 'ss.user_id')
                    ->where('ss.seller_id', $id)->get()->toArray();

                $value = [
                    'id' => $id,
                    'user_id' => $query[0]->user_id ?? '',
                    'store_name' => $query[0]->store_name ?? '',
                    'seller_name' => $query[0]->seller_name ?? '',
                    'seller_email' => $query[0]->seller_email ?? '',
                    'shop_logo' => isset($query[0]->shop_logo) ? getMediaImageUrl($query[0]->shop_logo, 'SELLER_IMG_PATH') : '',
                    'seller_mobile' => $query[0]->seller_mobile ?? '',
                    'seller_pincode' => $query[0]->seller_pincode ?? '',
                    'seller_city' => $query[0]->seller_city ?? ''
                ];

                array_push($seller, $value);
            }



            $sellers = $seller;
            $bank_transfer = isset($bank_transfer) ? $bank_transfer : [];
            $transaction_search_res = isset($transaction_search_res) ? $transaction_search_res : [];
            $items = $items;

            $settings = getSettings('system_settings', true);
            $settings = json_decode($settings, true);

            $shipping_method = getSettings('shipping_method', true);
            $shipping_method = json_decode($shipping_method, true);

            $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
            $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';

            return view('admin.pages.forms.edit_orders', compact('delivery_res', 'order_detls', 'bank_transfer', 'store_id', 'transaction_search_res', 'items', 'settings', 'shipping_method', 'sellers', 'currency'));
        }
    }

    public function update_order_status(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'status' => 'required_without:deliver_by|in:received,processed,shipped,delivered,cancelled,returned',
            'deliver_by' => 'sometimes|nullable|numeric',
            'order_item_id' => [
                'required_if:status,cancelled,returned',
                'min:1',
            ],
            'seller_id' => 'required',
        ], [
            'status.required_without' => 'Please select status or delivery boy for updation.',
            'status.in' => 'Invalid status value.',
            'deliver_by.numeric' => 'Delivery Boy Id must be numeric.',
            'order_item_id.required_if' => 'Please select at least one item of seller for order cancelation or return.',
            'order_item_id.min' => 'Please select at least one item of seller for order cancelation or return.',
            'seller_id.required' => 'Please select at least one seller to update order item(s).',
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
            ];
            return response()->json($response);
        }
        $settings = getSettings('system_settings', true);
        $settings = json_decode($settings, true);
        $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';

        //custom message
        if ($request->input('status') == 'received') {
            $type = ['type' => "customer_order_received"];
        } elseif ($request->input('status') == 'processed') {
            $type = ['type' => "customer_order_processed"];
        } elseif ($request->input('status') == 'shipped') {
            $type = ['type' => "customer_order_shipped"];
        } elseif ($request->input('status') == 'delivered') {
            $type = ['type' => "customer_order_delivered"];
        } elseif ($request->input('status') == 'cancelled') {
            $type = ['type' => "customer_order_cancelled"];
        } elseif ($request->input('status') == 'returned') {
            $type = ['type' => "customer_order_returned"];
        }

        $order_itam_ids = [];

        if ($request->input('status') == 'cancelled' || $request->input('status') == 'returned') {
            $order_itam_ids = $request->input('order_item_id');
        } else {
            $orderItemId = DB::table('order_items')
                ->select('id')
                ->where('order_id', $request->input('order_id'))
                ->where('seller_id', $request->input('seller_id'))
                ->where('active_status', '!=', 'cancelled')
                ->get();;
            foreach ($orderItemId as $ids) {
                array_push($order_itam_ids, $ids->id);
            }
        }

        if (empty($order_itam_ids)) {
            return response()->json([
                'error' => true,
                'message' =>
                labels('admin_labels.cannot_assign_delivery_boy_to_cancelled_orders', 'You cannot assign a delivery boy to cancelled orders.'),
                'data' => [],
            ]);
        }

        $s = [];

        foreach ($order_itam_ids as $ids) {
            $order_detail = fetchDetails('order_items', ['id' => $ids], ['is_sent', 'hash_link', 'product_variant_id', 'order_type']);
            $product_data = fetchDetails('product_variants', ['id' => $order_detail[0]->product_variant_id], 'product_id');
            $product_detail = fetchDetails('products', ['id' => $product_data[0]->product_id], 'type');
            if (empty($order_detail[0]->hash_link) || $order_detail[0]->hash_link == '' || $order_detail[0]->hash_link == null) {
                array_push($s, $order_detail[0]->is_sent);
            }
        }
        if (isset($order_detail[0]->order_type) && $order_detail[0]->order_type != 'combo_order') {
            $order_data = fetchDetails('order_items', ['id' => $order_itam_ids[0]], 'product_variant_id')[0]->product_variant_id;
            $product_id = fetchDetails('product_variants', ['id' => $order_data], 'product_id')[0]->product_id;
            $product_type = fetchDetails('products', ['id' => $product_id], 'type')[0]->type;
        } else {
            $product_type = fetchDetails('combo_products', ['id' => $order_detail[0]->product_variant_id], 'product_type');
            $product_type = isset($product_type) && !empty($product_type) ? $product_type[0]->product_type : '';
        }


        if ($product_type == 'digital_product' && in_array(0, $s)) {
            return response()->json([
                'error' => true,
                'message' =>
                labels('admin_labels.items_not_sent_select_sent_item', 'Some of the selected items have not been sent. Please select an item that has been sent.'),
                'data' => [],
            ]);
        }
        $order_items = fetchDetails('order_items', "", '*', "", "", "", "", "id", $order_itam_ids);


        if (empty($order_items)) {
            return response()->json([
                'error' => true,
                'message' => 'No Order Item Found.',
                'data' => [],
            ]);
        }

        if (count($order_itam_ids) != count($order_items)) {
            return response()->json([
                'error' => true,
                'message' =>
                labels('admin_labels.item_not_found_on_status_update', 'Some item was not found on status update.'),
                'data' => [],
            ]);
        }

        $order_id = $order_items[0]->order_id;
        $store_id = $order_items[0]->store_id;

        $order_method = fetchDetails('orders', ['id' => $order_id], 'payment_method');
        $bank_receipt = fetchDetails('order_bank_transfers', ['order_id' => $order_id]);
        $transaction_status = fetchDetails('transactions', ['order_id' => $order_id], 'status');

        /* validate bank transfer method status */
        if (isset($order_method[0]->payment_method) && $order_method[0]->payment_method == 'bank_transfer') {
            if ($request->input('status') != 'cancelled' && (empty($bank_receipt) || strtolower($transaction_status[0]->status) != 'success' || $bank_receipt[0]->status == "0" || $bank_receipt[0]->status == "1")) {
                return response()->json([
                    'error' => true,
                    'message' =>
                    labels('admin_labels.order_item_status_cant_update_bank_verification_remain', "Order item status can't update, Bank verification is remain from transactions for this order."),
                    'data' => [],
                ]);
            }
        }

        $current_status = fetchDetails('order_items', ['seller_id' => $request->input('seller_id'), 'order_id' => $request->input('order_id')], ['active_status', 'delivery_boy_id']);

        $awaitingPresent = false;

        foreach ($current_status as $item) {
            if ($item->active_status === 'awaiting') {
                $awaitingPresent = true;
                break;
            }
        }

        // delivery boy update here
        $message = '';
        $delivery_error = false;
        $delivery_boy_updated = 0;
        $delivery_boy_id = $request->filled('deliver_by') ? $request->input('deliver_by') : 0;

        // validate delivery boy when status is shipped

        if ($request->filled('status') && $request->input('status') === 'shipped') {
            if (!isset($current_status[0]->delivery_boy_id) || empty($current_status[0]->delivery_boy_id) || $current_status[0]->delivery_boy_id == 0) {
                if (!isset($delivery_boy_id) && empty($delivery_boy_id)) {
                    return response()->json([
                        'error' => true,
                        'message' =>
                        labels('admin_labels.select_delivery_boy_to_mark_order_shipped', 'Please select a delivery boy to mark this order as shipped.'),
                        'data' => [],
                    ]);
                }
            }
        }

        if (!empty($delivery_boy_id)) {
            if ($awaitingPresent) {
                return response()->json([
                    'error' => true,
                    'message' =>
                    labels('admin_labels.delivery_boy_cant_assign_to_awaiting_orders', "Delivery Boy can't assign to awaiting orders ! please confirm the order first."),
                    'data' => [],
                ]);
            } else {

                $delivery_boy = fetchDetails('users', ['id' => trim($delivery_boy_id)], 'id');
                if (empty($delivery_boy)) {
                    return response()->json([
                        'error' => true,
                        'message' => "Invalid Delivery Boy",
                        'data' => [],
                    ]);
                } else {

                    if (isset($order_items[0]->delivery_boy_id) && !empty($order_items[0]->delivery_boy_id)) {
                        $user_res = fetchDetails('users', "", ['fcm_id', 'username'], "", "", "", "", "id", array_column($order_items, "delivery_boy_id"));
                        $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                            ->where('user_fcm.user_id', $order_items[0]->delivery_boy_id)
                            ->where('users.is_notification_on', 1)
                            ->select('user_fcm.fcm_id')
                            ->get();
                    } else {
                        $user_res = fetchDetails('users', ['id' => $delivery_boy_id], ['fcm_id', 'username']);
                        $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                            ->where('user_fcm.user_id', $delivery_boy_id->delivery_boy_id)
                            ->where('users.is_notification_on', 1)
                            ->select('user_fcm.fcm_id')
                            ->get();
                    }
                    $fcm_ids = array();
                    foreach ($results as $result) {
                        if (is_object($result)) {
                            $fcm_ids[] = $result->fcm_id;
                        }
                    }
                    if (isset($user_res[0]) && !empty($user_res[0])) {
                        //custom message
                        $current_delivery_boy = array_column($order_items, "delivery_boy_id");

                        $custom_notification = fetchDetails('custom_messages', $type, '*');
                        if (!empty($current_delivery_boy[0]) && count($current_delivery_boy) > 1) {
                            for ($i = 0; $i < count($order_items); $i++) {
                                $username = isset($user_res[$i]->username) ? $user_res[$i]->username : '';
                                $hashtag_customer_name = '< customer_name >';
                                $hashtag_order_id = '< order_item_id >';
                                $hashtag_application_name = '< application_name >';
                                $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                                $hashtag = html_entity_decode($string);
                                $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($username, $order_items[0]->order_id, $app_name), $hashtag);
                                $message = outputEscaping(trim($data, '"'));
                                $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $username . ' ' . 'Order status updated to' . $request->input('status') . ' for order ID #' . $order_items[0]->order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
                                $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Order status updated";
                                $order_id = $order_items[0]->order_id;
                                $fcmMsg = array(
                                    'title' => "$title",
                                    'body' => "$customer_msg",
                                    'type' => "order",
                                    'order_id' => "$order_id",
                                    'store_id' => "$store_id",
                                );
                            }
                            $message = 'Delivery Boy Updated.';
                            $delivery_boy_updated = 1;
                        } else {
                            if (isset($order_items[0]->delivery_boy_id) && $order_items[0]->delivery_boy_id == $request->input('deliver_by')) {

                                $custom_notification = fetchDetails('custom_messages', $type, '*');
                                $hashtag_customer_name = '< customer_name >';
                                $hashtag_order_id = '< order_item_id >';
                                $hashtag_application_name = '< application_name >';
                                $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                                $hashtag = html_entity_decode($string);
                                $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_items[0]->order_id, $app_name), $hashtag);
                                $message = outputEscaping(trim($data, '"'));
                                $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $user_res[0]->username . ' ' . 'Order status updated to' . $request->input('status') . ' for order ID #' . $order_items[0]->order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
                                $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Order status updated";
                                $order_id = $order_items[0]->order_id;
                                $fcmMsg = array(
                                    'title' => "$title",
                                    'body' => "$customer_msg",
                                    'type' => "order",
                                    'order_id' => "$order_id",
                                    'store_id' => "$store_id",
                                );
                                $delivery_boy_updated = 1;
                            } else {
                                $custom_notification = fetchDetails('custom_messages', ['type' => "delivery_boy_order_deliver"], '*');

                                $hashtag_customer_name = '< customer_name >';
                                $hashtag_order_id = '< order_id >';
                                $hashtag_application_name = '< application_name >';
                                $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                                $hashtag = html_entity_decode($string);
                                $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_items[0]->order_id, $app_name), $hashtag);
                                $message = outputEscaping(trim($data, '"'));
                                $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $user_res[0]->username . ' ' . ' you have new order to be deliver order ID #' . $order_items[0]->order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
                                $order_id = $order_items[0]->order_id;
                                $title = (!empty($custom_notification)) ? $custom_notification[0]->title : " Order status updated";
                                $fcmMsg = array(
                                    'title' => "$title",
                                    'body' => "$customer_msg",
                                    'type' => "order",
                                    'order_id' => "$order_id",
                                    'store_id' => "$store_id"
                                );
                                $message = 'Delivery Boy Updated.';
                                $delivery_boy_updated = 1;
                            }
                        }
                    }
                    if (!empty($fcm_ids)) {
                        sendNotification('', $fcm_ids, $fcmMsg);
                    }

                    if (updateOrder(['delivery_boy_id' => $delivery_boy_id], $order_itam_ids, false, 'order_items')) {
                        $delivery_error = false;
                    }
                }
            }
        }

        $item_ids = implode(",", $order_itam_ids);

        $res = validateOrderStatus($item_ids, $request->input('status'));

        if ($res['error']) {
            return response()->json([
                'error' => $delivery_boy_updated == 1 ? false : true,
                'message' => ($request->filled('status') && $delivery_error == false) ? $message . $res['message'] : $message,
                'data' => [],
            ]);
        }

        if (!empty($order_items)) {
            for ($j = 0; $j < count($order_items); $j++) {
                $order_item_id = $order_items[$j]->id;
                /* velidate bank transfer method status */

                if ($order_method[0]->payment_method == 'bank_transfer') {

                    if ($request->input('status') != 'cancelled' && (empty($bank_receipt) || strtolower($transaction_status[0]->status) != 'success' || $bank_receipt[0]->status == "0" || $bank_receipt[0]->status == "1")) {
                        return response()->json([
                            'error' => true,
                            'message' =>
                            labels('admin_labels.order_item_status_cant_update_bank_verification_remain', 'Order item status can not update, Bank verification is remain from transactions for this order.'),
                            'data' => [],
                        ]);
                    }
                }

                // processing order items

                $order_item_res = DB::table('order_items as oi')
                    ->selectRaw('*, (Select count(id) from order_items where order_id = oi.order_id ) as order_counter')
                    ->selectRaw('(Select count(active_status) from order_items where active_status ="cancelled" and order_id = oi.order_id ) as order_cancel_counter')
                    ->selectRaw('(Select count(active_status) from order_items where active_status ="returned" and order_id = oi.order_id ) as order_return_counter')
                    ->selectRaw('(Select count(active_status) from order_items where active_status ="delivered" and order_id = oi.order_id ) as order_delivered_counter')
                    ->selectRaw('(Select count(active_status) from order_items where active_status ="processed" and order_id = oi.order_id ) as order_processed_counter')
                    ->selectRaw('(Select count(active_status) from order_items where active_status ="shipped" and order_id = oi.order_id ) as order_shipped_counter')
                    ->selectRaw('(Select status from orders where id = oi.order_id ) as order_status')
                    ->where('id', $order_item_id)
                    ->get()
                    ->toArray();

                if (updateOrder(['status' => $request->input('status')], ['id' => $order_item_res[0]->id], true, 'order_items')) {
                    updateOrder(['active_status' => $request->input('status')], ['id' => $order_item_res[0]->id], false, 'order_items');
                    process_refund($order_item_res[0]->id, $request->input('status'), 'order_items');
                    if (trim($request->input('status')) == 'cancelled' || trim($request->input('status')) == 'returned') {
                        $data = fetchDetails('order_items', ['id' => $order_item_id], ['product_variant_id', 'quantity', 'order_type']);

                        if ($data[0]->order_type == 'regular_order') {
                            updateStock($data[0]->product_variant_id, $data[0]->quantity, 'plus');
                        }
                        if ($data[0]->order_type == 'combo_order') {
                            updateComboStock($data[0]->product_variant_id, $data[0]->quantity, 'plus');
                        }
                    }

                    if (($order_item_res[0]->order_counter == intval($order_item_res[0]->order_cancel_counter) + 1 && $request->input('status') == 'cancelled') || ($order_item_res[0]->order_counter == intval($order_item_res[0]->order_return_counter) + 1 && $request->input('status') == 'returned') || ($order_item_res[0]->order_counter == intval($order_item_res[0]->order_delivered_counter) + 1 && $request->input('status') == 'delivered') || ($order_item_res[0]->order_counter == intval($order_item_res[0]->order_processed_counter) + 1 && $request->input('status') == 'processed') || ($order_item_res[0]->order_counter == intval($order_item_res[0]->order_shipped_counter) + 1 && $request->input('status') == 'shipped')) {
                        /* process the refer and earn */
                        $user = fetchDetails('orders', ['id' => $order_item_res[0]->order_id], 'user_id');
                        $user_id = $user[0]->user_id;
                        $response = processReferralBonus($user_id, $order_item_res[0]->order_id, $request->input('status'));
                    }
                }
                // Update login id in order_item table
                updateDetails(['updated_by' => auth()->id()], ['order_id' => $order_item_res[0]->order_id, 'seller_id' => $order_item_res[0]->seller_id], 'order_items');
            }

            $user = fetchDetails('orders', ['id' => $order_item_res[0]->order_id], 'user_id');
            $user_res = fetchDetails('users', ['id' => $user[0]->user_id], ['username', 'fcm_id']);
            $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                ->where('user_fcm.user_id', $user[0]->user_id)
                ->where('users.is_notification_on', 1)
                ->select('user_fcm.fcm_id')
                ->get();
            $fcm_ids = array();
            foreach ($results as $result) {
                if (is_object($result)) {
                    $fcm_ids[] = $result->fcm_id;
                }
            }
            $custom_notification = fetchDetails('custom_messages', $type, '*');
            $hashtag_customer_name = '< customer_name >';
            $hashtag_order_id = '< order_item_id >';
            $hashtag_application_name = '< application_name >';
            $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
            $hashtag = html_entity_decode($string);
            $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_item_res[0]->id, $app_name), $hashtag);
            $message = outputEscaping(trim($data, '"'));
            $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $user_res[0]->username . ' Order status updated to' . $request->input('val') . ' for order ID #' . $order_item_res[0]->order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';

            $title = (!empty($custom_notification)) ? $custom_notification[0]->title : " Order status updated";
            $order_id = $order_item_res[0]->order_id;

            $fcmMsg = array(
                'title' => "$title",
                'body' => "$customer_msg",
                'type' => "order",
                'order_id' => "$order_id",
                'store_id' => "$store_id",
            );
            $registrationIDs_chunks = array_chunk($fcm_ids, 1000);
            sendNotification('', $registrationIDs_chunks, $fcmMsg);

            $seller_id = Seller::where('id', $order_item_res[0]->seller_id)->value('user_id');
            $seller_res = fetchDetails('users', ['id' => $seller_id], ['username', 'fcm_id']);

            $seller_fcm_ids = array();
            $seller_results = fetchDetails('user_fcm', ['user_id' => $seller_id], 'fcm_id');
            foreach ($seller_results as $result) {
                if (is_object($result)) {
                    $seller_fcm_ids[] = $result->fcm_id;
                }
            }
            if (!empty($seller_res[0]->fcm_id)) {
                $hashtag_customer_name = '< customer_name >';
                $hashtag_order_id = '< order_item_id >';
                $hashtag_application_name = '< application_name >';
                $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                $hashtag = html_entity_decode($string);
                $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($seller_res[0]->username, $order_item_res[0]->id, $app_name), $hashtag);
                $message = outputEscaping(trim($data, '"'));
                $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $seller_res[0]->username . ' Order status updated to ' . $request->input('status') . ' for your order ID #' . $order_item_res[0]->order_id . ' please take note of it! Regards ' . $app_name . '';

                $title = (!empty($custom_notification)) ? $custom_notification[0]->title : " Order status updated";
                $order_id = $order_item_res[0]->order_id;
                $fcmMsg = array(
                    'title' => "$title",
                    'body' => "$customer_msg",
                    'type' => "order",
                    'order_id' => "$order_id",
                    'store_id' => "$store_id",
                );
                $seller_registrationIDs_chunks = array_chunk($seller_fcm_ids, 1000);
                sendNotification('', $seller_registrationIDs_chunks, $fcmMsg);
            }

            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.status_updated_successfully', 'Status updated successfully.'),
                'data' => [],
            ]);
        }
    }

    public function update_order_tracking(Request $request)
    {
        $rules = [
            'courier_agency' => 'required|string',
            'tracking_id' => 'required',
            'url' => 'required|url',
            'order_id' => 'required|numeric|exists:orders,id',
            'seller_id' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $order_id = $request->input('order_id');
        $order_item_id = $request->input('order_item_id');
        $seller_id = $request->input('seller_id');
        $courier_agency = $request->input('courier_agency');
        $tracking_id = $request->input('tracking_id');
        $url = $request->input('url');

        $order_item_ids = fetchDetails('order_items', ['order_id' => $order_id, 'seller_id' => $seller_id], 'id');

        foreach ($order_item_ids as $ids) {
            $data = [
                'order_id' => $order_id,
                'order_item_id' => $ids->id,
                'courier_agency' => $courier_agency,
                'tracking_id' => $tracking_id,
                'url' => $url,
            ];

            if (isExist(['order_item_id' => $ids->id, 'order_id' => $order_id], 'order_trackings', null)) {
                if (updateDetails($data, ['order_id' => $order_id, 'order_item_id' => $ids->id], 'order_trackings') == TRUE) {
                    $response['error'] = false;
                    $response['message'] =
                        labels('admin_labels.tracking_details_update_successfully', 'Tracking details Update Successfuly.');
                } else {
                    $response['error'] = true;
                    $response['message'] =
                        labels('admin_labels.tracking_details_update_failed', 'Not Updated. Try again later.');
                }
            } else {
                if (OrderTracking::create($data)) {
                    $response['error'] = false;
                    $response['message'] =
                        labels('admin_labels.tracking_details_insert_successfully', 'Tracking details Insert Successfuly.');
                } else {
                    $response['error'] = true;
                    $response['message'] =
                        labels('admin_labels.tracking_details_insert_failed', 'Not Inserted. Try again later.');
                }
            }
        }
        return response()->json($response);
    }

    public function create_shiprocket_order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pickup_location' => 'required',
            'parcel_weight' => 'required',
            'parcel_height' => 'required',
            'parcel_breadth' => 'required',
            'parcel_length' => 'required',
        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->first(),
            ];
            return response()->json($response);
        }

        $request['order_items'] = json_decode($request['order_items'][0], 1);
        $shiprocket = new Shiprocket();
        $order_items = $request['order_items'];
        $items = [];
        $subtotal = 0;
        $order_id = 0;

        $pickup_location_pincode = fetchDetails('pickup_locations', ['pickup_location' => $request['pickup_location']], 'pincode');
        $user_data = fetchDetails('users', ['id' => $request['user_id']], ['username', 'email']);
        $order_data = fetchDetails('orders', ['id' => $request['order_id']], ['created_at', 'address_id', 'mobile', 'payment_method', 'delivery_charge']);
        $address_data = fetchDetails('addresses', ['id' => $order_data[0]->address_id], ['address', 'city_id', 'pincode', 'state', 'country']);
        $city_data = fetchDetails('cities', ['id' => $address_data[0]->city_id], 'name');

        $availibility_data = [
            'pickup_postcode' => $pickup_location_pincode[0]->pincode,
            'delivery_postcode' => $address_data[0]->pincode,
            'cod' => (strtoupper($order_data[0]->payment_method) == 'COD') ? '1' : '0',
            'weight' => $request['parcel_weight'],
        ];

        $check_deliveribility = $shiprocket->check_serviceability($availibility_data);

        $get_currier_id = shiprocket_recomended_data($check_deliveribility);

        foreach ($order_items as $row) {
            if ($row['pickup_location'] == $_POST['pickup_location'] && $row['seller_id'] == $_POST['shiprocket_seller_id']) {
                $order_item_id[] = $row['id'];
                $order_id .= '-' . $row['id'];
                $order_item_data = fetchDetails('order_items', ['id' => $row['id']], 'sub_total');
                $subtotal += $order_item_data[0]->sub_total;
                if (isset($row['product_variants']) && !empty($row['product_variants'])) {
                    $sku = $row['product_variants'][0]['sku'];
                } else {
                    $sku = $row['sku'];
                }
                $row['product_slug'] = strlen($row['product_slug']) > 8 ? substr($row['product_slug'], 0, 8) : $row['product_slug'];
                $temp['name'] = $row['pname'];
                $temp['sku'] = isset($sku) && !empty($sku) ? $sku : $row['product_slug'];
                $temp['units'] = $row['quantity'];
                $temp['selling_price'] = $row['price'];
                $temp['discount'] = $row['discounted_price'];
                $temp['tax'] = $row['tax_amount'];
                array_push($items, $temp);
            }
        }

        $order_item_ids = implode(",", $order_item_id);
        $random_id = '-' . rand(10, 10000);
        $delivery_charge = (strtoupper($order_data[0]->payment_method) == 'COD') ? $order_data[0]->delivery_charge : 0;
        $create_order = [
            'order_id' => $request['order_id'] . $order_id . $random_id,
            'order_date' => $order_data[0]->created_at,
            'pickup_location' => $request['pickup_location'],
            'billing_customer_name' => $user_data[0]->username,
            'billing_last_name' => "",
            'billing_address' => $address_data[0]->address,
            'billing_city' => $city_data[0]->name,
            'billing_pincode' => $address_data[0]->pincode,
            'billing_state' => $address_data[0]->state,
            'billing_country' => $address_data[0]->country,
            'billing_email' => $user_data[0]->email,
            'billing_phone' => $order_data[0]->mobile,
            'shipping_is_billing' => true,
            'order_items' => $items,
            'payment_method' => (strtoupper($order_data[0]->payment_method) == 'COD') ? 'COD' : 'Prepaid',
            'sub_total' => $subtotal + $delivery_charge,
            'length' => $request['parcel_length'],
            'breadth' => $request['parcel_breadth'],
            'height' => $request['parcel_height'],
            'weight' => $request['parcel_weight'],
        ];

        $response = $shiprocket->create_order($create_order);

        if (isset($response['status_code']) && $response['status_code'] == 1) {
            $courier_company_id = $get_currier_id['courier_company_id'];
            $order_tracking_data = [
                'order_id' => $request['order_id'],
                'order_item_id' => $order_item_ids,
                'shiprocket_order_id' => $response['order_id'],
                'shipment_id' => $response['shipment_id'],
                'courier_company_id' => $courier_company_id,
                'pickup_status' => 0,
                'pickup_scheduled_date' => '',
                'pickup_token_number' => '',
                'status' => 0,
                'others' => '',
                'pickup_generated_date' => '',
                'data' => '',
                'date' => '',
                'manifest_url' => '',
                'label_url' => '',
                'invoice_url' => '',
                'is_canceled' => 0,
                'tracking_id' => '',
                'url' => ''
            ];
            OrderTracking::create($order_tracking_data);
        }
        if (isset($response['status_code']) && $response['status_code'] == 1) {
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.shiprocket_order_created_successfully', 'Shiprocket order created successfully');
            $response['data'] = $response;
        } else {
            $response['error'] = true;
            $response['message'] =
                labels('admin_labels.shiprocket_order_not_created_successfully', 'Shiprocket order not created successfully');
            $response['data'] = $response;
        }
        return response()->json($response);
    }

    public function generate_awb(Request $request)
    {
        $res = generate_awb($request['shipment_id']);
        if (!empty($res) && $res['awb_assign_status'] == 1) {
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.awb_generated_successfully', 'AWB generated successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] =
                labels('admin_labels.awb_not_generated', 'AWB not generated');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function send_pickup_request(Request $request)
    {
        $res = send_pickup_request($request['shipment_id']);

        if (!empty($res)) {
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.request_send_successfully', 'Request send successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] =
                labels('admin_labels.request_not_sent', 'Request not sent');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function cancel_shiprocket_order(Request $request)
    {
        $res = cancel_shiprocket_order($request['shiprocket_order_id']);
        if (!empty($res) && $res['status'] == 200) {
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.order_cancelled_successfully', 'Order cancelled successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] =
                labels('admin_labels.order_not_cancelled', 'Order not cancelled');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function generate_label(Request $request)
    {
        $res = generate_label($request['shipment_id']);
        if (!empty($res)) {
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.label_generated_successfully', 'Label generated successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] =
                labels('admin_labels.label_not_generated', 'Label not generated');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function generate_invoice(Request $request)
    {
        $res = generate_invoice($request['order_id']);
        if (!empty($res) && isset($res['is_invoice_created']) && $res['is_invoice_created'] == 1) {
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.invoice_generated_successfully', 'Invoice generated successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] =
                labels('admin_labels.invoice_not_generated', 'Invoice not generated');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function send_digital_product(Request $request)
    {

        $rules = [
            'message' => 'required',
            'subject' => 'required',
            'pro_input_file' => 'required',
        ];

        $messages = [
            'pro_input_file.required' => labels('admin_labels.select_attachment_file', 'Please select Attachment file.'),
        ];

        if ($response = validatePanelRequest($request, $rules, $messages)) {
            return $response;
        }

        $message = str_replace('\r\n', '&#13;&#10;', $request['message']);

        $attachment = asset(config('constants.MEDIA_PATH') . $request['pro_input_file']);
        $to = $request['email'];
        $subject = $request['subject'];

        $mail = sendDigitalProductMail($to, $subject, $message, $attachment);

        if ($mail['error'] == true) {
            $response['error'] = true;
            $response['message'] =
                labels('admin_labels.mail_not_sent_try_manually', 'Cannot send mail. You can try to send mail manually.');
            $response['data'] = $mail['message'];
            return response()->json($response);
        } else {
            $response['error'] = false;
            $response['message'] = 'Mail sent successfully.';
            $response['data'] = array();

            updateDetails(['active_status' => 'delivered'], ['id' => $request['order_item_id']], 'order_items');
            updateDetails(['is_sent' => 1], ['id' => $request['order_item_id']], 'order_items');
            $data = [
                'order_id' => $request['order_id'],
                'order_item_id' => $request['order_item_id'],
                'subject' => $request['subject'],
                'message' => $request['message'],
                'file_url' => $request['pro_input_file'],
            ];
            DigitalOrdersMail::create($data);

            return response()->json($response);
        }
    }


    public function destroyReceipt($id)
    {

        if (empty($id)) {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.something_went_wrong', 'Something went wrong');
        }

        $data = fetchDetails('order_bank_transfers', ['id' => $id], '*');

        if ($data[0]->disk == 's3') {
            // Specify the path and disk from which you want to delete the file

            $path = $data[0]->attachments;
            // Call the removeFile method to delete the file
            removeMediaFile($path, $data[0]->disk);
            deleteDetails(['id' => $id], "order_bank_transfers");

            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.deleted_successfully', 'Deleted Successfully');
        } else if (deleteDetails(['id' => $id], "order_bank_transfers")) {
            $response['error'] = false;
            $response['message'] =
                labels('admin_labels.deleted_successfully', 'Deleted Successfully');
        } else {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.something_went_wrong', 'Something went wrong');
        }
        return response()->json($response);
    }

    public function update_receipt_status(Request $request)
    {

        $rules = [
            'order_id' => 'required|exists:orders,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        } else {
            $order_id = $request->input('order_id');
            $store_id = fetchDetails('orders', ['id' => $order_id], 'store_id');
            $store_id = isset($store_id) && !empty($store_id) ? $store_id[0]->store_id : "";
            $user_id = $request->input('user_id');
            $status = $request->input('status');

            if (updateDetails(['status' => $status], ['order_id' => $order_id], 'order_bank_transfers')) {
                if ($status == 1) {
                    $status = "Rejected";
                } else if ($status == 2) {
                    $status = "Accepted";
                    updateDetails(['active_status' => 'received'], ['order_id' => $order_id], 'order_items');
                    $status = json_encode(array(array('received', date("d-m-Y h:i:sa"))));
                    updateDetails(['status' => $status], ['order_id' => $order_id], 'order_items', false);
                } else {
                    $status = "Pending";
                }
                //custom message
                $custom_notification = fetchDetails('custom_messages', ['type' => "bank_transfer_receipt_status"], '*');
                $hashtag_status = '< status >';
                $hashtag_order_id = '< order_id >';
                $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                $hashtag = html_entity_decode($string);
                $data = str_replace(array($hashtag_status, $hashtag_order_id), array($status, $order_id), $hashtag);
                $message = outputEscaping(trim($data, '"'));
                $customer_title = (!empty($custom_notification)) ? $custom_notification[0]->title : 'Bank Transfer Receipt Status';
                $customer_msg = (!empty($custom_notification)) ? $message : 'Bank Transfer Receipt' . $status . ' for order ID: ' . $order_id;
                $user = fetchDetails("users", ['id' => $user_id], ['email', 'fcm_id']);

                $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                    ->where('user_fcm.user_id', $user_id)
                    ->where('users.is_notification_on', 1)
                    ->select('user_fcm.fcm_id')
                    ->get();
                foreach ($results as $result) {
                    if (is_object($result)) {
                        $fcm_ids[] = $result->fcm_id;
                    }
                }

                if (!empty($fcm_ids)) {
                    $fcmMsg = array(
                        'title' => "$customer_title",
                        'body' => "$customer_msg",
                        'type' => "order",
                        'store_id' => "$store_id",
                    );
                    sendNotification('', $fcm_ids, $fcmMsg);
                }
                $response['error'] = false;
                $response['message'] =
                    labels('admin_labels.updated_successfully', 'Updated Successfully');
            } else {
                $response['error'] = true;
                $response['message'] = labels('admin_labels.something_went_wrong', 'Something went wrong');
            }
            return response()->json($response);
        }
    }
    public function destroy($id)
    {

        $delete = [
            "order_items" => 0,
            "orders" => 0,
            "order_bank_transfer" => 0
        ];

        $orders = DB::table('order_items as oi')
            ->join('orders as o', 'o.id', '=', 'oi.order_id')
            ->where('oi.order_id', $id)
            ->get()
            ->toArray();

        if (!empty($orders)) {
            // delete orders

            if (deleteDetails(['order_id' => $id], 'order_items')) {
                $delete['order_items'] = 1;
            }
            if (deleteDetails(['id' => $id], 'orders')) {
                $delete['orders'] = 1;
            }
            if (deleteDetails(['order_id' => $id], 'order_bank_transfer')) {
                $delete['order_bank_transfer'] = 1;
            }
        }

        if ($delete['order_items']) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.deleted_successfully', 'Deleted Successfully'),
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }


    public function order_item_destroy($id)
    {
        $delete = array(
            "order_items" => 0,
            "orders" => 0,
            "order_bank_transfer" => 0
        );
        /* check order items */
        $order_items = fetchDetails('order_items', ['id' => $id], ['id', 'order_id']);
        if (deleteDetails(['id' => $id], 'order_items')) {
            $delete['order_items'] = 1;
        }
        $res_order_id = array_values(array_unique(array_column($order_items, "order_id")));

        for ($i = 0; $i < count($res_order_id); $i++) {
            $orders = DB::table('order_items as oi')
                ->rightJoin('orders as o', 'o.id', '=', 'oi.order_id')
                ->where('oi.order_id', $res_order_id)
                ->get();

            if (empty($orders)) {
                // delete orders
                if (deleteDetails(['id' => $res_order_id[$i]], 'orders')) {
                    $delete['orders'] = 1;
                }
                if (deleteDetails(['order_id' => $res_order_id[$i]], 'order_bank_transfer')) {
                    $delete['order_bank_transfer'] = 1;
                }
            }
        }

        if ($delete['order_items'] == true) {
            return response()->json([
                'error' => false,
                'message' => labels('admin_labels.deleted_successfully', 'Deleted Successfully'),
            ]);
        } else {
            return response()->json(['error' => labels('admin_labels.something_went_wrong', 'Something went wrong')]);
        }
    }
}
