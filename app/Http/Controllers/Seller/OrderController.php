<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Libraries\Shiprocket;
use App\Models\DigitalOrdersMail;
use App\Models\Order;
use App\Models\OrderItems;
use App\Models\OrderTracking;
use App\Models\Parcel;
use App\Models\Parcelitem;
use App\Models\Seller;
use App\Models\UserFcm;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use LaravelDaily\Invoices\Invoice;
use LaravelDaily\Invoices\Classes\Buyer;
use LaravelDaily\Invoices\Classes\InvoiceItem;
use LaravelDaily\Invoices\Classes\Party;

class OrderController extends Controller
{
    public function index()
    {
        $store_id = getStoreId();
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');
        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
        return view('seller.pages.tables.manage_orders', compact('currency', 'store_id', 'seller_id'));
    }


    public function generatInvoicePDF($id, $seller_id = '')
    {

        $user_id = Auth::id();

        if (isset($user_id) && !empty($user_id)) {
            $seller_id = Seller::where('user_id', $user_id)->value('id');
        }

        $res = getOrderDetails(['o.id' => $id, 'oi.seller_id' => $seller_id], true);
        $seller_ids = array_values(array_unique(array_column($res, "seller_id")));
        $seller_user_ids = [];
        foreach ($seller_ids as $id) {
            $seller_user_ids[] = Seller::where('id', $id)->value('user_id');
        }

        if (!empty($res)) {
            $items = [];
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
                $temp['delivery_boy'] = $row->delivery_boy;
                $temp['mobile_number'] = $row->mobile_number;
                $temp['active_status'] = $row->oi_active_status;
                $temp['hsn_code'] = isset($row->hsn_code) ? $row->hsn_code : '';
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
                'promo_code' => isset($promo_code) && !empty($promo_code) ? $promo_code[0]->promo_code : '',
                'promo_code_discount' => isset($promo_code) && !empty($promo_code) ? $promo_code[0]->discount : '',
                'promo_code_discount_type' => isset($promo_code) && !empty($promo_code) ? $promo_code[0]->discount_type : '',
            ],
        ]);

        $client = new Party([
            'custom_fields' => $sellers,
        ]);

        $invoice = Invoice::make()
            ->buyer($customer)
            ->seller($client)
            ->logo(public_path('/storage/user_image//1697269515.jpg'))
            ->setCustomData($items)
            ->addItem($item1)
            ->template('invoice');


        return $invoice->stream();
    }
    public function generatParcelInvoicePDF($id, $from_app = false)
    {
        $user_id = Auth::id();
        $seller_id = Seller::where('user_id', $user_id)->value('id');

        $parcels = fetchDetails('parcels', ['id' => $id]);

        $parcel_items = fetchDetails('parcel_items', ['parcel_id' => $id]);

        $orders = fetchOrderItems('', '', '', '', 10, 0, 'id', 'DESC', '', '', '', $seller_id, $parcels[0]->order_id, $parcels[0]->store_id);

        $parcel_details = viewAllParcels('', $id, '', '0', '10', 'DESC');
        $parcel_details = json_decode($parcel_details->getContent(), true);
        $parcel_details = $parcel_details['data'] ?? [];
        // dd($parcel_details);
        $res = getOrderDetails(['o.id' => $parcels[0]->order_id], false);
        $seller_ids = array_values(array_unique(array_column($res, "seller_id")));
        // dd($parcel_items);
        $seller_user_ids = [];
        foreach ($seller_ids as $id) {
            $seller_user_ids[] = Seller::where('id', $id)->value('user_id');
        }
        if (!empty($res)) {

            $items = [];
            foreach ($parcel_items as $key => $row) {
                foreach ($orders['order_data'] as $order) {
                    // dd($order);
                    if ($order->id == $row->order_item_id) {
                        $parcel_items[$key]->pname = $order->product_name;
                        $parcel_items[$key]->seller_id = $order->seller_id;
                        $parcel_items[$key]->price = $order->price;
                        $parcel_items[$key]->product_id = $order->product_id;
                        $parcel_items[$key]->product_variant_id = $order->product_variant_id;
                        $parcel_items[$key]->discounted_price = $order->discounted_price;
                        $parcel_items[$key]->tax_ids = $order->tax_ids;
                        $parcel_items[$key]->tax_percent = $order->tax_percent;
                        $parcel_items[$key]->tax_amount = $order->tax_amount;
                        $parcel_items[$key]->delivery_boy = $order->deliver_by;
                        $parcel_items[$key]->delivery_boy_id = $order->delivery_boy_id;
                        $parcel_items[$key]->active_status = $order->active_status;
                        $parcel_items[$key]->hsn_code = $order->hsn_code ?? "";
                        $parcel_items[$key]->is_prices_inclusive_tax = $order->is_prices_inclusive_tax;
                    }
                }
            }
        }

        $item1 = InvoiceItem::make('Service 1')->pricePerUnit(2);
        $sellers = [
            'seller_ids' => $seller_ids,
            'seller_user_ids' => $seller_user_ids,
            'mobile_number' => $res[0]->mobile_number,
        ];
        $client = new Party([
            'custom_fields' => $sellers,
        ]);
        $customer = new Buyer([
            'name' => $res[0]->uname,
            'custom_fields' => [
                'address' => $res[0]->address,
                'order_id' => $res[0]->id,
                'date_added' => $res[0]->created_at,
                'store_id' => $res[0]->store_id,
                'payment_method' => $res[0]->payment_method,
                'discount' => $res[0]->discount,
                'parcel_details' => $parcel_details,
            ],
        ]);


        $invoice = Invoice::make()
            ->buyer($customer)
            ->seller($client)
            ->logo(public_path('/storage/user_image//1697269515.jpg'))
            ->setCustomData($parcel_items)
            ->addItem($item1)
            ->template('parcel_invoice');

        if ($from_app == false) {
            return $invoice->stream();
        } else {
            return view('vendor.invoices.templates.parcel_invoice', compact('client', 'customer', 'sellers', 'item1', 'items', 'invoice', 'parcel_details'));
        }
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
        $userId = request()->input('user_id');
        $orderStatus = request()->input('order_status');
        $paymentMethod = request()->input('payment_method');
        $orderType = request()->input('order_type');
        $sellerId = Auth::id();
        $seller_id = Seller::where('user_id', $sellerId)->value('id');

        $multipleWhere = [];

        if (!empty($search)) {
            $multipleWhere = [
                'o.id' => $search,
                'oi.id' => $search,
                'u.username' => $search,
                'u.mobile' => $search,
                'oi.seller_id' => $search,
                'oi.user_id' => $search,
                'oi.product_name' => $search,
                'oi.created_at' => $search,
            ];
        }

        // Count query
        $countQuery = DB::table('order_items as oi')
            ->leftJoin('users as u', 'u.id', '=', 'oi.delivery_boy_id')
            ->leftJoin('seller_data as sd', 'sd.id', '=', 'oi.seller_id')
            ->leftJoin('users as us', 'us.id', '=', 'sd.user_id')
            ->leftJoin('orders as o', 'o.id', '=', 'oi.order_id')
            ->leftJoin('product_variants as v', 'oi.product_variant_id', '=', 'v.id')
            ->leftJoin('products as p', 'v.product_id', '=', 'p.id')
            ->leftJoin('users as un', 'o.user_id', '=', 'un.id')
            ->selectRaw('COUNT(o.id) as total');

        $countQuery->where('oi.store_id', $store_id);
        $countQuery->where('o.is_pos_order', 0);
        if ($startDate && $endDate) {
            $countQuery->whereDate('oi.created_at', '>=', $startDate)
                ->whereDate('oi.created_at', '<=', $endDate);
        }

        if ($search) {
            $countQuery->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }

        if ($deliveryBoyId) {
            $countQuery->where('oi.delivery_boy_id', $deliveryBoyId);
        }

        if ($seller_id) {
            $countQuery->where('oi.seller_id', $seller_id)
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
                'p.id as product_id'
            );

        $searchQuery->where('oi.store_id', $store_id);
        $searchQuery->where('o.is_pos_order', 0);
        if ($startDate && $endDate) {
            $searchQuery->whereDate('oi.created_at', '>=', $startDate)
                ->whereDate('oi.created_at', '<=', $endDate);
        }

        if ($search) {
            if ($search) {
                $searchQuery->where(function ($query) use ($multipleWhere) {
                    foreach ($multipleWhere as $column => $value) {
                        $query->orWhere($column, 'like', '%' . $value . '%');
                    }
                });
            }
        }

        if ($deliveryBoyId) {
            $searchQuery->where('oi.delivery_boy_id', $deliveryBoyId);
        }

        if ($seller_id) {
            $searchQuery->where('oi.seller_id', $seller_id)
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
        $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
        $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
        $count = 1;
        $language_code = get_language_code();
        foreach ($userDetails as $row) {
            // dd($row);
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
            $tempRow['payment_method'] = $row->payment_method;
            $tempRow['delivery_boy_id'] = $row->delivery_boy_id;
            $tempRow['product_variant_id'] = $row->product_variant_id;
            $tempRow['delivery_date'] = $row->updated_at;
            $tempRow['delivery_time'] = $row->delivery_time;
            $tempRow['courier_agency'] = (isset($row->courier_agency) && !empty($row->courier_agency)) ? $row->courier_agency : "";
            $tempRow['tracking_id'] = (isset($row->tracking_id) && !empty($row->tracking_id)) ? $row->tracking_id : "";
            $tempRow['url'] = (isset($row->url) && !empty($row->url)) ? $row->url : "";
            if (isset($row->items[0]->updated_by) && !empty($row->items[0]->updated_by)) {
                $updated_username = fetchDetails('users', ['id' => $row->items[0]->updated_by], 'username');
                $tempRow['updated_by'] = $updated_username[0]->username;
            } else {
                $tempRow['updated_by'] = '';
            }
            $tempRow['status'] = $status;
            $tempRow['transaction_status'] = $transaction_status;
            $tempRow['active_status'] = $active_status;
            $tempRow['mail_status'] = $mail_status;
            $tempRow['date_added'] = Carbon::parse($row->created_at)->format('d-m-Y');

            $edit_url = route('seller.orders.edit', $row->order_id);
            $delete_url = route('orders.destroy', $row->order_id);

            $action = '<div class="d-flex align-items-center">
                <a href="' . $edit_url . '" class="btn single_action_button" title="Edit">
                    <i class="bx bx-pencil mx-2"></i>
                </a>
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

    public function edit($id)
    {
        $main_order_id = $id;
        $store_id = getStoreId();
        $sellerId = Auth::id();
        $seller_id = Seller::where('user_id', $sellerId)->value('id');
        $res = getOrderDetails(['o.id' => $id, 'oi.seller_id' => $seller_id], '', '', $store_id);
        $seller_store = DB::table('seller_store')->where('user_id', $sellerId)->where('store_id', $store_id)->select('city', 'zipcode', 'deliverable_zones', 'permissions', 'deliverable_type')->get();
        $seller_zone_ids = isset($seller_store) ? explode(',', $seller_store[0]->deliverable_zones) : [];
        $deliverable_type = isset($seller_store) ? $seller_store[0]->deliverable_type : 1;
        $seller_city = isset($seller_store) ? $seller_store[0]->city : "";
        $seller_zipcode = isset($seller_store) ? $seller_store[0]->zipcode : "";

        $store_deliverability_type = fetchDetails('stores', ['id' => $store_id], 'product_deliverability_type');
        $store_deliverability_type = isset($store_deliverability_type) && !empty($store_deliverability_type) ? $store_deliverability_type[0]->product_deliverability_type : "";


        $permissions = isset($seller_store) ? $seller_store[0]->permissions : '';
        $seller_permissions = !empty($permissions) ? json_decode($permissions, true) : [];

        $is_customer_privacy_permission = (isset($seller_permissions['customer_privacy']) && $seller_permissions['customer_privacy'] == 1) ? 1 : 0;

        if ($res == null || empty($res)) {
            return view('admin.pages.views.no_data_found');
        } else {
            // not showing delivery boy if deliverable type is all

            // $delivery_res = DB::table('users as u')
            //     ->select('u.*', 'c.name as city_name')
            //     ->leftJoin('cities as c', 'u.city', '=', 'c.id')
            //     ->where(function ($query) use ($seller_zone_ids) {
            //         if (!empty($seller_zone_ids)) {
            //             foreach ($seller_zone_ids as $zone_id) {
            //                 $query->orWhereRaw("FIND_IN_SET(?, u.serviceable_zones)", [$zone_id]);
            //             }
            //         }
            //     })
            //     ->groupBy('u.id')
            //     ->get()
            //     ->toArray();

            $delivery_res = DB::table('users as u')->where('role_id', 3)
                ->select('u.*', 'c.name as city_name')
                ->leftJoin('cities as c', 'u.city', '=', 'c.id')
                ->when($deliverable_type != 1 && !empty($seller_zone_ids), function ($query) use ($seller_zone_ids) {
                    $query->where(function ($subQuery) use ($seller_zone_ids) {
                        foreach ($seller_zone_ids as $zone_id) {
                            $subQuery->orWhereRaw("FIND_IN_SET(?, u.serviceable_zones)", [$zone_id]);
                        }
                    });
                })
                ->groupBy('u.id')
                ->get()
                ->toArray();
            if ($res[0]->payment_method == "bank_transfer") {
                $bank_transfer = fetchDetails('order_bank_transfers', ['order_id' => $res[0]->order_id]);
            }

            $items = $seller = [];

            foreach ($res as $row) {

                $multipleWhere = ['seller_id' => $row->seller_id, 'order_id' => $row->id];
                $orderChargeData = DB::table('order_charges')->where($multipleWhere)->get();
                $updated_username = isset($row->updated_by) && !empty($row->updated_by) && $row->updated_by != 0 ? fetchDetails('users', ['id' => $row->updated_by], 'username') : '';
                $updated_username = isset($updated_username) && !empty($updated_username) ? $updated_username[0]->username : '';
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
                    'attachment' => isset($row->attachment) && $row->attachment != null ? asset('/storage/' . $row->attachment) : "",
                    'is_returnable' => $row->is_returnable,
                    'tax_amount' => $row->tax_amount,
                    'wallet_balance' => $row->wallet_balance,
                    'txn_id' => $row->txn_id,
                    'discounted_price' => $row->discounted_price,
                    'price' => $row->price,
                    'item_subtotal' => (strval($row->sub_total)),
                    'updated_by' => $updated_username,
                    'deliver_by' => $deliver_by,
                    'active_status' => $row->oi_active_status,
                    'product_image' => $row->product_image,
                    'product_variants' => getVariantsValuesById($row->product_variant_id),
                    'pickup_location' => $row->pickup_location,
                    'seller_otp' => $orderChargeData ?? $orderChargeData[0]->otp,
                    'seller_delivery_charge' => isset($orderChargeData[0]) ? $orderChargeData[0]->delivery_charge : 0,
                    'seller_promo_discount' => isset($orderChargeData[0]) ? $orderChargeData[0]->promo_discount : 0,
                    'is_sent' => $row->is_sent,
                    'seller_id' => $row->seller_id,
                    'download_allowed' => $row->download_allowed,
                    'user_email' => $row->user_email,
                    'user_profile' => getMediaImageUrl($row->user_profile, 'USER_IMG_PATH'),
                    'product_slug' => $row->product_slug,
                    'sku' => isset($row->product_sku) && !empty($row->product_sku) ? $row->product_sku : $row->sku,
                    'delivered_quantity' => isset($row->delivered_quantity) && !empty($row->delivered_quantity) ? $row->delivered_quantity : '',
                    'order_type' => isset($row->order_type) && !empty($row->order_type) ? $row->order_type : ''

                ];
                array_push($items, $temp);
            }
            $order_detls = $res;
            // dd($res);
            // $sellers_id = collect($res)->pluck('seller_id')->unique()->values()->all();
            $sellers_id = collect($res)->pluck('oi_seller_id')->unique()->values()->all();
            // dd($sellers_id);
            foreach ($sellers_id as $id) {
                $query = DB::table('seller_store as ss')
                    ->select(
                        'ss.store_name',
                        'ss.logo as shop_logo',
                        'ss.user_id as user_id',
                        'u.mobile as seller_mobile',
                        'u.city as seller_city',
                        'u.pincode as seller_pincode',
                        'u.email as seller_email',
                        'u.username as seller_name',
                    )
                    ->leftJoin('users as u', 'u.id', '=', 'ss.user_id')
                    ->where('ss.seller_id', $id)->get()->toArray();

                $value = [
                    'id' => $id,
                    'user_id' => $query[0]->user_id,
                    'store_name' => $query[0]->store_name,
                    'shop_logo' => getMediaImageUrl($query[0]->shop_logo, 'STORE_IMG_PATH'),
                    'seller_mobile' => $query[0]->seller_mobile,
                    'seller_pincode' => $query[0]->seller_pincode,
                    'seller_email' => $query[0]->seller_email,
                    'seller_name' => $query[0]->seller_name,
                    'seller_city' => $query[0]->seller_city,
                ];
                array_push($seller, $value);
            }
            $sellers = $seller;
            $bank_transfer = isset($bank_transfer) ? $bank_transfer : [];
            $items = $items;
            $settings = getSettings('system_settings', true);
            $settings = json_decode($settings, true);

            $shipping_method = getSettings('shipping_method', true);
            $shipping_method = json_decode($shipping_method, true);
            $currencyDetails = fetchDetails('currencies', ['is_default' => 1], 'symbol');
            $currency = !empty($currencyDetails) ? $currencyDetails[0]->symbol : '';
            $mobile_data = fetchDetails('addresses', ['id' => $order_detls[0]->address_id], 'mobile');
            $order_tracking = fetchDetails('order_trackings', ['order_id' => $main_order_id]);
            return view('seller.pages.forms.edit_orders', compact('delivery_res', 'store_id', 'order_detls', 'mobile_data', 'bank_transfer', 'items', 'settings', 'shipping_method', 'sellers', 'currency', 'order_tracking', 'is_customer_privacy_permission'));
        }
    }

    public function update_order_status(Request $request)
    {
        $sellerId = Auth::id();
        $seller_id = Seller::where('user_id', $sellerId)->value('id');
        if (isset($request->type) && $request->type == "digital") {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:received,delivered',
                'order_id' => 'required',
                'order_item_ids' => 'required|array',
            ], [
                'order_item_ids.required' => 'Please select at least one item for update order status.',
            ]);

            if ($validator->fails()) {
                $response = [
                    'error' => true,
                    'message' => $validator->errors()->all(),
                ];
                return response()->json($response);
            }
            $order_id = $request->input('order_id') ?? "";
            $status = $request->input('status') ?? "";
            $order_item_ids = $request->input('order_item_ids') ?? '';
            $order_details = fetchOrders($order_id, '', '', '', '10', '0', 'o.id', 'DESC', '', '', '', '', '', '', $seller_id);
            if (empty($order_details['order_data'])) {
                $response = [
                    'error' => true,
                    'message' => 'Order Not Found',
                ];
                return response()->json($response);
            }
            $order_details = $order_details['order_data'];
            $user_id = $order_details[0]->user_id;
            $store_id = $order_details[0]->store_id;
            $awaitingPresent = false;
            $items_to_update = $order_details[0]->order_items->filter(function ($item) use ($order_item_ids) {
                return in_array($item->id, $order_item_ids);
            });

            if ($items_to_update->isEmpty()) {
                return response()->json([
                    'error' => true,
                    'message' => 'Order Item Not Found',
                ]);
            }

            $awaitingPresent = false;

            foreach ($items_to_update as $item) {
                if ($item->active_status === 'awaiting') {
                    $awaitingPresent = true;
                    break;
                }
                if ($status != 'received' && $status != 'delivered') {
                    return response()->json([
                        'error' => true,
                        'message' => 'Invalid Status Pass',
                    ]);
                }
                if ($item->active_status == $status) {
                    $response = [
                        'error' => true,
                        'message' => "One Of This Product Already Marked As " . $status . ".",
                    ];
                    return response()->json($response);
                }
                if ($item->active_status == 'delivered' && $status != 'delivered') {
                    return response()->json([
                        'error' => true,
                        'message' => "Order Item is Delivered. You Can't Change It Again To " . $status . ".",
                    ]);
                }
            }

            if ($awaitingPresent) {
                return response()->json([
                    'error' => true,
                    'message' => "You Can Not Change Status Of Awaiting Order! Please confirm the order first.",
                ]);
            }

            // Perform the update for each item and send notification if successful
            foreach ($items_to_update as $item) {
                if (updateOrder(['status' => $status], ['id' => $item->id], true, 'order_items', '', 1)) {
                    updateOrder(['active_status' => $status], ['id' => $item->id], false, 'order_items', '', 1);
                    updateDetails(['updated_by' => auth()->id()], ['order_id' => $order_id, 'seller_id' => $seller_id], 'order_items');

                    // Customize the notification message based on status
                    $type = [
                        'type' => match ($status) {
                            'received' => "customer_order_received",
                            'processed' => "customer_order_processed",
                            'shipped' => "customer_order_shipped",
                            'delivered' => "customer_order_delivered",
                            'cancelled' => "customer_order_cancelled",
                            'returned' => "customer_order_returned",
                            default => null
                        }
                    ];

                    $settings = getSettings('system_settings', true);
                    $settings = json_decode($settings, true);
                    $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';

                    $user = fetchDetails('orders', ['id' => $order_id], 'user_id');
                    $user_res = fetchDetails('users', ['id' => $user[0]->user_id], ['username', 'fcm_id']);


                    $custom_notification = fetchDetails('custom_messages', $type, '*');
                    $hashtag_customer_name = '< customer_name >';
                    $hashtag_order_id = '< order_item_id >';
                    $hashtag_application_name = '< application_name >';
                    $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                    $hashtag = html_entity_decode($string);
                    $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_id, $app_name), $hashtag);
                    $message = outputEscaping(trim($data, '"'));
                    $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $user_res[0]->username . ' Order status updated to' . $request->input('val') . ' for order ID #' . $order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
                    $fcm_ids = array();
                    $title = (!empty($custom_notification)) ? $custom_notification[0]->title : " Order status updated";
                    $order_id = $order_id;
                    $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                        ->where('user_fcm.user_id', $user[0]->user_id)
                        ->where('users.is_notification_on', 1)
                        ->select('user_fcm.fcm_id')
                        ->get();
                    $fcm_ids = array();
                    if (!empty($results)) {
                        $fcmMsg = array(
                            'title' => "$title",
                            'body' => "$customer_msg",
                            'type' => "order",
                            'order_id' => "$order_id",
                            'store_id' => "$store_id",
                        );

                        foreach ($results as $result) {
                            if (is_object($result)) {
                                $fcm_ids[] = $result->fcm_id;
                            }
                        }
                        $user_registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                        sendNotification('', $user_registrationIDs_chunks, $fcmMsg);
                    }
                }
            }
            return response()->json([
                'error' => false,
                'message' => 'Status updated successfully.',
                'data' => [],
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'status' => 'required_without:deliver_by|in:received,processed,shipped,delivered,cancelled,returned',
                'deliver_by' => 'sometimes|nullable|numeric',
                'parcel_id' => 'required',
            ], [
                'status.required_without' => 'Please select status for updation.',
                'status.in' => 'Invalid status value.',
                'deliver_by.numeric' => 'Delivery Boy Id must be numeric.',
            ]);

            if ($validator->fails()) {
                $response = [
                    'error' => true,
                    'message' => $validator->errors()->all(),
                ];
                return response()->json($response);
            }

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
            $settings = getSettings('system_settings', true);
            $settings = json_decode($settings, true);
            $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : '';
            $parcel_id = $request->input('parcel_id') ?? "";
            $parcel = fetchDetails('parcels', ['id' => $parcel_id], '*');
            if (empty($parcel)) {
                $response = [
                    'error' => true,
                    'message' => 'Parcel Not Found',
                ];
                return response()->json($response);
            }
            $parcel_items = fetchDetails('parcel_items', ['parcel_id' => $parcel[0]->id], '*');
            $order_id = $parcel[0]->order_id;
            $order_item_data = fetchDetails('order_items', ['order_id' => $order_id], '*');
            if (empty($order_item_data)) {
                $response = [
                    'error' => true,
                    'message' => 'Order Item Not Found',
                ];
                return response()->json($response);
            }
            $user_id = $order_item_data[0]->user_id;
            $store_id = $order_item_data[0]->store_id;
            $delivery_boy_updated = 0;
            $message = '';

            $delivery_boy_id = $request->filled('deliver_by') ? $request->input('deliver_by') : 0;
            if ($request->filled('status') && $request->input('status') === 'processed') {
                if (!isset($delivery_boy_id) || empty($delivery_boy_id) || $delivery_boy_id == 0) {
                    return response()->json([
                        'error' => true,
                        'message' => labels('admin_labels.select_delivery_boy_to_mark_order_processed', 'Please select a delivery boy to mark this order as processed.'),
                        'data' => [],
                    ]);
                }
            }
            if ($request->filled('status') && $request->input('status') === 'shipped') {
                if ((!isset($order_item_data[0]->delivery_boy_id) || empty($order_item_data[0]->delivery_boy_id) || $order_item_data[0]->delivery_boy_id == 0) && (empty($request->filled('deliver_by')) || $request->filled('deliver_by') == '')) {
                    return response()->json([
                        'error' => true,
                        'message' => labels('admin_labels.select_delivery_boy_to_mark_order_shipped', 'Please select a delivery boy to mark this order as shipped.'),
                        'data' => [],
                    ]);
                }
            }
            $awaitingPresent = false;
            foreach ($parcel as $item) {
                if ($item->active_status === 'awaiting') {
                    $awaitingPresent = true;
                    break;
                }
            }
            if (!empty($delivery_boy_id)) {
                if ($awaitingPresent) {
                    return response()->json([
                        'error' => true,
                        'message' => labels('admin_labels.delivery_boy_cant_assign_to_awaiting_orders', "Delivery Boy can't assign to awaiting orders ! please confirm the order first."),
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
                        $current_delivery_boy = fetchDetails('parcels', ['id' => $parcel_id], '*');

                        if (isset($current_delivery_boy[0]->delivery_boy_id) && !empty($current_delivery_boy[0]->delivery_boy_id)) {
                            $user_res = fetchDetails('users', ['id' => $current_delivery_boy[0]->delivery_boy_id], ['username', 'fcm_id']);
                        } else {
                            $user_res = fetchDetails('users', ['id' => $delivery_boy_id], ['username', 'fcm_id']);
                        }
                        if (isset($user_res[0]) && !empty($user_res[0])) {
                            $custom_notification = fetchDetails('custom_messages', $type, '*');
                            if (!empty($current_delivery_boy[0]) && count($current_delivery_boy) > 1) {
                                for ($i = 0; $i < count($current_delivery_boy); $i++) {
                                    $username = isset($user_res[$i]->username) ? $user_res[$i]->username : '';
                                    $hashtag_customer_name = '< customer_name >';
                                    $hashtag_order_id = '< order_item_id >';
                                    $hashtag_application_name = '< application_name >';
                                    $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                                    $hashtag = html_entity_decode($string);
                                    $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($username, $order_id, $app_name), $hashtag);
                                    $message = outputEscaping(trim($data, '"'));
                                    $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $username . ' ' . 'Order status updated to' . $request->input('status') . ' for order ID #' . $order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
                                    $title = (!empty($custom_notification)) ? $custom_notification[0]->title : "Order status updated";
                                    $order_id = $order_id;
                                    $fcmMsg = array(
                                        'title' => "$title",
                                        'body' => "$customer_msg",
                                        'type' => "order",
                                        'order_id' => "$order_id",
                                        'store_id' => "$store_id",
                                    );
                                    if (!empty($user_res[$i]->fcm_id)) {
                                        $fcm_ids[0][] = $user_res[$i]->fcm_id;
                                    }
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
                                    $message = 'Delivery Boy Updated';
                                    $delivery_boy_updated = 1;
                                } else {
                                    $custom_notification = fetchDetails('custom_messages', ['type' => "delivery_boy_order_deliver"], '*');

                                    $hashtag_customer_name = '< customer_name >';
                                    $hashtag_order_id = '< order_id >';
                                    $hashtag_application_name = '< application_name >';
                                    $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
                                    $hashtag = html_entity_decode($string);
                                    $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_id, $app_name), $hashtag);
                                    $message = outputEscaping(trim($data, '"'));
                                    $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $user_res[0]->username . ' ' . ' you have new order to be deliver order ID #' . $order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
                                    $title = (!empty($custom_notification)) ? $custom_notification[0]->title : " Order status updated";
                                    $order_id = $order_id;
                                    $fcmMsg = array(
                                        'title' => "$title",
                                        'body' => "$customer_msg",
                                        'type' => "order",
                                        'order_id' => "$order_id",
                                        'store_id' => "$store_id",
                                    );
                                    $message = 'Delivery Boy Updated.';
                                    $delivery_boy_updated = 1;
                                }
                                if (!empty($user_res[0]->fcm_id)) {
                                    $fcm_ids[0][] = $user_res[0]->fcm_id;
                                }
                            }
                        }
                        if (updateOrder(['delivery_boy_id' => $delivery_boy_id], ['id' => $parcel_id], false, 'parcels')) {
                            foreach ($parcel_items as $item) {
                                $res = updateOrder(['delivery_boy_id' => $delivery_boy_id], ['id' => $item->order_item_id], false, 'order_items');
                            }
                            $delivery_error = false;
                        }
                    }
                }
            }
            if (($request->filled('status')) && !empty($request->filled('status')) && $request->filled('status') != '') {

                $res = validateOrderStatus($parcel_id, $request->input('status'), 'parcels', '', '', $parcel[0]->type);

                if ($res['error']) {
                    return response()->json([
                        'error' => true,
                        'message' => ($request->filled('status')) ? $message . $res['message'] : $message,
                        'data' => [],
                    ]);
                }
            }
            $order_method = fetchDetails('orders', ['id' => $order_id], 'payment_method');
            $bank_receipt = fetchDetails('order_bank_transfers', ['order_id' => $order_id]);
            $transaction_status = fetchDetails('transactions', ['order_id' => $order_id], 'status');

            if (isset($order_method[0]->payment_method) && $order_method[0]->payment_method == 'bank_transfer') {
                if ($request->input('status') != 'cancelled' && (empty($bank_receipt) || strtolower($transaction_status[0]->status) != 'success' || $bank_receipt[0]->status == "0" || $bank_receipt[0]->status == "1")) {
                    return response()->json([
                        'error' => true,
                        'message' => labels('admin_labels.order_item_status_cant_update_bank_verification_remain', "Order item status can't update, Bank verification is remain from transactions for this order."),
                        'data' => [],
                    ]);
                }
            }

            // processing order items
            $response_data = [];
            if (updateOrder(['status' => $request->input('status')], ['id' => $parcel_id], true, 'parcels')) {
                updateOrder(['active_status' => $request->input('status')], ['id' => $parcel_id], false, 'parcels');
                foreach ($parcel_items as $item) {
                    updateOrder(['status' => $request->input('status')], ['id' => $item->order_item_id], true, 'order_items');
                    updateOrder(['active_status' => $request->input('status'), 'delivery_boy_id' => $delivery_boy_id], ['id' => $item->order_item_id], false, 'order_items');
                    $data = [
                        'order_item_id' => $item->order_item_id,
                        'status' => $request->input('status')
                    ];
                    array_push($response_data, $data);
                }
            }
            updateDetails(['updated_by' => auth()->id()], ['order_id' => $parcel[0]->order_id, 'seller_id' => $seller_id], 'order_items');

            $user = fetchDetails('orders', ['id' => $order_id], 'user_id');
            $user_res = fetchDetails('users', ['id' => $user[0]->user_id], ['username', 'fcm_id']);


            $custom_notification = fetchDetails('custom_messages', $type, '*');
            $hashtag_customer_name = '< customer_name >';
            $hashtag_order_id = '< order_item_id >';
            $hashtag_application_name = '< application_name >';
            $string = isset($custom_notification) && !empty($custom_notification) ? json_encode($custom_notification[0]->message, JSON_UNESCAPED_UNICODE) : '';
            $hashtag = html_entity_decode($string);
            $data = str_replace(array($hashtag_customer_name, $hashtag_order_id, $hashtag_application_name), array($user_res[0]->username, $order_id, $app_name), $hashtag);
            $message = outputEscaping(trim($data, '"'));
            $customer_msg = (!empty($custom_notification)) ? $message : 'Hello Dear ' . $user_res[0]->username . ' Order status updated to' . $request->input('val') . ' for order ID #' . $order_id . ' please take note of it! Thank you. Regards ' . $app_name . '';
            $fcm_ids = array();
            $title = (!empty($custom_notification)) ? $custom_notification[0]->title : " Order status updated";
            $order_id = $order_id;
            $results = UserFcm::join('users', 'user_fcm.user_id', '=', 'users.id')
                ->where('user_fcm.user_id', $user[0]->user_id)
                ->where('users.is_notification_on', 1)
                ->select('user_fcm.fcm_id')
                ->get();
            $fcm_ids = array();
            if (!empty($results)) {
                $fcmMsg = array(
                    'title' => "$title",
                    'body' => "$customer_msg",
                    'type' => "order",
                    'order_id' => "$order_id",
                    'store_id' => "$store_id",
                );

                foreach ($results as $result) {
                    if (is_object($result)) {
                        $fcm_ids[] = $result->fcm_id;
                    }
                }
                $user_registrationIDs_chunks = array_chunk($fcm_ids, 1000);
                sendNotification('', $user_registrationIDs_chunks, $fcmMsg);
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
        $validator = Validator::make($request->all(), [
            'courier_agency' => 'required|string',
            'tracking_id' => 'required',
            'url' => 'required',
            'parcel_id' => 'required',

        ], [
            'required' => 'The :attribute field is required.',
            'numeric' => 'The :attribute field must be a number.',

        ]);

        if ($validator->fails()) {
            $response = [
                'error' => true,
                'message' => $validator->errors()->all(),
            ];
            return response()->json($response);
        }

        $order_id = $request->input('order_id');
        $limit = $request->input('limit') ?? 25;
        $offset = $request->input('offset') ?? 0;

        $order = $request->input('order') ?? 'DESC';
        $order_item_id = $request->input('order_item_id');
        $sellerId = Auth::id();
        $seller_id = Seller::where('user_id', $sellerId)->value('id');
        $courier_agency = $request->input('courier_agency');
        $tracking_id = $request->input('tracking_id');
        $parcel_id = $request->input('parcel_id');
        $url = $request->input('url');

        $store_id = fetchDetails('parcels', ['id' => $parcel_id], 'store_id');
        $store_id = isset($store_id) && !empty($store_id) ? $store_id[0]->store_id : "";

        $parcel_details = viewAllParcels('', $parcel_id, $seller_id, $offset, $limit, $order, 1, '', '', $store_id);

        if (isset($parcel_details->original) && empty($parcel_details->original['data'])) {
            $response['error'] = true;
            $response['message'] = "Parcel Not Found.";
            $response['data'] = [];
            return response()->json($response);
        }
        $parcel_details = $parcel_details->original['data'][0];
        if (isset($parcel_details['is_shiprocket_order']) && $parcel_details['is_shiprocket_order'] == 1) {
            $response['error'] = true;
            $response['message'] = "This is An Shiprocket Parcel You Can't Add Tracking Details Manually.";
            $response['data'] = [];
            return response()->json($response);
        }
        $order_id = $parcel_details['order_id'];
        $data = array(
            'parcel_id' => $parcel_id,
            'order_id' => $order_id,
            'courier_agency' => $courier_agency,
            'tracking_id' => $tracking_id,
            'url' => $url,
        );

        if (isExist(['parcel_id' => $parcel_id, 'shipment_id' => 0], 'order_trackings', null)) {
            if (updateDetails($data, ['parcel_id' => $parcel_id, 'shipment_id' => 0], 'order_trackings') == TRUE) {
                $response['error'] = false;
                $response['message'] = labels('admin_labels.tracking_details_update_successfully', 'Tracking details Update Successfuly.');
            } else {
                $response['error'] = true;
                $response['message'] = labels('admin_labels.tracking_details_update_failed', 'Not Updated. Try again later.');
            }
        } else {
            if (OrderTracking::create($data)) {
                $response['error'] = false;
                $response['message'] = labels('admin_labels.tracking_details_insert_successfully', 'Tracking details Insert Successfuly.');
            } else {
                $response['error'] = true;
                $response['message'] = labels('admin_labels.tracking_details_insert_failed', 'Not Inserted. Try again later.');
            }
        }

        return response()->json($response);
    }

    public function get_order_tracking(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'parcel_id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => $validator->errors()->all(),
            ]);
        }

        $parcel_id = $request->input('parcel_id');

        // 🔹 Fetch tracking details
        $tracking = OrderTracking::where('parcel_id', $parcel_id)->where('shipment_id', 0)->first();

        if ($tracking) {
            return response()->json([
                'error' => false,
                'message' => 'Tracking details found.',
                'data' => $tracking,
            ]);
        } else {
            return response()->json([
                'error' => true,
                'message' => 'No tracking details found.',
                'data' => [],
            ]);
        }
    }


    public function create_shiprocket_order(Request $request, $fromApp = false)
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
                'message' => $validator->errors()->all(),
            ];
            return response()->json($response);
        }

        if ($fromApp == false) {
            $request['order_items'] = json_decode($request['order_items'][0], 1);
            $request['parcel_data'] = json_decode($_POST['parcel_data'][0], 1);
            $order_items = $request['order_items'];
            $parcel_data = $request['parcel_data'];
        } else {
            $store_id = $request->input('store_id') ?? '';
            $limit = $request->input('limit') ?? 25;
            $offset = $request->input('offset') ?? 0;

            $order = $request->input('order') ?? 'DESC';
            $order_item_id = $request->input('order_item_id');
            $order_id = $request->input('order_id');
            $sellerId = Auth::id();
            $seller_id = Seller::where('user_id', $sellerId)->value('id');
            $order_items = fetchOrderItems('', '', '', '', 10, 0, 'id', 'DESC', '', '', '', $seller_id, $order_id, $store_id);
            $order_items = isset($order_items) && !empty($order_items['order_data']) ? $order_items['order_data'] : "";
            $parcel_data[0]['parcel_id'] = $request['parcel_id'];
            $parcel_data = viewAllParcels('', $request['parcel_id'], $seller_id, $offset, $limit, $order, 1, '', '', $store_id);
            $parcel_data = isset($parcel_data) && !empty($parcel_data->original['data']) ? $parcel_data->original['data'] : "";
        }
        if ($fromApp == false) {
            if (isExist(['parcel_id' => $parcel_data[0]['parcel_id'], 'is_canceled' => 0], 'order_trackings')) {
                $response['error'] = false;
                $response['message'] = labels('admin_labels.order_already_created', 'Shiprocket order Already Created.');
                return response()->json($response);
            }
        } else {

            if (isExist(['parcel_id' => $parcel_data[0]['id'], 'is_canceled' => 0], 'order_trackings')) {
                $response = [
                    'error' => false,
                    'message' => labels('admin_labels.order_already_created', 'Shiprocket order Already Created.'),
                    'data' => []
                ];
                return response()->json($response);
            }
        }

        $shiprocket = new Shiprocket();

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

        if ($fromApp == false) {
            foreach ($parcel_data as $parcel_item) {
                foreach ($order_items as $row) {
                    $row = (array) $row;

                    $random_no = '-' . rand(10, 10000);
                    if ($row['pickup_location'] == $request['pickup_location'] && $row['seller_id'] == $request['shiprocket_seller_id']) {
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
                        $temp['sku'] = isset($sku) && !empty($sku) ? $sku . $random_no : $row['product_slug'] . $random_no;
                        $subtotal += (int) $parcel_item['quantity'] * (int) $parcel_item['unit_price'];
                        $temp['total_units'] = $parcel_item['quantity'];
                        $temp['units'] = $parcel_item['quantity'];
                        $temp['selling_price'] = $row['price'];
                        $temp['discount'] = $row['discounted_price'];
                        $temp['tax'] = $row['tax_amount'];
                        array_push($items, $temp);
                    }
                }
            }
        } else {
            foreach ($parcel_data[0]['items'] as $parcel_item) {
                foreach ($order_items as $row) {
                    $row = (array) $row;

                    $random_no = '-' . rand(10, 10000);
                    if ($row['pickup_location'] == $request['pickup_location'] && $row['seller_id'] == $request['shiprocket_seller_id']) {
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
                        $temp['sku'] = isset($sku) && !empty($sku) ? $sku . $random_no : $row['product_slug'] . $random_no;
                        $subtotal += (int) $parcel_item['quantity'] * (int) $parcel_item['unit_price'];
                        $temp['total_units'] = $parcel_item['quantity'];
                        $temp['units'] = $parcel_item['quantity'];
                        $temp['selling_price'] = $row['price'];
                        $temp['discount'] = $row['discounted_price'];
                        $temp['tax'] = $row['tax_amount'];
                        array_push($items, $temp);
                    }
                }
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
                'parcel_id' => isset($parcel_data[0]['id']) ? $parcel_data[0]['id'] : $parcel_data[0]['parcel_id'],
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
                'tracking_id' => $response['channel_order_id'],
                'url' => ''
            ];
            OrderTracking::create($order_tracking_data);
        }
        if (isset($response['status_code']) && $response['status_code'] == 1) {
            $response['error'] = false;
            $response['message'] = labels('admin_labels.shiprocket_order_created_successfully', 'Shiprocket order created successfully');
            $response['data'] = $response;
        } else {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.shiprocket_order_not_created_successfully', 'Shiprocket order not created successfully');
            $response['data'] = $response;
        }
        return response()->json($response);
    }

    public function generate_awb(Request $request)
    {
        $res = generate_awb($request['shipment_id']);
        if (!empty($res) && $res['awb_assign_status'] == 1) {
            $response['error'] = false;
            $response['message'] = labels('admin_labels.awb_generated_successfully', 'AWB generated successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.awb_not_generated', 'AWB not generated');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function send_pickup_request(Request $request)
    {
        $res = send_pickup_request($request['shipment_id']);

        if (!empty($res)) {
            $response['error'] = false;
            $response['message'] = labels('admin_labels.request_send_successfully', 'Request send successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.request_not_sent', 'Request not sent');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function cancel_shiprocket_order(Request $request)
    {
        $res = cancel_shiprocket_order($request['shiprocket_order_id']);
        if (!empty($res) && (isset($res['status']) && $res['status'] == 200 || $res['status_code'] == 200)) {
            $response['error'] = false;
            $response['message'] = labels('admin_labels.order_cancelled_successfully', 'Order cancelled successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.order_not_cancelled', 'Order not cancelled');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function generate_label(Request $request)
    {
        $res = generate_label($request['shipment_id']);
        if (!empty($res)) {
            $response['error'] = false;
            $response['message'] = labels('admin_labels.label_generated_successfully', 'Label generated successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.label_not_generated', 'Label not generated');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function generate_invoice(Request $request)
    {
        $res = generate_invoice($request['order_id']);
        if (!empty($res) && isset($res['is_invoice_created']) && $res['is_invoice_created'] == 1) {
            $response['error'] = false;
            $response['message'] = labels('admin_labels.invoice_generated_successfully', 'Invoice generated successfully');
            $response['data'] = $res;
        } else {
            $response['error'] = true;
            $response['message'] = labels('admin_labels.invoice_not_generated', 'Invoice not generated');
            $response['data'] = array();
        }
        return response()->json($response);
    }

    public function getSellerOrderTrackingList(Request $request)
    {
        $offset = 0;
        $limit = 10;
        $sort = 'id';
        $order = 'DESC';
        $multipleWhere = [];
        $where = [];

        if ($request->has('offset')) {
            $offset = $request->input('search') || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        }
        if ($request->has('limit')) {
            $limit = $request->input('limit');
        }
        if ($request->has('sort')) {
            $sort = $request->input('sort');
        }
        if ($request->has('order')) {
            $order = $request->input('order');
        }

        if ($request->has('search') && trim($request->input('search')) !== '') {
            $search = trim($request->input('search'));
            $multipleWhere = [
                ['id', 'LIKE', $search],
                ['order_id', 'LIKE', $search],
                ['tracking_id', 'LIKE', $search],
                ['courier_agency', 'LIKE', $search],
                ['order_item_id', 'LIKE', $search],
                ['url', 'LIKE', $search],
            ];
        }
        if ($request->has('order_id') && $request->input('order_id') !== '') {
            $where = ['order_id' => $request->input('order_id')];
        }

        $count_res = DB::table('order_trackings')->select(DB::raw('COUNT(id) as total'));

        if (!empty($multipleWhere)) {
            $count_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }
        if (!empty($where)) {
            $count_res->where($where);
        }

        $total = $count_res->first()->total;

        $search_res = DB::table('order_trackings')->select('*');

        if (!empty($multipleWhere)) {
            $search_res->where(function ($query) use ($multipleWhere) {
                foreach ($multipleWhere as $column => $value) {
                    $query->orWhere($column, 'like', '%' . $value . '%');
                }
            });
        }
        if (!empty($where)) {
            $search_res->where($where);
        }

        $order_tracking_data = $search_res->orderBy($sort, $order)->skip($offset)->take($limit)->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $tempRow = array();
        foreach ($order_tracking_data as $row) {
            $tempRow['id'] = $row->id ?? '';
            $tempRow['order_id'] = $row->order_id ?? '';
            $tempRow['order_item_id'] = $row->order_item_id ?? '';
            $tempRow['courier_agency'] = $row->courier_agency ?? '';
            $tempRow['tracking_id'] = $row->tracking_id ?? '';
            $tempRow['url'] = $row->url ?? '';
            $tempRow['shiprocket_order_id'] = $row->shiprocket_order_id ?? '';
            $tempRow['shipment_id'] = $row->shipment_id ?? '';
            $tempRow['courier_company_id'] = $row->courier_company_id ?? '';
            $tempRow['awb_code'] = $row->awb_code ?? '';
            $tempRow['pickup_status'] = $row->pickup_status ?? '';
            $tempRow['pickup_scheduled_date'] = $row->pickup_scheduled_date ?? '';
            $tempRow['pickup_token_number'] = $row->pickup_token_number ?? '';
            $tempRow['status'] = $row->status ?? '';
            $tempRow['others'] = $row->others ?? '';
            $tempRow['pickup_generated_date'] = $row->pickup_generated_date ?? '';
            $tempRow['data'] = $row->data ?? '';
            $tempRow['is_canceled'] = $row->is_canceled ?? '';
            $tempRow['manifest_url'] = $row->manifest_url ?? '';
            $tempRow['label_url'] = $row->label_url ?? '';
            $tempRow['invoice_url'] = $row->invoice_url ?? '';
            $tempRow['date'] = $row->created_at ?? '';

            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return $bulkData;
    }

    public function getSellerSalesList(Request $request)
    {

        $search = trim($request->input('search', ''));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'o.id');
        $seller_id = $request->input('seller_id');

        $date = now()->toDateString();

        $query = DB::table('orders AS o')
            ->select('o.*', 'oitems.*', 'u.username', 'u.email', 'u.mobile', 'ss.store_name', 'us.username AS seller_name')
            ->leftJoin('users AS u', 'u.id', '=', 'o.user_id')
            ->leftJoin('order_items AS oitems', 'o.id', '=', 'oitems.order_id')
            ->when(!empty($seller_id), function ($query) use ($seller_id) {
                $query->leftJoin('order_items AS oi', 'oi.order_id', '=', 'o.id')

                    ->leftJoin('seller_store AS ss', 'oi.seller_id', '=', 'ss.seller_id')
                    ->leftJoin('users AS us', 'us.id', '=', 'ss.user_id')
                    ->where('oi.seller_id', $seller_id);
            })
            ->when(
                (!empty($request->input('start_date')) && !empty($request->input('end_date'))),
                function ($query) use ($request) {
                    $start_date = $request->input('start_date');
                    $end_date = $request->input('end_date');
                    $query->whereDate('oi.created_at', '>=', $start_date)
                        ->whereDate('oi.created_at', '<=', $end_date);
                },
                function ($query) use ($date) {
                    $query->whereDate('oi.created_at', '>=', $date);
                }
            )
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('u.username', 'LIKE', $search)
                        ->orWhere('u.email', 'LIKE', $search)
                        ->orWhere('u.mobile', 'LIKE', $search)
                        ->orWhere('o.final_total', 'LIKE', $search)
                        ->orWhere('o.created_at', 'LIKE', $search)
                        ->orWhere('o.id', 'LIKE', $search);
                });
            })
            ->groupBy('o.id')
            ->orderBy($sort, 'DESC')
            ->skip($offset)
            ->take($limit);

        $user_details = $query->get();

        $total = count($user_details);

        $bulkData = array();
        $rows = array();
        $tempRow = array();
        $total_amount = 0;
        $final_total_amount = 0;
        $total_delivery_charge = 0;
        foreach ($user_details as $row) {
            $total_amount += intval($row->total ?? 0);
            $final_total_amount += intval($row->final_total ?? 0);
            $total_delivery_charge += intval($row->delivery_charge ?? 0);

            $tempRow['id'] = $row->id ?? '';
            $tempRow['name'] = $row->username ?? '';
            $tempRow['total'] = $row->total ?? '';
            $tempRow['tax_amount'] = $row->tax_amount ?? '';
            $tempRow['discounted_price'] = isset($row->discounted_price) && $row->discounted_price !== '' ? $row->discounted_price : '0';
            $tempRow['delivery_charge'] = $row->delivery_charge ?? '';
            $tempRow['final_total'] = $row->final_total ?? '';
            $tempRow['payment_method'] = $row->payment_method ?? '';
            $tempRow['store_name'] = $row->store_name ?? '';
            $tempRow['seller_name'] = $row->seller_name ?? '';
            $tempRow['date_added'] = $row->created_at ?? '';

            $rows[] = $tempRow;
        }
        $bulkData['error'] = false;
        $bulkData['message'] = "Data Retrived Successfully";
        $bulkData['total'] = $total;
        $bulkData['grand_total'] = "$total_amount";
        $bulkData['total_delivery_charge'] = "$total_delivery_charge";
        $bulkData['grand_final_total'] = "$final_total_amount";
        $bulkData['rows'] = $rows;
        return $bulkData;
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

        $validationResponse = validatePanelRequest($request, $rules, $messages);
        if ($validationResponse !== null) {
            return $validationResponse;
        }
        $message = str_replace('\r\n', '&#13;&#10;', $request['message']);

        $attachment = asset(config('constants.MEDIA_PATH') . $request['pro_input_file']);
        $to = $request['email'];
        $subject = $request['subject'];

        $mail = sendDigitalProductMail($to, $subject, $message, $attachment);

        if ($mail['error'] == true) {
            $response['error'] = true;
            $response['message'] = "Cannot send mail. You can try to send mail manually.";
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
    // create parcel

    public function create_parcel(Request $request)
    {

        $rules = [
            'selected_items' => 'required|array',
            'selected_items.*' => 'required|distinct',
            'parcel_title' => 'required|string|max:255',
            'order_id' => 'required|string|max:255',
        ];

        $messages = [
            'selected_items.required' => 'Please select at least one item.',
        ];

        $validationResponse = validatePanelRequest($request, $rules, $messages);
        if ($validationResponse !== null) {
            return $validationResponse;
        }
        $res = createParcel($request);
        if ($res['error'] == false) {
            $response['error'] = $res['error'];
            $response['message'] = $res['message'];
            $response['data'] = $res['data'];
            return response()->json($response);
        }
        $response['error'] = $res['error'];
        $response['message'] = $res['message'];
        return response()->json($response);
    }


    public function parcel_list(Request $request, $seller_id = '', $delivery_boy_id = '')
    {
        // dd($request);
        $search = trim($request->input('search', ''));
        $offset = $search || (request('pagination_offset')) ? (request('pagination_offset')) : 0;
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'parcels.id');
        $order = $request->input('order', 'ASC');
        $order_id = $request->input('order_id', 0);

        $query = Parcel::select('parcels.id', 'parcels.order_id', 'parcels.name', 'parcels.active_status as status', 'parcels.type as order_parcel_type', 'parcels.created_at', 'parcels.otp')
            ->join('parcel_items', 'parcel_items.parcel_id', '=', 'parcels.id')
            ->join('orders', 'orders.id', '=', 'parcels.order_id')
            ->join('order_items', 'order_items.id', '=', 'parcel_items.order_item_id')
            ->join('users', 'users.id', '=', 'orders.user_id');

        if ($order_id) {
            $query->where('orders.id', $order_id);
        } elseif ($delivery_boy_id) {
            $query->where('parcels.delivery_boy_id', $delivery_boy_id);
        }

        if ($seller_id) {
            $query->where('order_items.seller_id', $seller_id);
        }
        if ($request->seller_id) {
            $query->where('order_items.seller_id', $request->seller_id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->orWhere('parcels.id', 'like', "%{$search}%")
                    ->orWhere('parcels.name', 'like', "%{$search}%")
                    ->orWhere('parcels.active_status', 'like', "%{$search}%")
                    ->orWhere('parcels.created_at', 'like', "%{$search}%");
            });
        }

        $total = $query->distinct()->count('parcels.id');


        $parcels = $query->groupBy('parcels.id')
            ->orderBy($sort, $order)
            ->limit($limit)
            ->offset($offset)
            ->get();

        $rows = [];

        foreach ($parcels as $parcel) {
            // dd($parcel);
            if ($parcel->status == 'awaiting') {
                $status = '<label class="badge bg-secondary">' . ucfirst($parcel->status) . '</label>';
            }
            if ($parcel->status == 'received') {
                $status = '<label class="badge bg-primary">' . ucfirst($parcel->status) . '</label>';
            }
            if ($parcel->status == 'processed') {
                $status = '<label class="badge bg-info">' . ucfirst($parcel->status) . '</label>';
            }
            if ($parcel->status == 'shipped') {
                $status = '<label class="badge bg-warning">' . ucfirst($parcel->status) . '</label>';
            }
            if ($parcel->status == 'delivered') {
                $status = '<label class="badge bg-success">' . ucfirst($parcel->status) . '</label>';
            }
            if ($parcel->status == 'returned' || $parcel->status == 'cancelled') {
                $status = '<label class="badge bg-danger">' . ucfirst($parcel->status) . '</label>';
            }
            if ($parcel->status == 'return_request_decline') {
                $status = '<label class="badge bg-danger">Return Declined</label>';
            }
            if ($parcel->status == 'return_request_approved') {
                $status = '<label class="badge bg-success">Return Approved</label>';
            }
            if ($parcel->status == 'return_request_pending') {
                $status = '<label class="badge bg-secondary">Return Requested</label>';
            }

            $parcelItems = ParcelItem::select(
                'order_items.*',
                'users.username',
                'parcels.active_status',
                'parcels.delivery_boy_id',
                'parcel_items.*',
                'orders.payment_method',
                'orders.mobile',
                'order_items.order_type as order_item_order_type',
                'order_items.active_status as item_status',
            )
                ->join('order_items', 'order_items.id', '=', 'parcel_items.order_item_id')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('users', 'users.id', '=', 'order_items.user_id')
                ->join('parcels', 'parcels.id', '=', 'parcel_items.parcel_id');

            // Conditional join based on order type
            // Add conditional join logic based on the `order_item_order_type`
            $parcelItems->leftJoin('combo_products', function ($join) {
                $join->on('combo_products.id', '=', 'parcel_items.product_variant_id')
                    ->where('order_items.order_type', '=', 'combo_order');
            })->leftJoin('product_variants', function ($join) {
                $join->on('product_variants.id', '=', 'parcel_items.product_variant_id')
                    ->where('order_items.order_type', '!=', 'combo_order');
            })->leftJoin('products', function ($join) {
                $join->on('products.id', '=', 'product_variants.product_id')
                    ->where('order_items.order_type', '!=', 'combo_order');
            });

            // Add select columns conditionally
            $parcelItems->addSelect(
                DB::raw("CASE
                    WHEN order_items.order_type = 'combo_order' THEN combo_products.image
                    ELSE products.image
                END as image")
            );

            // Add where clause and execute the query
            $parcelItems = $parcelItems->where('parcel_items.parcel_id', $parcel->id)->get();

            $productNames = [];
            $quantities = [];

            foreach ($parcelItems as $item) {
                $productNames[] = $item->product_name;
                $quantities[] = $item->quantity;
                $item->image = getMediaImageUrl($item->image);
            }
            $order_tracking_data = fetchDetails('order_trackings', ['parcel_id' => $parcel->id, 'shipment_id' => 0], ['courier_agency', 'tracking_id', 'url']);

            $action = '<div class="d-flex action-icons">
                        <a href="javascript:void(0)" class="me-2 btn btn-primary view_parcel_items"
                            data-items=\'' . htmlspecialchars(json_encode($parcelItems), ENT_QUOTES, 'UTF-8') . '\'
                            data-bs-toggle="modal" data-bs-target="#view_parcel_items_modal"
                            data-id="' . $parcel->id . '">
                            <i class="bx bxs-show text-white"></i>
                        </a>
                        <a href="' . route("seller.orders.generatParcelInvoicePDF", $parcel->id) . '" class="me-2 btn btn-success">
                            <i class="bx bxs-file-blank text-white"></i>
                        </a>
                        <a href="javascript:void(0)" class="me-2 btn btn-warning parcel_status_btn"
                            data-id="' . $parcel->id . '"
                            data-parcel-name="' . htmlspecialchars($parcel->name, ENT_QUOTES, 'UTF-8') . '"
                            data-status="' . htmlspecialchars($parcel->status, ENT_QUOTES, 'UTF-8') . '"
                            data-items=\'' . htmlspecialchars(json_encode($parcelItems), ENT_QUOTES, 'UTF-8') . '\'
                            data-bs-toggle="modal" data-bs-target="#parcel_status_modal">
                            <i class="bx bx-pencil text-white"></i>
                        </a>
                        <a href="javascript:void(0)" class="me-2 btn btn-danger delete_parcel" data-id="' . $parcel->id . '" onclick="delete_parcel(' . $parcel->id . ')" title="Delete">
                            <i class="bx bx-trash text-white"></i>
                        </a>
                        <a href="javascript:void(0)" class="edit_seller_order_tracking me-2 btn btn-info"
                            data-id="' . $parcel->id . '"
                            data-order-id="' . $parcel->order_id . '"
                            data-tracking-data=\'' . htmlspecialchars(json_encode($order_tracking_data), ENT_QUOTES, 'UTF-8') . '\'
                            data-bs-toggle="modal" data-bs-target="#order_tracking_modal">
                            <i class="bx bx-map text-white"></i>
                        </a>

                    </div>';
            $rows[] = [
                'id' => $parcel->id,
                'order_id' => $parcel->order_id,
                'username' => $parcelItems[0]->username,
                'mobile' => $parcelItems[0]->mobile,
                'product_name' => implode(', ', $productNames),
                'quantity' => implode(', ', $quantities),
                'name' => ucfirst($parcel->name) ?? "",
                'payment_method' => $parcelItems[0]->payment_method ?? '',
                'status' => $status,
                'otp' => '<label class="badge bg-dark-danger">' . ($parcel->otp ?? '') . '</label>',
                'created_at' => Carbon::parse($parcel->created_at)->format('d-m-Y'),
                'operate' => $action
            ];
        }

        return response()->json([
            'total' => $total,
            'rows' => $rows
        ]);
    }

    public function delete_parcel(Request $request)
    {
        $rules = [
            'id' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $parcel_id = $request->id ?? "";
        $res = deleteParcel($parcel_id);

        if ($res['error'] == false) {
            $response['error'] = $res['error'];
            $response['message'] = $res['message'];
            $response['data'] = $res['data'];
            return response()->json($response);
        }
        $response['error'] = $res['error'];
        $response['message'] = $res['message'];
        return response()->json($response);
    }
    public function update_shiprocket_order_status(Request $request)
    {
        $rules = [
            'tracking_id' => 'required',
        ];

        if ($response = validatePanelRequest($request, $rules)) {
            return $response;
        }
        $tracking_id = $request->tracking_id ?? "";
        $res = update_shiprocket_order_status($tracking_id);

        $response = [
            'error' => !empty($res['error']),
            'message' => $res['message'],
            'data' => $res['data'] ?? []
        ];

        return response()->json($response);
    }
}
