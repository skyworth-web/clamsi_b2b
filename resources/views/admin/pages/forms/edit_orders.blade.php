@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.edit_orders', 'Edit Orders') }}
@endsection
@section('content')

    <x-admin.breadcrumb :title="labels('admin_labels.order_details', 'Order Details')" :subtitle="labels('admin_labels.see_every_detail_steer_every_step', 'See Every Detail Steer Every Step')" :breadcrumbs="[
        ['label' => labels('admin_labels.manage_orders', 'Manage Orders')],
        ['label' => labels('admin_labels.orders', 'Orders')],
    ]" />


    <section>

        <div class="card content-area p-3">
            <div class="align-items-center d-flex justify-content-between">
                <div>
                    <span class="body-default text-muted">{{ labels('admin_labels.order_number', 'Order Number') }}</span>
                    <p class="lead">#{{ $order_detls[0]->id }}</p>
                </div>
                <div class="align-items-center d-flex">
                    <span class="body-default text-muted">{{ labels('admin_labels.order_date', 'Order Date') }} :</span>
                    <span class="body-default me-3"><?= date('d M, Y', strtotime($order_detls[0]->created_at)) ?></span>

                    <a href="{{ route('admin.orders.generatInvoicePDF', $order_detls[0]->order_id) }}"
                        class="btn btn-primary btn-sm instructions_files"><i
                            class='bx bx-download me-1'></i>{{ labels('admin_labels.invoice', 'Invoice') }}
                    </a>



                </div>
            </div>
        </div>
        <div class="row mt-5 order-info">
            <div class="col-md-4">
                <div class="card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>{{ labels('admin_labels.customer_info', 'Customer Info') }}</h6>
                            <div class="d-flex mt-3 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.name', 'Name') }}:</span>
                                <span class="caption text-muted">{{ $order_detls[0]->user_name }}</span>
                            </div>

                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.mobile', 'Contact') }}:</span>
                                @if ($order_detls[0]->mobile != '' && isset($order_detls[0]->mobile))
                                    <span class="caption text-muted">{{ $order_detls[0]->mobile }}</span>
                                @else
                                    <span
                                        class="caption text-muted">{{ isset($mobile_data) ? $mobile_data[0]->mobile : '' }}</span>
                                @endif
                            </div>
                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.email', 'Email') }}:</span>
                                <span class="caption text-muted">{{ $order_detls[0]->email }}</span>
                            </div>
                        </div>
                        <div>
                            <img alt="" src="{{ $items[0]['user_profile'] }}" class="customer-img-box">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>{{ labels('admin_labels.shipping_info', 'Shipping Info') }}</h6>
                            <div class="d-flex mt-3 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.name', 'Name') }}:</span>
                                <span class="caption text-muted">{{ $order_detls[0]->user_name }}</span>
                            </div>

                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.mobile', 'Contact') }}:</span>
                                @if ($order_detls[0]->mobile != '' && isset($order_detls[0]->mobile))
                                    <span class="caption text-muted">{{ $order_detls[0]->mobile }}</span>
                                @else
                                    <span
                                        class="caption text-muted">{{ isset($mobile_data) ? $mobile_data[0]->mobile : '' }}</span>
                                @endif
                            </div>
                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.address', 'Address') }}:</span>
                                <span class="caption text-muted">{{ $order_detls[0]->address }}</span>
                            </div>
                        </div>
                        <div>
                            <img alt="" src="{{ $items[0]['user_profile'] }}" class="customer-img-box">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>{{ labels('admin_labels.seller_info', 'Seller Info') }}</h6>
                            <div class="d-flex mt-3 align-items-center">
                                <span
                                    class="body-default me-1">{{ labels('admin_labels.seller_name', 'Seller Name') }}:</span>
                                <span class="caption text-muted">{{ $sellers[0]['seller_name'] }}</span>
                            </div>

                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.mobile', 'Contact') }}:</span>
                                <span class="caption text-muted">{{ $sellers[0]['seller_mobile'] }}</span>
                            </div>
                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.email', 'Email') }}:</span>
                                <span class="caption text-muted">{{ $sellers[0]['seller_email'] }}</span>
                            </div>
                        </div>
                        <div>
                            <img alt=""
                                src="{{ !empty($sellers[0]['shop_logo']) ? getmediaimageurl($sellers[0]['shop_logo']) : '' }}"
                                class="customer-img-box">
                        </div>

                    </div>
                </div>
            </div>

        </div>
        <div class="row mt-5 order-detail">
            <div class="col-lg-8 col-xl-9">

                <form id="update_form">
                    @for ($i = 0; $i < count($sellers); $i++)
                        @php
                            $seller_data = fetchDetails(
                                'users',
                                ['id' => $sellers[$i]['user_id']],
                                ['username', 'fcm_id'],
                            );
                            $seller_otp = fetchDetails(
                                'order_items',
                                ['order_id' => $order_detls[0]->order_id, 'seller_id' => $sellers[$i]['id']],
                                'otp',
                            )[0]->otp;
                            $order_caharges_data = fetchDetails('order_charges', [
                                'order_id' => $order_detls[0]->order_id,
                                'seller_id' => $sellers[$i]['id'],
                            ]);
                            $seller_order = getOrderDetails(
                                ['o.id' => $order_detls[0]->order_id, 'oi.seller_id' => $sellers[$i]['id']],
                                '',
                                '',
                                $store_id,
                            );
                            $pickup_location = collect($seller_order)
                                ->pluck('pickup_location')
                                ->unique()
                                ->values()
                                ->all();
                        @endphp
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <span
                                            class="caption text-muted">{{ labels('admin_labels.seller', 'Seller') }}</span>

                                        <p>{{ $seller_data[0]->username }}</p>
                                    </div>
                                    <div>
                                        <span>{{ labels('admin_labels.otp', 'OTP') }}</span>
                                        <p class="btn-primary btn-sm">
                                            {{ isset($order_caharges_data[0]->otp) ? $order_caharges_data[0]->otp : $seller_otp }}
                                        </p>

                                    </div>
                                </div>

                                @for ($j = 0; $j < count($pickup_location); $j++)
                                    @php
                                        // --------------------------------------- code for shiprocket
                                        //-----------------------------------------------

                                        $ids = '';
                                        foreach ($seller_order as $row) {
                                            if ($row->pickup_location == $pickup_location[$j]) {
                                                $ids .= $row->order_item_id . ',';
                                            }
                                        }
                                        $order_item_ids = explode(',', trim($ids, ','));

                                        $order_tracking_data = getShipmentId(
                                            $order_item_ids[0],
                                            $order_detls[0]->order_id,
                                        );

                                        $shiprocket_order =
                                            isset($order_tracking_data[0]['shiprocket_order_id']) &&
                                            $order_tracking_data[0]['shiprocket_order_id'] != 0
                                                ? get_shiprocket_order($order_tracking_data[0]['shiprocket_order_id'])
                                                : [];

                                        foreach ($order_item_ids as $id) {
                                            $active_status = fetchDetails(
                                                'order_items',
                                                ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                'active_status',
                                            )[0]->active_status;

                                            if (
                                                isset($shiprocket_order['data']) &&
                                                $shiprocket_order['data']['status'] == 'PICKUP SCHEDULED' &&
                                                $active_status != 'shipped'
                                            ) {
                                                updateOrder(
                                                    ['active_status' => 'shipped'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    false,
                                                    'order_items',
                                                );
                                                updateOrder(
                                                    ['status' => 'shipped'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    true,
                                                    'order_items',
                                                );
                                                $type = ['type' => 'customer_order_shipped'];
                                                $order_status = 'shipped';
                                            }
                                            if (
                                                isset($shiprocket_order['data']) &&
                                                ($shiprocket_order['data']['status'] == 'CANCELED' ||
                                                    $shiprocket_order['data']['status'] == 'CANCELLATION REQUESTED') &&
                                                $active_status != 'cancelled'
                                            ) {
                                                updateOrder(
                                                    ['active_status' => 'cancelled'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    false,
                                                    'order_items',
                                                );
                                                updateOrder(
                                                    ['status' => 'cancelled'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    true,
                                                    'order_items',
                                                );
                                                $type = ['type' => 'customer_order_cancelled'];
                                                $order_status = 'cancelled';
                                            }
                                            if (
                                                isset($shiprocket_order['data']) &&
                                                strtolower($shiprocket_order['data']['status']) == 'delivered' &&
                                                $active_status != 'delivered'
                                            ) {
                                                updateOrder(
                                                    ['active_status' => 'delivered'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    false,
                                                    'order_items',
                                                );
                                                updateOrder(
                                                    ['status' => 'delivered'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    true,
                                                    'order_items',
                                                );
                                                $type = ['type' => 'customer_order_delivered'];
                                                $order_status = 'delivered';
                                            }
                                            if (
                                                isset($shiprocket_order['data']) &&
                                                $shiprocket_order['data']['status'] == 'READY TO SHIP' &&
                                                $active_status != 'processed'
                                            ) {
                                                updateOrder(
                                                    ['active_status' => 'processed'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    false,
                                                    'order_items',
                                                );
                                                updateOrder(
                                                    ['status' => 'processed'],
                                                    ['id' => $id, 'seller_id' => $sellers[$i]['id']],
                                                    true,
                                                    'order_items',
                                                );
                                                $type = ['type' => 'customer_order_processed'];
                                                $order_status = 'processed';
                                            }

                                            //send notification while shiprocket order status changed
                                            if (isset($type) && !empty($type)) {
                                                $settings = getSettings('system_settings', true);
                                                $settings = json_decode($settings, true);
                                                $app_name =
                                                    isset($settings['app_name']) && !empty($settings['app_name'])
                                                        ? $settings['app_name']
                                                        : '';
                                                $custom_notification = fetchDetails('custom_messages', $type, '*');
                                                $hashtag_customer_name = '< customer_name >';
                                                $hashtag_order_id = '< order_item_id >';
                                                $hashtag_application_name = '< application_name >';
                                                $string =
                                                    isset($custom_notification) && !empty($custom_notification)
                                                        ? json_encode(
                                                            $custom_notification[0]->message,
                                                            JSON_UNESCAPED_UNICODE,
                                                        )
                                                        : '';
                                                $hashtag = html_entity_decode($string);
                                                $data = str_replace(
                                                    [
                                                        $hashtag_customer_name,
                                                        $hashtag_order_id,
                                                        $hashtag_application_name,
                                                    ],
                                                    [$order_detls[0]->uname, $order_detls[0]->id, $app_name],
                                                    $hashtag,
                                                );
                                                $message = outputEscaping(trim($data, '"'));
                                                $customer_msg = !empty($custom_notification)
                                                    ? $message
                                                    : 'Hello Dear ' .
                                                        $order_detls[0]->uname .
                                                        ' Order status updated to' .
                                                        $order_status .
                                                        ' for order ID #' .
                                                        $order_detls[0]->id .
                                                        ' please take note of it! Thank you. Regards ' .
                                                        $app_name .
                                                        '';
                                                $seller_msg = !empty($custom_notification)
                                                    ? $message
                                                    : 'Hello Dear ' .
                                                        $seller_data[0]->username .
                                                        ' Order status updated to' .
                                                        $order_status .
                                                        ' for order ID #' .
                                                        $order_detls[0]->id .
                                                        ' please take note of it! Thank you. Regards ' .
                                                        $app_name .
                                                        '';
                                                $fcmMsg = [
                                                    'title' => !empty($custom_notification)
                                                        ? $custom_notification[0]->title
                                                        : 'Order status updated',
                                                    'body' => $customer_msg,
                                                    'type' => 'order',
                                                    'store_id' => "$store_id",
                                                ];
                                                $seller_fcmMsg = [
                                                    'title' => !empty($custom_notification)
                                                        ? $custom_notification[0]->title
                                                        : 'Order status updated',
                                                    'body' => $seller_msg,
                                                    'type' => 'order',
                                                    'store_id' => "$store_id",
                                                ];
                                                $user_res = fetchDetails(
                                                    'users',
                                                    ['id' => $order_detls[0]->user_id],
                                                    'fcm_id',
                                                );
                                                $fcm_ids = $seller_fcm_ids = [];

                                                //send notification to customer
                                                if (!empty($user_res[0]->fcm_id)) {
                                                    $fcm_ids[0][] = $user_res[0]->fcm_id;
                                                    sendNotification('', $fcm_ids, $fcmMsg);
                                                }

                                                //send notification to seller
                                                if (!empty($seller_data[0]->fcm_id)) {
                                                    $seller_fcm_ids[0][] = $seller_data[0]->fcm_id;
                                                    sendNotification('', $seller_fcm_ids, $seller_fcmMsg);
                                                }
                                            }
                                        }
                                    @endphp
                                    <input type="hidden" name="edit_order_id" value="{{ $order_detls[0]->order_id }}">
                                    @php
                                        $total = 0;
                                        $tax_amount = 0;
                                    @endphp

                                    <div class="table-responsive mt-4">
                                        <table
                                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 edit-order-table">
                                            <thead class="thead-light thead-50 text-capitalize">
                                                <tr>

                                                    <th class="w-40">
                                                        {{ labels('admin_labels.product_items', 'Product Items') }}
                                                    </th>
                                                    <th>{{ labels('admin_labels.variations', 'Variation') }}</th>
                                                    <th>{{ labels('admin_labels.discount', 'Discount') }}</th>
                                                    <th>{{ labels('admin_labels.price', 'Price') }}</th>
                                                    <th>{{ labels('admin_labels.quantity', 'Qty') }}</th>
                                                    <th>{{ labels('admin_labels.deliver_by', 'Deliver By') }}</th>
                                                    <th>{{ labels('admin_labels.active_status', 'Active Status') }}</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $item_subtotal = 0;
                                                    $total = 0;
                                                    $tax_amount = 0;
                                                @endphp

                                                @foreach ($items as $item)
                                                    @php
                                                        $selected = '';
                                                        $item['discounted_price'] =
                                                            $item['discounted_price'] == ''
                                                                ? 0
                                                                : $item['discounted_price'];
                                                        $total += $subtotal =
                                                            $item['quantity'] != 0 &&
                                                            ($item['discounted_price'] != '' &&
                                                                $item['discounted_price'] > 0) &&
                                                            $item['price'] > $item['discounted_price']
                                                                ? $item['price'] - $item['discounted_price']
                                                                : $item['price'] * $item['quantity'];
                                                        $tax_amount += $item['tax_amount'];
                                                        $total += $subtotal = $tax_amount;
                                                        $item_subtotal += $item['item_subtotal'];
                                                    @endphp

                                                    @if ($sellers[$i]['id'] == $item['seller_id'])
                                                        @if ($pickup_location[$j] == $item['pickup_location'])
                                                            @php
                                                                $order_tracking_data = getShipmentId(
                                                                    $item['id'],
                                                                    $order_detls[0]->id,
                                                                );
                                                                $product_name = json_decode($row->pname, true);
                                                                $product_name = $product_name['en'] ?? '';
                                                                // dd($item)
                                                            @endphp
                                                            <tr>
                                                                <td class="align-items-center d-flex">
                                                                    <img alt=""
                                                                        class="avatar avatar-60 rounded ms-3"
                                                                        src="{{ getMediaImageUrl($item['product_image']) }}"
                                                                        alt="Image Description">
                                                                    <div class="ms-2">
                                                                        <h6 class="title-color">{{ $product_name }}</h6>
                                                                    </div>
                                                                </td>
                                                                <td>{{ isset($item['product_variants']) && !empty($item['product_variants']) ? str_replace(',', ' | ', $item['product_variants'][0]['variant_values']) : '-' }}
                                                                </td>
                                                                <td>{{ $item['discounted_price'] }}</td>
                                                                <td>{{ $item['price'] }}</td>
                                                                <td>{{ $item['quantity'] }}</td>
                                                                <td>{{ $item['deliver_by'] }}</td>

                                                                @php
                                                                    $badges = [
                                                                        'awaiting' => 'secondary',
                                                                        'received' => 'primary',
                                                                        'processed' => 'info',
                                                                        'shipped' => 'warning',
                                                                        'delivered' => 'success',
                                                                        'returned' => 'danger',
                                                                        'cancelled' => 'danger',
                                                                        'return_request_approved' => 'success',
                                                                        'return_request_decline' => 'danger',
                                                                        'return_request_pending' => 'warning',
                                                                        'return_pickedup' => 'success',
                                                                    ];

                                                                    if (
                                                                        $item['active_status'] ==
                                                                        'return_request_pending'
                                                                    ) {
                                                                        $status = 'Return Requested';
                                                                    } elseif (
                                                                        $item['active_status'] ==
                                                                        'return_request_approved'
                                                                    ) {
                                                                        $status = 'Return Approved';
                                                                    } elseif (
                                                                        $item['active_status'] ==
                                                                        'return_request_decline'
                                                                    ) {
                                                                        $status = 'Return Declined';
                                                                    } else {
                                                                        $status = $item['active_status'];
                                                                    }
                                                                @endphp

                                                                <td>
                                                                    <small><span
                                                                            class="mt-1 badge badge-sm bg-{{ $badges[$item['active_status']] }}">{{ $status }}</span></small>
                                                                </td>

                                                                @if ($item['product_type'] == 'digital_product' && $item['download_allowed'] == 0 && $item['is_sent'] == 0)
                                                                    <td>
                                                                        <a href="javascript:void(0)"
                                                                            class="btn reset-btn ml-3"
                                                                            id="sendDigitalProductMail"
                                                                            data-bs-target="#sendMailModal"
                                                                            data-bs-toggle="modal" title="Edit"
                                                                            data-id="{{ $item['id'] }}">
                                                                            <i class="fas fa-paper-plane"></i>
                                                                        </a>
                                                                        <a href="https://mail.google.com/mail/?view=cm&fs=1&tf=1&to={{ $item['user_email'] }}"
                                                                            class="btn btn-danger ml-3" target="_blank">
                                                                            <i class="fab fa-google"></i>
                                                                        </a>
                                                                    </td>
                                                                @endif
                                                            </tr>
                                                        @endif
                                                    @endif
                                                @endforeach

                                            </tbody>
                                        </table>
                                    </div>


                                    @if (
                                        $shipping_method['shiprocket_shipping_method'] == 1 &&
                                            isset($pickup_location[$j]) &&
                                            !empty($pickup_location[$j]) &&
                                            $pickup_location[$j] != 'NULL')
                                        <div class="align-items-center d-flex justify-content-between">
                                            <div class="align-items-center d-flex">
                                                @if ($items[0]['product_type'] != 'digital_product' && empty($order_tracking_data[0]['shipment_id']))
                                                    <input type="radio" name="pickup_location"
                                                        class="check_create_order form-check-input"
                                                        data-id="{{ $sellers[$i]['id'] }}" id="{{ $pickup_location[$j] }}"
                                                        disabled />
                                                @endif


                                                <div class="ms-3">
                                                    <span
                                                        class="text-muted caption">{{ labels('admin_labels.pickup_location', 'Pickup Location') }}
                                                        :</span>
                                                    <p class="text-capitalize m-0">{{ $pickup_location[$j] }}</p>
                                                </div>
                                            </div>
                                            <div>
                                                @if (isset($items[0]['product_type']) && $items[0]['product_type'] != 'digital_product')
                                                    @if ($shipping_method['shiprocket_shipping_method'] == 1)
                                                        <button type="button"
                                                            class="btn btn-dark create_shiprocket_order"
                                                            data-bs-target="#order_parcel_modal" data-bs-toggle="modal">
                                                            {{ labels('admin_labels.create_order', 'Create Shiprocket Order') }}</button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                        <!-- ----------------------------- code for create shiprocket order --------------------------- -->

                                        <div class="align-items-center d-flex mt-3">

                                            @php
                                                $isShipmentCreated =
                                                    !empty($shiprocket_order) &&
                                                    isset($shiprocket_order['data']) &&
                                                    !empty($shiprocket_order['data']) &&
                                                    isset($order_tracking_data[0]['shipment_id']) &&
                                                    !empty($order_tracking_data[0]['shipment_id']) &&
                                                    $order_tracking_data[0]['is_canceled'] != 1 &&
                                                    $shiprocket_order['data']['status'] != 'CANCELED';
                                            @endphp

                                            @if ($isShipmentCreated)
                                                <div class="col-md-1 ms-3">
                                                    <span class="badge bg-success ml-1">Order created</span>
                                                </div>
                                            @endif

                                            @php
                                                $isProductTypeNotDigital =
                                                    isset($items[0]['product_type']) &&
                                                    $items[0]['product_type'] != 'digital_product';
                                                $isShipmentIdEmpty = empty($order_tracking_data[0]['shipment_id']);
                                            @endphp

                                            @if ($isProductTypeNotDigital && $isShipmentIdEmpty)
                                                <div class="col-md-1">
                                                    <span class="badge bg-primary ml-1">Order not created</span>
                                                </div>
                                            @endif


                                            @php
                                                $isOrderCancelled =
                                                    (!empty($shiprocket_order) &&
                                                        isset($shiprocket_order['data']) &&
                                                        !empty($shiprocket_order['data']) &&
                                                        (isset($order_tracking_data[0]['is_canceled']) &&
                                                            $order_tracking_data[0]['is_canceled'] != 0)) ||
                                                    (isset($shiprocket_order['data']) &&
                                                        $shiprocket_order['data']['status'] == 'CANCELED');
                                            @endphp

                                            @if ($isOrderCancelled)
                                                <div class="col-md-1 me-2 ms-3">
                                                    <span class="badge bg-danger ml-1">Order cancelled</span>
                                                </div>
                                            @endif
                                            <div class="col-md-6 d-flex gap-2 ms-5">
                                                @if (isset($order_tracking_data[0]) &&
                                                        isset($order_tracking_data[0]['shipment_id']) &&
                                                        $order_tracking_data[0]['shipment_id'] != 0)
                                                    @if (isset($order_tracking_data[0]['shipment_id']) &&
                                                            (empty($order_tracking_data[0]['awb_code']) || $order_tracking_data[0]['awb_code'] == 'NULL') &&
                                                            isset($shiprocket_order['data']) &&
                                                            $shiprocket_order['data']['status'] != 'CANCELED')
                                                        <button type="button" title="Generate AWB"
                                                            class="btn btn-primary btn-sm mr-1 generate_awb"
                                                            data-fromadmin="1"
                                                            id="{{ $order_tracking_data[0]['shipment_id'] }}">AWB</button>
                                                    @else
                                                        @if (
                                                            !empty($shiprocket_order) &&
                                                                empty($order_tracking_data[0]['pickup_scheduled_date']) &&
                                                                ($shiprocket_order['data']['status_code'] != 4 || $shiprocket_order['data']['status'] != 'PICKUP SCHEDULED') &&
                                                                $shiprocket_order['data']['status'] != 'CANCELED' &&
                                                                $shiprocket_order['data']['status'] != 'CANCELLATION REQUESTED')
                                                            <button type="button" title="Send Pickup Request"
                                                                class="btn btn-primary btn-sm mr-1 send_pickup_request"
                                                                data-fromadmin="1"
                                                                name="{{ $order_tracking_data[0]['shipment_id'] }}"><i
                                                                    class="fas fa-shipping-fast"></i></button>
                                                        @endif

                                                        @if (isset($order_tracking_data[0]['is_canceled']) && $order_tracking_data[0]['is_canceled'] == 0)
                                                            <button type="button" title="Cancel Order"
                                                                class="btn btn-primary btn-sm mr-1 cancel_shiprocket_order"
                                                                data-fromadmin="1"
                                                                name="{{ $order_tracking_data[0]['shiprocket_order_id'] }}"><i
                                                                    class="fas fa-redo-alt"></i></button>
                                                        @endif

                                                        @if (isset($order_tracking_data[0]['label_url']) && !empty($order_tracking_data[0]['label_url']))
                                                            <a href="{{ $order_tracking_data[0]['label_url'] }}"
                                                                title="Download Label" data-fromadmin="1"
                                                                class="btn btn-primary btn-sm mr-1 download_label text-white gap-2"><i
                                                                    class="fas fa-download"></i> Label</a>
                                                        @else
                                                            <button type="button" title="Generate Label"
                                                                class="btn btn-primary btn-sm mr-1 generate_label"
                                                                data-fromadmin="1"
                                                                name="{{ $order_tracking_data[0]['shipment_id'] }}"><i
                                                                    class="fas fa-tags"></i></button>
                                                        @endif

                                                        @if (isset($order_tracking_data[0]['invoice_url']) && !empty($order_tracking_data[0]['invoice_url']))
                                                            <a href="{{ $order_tracking_data[0]['invoice_url'] }}"
                                                                title="Download Invoice" data-fromadmin="1"
                                                                class="btn btn-primary btn-sm mr-1 download_invoice text-white gap-2"><i
                                                                    class="fas fa-download"></i> Invoice</a>
                                                        @else
                                                            <button type="button" title="Generate Invoice"
                                                                class="btn btn-primary btn-sm mr-1 generate_invoice"
                                                                data-fromadmin="1"
                                                                name="{{ $order_tracking_data[0]['shiprocket_order_id'] }}"><i
                                                                    class="far fa-money-bill-alt"></i></button>
                                                        @endif

                                                        @if (isset($order_tracking_data[0]['awb_code']) && !empty($order_tracking_data[0]['awb_code']))
                                                            <a href="https://shiprocket.co/tracking/{{ $order_tracking_data[0]['awb_code'] }}"
                                                                target="_blank" title="Track Order"
                                                                class="btn btn-primary action-btn btn-sm mr-1 track_order text-white"
                                                                data-order-id="{{ $order_tracking_data[0]['shiprocket_order_id'] }}">
                                                                <i class="fas fa-map-marker-alt"></i>
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endif
                                            </div>

                                        </div>
                                    @endif
                                @endfor
                            </div>
                        </div>
                    @endfor
                </form>


            </div>
            <div class="col-lg-4 col-xl-3">


                <div class="card">
                    <h6 class="mb-3">{{ labels('admin_labels.payment_info', 'Payment Info') }}</h6>
                    @if (isset($order_detls[0]->txn_id) && !empty($order_detls[0]->txn_id))
                        <div class="d-flex ">
                            <span>{{ labels('admin_labels.id', 'ID') }}:</span>
                            <span class="text-muted ms-1">#{{ $order_detls[0]->txn_id }}</span>
                        </div>
                    @endif
                    <div class="d-flex mt-2 align-items-center">
                        <span>{{ labels('admin_labels.payment_method', 'Payment Method') }}:</span>
                        <span class="text-muted ms-1">{{ $order_detls[0]->payment_method }}</span>
                        @if (isset($transaction_search_res) && !empty($transaction_search_res))
                            <a href="javascript:void(0)" class="edit_transaction btn active ms-5"
                                title="Update bank transfer receipt status"
                                data-id="{{ $transaction_search_res[0]->id }}"
                                data-txn_id="{{ $transaction_search_res[0]->txn_id }}"
                                data-status="{{ $transaction_search_res[0]->status }}"
                                data-message="{{ $transaction_search_res[0]->message }}"
                                data-bs-target="#payment_transaction_modal" data-bs-toggle="modal">
                                <i class='bx bxs-pencil me-1'></i>{{ labels('admin_labels.edit', 'Edit') }}
                            </a>
                        @endif

                    </div>
                    @if (!empty($bank_transfer))
                        <table class="table">
                            <th></th>
                            <tbody>
                                <tr>
                                    <td>
                                        @php
                                            $status = ['history', 'ban', 'check'];
                                        @endphp

                                        <div class="row">
                                            @php $i = 1; @endphp
                                            @foreach ($bank_transfer as $row1)
                                                @php
                                                    $isPublicDisk = $row1->disk == 'public' ? 1 : 0;
                                                    $imagePath = $isPublicDisk
                                                        ? getImageUrl($row1->attachments)
                                                        : $row1->attachments;
                                                @endphp
                                                <div
                                                    class="col-md-12 align-items-center d-flex justify-content-between mb-2 mt-2">
                                                    <small>[<a href="{{ $imagePath }}"
                                                            target="_blank">Attachment{{ $i }} </a>]</small>
                                                    @if ($row1->status == 0)
                                                        <label for=""
                                                            class="badge bg-warning ms-1">Pending</label>
                                                    @elseif ($row1->status == 1)
                                                        <label for=""
                                                            class="badge bg-danger ms-1">Rejected</label>
                                                    @elseif ($row1->status == 2)
                                                        <label for=""
                                                            class="badge bg-primary ms-1">Accepted</label>
                                                    @else
                                                        <label for="" class="badge bg-danger ms-1">Invalid
                                                            Value</label>
                                                    @endif
                                                    <button class="btn btn-primary btn-xs ms-1 mb-1 delete-data"
                                                        title="Delete"
                                                        data-url="{{ route('admin.orders.delete_receipt', $row1->id) }}"
                                                        data-id="{{ $row1->id }}">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                                @php $i++; @endphp
                                            @endforeach
                                        </div>

                                        <div class="col-md-12">
                                            <select name="update_receipt_status" id="update_receipt_status"
                                                class="form-select status" data-id="{{ $order_detls[0]->id }}"
                                                data-user_id="{{ $order_detls[0]->user_id }}">
                                                <option value=''>
                                                    {{ labels('admin_labels.select', 'Select Status') }}</option>
                                                <option value="1"
                                                    {{ isset($bank_transfer[0]->status) && $bank_transfer[0]->status == 1 ? 'selected' : '' }}>
                                                    Rejected
                                                </option>
                                                <option value="2"
                                                    {{ isset($bank_transfer[0]->status) && $bank_transfer[0]->status == 2 ? 'selected' : '' }}>
                                                    Accepted
                                                </option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    @endif

                </div>

                <div class="card mt-5">
                    <h6>{{ labels('admin_labels.total_order_amount', 'Total Order Amount') }}</h6>
                    <div class="mt-3">
                        <span class="text-muted float-start">{{ labels('admin_labels.sub_total', 'Sub Total') }}</span>
                        <span class="float-end">{{ formateCurrency(formatePriceDecimal($item_subtotal)) }}</span>
                    </div>
                    <div class="mt-3">
                        <span
                            class="text-muted float-start">{{ labels('admin_labels.delivery_charges', 'Shipping Charges') }}</span>
                        <span
                            class="float-end">{{ formateCurrency(formatePriceDecimal($items[0]['seller_delivery_charge'])) }}</span>
                    </div>
                    <div class="mt-3">
                        <span
                            class="text-muted float-start">{{ labels('admin_labels.wallet_balance', 'Wallet Balance') }}</span>
                        <span
                            class="float-end">{{ formateCurrency(formatePriceDecimal($items[0]['wallet_balance'])) }}</span>
                    </div>
                    <div class="mt-3">
                        <span
                            class="text-muted float-start">{{ labels('admin_labels.discount_amount', 'Discount Amount') }}</span>
                        <span class="float-end">
                            {{ formateCurrency(formatePriceDecimal($items[0]['seller_promo_discount'])) }}
                        </span>

                    </div>
                    <hr class="mt-3">
                    <div>
                        @php
                            $total =
                                $item_subtotal +
                                (float) ('' . $order_detls[0]->delivery_charge) -
                                $order_detls[0]->promo_discount -
                                $items[0]['wallet_balance'];
                        @endphp

                        <span class="float-start">{{ labels('admin_labels.total_amount', 'Total Amount') }}</span>
                        <h6 class="float-end">{{ formateCurrency(formatePriceDecimal($total)) }}</h6>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- modal for order tracking -->
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="order_tracking_modal"
        aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="user_name">{{ labels('admin_labels.order_tracking', 'Order Tracking') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <!-- form start -->
                                <form class="form-horizontal submit_form" id="order_tracking_form"
                                    action="{{ route('admin.orders.update_order_tracking') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @method('POST')
                                    @csrf
                                    <input type="hidden" name="order_id" id="order_id">
                                    <input type="hidden" name="order_item_id" id="order_item_id">
                                    <input type="hidden" name="seller_id" id="seller_id">
                                    <div class="card-body pad">
                                        <div class="form-group ">
                                            <label
                                                for="courier_agency">{{ labels('admin_labels.courier_agency', 'Courier Agency') }}</label>
                                            <input type="text" class="form-control" name="courier_agency"
                                                id="courier_agency" placeholder="Courier Agency" />
                                        </div>
                                        <div class="form-group ">
                                            <label
                                                for="tracking_id">{{ labels('admin_labels.tracking_id', 'Tracking Id') }}</label>
                                            <input type="text" class="form-control" name="tracking_id"
                                                id="tracking_id" placeholder="Tracking Id" />
                                        </div>
                                        <div class="form-group ">
                                            <label for="url">{{ labels('admin_labels.url', 'Url') }}</label>
                                            <input type="text" class="form-control" name="url" id="url"
                                                placeholder="URL" />
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="reset"
                                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                            <button type="submit" class="btn btn-primary submit_button"
                                                id="submit_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- model for update bank transfer recipt  -->
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="payment_transaction_modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="user_name"></h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <!-- form start -->
                                <form class="form-horizontal " id="edit_transaction_form"
                                    action="{{ route('admin.customers.edit_transactions') }}" method="POST"
                                    enctype="multipart/form-data">
                                    <input type="hidden" name="id" id="id">
                                    <div class="modal-body">
                                        <div class="col-md-12">
                                            <label for="transaction" class="mb-2 mt-2">
                                                {{ labels('admin_labels.update_transaction', 'Update Transaction') }}
                                            </label>
                                            <select class="form-control form-select" name="status" id="t_status">
                                                <option value="awaiting"> Awaiting </option>
                                                <option value="success"> Success </option>
                                                <option value="failed"> Failed </option>
                                            </select>
                                        </div>
                                        <div class="col-md-12">
                                            <label for="txn_id"
                                                class="mb-2 mt-2">{{ labels('admin_labels.transaction_id', 'Transaction ID') }}</label>
                                            <input type="text" class="form-control" name="txn_id" id="txn_id"
                                                placeholder="txn_id" />
                                        </div>
                                        <div class="col-md-12">
                                            <label for="message"
                                                class="mb-2 mt-2">{{ labels('admin_labels.message', 'Message') }}</label>
                                            <input type="text" class="form-control" name="message"
                                                id="transaction_message" placeholder="Message" />
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="reset"
                                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                            <button type="submit" class="btn btn-primary submit_button"
                                                id="">{{ labels('admin_labels.update_transaction', 'Update Transaction') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                        </div>

                    </div>


                </div>
                </form>
            </div>
        </div>
    </div>


    <!-- modal for create shiprocket order -->
    <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="order_parcel_modal"
        data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ labels('admin_labels.create_shiprocket_order_parcel', 'Create Shiprocket Order Parcel') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="card card-info">
                                <!-- form start -->
                                <form class="form-horizontal " id="shiprocket_order_parcel_form" action=""
                                    method="POST">
                                    @method('POST')
                                    @csrf
                                    @php
                                        $total_items = count($items);
                                    @endphp

                                    <div class="card-body pad">
                                        <div class="form-group">

                                            <input type="hidden" id="order_id" name="order_id"
                                                value="{{ $order_detls[0]->id }}" />
                                            <input type="hidden" name="user_id" id="user_id"
                                                value="{{ $order_detls[0]->user_id }}" />
                                            <input type="hidden" name="total_order_items" id="total_order_items"
                                                value="{{ $total_items }}" />
                                            <input type="hidden" name="shiprocket_seller_id" value="" />
                                            <input type="hidden" name="fromadmin" value="1" id="fromadmin" />
                                            <textarea id="order_items" name="order_items[]" hidden><?= json_encode($items, JSON_FORCE_OBJECT) ?></textarea>
                                        </div>
                                        <div class="mt-1 p-2 text-white rounded create-parcel-note">
                                            <p>
                                                <b>Note:</b> Make your pickup location associated with the order is
                                                verified from <a class="text-white text-decoration-underline"
                                                    href="https://app.shiprocket.in/company-pickup-location?redirect_url="
                                                    target="_blank"> Shiprocket
                                                    Dashboard </a> and then in <a href="" target="_blank"
                                                    class="text-white text-decoration-underline"> admin panel
                                                </a>. If it is not verified you will not be able to generate AWB
                                                later on.
                                            </p>
                                        </div>
                                        <div class="form-group row mt-4">
                                            <div class="col-4">
                                                <label class="form-label"
                                                    for="txn_amount">{{ labels('admin_labels.pickup_location', 'Pickup Location') }}</label>
                                            </div>
                                            <div class="col-8">
                                                <input type="text" class="form-control" name="pickup_location"
                                                    id="pickup_location" placeholder="Pickup Location" value=""
                                                    readonly />
                                            </div>
                                        </div>
                                        <ul>
                                            <li>
                                                <h6>{{ labels('admin_labels.total_weight_of_parcel', 'Total Weight Of Parcel') }}
                                                </h6>
                                            </li>
                                        </ul>
                                        <div class="form-group row mt-4">
                                            <div class="col-3">
                                                <label class="form-label" for="parcel_weight"
                                                    class="control-label col-md-12">{{ labels('admin_labels.weight', 'Weight') }}
                                                    <small>(kg)</small> <span
                                                        class='text-asterisks text-xs'>*</span></label>
                                                <input type="number" min=0 class="form-control" name="parcel_weight"
                                                    placeholder="Parcel Weight" id="parcel_weight" value=""
                                                    step=".01">
                                            </div>
                                            <div class="col-3">
                                                <label class="form-label" for="parcel_height"
                                                    class="control-label col-md-12">{{ labels('admin_labels.height', 'Height') }}
                                                    <small>(cms)</small> <span
                                                        class='text-asterisks text-xs'>*</span></label>
                                                <input type="number" class="form-control" name="parcel_height"
                                                    placeholder="Parcel Height" id="parcel_height" value=""
                                                    min="1">
                                            </div>
                                            <div class="col-3">
                                                <label class="form-label" for="parcel_breadth"
                                                    class="control-label col-md-12">{{ labels('admin_labels.breadth', 'Breadth') }}
                                                    <small>(cms)</small>
                                                    <span class='text-asterisks text-xs'>*</span></label>
                                                <input type="number" class="form-control" name="parcel_breadth"
                                                    placeholder="Parcel Breadth" id="parcel_breadth" value=""
                                                    min="1">
                                            </div>
                                            <div class="col-3">
                                                <label class="form-label" for="parcel_length"
                                                    class="control-label col-md-12">{{ labels('admin_labels.length', 'Length') }}
                                                    <small>(cms)</small> <span
                                                        class='text-asterisks text-xs'>*</span></label>
                                                <input type="number" class="form-control" name="parcel_length"
                                                    placeholder="Parcel Length" id="parcel_length" value=""
                                                    min="1">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer  d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary"
                                            data-dismiss="modal">{{ labels('admin_labels.close', 'Close') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary create_shiprocket_parcel">{{ labels('admin_labels.create_order', 'Create Order') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal for send digital product -->
    <div id="sendMailModal" class="modal fade editSendMail" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-focus="false">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ labels('admin_labels.manage_digital_product', 'Manage Digital Product') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>

                <div class="modal-body ">
                    <form class="form-horizontal form-submit-event submit_form"
                        action="{{ route('admin.orders.send_digital_product') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <input type="hidden" name="order_id" value="<?= $order_detls[0]->order_id ?>">
                            <input type="hidden" name="order_item_id" value="">
                            <input type="hidden" name="username" value="<?= $order_detls[0]->uname ?>">
                            <div class="row form-group">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="product_name">{{ labels('admin_labels.email', 'Customer Email') }}
                                        </label>
                                        <input type="text" class="form-control" id="email" name="email"
                                            value="<?= $order_detls[0]->user_email ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="product_name">{{ labels('admin_labels.subject', 'Subject') }}
                                        </label>
                                        <input type="text" class="form-control" id="subject"
                                            placeholder="Enter Subject for email" name="subject" value="">
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="product_name">{{ labels('admin_labels.message', 'Message') }}</label>
                                        <textarea class="textarea" id="mail_msg" placeholder="Message for Email" name="message"></textarea>
                                    </div>
                                </div>

                                <div class="col-12 mt-2" id="digital_media_container">
                                    <label for="image" class="ml-2">{{ labels('admin_labels.file', 'File') }} <span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <div class='col-md-12'><a class="uploadFile img btn btn-primary text-white btn-sm"
                                            data-input='pro_input_file' data-isremovable='0'
                                            data-media_type="archive,document" data-is-multiple-uploads-allowed='0'
                                            data-bs-toggle="modal" data-bs-target="#media-upload-modal"
                                            value="Upload Photo"><i class='fa fa-upload'></i> Upload</a></div>
                                    <div class="container-fluid row image-upload-section">
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3" id="submit_btn"
                                value="Save">{{ labels('admin_labels.send_mail', 'Send Mail') }}</button>
                        </div>
                    </form>
                </div>
                <div class="d-flex justify-content-center">
                    <div class="form-group" id="error_box">
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
