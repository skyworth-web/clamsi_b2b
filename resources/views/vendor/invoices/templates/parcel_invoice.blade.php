<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style type="text/css" media="screen">
        html {
            font-family: "Poppins", sans-serif;
            font-family: "Rubik", sans-serif !important;
            line-height: 1.15;
            margin: 0;
        }

        body {
            font-family: "Poppins", sans-serif;
            font-family: "Rubik", sans-serif !important;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            text-align: left;
            background-color: #fff;
            font-size: 10px;
            margin: 36pt;
        }

        .d-flex {
            display: flex !important;
        }

        h4 {
            margin-top: 0;
            margin-bottom: 0.5rem;
        }

        p {
            margin-top: 0;
            margin-bottom: 1rem;
        }

        strong {
            font-weight: bolder;
        }

        img {
            vertical-align: middle;
            border-style: none;
        }

        table {
            border-collapse: collapse;
        }

        th {
            text-align: inherit;
        }

        h4,
        .h4 {
            margin-bottom: 0.5rem;
            font-weight: 500;
            line-height: 1.2;
        }

        h4,
        .h4 {
            font-size: 1.5rem;
        }

        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #212529;
        }

        .table th,
        .table td {
            padding: 0.75rem;
            vertical-align: top;
        }

        .table.table-items td {
            border-top: 1px solid #dee2e6;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #dee2e6;
        }

        .mt-5 {
            margin-top: 3rem !important;
        }

        .pr-0,
        .px-0 {
            padding-right: 0 !important;
        }

        .pl-0,
        .px-0 {
            padding-left: 0 !important;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-uppercase {
            text-transform: uppercase !important;
        }

        * {
            font-family: "DejaVu Sans";
        }

        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        table,
        th,
        tr,
        td,
        p,
        div {
            line-height: 1.1;
        }

        .party-header {
            font-size: 1.5rem;
            font-weight: 400;
        }

        .total-amount {
            font-size: 12px;
            font-weight: 700;
        }

        .border-0 {
            border: none !important;
        }

        .cool-gray {
            color: #6b7280;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .container-fluid {
            margin-left: auto;
            margin-right: auto;
            width: 100%;
        }

        .text-left {
            text-align: left !important;
        }

        .text-right {
            text-align: right !important;
        }

        .row {
            display: -ms-flexbox;
            display: flex;
            -ms-flex-wrap: wrap;
            flex-wrap: wrap;
            margin-right: -7.5px;
            margin-left: -7.5px;
        }

        .col-md-6 {
            -ms-flex: 0 0 50%;
            flex: 0 0 50%;
            max-width: 50%;
        }

        .align-items-end {
            -ms-flex-align: end !important;
            align-items: flex-end !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .mt-3 {
            margin-top: 1rem !important;
        }

        h5 {
            font-size: 20px;
            line-height: 24px;
            font-weight: 600;
        }
    </style>

</head>

<body>

    <div class="content-wrapper" style="min-height: 237.5px;">
        <!-- Content Header (Page header) -->
        <!-- Main content -->
        @php
            $settings = getSettings('system_settings', true);
            $settings = json_decode($settings, true);
        @endphp
        <section class="content">
            <div class="container-fluid">
                <div class="row  m-3">
                    <div class="col-md-12">
                        @php
                            $parcel_details = $invoice->buyer->custom_fields['parcel_details'][0];
                            // dd($parcel_details);
                        @endphp
                        <!-- /.row -->
                        <!-- Table row -->
                        <!-- seller container -->
                        {{-- @dd($invoice->buyer->custom_fields['parcel_details'][0]); --}}
                        @for ($i = 0; $i < count($invoice->seller->custom_fields['seller_ids']); $i++)
                            @php

                                $s_user_data = fetchDetails(
                                    'users',
                                    ['id' => $invoice->seller->custom_fields['seller_user_ids'][$i]],
                                    ['email', 'mobile', 'address', 'country_code', 'city', 'pincode'],
                                );

                                $seller_data = fetchDetails(
                                    'seller_data',
                                    ['user_id' => $invoice->seller->custom_fields['seller_user_ids'][$i]],
                                    ['pan_number', 'authorized_signature'],
                                );

                                $seller_store_data = fetchDetails(
                                    'seller_store',
                                    [
                                        'seller_id' => $invoice->seller->custom_fields['seller_ids'][$i],
                                        'store_id' => $invoice->buyer->custom_fields['store_id'],
                                    ],
                                    ['store_name', 'logo', 'tax_name', 'tax_number'],
                                );

                                $order_caharges_data = fetchDetails('order_charges', [
                                    'order_id' => $invoice->buyer->custom_fields['order_id'],
                                    'seller_id' => $invoice->seller->custom_fields['seller_ids'][$i],
                                ]);

                                $seller_signature = getMediaImageUrl(
                                    $seller_data[0]->authorized_signature,
                                    'SELLER_IMG_PATH',
                                );
                                $seller_logo = getMediaImageUrl($seller_store_data[0]->logo, 'SELLER_IMG_PATH');
                            @endphp

                            <div class="card card-info mb-4" id="invoice-1626">
                                <div class="container-fluid">
                                    <div class="row mt-2" id="section-not-to-print">
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4 text-center">
                                            <h3><strong>{{ labels('front_messages.invoice', 'Invoice') }}
                                                </strong></h3>
                                        </div>
                                        <div class="col-md-4"></div>
                                    </div>
                                    <div class="print-section">
                                        <table class="table">
                                            <tr>
                                                <td class="text-left"><img src="{{ $seller_logo }}" alt="logo"
                                                        height="80"></td>
                                                <td class="text-right">
                                                    <b>Parcel No : </b>#
                                                    {{ $parcel_details['id'] }} <br>
                                                    <b>Order Date: </b>
                                                    {{ \Carbon\Carbon::parse($invoice->buyer->custom_fields['date_added'])->toDateString() }}
                                                </td>
                                            </tr>
                                        </table>

                                        <table class="table">
                                            <tr>
                                                <td>
                                                    <strong>
                                                        <p>{{ labels('front_messages.sold_by', 'Sold By') }}</p>
                                                    </strong>
                                                    {{ ucfirst($seller_store_data[0]->store_name) }}<br>
                                                    {{ ucfirst($s_user_data[0]->address) }}<br>
                                                    <p>{{ labels('front_messages.email', 'Email:') }}
                                                        {{ $s_user_data[0]->email }}<br>
                                                        {{ labels('front_messages.customer_care', 'Customer Care :') }}
                                                        {{ $s_user_data[0]->mobile }}</p>

                                                    <strong>
                                                    </strong>
                                                    <p><strong>{{ labels('front_messages.pan_number', 'Pan Number :') }}</strong>{{ $seller_data[0]->pan_number }}
                                                    </p>
                                                </td>


                                                <td class="text-left">
                                                    <strong>
                                                        <p>{{ labels('front_messages.shipping_address', 'Shipping Address') }}
                                                        </p>
                                                    </strong>
                                                    <span>
                                                        {{ $invoice->buyer->name }}<br>
                                                        {{ $invoice->buyer->custom_fields['address'] }}<br>
                                                        {{ $invoice->seller->custom_fields['mobile_number'] }}
                                                    </span>
                                                    <br>
                                                    <b>{{ labels('front_messages.order_no', 'Order No :') }}</b>#
                                                    {{ $invoice->buyer->custom_fields['order_id'] }} <br>
                                                    <b>{{ labels('front_messages.order_date', 'Order Date:') }}</b>
                                                    {{ \Carbon\Carbon::parse($invoice->buyer->custom_fields['date_added'])->format('j F Y') }}
                                                </td>

                                            </tr>

                                        </table>
                                        <div class="row m-3">
                                            <p>{{ labels('front_messages.product_details', 'Product Details:') }}
                                            </p>
                                        </div>
                                        <div class="row m-3">
                                            <div class="col-md-12 table-responsive">
                                                <table class="table borderless text-center text-sm">
                                                    <thead class="">
                                                        <tr>
                                                            <th>{{ labels('front_messages.sr_no', 'Sr No.') }}</th>
                                                            <th>{{ labels('front_messages.name', 'Name') }}</th>
                                                            <th>{{ labels('front_messages.variants', 'Variants') }}
                                                            </th>
                                                            <th>{{ labels('front_messages.price', 'Price') }}</th>
                                                            <th>
                                                            </th>
                                                            <th>
                                                            </th>
                                                            <th>{{ labels('front_messages.qty', 'Qty') }}</th>
                                                            <th>{{ labels('front_messages.subtotal', 'SubTotal (₹)') }}
                                                            </th>
                                                        </tr>

                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $j = 1;
                                                            $total = $quantity = $total_tax = $total_discount = $final_sub_total = 0;
                                                        @endphp
                                                        @foreach ($invoice->getCustomData() as $row)
                                                            {{-- @dd($row); --}}
                                                            @php
                                                                $product_variants = getVariantsValuesById(
                                                                    $row->product_variant_id,
                                                                );
                                                                $product_variants =
                                                                    isset($product_variants[0]['variant_values']) &&
                                                                    !empty($product_variants[0]['variant_values'])
                                                                        ? str_replace(
                                                                            ',',
                                                                            ' | ',
                                                                            $product_variants[0]['variant_values'],
                                                                        )
                                                                        : '-';
                                                                if (
                                                                    isset($row->is_prices_inclusive_tax) &&
                                                                    $row->is_prices_inclusive_tax == 1
                                                                ) {
                                                                    $tax_amount =
                                                                        $row->price -
                                                                        $row->price * (100 / (100 + $row->tax_percent));
                                                                } else {
                                                                    $tax_amount =
                                                                        $row->price * ($row->tax_percent / 100);
                                                                }
                                                                $total +=
                                                                    floatval($row->price + $tax_amount) *
                                                                    floatval($row->quantity);
                                                                $quantity += floatval($row->quantity);
                                                                $total_tax += floatval($row->tax_amount);
                                                                $price_without_tax = $row->price - $tax_amount;
                                                                $sub_total = floatval($row->price) * $row->quantity;
                                                                $final_sub_total += $sub_total;
                                                                // dd($row);
                                                                // $product_name = json_decode($row->pname, true);
                                                                // $product_name = $product_name['en'] ?? '';
                                                            @endphp

                                                            <tr>
                                                                <td>{{ $j }}<br></td>
                                                                <td class="w-25">{{ $row->pname }}<br></td>
                                                                <td class="w-25">{{ $product_variants }}<br></td>
                                                                <td>{{ formateCurrency(formatePriceDecimal($row->price)) }}<br>
                                                                </td>
                                                                <td>
                                                                </td>
                                                                <td><br></td>
                                                                <td>{{ $row->quantity }}<br></td>
                                                                <td>{{ formateCurrency(formatePriceDecimal($sub_total)) }}<br>
                                                                </td>
                                                            </tr>
                                                            @php
                                                                $j++;
                                                            @endphp
                                                        @endforeach
                                                    </tbody>

                                                    <tbody>
                                                        <tr>
                                                            <th></th>
                                                            <!-- <th></th> -->
                                                            <!-- <th></th> -->
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>{{ labels('front_messages.total', 'Total') }}
                                                            </th>
                                                            <th>{{ $quantity }} <br>
                                                            </th>
                                                            <th> {{ formateCurrency(formatePriceDecimal($final_sub_total)) }}<br>
                                                            </th>
                                                        </tr>
                                                        <!--  -->
                                                    </tbody>
                                                </table>


                                            </div>
                                            <!-- /.col -->
                                        </div>
                                        <table class="table">
                                            <tr>
                                                <td class="text-left">
                                                    <b>{{ labels('front_messages.payment_method', 'Payment Method :') }}</b>
                                                    {{ $invoice->buyer->custom_fields['payment_method'] }}
                                                </td>
                                                <td>
                                                    <table class="table borderless text-sm text-right">
                                                        @php
                                                            // dd($invoice->buyer->custom_fields['discount']);
                                                            $item_total =
                                                                floatval($final_sub_total) +
                                                            $invoice->buyer->custom_fields['discount']; @endphp
                                                        <tr>
                                                            {{-- <td>{{ labels('front_messages.total_order_price', 'Total Order Price') }}
                                                                ({{ $currency_symbol }})</td>
                                                            <td>+ {{ formatePriceDecimal($item_total) }} </td> --}}
                                                        </tr>
                                                        <tr>
                                                            <td>{{ labels('front_messages.delivery_charge', 'Delivery Charge') }}
                                                                ({{ $currency_symbol }})</td>
                                                            @php
                                                                $delivery_charge = 0;

                                                            @endphp
                                                            {{-- @dd($parcel_details); --}}
                                                            <td>+
                                                                @if (!empty($parcel_details))
                                                                    @php
                                                                        $delivery_charge =
                                                                            $parcel_details['delivery_charge'];
                                                                    @endphp
                                                                @endif

                                                                {{ formatePriceDecimal($delivery_charge) }}
                                                            </td>
                                                            @php $total += $order_caharges_data[0]->delivery_charge; @endphp
                                                        </tr>
                                                        @if (isset($invoice->buyer->custom_fields['promo_code']))
                                                            <tr>
                                                                <th>{{ labels('front_messages.promo_discount', 'Promo') }}
                                                                    ({{ $invoice->buyer->custom_fields['promo_code'] }})
                                                                    {{ labels('front_messages.discount', 'Discount') }}
                                                                    ({{ floatval($invoice->buyer->custom_fields['promo_code_discount']) }}
                                                                    {{ $invoice->buyer->custom_fields['promo_code_discount_type'] == 'percentage' ? '%' : ' ' }})
                                                                </th>
                                                                <td>-
                                                                    @php
                                                                        echo $order_caharges_data[0]->promo_discount;
                                                                        $total =
                                                                            $total -
                                                                            $order_caharges_data[0]->promo_discount;
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                        @endif
                                                        @if (isset($invoice->buyer->custom_fields['discount']) &&
                                                                $invoice->buyer->custom_fields['discount'] > 0 &&
                                                                $invoice->buyer->custom_fields['discount'] != null)
                                                            <tr>
                                                                {{-- <th>{{ labels('front_messages.special_discount', 'Special Discount') }}

                                                                </th>
                                                                <td>-
                                                                    {{ $currency_symbol }}({{ $invoice->buyer->custom_fields['discount'] }})
                                                                    @php
                                                                        // $special_discount = round(
                                                                        //     ($total *
                                                                        //         $invoice->buyer->custom_fields[
                                                                        //             'discount'
                                                                        //         ]) /
                                                                        //         100,
                                                                        //     2,
                                                                        // );
                                                                        // $total = floatval($total - $special_discount);
                                                                        $total = floatval($total);
                                                                        // echo $special_discount;
                                                                    @endphp
                                                                </td> --}}
                                                            </tr>
                                                        @endif
                                                        <tr>
                                                            <td>{{ labels('front_messages.final_total', 'Final Total') }}
                                                                ({{ $currency_symbol }})</td>
                                                            @php
                                                                $final_total =
                                                                    $final_sub_total +
                                                                    $parcel_details['delivery_charge'];
                                                            @endphp
                                                            <td>{{ formatePriceDecimal($final_total) }}</td>
                                                        </tr>
                                                    </table>
                                                </td>

                                            </tr>
                                        </table>
                                        <table class="table">
                                            <tr>
                                                <td></td>
                                                <td class="text-right">
                                                    <p>{{ isKeySetAndNotEmpty($settings, 'app_name') ? $settings['app_name'] : '' }}
                                                    </p>
                                                    <img src="{{ $seller_signature }}" alt="logo" height="50">
                                                    <p class="mt-3">
                                                        {{ labels('front_messages.authorized_signatory', 'Authorized Signatory') }}
                                                    </p>
                                                </td>

                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        @endfor

                        <!--/.card-->
                    </div>
                    <!--/.col-md-12-->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
</body>

</html>

</html>
