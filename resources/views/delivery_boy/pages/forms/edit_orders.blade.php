@extends('delivery_boy/layout')
@php
    use App\Models\OrderItems;
@endphp
@section('title')
    {{ labels('admin_labels.manage_orders', 'Manage Orders') }}
@endsection
@section('content')
    <x-delivery_boy.breadcrumb :title="labels('admin_labels.order_details', 'Order Details')" :subtitle="labels('admin_labels.see_every_detail_steer_every_step', 'See Every Detail Steer Every Step')" :breadcrumbs="[
        ['label' => labels('admin_labels.manage_orders', 'Manage Orders')],
        ['label' => labels('admin_labels.orders', 'Orders')],
    ]" />

    <section>

        <div class="card content-area p-3">
            <div class="align-items-center d-flex justify-content-between">
                <div>
                    <span class="body-default text-muted">{{ labels('admin_labels.order_number', 'Order Number') }}</span>
                    <p class="lead">#{{ $order_detls['order_id'] }}</p>
                </div>
                <div class="align-items-center d-flex">
                    <span class="body-default text-muted">{{ labels('admin_labels.order_date', 'Order Date') }} :</span>
                    <span class="body-default me-3"><?= date('d M, Y', strtotime($order_detls['created_at'])) ?></span>
                </div>
            </div>
        </div>
        <div class="row mt-5 order-info">
            <div class="col-md-12">
                <div class="card">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6>{{ labels('admin_labels.customer_info', 'Customer Info') }}</h6>
                            <div class="d-flex mt-3 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.name', 'Name') }}:</span>
                                <span class="caption text-muted">{{ $order_detls['username'] }}</span>
                            </div>

                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.mobile', 'Contact') }}:</span>
                                @if (isset($order_detls['mobile']))
                                    <span class="caption text-muted">{{ $order_detls['mobile'] }}</span>
                                @else
                                    <span
                                        class="caption text-muted">{{ isset($mobile_data) ? $mobile_data[0]->mobile : '' }}</span>
                                @endif
                            </div>
                            <div class="d-flex mt-2 align-items-center">
                                <span class="body-default me-1">{{ labels('admin_labels.address', 'Address') }}:</span>
                                <span class="caption text-muted">{{ $order_detls['address'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="m-0 row">
            <div class="content-area p-3 card mt-2 col-md-8">
                <div class="card-body">
                    <tr>
                        <th class="w-10px" id="">
                            <h6 class="mb-4">{{ labels('admin_labels.items', 'Items') }}</h6>
                        </th>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            @foreach ($sellers as $seller)
                                @php
                                    $otp_system = $system_settings['order_delivery_otp_system'];
                                    $seller_data = fetchDetails(
                                        'users',
                                        ['id' => $seller['user_id']],
                                        ['username', 'fcm_id'],
                                    );
                                    $seller_otp = fetchDetails(
                                        'order_items',
                                        ['order_id' => $order_detls['order_id'], 'seller_id' => $seller['id']],
                                        'otp',
                                    )[0]->otp;
                                    $order_charges_data = fetchDetails('order_charges', [
                                        'order_id' => $order_detls['order_id'],
                                        'seller_id' => $seller['id'],
                                    ]);
                                    $seller_order = getOrderDetails([
                                        'o.id' => $order_detls['order_id'],
                                        'oi.seller_id' => $seller['id'],
                                    ]);
                                    $user_id = auth()->check() ? auth()->user()->id : null;
                                    $total = 0;
                                    $tax_amount = 0;
                                @endphp

                                <div>
                                    <div>
                                        <div class="d-flex justify-content-sm-start gap-2">
                                            <span class="title-color">Seller:</span>
                                            <span
                                                class="badge badge-sm bg-primary d-flex align-items-center">{{ ucwords($seller_data[0]->username) }}</span>
                                        </div>

                                        <input type="hidden" name="edit_order_id" value="{{ $order_detls['order_id'] }}">
                                        <input type="hidden" name="delivery_boy_id" value="{{ $user_id }}">

                                        <div class="table-responsive mt-4">
                                            <table
                                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 edit-order-table">
                                                <thead class="thead-light thead-50 text-capitalize">
                                                    <tr>
                                                        <th>{{ labels('admin_labels.name', 'Name') }}</th>
                                                        <th>{{ labels('admin_labels.image', 'Image') }}</th>
                                                        <th>{{ labels('admin_labels.quantity', 'Quantity') }}</th>
                                                        <th>{{ labels('admin_labels.product_type', 'Product Type') }}</th>
                                                        <th>{{ labels('admin_labels.variations', 'Variant') }}</th>
                                                        <th>{{ labels('admin_labels.discount', 'Discounted Price') }}</th>
                                                        <th>{{ labels('admin_labels.active_status', 'Active Status') }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($items as $item)
                                                        @php
                                                            $selected = '';
                                                            $item['discounted_price'] = $item['discounted_price'] ?: 0;
                                                            $subtotal =
                                                                $item['quantity'] != 0 &&
                                                                $item['discounted_price'] > 0 &&
                                                                $item['price'] > $item['discounted_price']
                                                                    ? $item['price'] - $item['discounted_price']
                                                                    : $item['price'] * $item['quantity'];
                                                            $total += $subtotal;
                                                            $tax_amount += $item['tax_amount'];
                                                            $item_subtotal = $item['item_subtotal'];
                                                            $total += $tax_amount;
                                                        @endphp

                                                        @if ($seller['id'] == $item['seller_id'])
                                                            @php
                                                                $order_tracking_data = getShipmentId(
                                                                    $item['id'],
                                                                    $order_detls['id'],
                                                                );
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

                                                            @endphp
                                                            @php
                                                                if (
                                                                    $item['active_status'] == 'return_request_pending'
                                                                ) {
                                                                    $status = 'Return Requested';
                                                                } elseif (
                                                                    $item['active_status'] == 'return_request_approved'
                                                                ) {
                                                                    $status = 'Return Approved';
                                                                } elseif (
                                                                    $item['active_status'] == 'return_request_decline'
                                                                ) {
                                                                    $status = 'Return Declined';
                                                                } else {
                                                                    $status = $item['active_status'];
                                                                }
                                                            @endphp
                                                            <tr>
                                                                <td>
                                                                    <h6 class="title-color">
                                                                        {{ $item['pname'] }}
                                                                    </h6>
                                                                </td>
                                                                <td class="align-items-center d-flex">
                                                                    <div class="order-image-box">
                                                                        <a href={{ getMediaImageUrl($item['product_image']) }}
                                                                            data-lightbox="image-'{{ $item['product_id'] }}'">
                                                                            <img class="rounded"
                                                                                src="{{ getMediaImageUrl($item['product_image']) }}"
                                                                                alt="{{ $item['pname'] }}">
                                                                        </a>
                                                                    </div>
                                                                </td>
                                                                <td>{{ $item['quantity'] }}</td>
                                                                <td>{{ ucwords(str_replace('_', ' ', $item['product_type'])) }}
                                                                </td>
                                                                <td>{{ isset($item['product_variants']) && !empty($item['product_variants'][0]['variant_values'])
                                                                    ? str_replace(',', ' | ', $item['product_variants'][0]['variant_values'])
                                                                    : '-' }}
                                                                </td>
                                                                <td>{{ $item['discounted_price'] > 0 ? $item['discounted_price'] : $item['price'] }}
                                                                </td>
                                                                <td>
                                                                    <small>
                                                                        <span
                                                                            class="badge badge-sm bg-{{ $badges[$item['active_status']] }}">
                                                                            {{ $status }}
                                                                        </span>
                                                                    </small>
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
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </td>
                    </tr>
                </div>
            </div>
            <div class="col-md-4 mt-2">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-center align-items-center">
                            <h6 class="mb-3">{{ labels('admin_labels.order_status', 'Order Status') }}</h6>
                        </div>
                        <select name="status" class="form-control parcel_status mb-3 mt-3">
                            <option value=''>Select Status</option>
                            <option value="received" <?= $item['active_status'] == 'received' ? 'selected' : '' ?>>
                                Received</option>
                            <option value="processed" <?= $item['active_status'] == 'processed' ? 'selected' : '' ?>>
                                Processed
                            </option>
                            <option value="shipped" <?= $item['active_status'] == 'shipped' ? 'selected' : '' ?>>
                                Shipped</option>
                            <option value="delivered" <?= $item['active_status'] == 'delivered' ? 'selected' : '' ?>>
                                Delivered
                            </option>
                        </select>
                        @if ($otp_system == 1)
                            <input type="number" name="otp" id="otp" min="0"
                                class="form-control my-2 d-none otp-field" placeholder="Enter Otp Here">
                        @endif

                        <div class="d-flex justify-content-end align-items-center">
                            <button type="button" class="btn btn-primary update_status_delivery_boy"
                                data-id='<?= $order_detls['parcel_id'] ?>'
                                data-otp-system='<?= $otp_system != 0 ? '1' : '0'
                                ?>'>{{ labels('admin_labels.update', 'Update') }}</button>
                        </div>
                        @if (isset($order_detls['discount']) && $order_detls['discount'] > 0)
                            @php
                                $discount = $order_detls['total_payable'] * ($order_detls['discount'] / 100);
                                $total = round($order_detls['total_payable'] - $discount, 2);
                            @endphp
                        @endif

                        @if (
                            ($order_detls['payment_method'] == 'COD' || $order_detls['payment_method'] == 'cod') &&
                                $order_detls['is_cod_collected'] == 1)
                            <p class="m-0 mt-2 font-weight-bold h5 text-success">Cash Collected</p>
                        @elseif ($order_detls['payment_method'] != 'cod' && $order_detls['payment_method'] != 'COD')
                            <p class="m-0 mt-2 font-weight-bold h5 text-success">Payment Online Done</p>
                        @elseif (
                            ($order_detls['payment_method'] == 'COD' || $order_detls['payment_method'] == 'cod') &&
                                $order_detls['is_cod_collected'] == 0)
                            <p class="m-0 mt-2 font-weight-bold h5 bg-danger">Cash On Delivery. Collect<span
                                    class="text-middle-line mx-1">{{ formateCurrency(formatePriceDecimal($total + $order_detls['delivery_charge'] - $order_detls['wallet_balance'] - $order_detls['promo_discount'])) }}</span>
                            </p>
                        @endif
                    </div>
                </div>
                <div class="card mt-2">
                    <div class="card-body">
                        <!-- Total -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Total</span></div>
                            <div class="col-md-6 text-right">{{ $total }}</div>
                        </div>

                        <!-- Tax -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Tax</span></div>
                            <div class="col-md-6 text-right">{{ $tax_amount }} <small>(All Tax Included In
                                    Total)</small>
                            </div>
                        </div>

                        <!-- Delivery Charge -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Delivery Charge</span></div>
                            <div class="col-md-6 text-right">
                                {{ formateCurrency(formatePriceDecimal($items[0]['seller_delivery_charge'])) }}
                                @php $total += $order_detls['delivery_charge']; @endphp
                            </div>
                        </div>

                        <!-- Wallet Balance -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Wallet Balance</span></div>
                            <div class="col-md-6 text-right">
                                {{ number_format($items[0]['wallet_balance'], 2) }}
                                @php $total -= $items[0]['wallet_balance']; @endphp
                            </div>
                        </div>

                        <!-- Discount -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Discount %</span></div>
                            <div class="col-md-6 text-right">
                                {{ formateCurrency(formatePriceDecimal($items[0]['seller_promo_discount'])) }}</div>
                        </div>

                        <!-- Promo Code Discount -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Promo Code Discount</span></div>
                            <div class="col-md-6 text-right">
                                {{ formateCurrency(formatePriceDecimal($items[0]['seller_promo_discount'])) }}</div>
                        </div>

                        <!-- Payable Total -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Payable Total</span></div>
                            <div class="col-md-6 text-right">
                                @if (
                                    ($order_detls['payment_method'] == 'COD' || $order_detls['payment_method'] == 'cod') &&
                                        $order_detls['is_cod_collected'] == 0)
                                    {{ formateCurrency(formatePriceDecimal($total)) }}
                                @else
                                    <span class="text-danger">0</span>
                                @endif
                            </div>
                        </div>

                        <!-- Deliver By -->
                        {{-- <div class="row mb-2">
                        <div class="col-md-6"><span class="text-muted float-start">Deliver By</span></div>
                        <div class="col-md-6 text-right">{{ $item['deliver_by'] }}</div>
                    </div> --}}

                        <!-- Payment Method -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Payment Method</span></div>
                            <div class="col-md-6 text-right">{{ $order_detls['payment_method'] }}</div>
                        </div>

                        <!-- Address -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Address</span></div>
                            <div class="col-md-6 text-right">{{ $order_detls['address'] }}</div>
                        </div>

                        <!-- Delivery Date & Time -->
                        <div class="row mb-2">
                            <div class="col-md-6"><span class="text-muted float-start">Delivery Date & Time</span></div>
                            <div class="col-md-6 text-right">
                                {{ !empty($order_detls['delivery_date']) && $order_detls['delivery_date'] != null
                                    ? date('d-M-Y', strtotime($order_detls['delivery_date'])) . ' - ' . $order_detls['delivery_time']
                                    : 'Anytime' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
