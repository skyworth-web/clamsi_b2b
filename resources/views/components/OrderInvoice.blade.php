<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="{{ getMediaImageUrl($web_settings['favicon']) }}" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="{{ asset('frontend/elegant/css/plugins.css') }}" rel="stylesheet">
    <title>{{ labels('front_messages.order_invoice', 'Order Invoice') }} | {{ $system_settings['app_name'] }}</title>
</head>
<style>
    .image-box {
        width: 170px;
        height: 50px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .image-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }

    .btn-primary {
        background-color: #041632;
        border: solid 1px #041632;
    }

    .btn-primary:hover {
        background-color: #f4a51c;
        border: solid 1px #f4a51c;
    }

    @media print {

        #section-not-to-print,
        #section-not-to-print * {
            display: none;
        }
    }
</style>

<body>

    <!-- Header -->

    @php
    $order_details = $order_details['order_data'][0];
    if ($order_details->address_id != null || $order_details->address_id != '') {
    $userName = $order_details->order_recipient_person;
    } else {
    $userName = $order_details->username;
    }
    @endphp
    <section class="bg-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-lg-6">
                    <div class="image-box">
                        <img src="{{ getMediaImageUrl($web_settings['favicon']) }}" alt="{{ $system_settings['app_name'] }}">
                    </div>
                    <p class="text-muted mt-3 text-capitalize">{{ labels('front_messages.hello', 'Hello') }}, {{ $userName }}.<br>{{ labels('front_messages.thank_you', 'Thank you for Your Order and for choosing our') }} {{ $system_settings['app_name'] }}. </p>
                </div>
                <div class="col-lg-6 text-end">
                    <h2 class="text-danger">{{ labels('front_messages.invoice', 'Invoice') }}</h2>
                    <p class="text-muted mt-3"><small>{{ labels('front_messages.order', 'ORDER') }}</small>
                        #{{ $order_details->id }}<br><small>{{ $order_details->created_at }}</small></p>
                </div>
            </div>
        </div>
    </section>
    <!-- /Header -->

    <!-- Order Details -->
    <section class="bg-light py-4">
        <div class="container">
            <div class="row">
                <div class="col">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">{{ labels('front_messages.item', 'Item') }}</th>
                                <th scope="col">{{ labels('front_messages.variant', 'Variant') }}</th>
                                <th scope="col" class="text-center">{{ labels('front_messages.quantity', 'Quantity') }}</th>
                                <th scope="col" class="text-end">{{ labels('front_messages.subtotal', 'Subtotal') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($order_details->order_items as $order_items)
                            <tr>
                                <td>{{ $order_items->product_name }}</td>
                                <td><small>{{ $order_items->variant_name }}</small></td>
                                <td class="text-center">{{ $order_items->quantity }}</td>
                                <td class="text-end">{{ $currency_symbol . $order_items->sub_total }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- /Order Details -->

    <!-- Total -->
    <section class="bg-light py-4">
        <div class="container">
            <div class="row">
                <div class="col">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="text-end">{{ labels('front_messages.subtotal', 'Subtotal') }}</td>
                                <td class="text-end">{{ $currency_symbol . $order_details->total }}</td>
                            </tr>
                            <tr>
                                <td class="text-end">{{ labels('front_messages.shipping_handling', 'Shipping &amp; Handling') }}</td>
                                <td class="text-end">+{{ $currency_symbol . $order_details->delivery_charge }}</td>
                            </tr>
                            <tr>
                                <td class="text-end">{{ labels('front_messages.coupon_discount', 'Coupon Discount') }}</td>
                                <td class="text-end">-{{ $currency_symbol . $order_details->promo_discount }}</td>
                            </tr>
                            @if ($order_details->wallet_balance != 0.0)
                            <tr>
                                <td class="text-end">{{ labels('front_messages.wallet_balance_used', 'Wallet Balance Used') }}</td>
                                <td class="text-end">{{ $currency_symbol . $order_details->wallet_balance }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-end"><strong>{{ labels('front_messages.grand_total', 'Grand Total (Incl.Tax)') }}</strong></td>
                                <td class="text-end">
                                    <strong>{{ $currency_symbol . $order_details->final_total }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    <!-- /Total -->

    <!-- Information -->
    @php
    $txn = fetchDetails('transactions', ['order_id' => $order_details->id], 'txn_id');
    $txn_id = "";
    if (count($txn) >= 1) {
    $txn_id = $txn[0]->txn_id;
    }
    @endphp
    <section class="bg-light py-4">
        <div class="container">
            <div class="row">
                <div class="col">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ labels('front_messages.billing_information', 'BILLING INFORMATION') }}</h6>
                            <p>{{$order_details->order_items[0]->store_name}}<br> {{$order_details->order_items[0]->seller_address}}<br> T:
                                {{$order_details->order_items[0]->seller_mobile}}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ labels('front_messages.payment_method', 'PAYMENT METHOD') }}</h6>
                            <p>{{ $order_details->wallet_balance == $order_details->final_total ? 'Wallet' : $order_details->payment_method }}<br>
                                {!!(count($txn) >= 1) ? labels('front_messages.transaction_id', 'Transaction ID') . ": <span class='text-danger'>" . $txn_id . "</span>" : ""!!}
                            </p>
                        </div>
                    </div>
                    @if ($order_details->type != 'digital_product')
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">{{ labels('front_messages.shipping_information', 'SHIPPING INFORMATION') }}</h6>
                            <p>{{ $order_details->address }}<br> T:
                                {{ $order_details->mobile }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!-- /Information -->

    <!-- Footer -->
    <section class="bg-light">
        <div class="container">
            <div class="row">
                <div class="col">
                    <p class="text-muted m-0">{{ labels('front_messages.have_a_nice_day', 'Have a nice day.') }}</p>
                </div>
            </div>
            <div class="d-flex justify-content-center align-items-center" id="section-not-to-print">
                <button class="btn btn-primary" onclick="{window.print()};">{{ labels('front_messages.print', 'Print') }}</button>
            </div>
        </div>
    </section>
    <!-- /Footer -->

    <!-- Bootstrap Bundle with Popper -->
    <script src="{{ asset('frontend/elegant/js/plugins.js') }}"></script>
</body>

</html>
