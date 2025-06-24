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

                        <!-- /.row -->
                        <!-- Table row -->
                        <!-- seller container -->


                        @for ($i = 0; $i < count($invoice->seller->custom_fields['seller_ids']); $i++)
                            @php

                                // Fetch seller data
                                $s_user_data = fetchDetails(
                                    'users',
                                    ['id' => $invoice->seller->custom_fields['seller_user_ids'][$i]],
                                    ['email', 'mobile', 'address', 'country_code', 'city', 'pincode'],
                                );

                                // Fetch seller data details
                                $seller_data = fetchDetails(
                                    'seller_data',
                                    ['user_id' => $invoice->seller->custom_fields['seller_user_ids'][$i]],
                                    ['pan_number', 'authorized_signature'],
                                );

                                // Fetch seller store details
                                $seller_store_data = fetchDetails(
                                    'seller_store',
                                    [
                                        'seller_id' => $invoice->seller->custom_fields['seller_ids'][$i],
                                        'store_id' => $invoice->buyer->custom_fields['store_id'],
                                    ],
                                    ['store_name', 'logo', 'tax_name', 'tax_number'],
                                );

                                // Fetch order charges
                                $order_caharges_data = fetchDetails('order_charges', [
                                    'order_id' => $invoice->buyer->custom_fields['order_id'],
                                    'seller_id' => $invoice->seller->custom_fields['seller_ids'][$i],
                                ]);

                                // Check if seller data exists and then access the authorized signature
                                $seller_signature = isset($seller_data[0])
                                    ? getMediaImageUrl($seller_data[0]->authorized_signature, 'SELLER_IMG_PATH')
                                    : null;

                                // Check if seller store data exists and then access the logo
                                $seller_logo = isset($seller_store_data[0])
                                    ? getMediaImageUrl($seller_store_data[0]->logo, 'SELLER_IMG_PATH')
                                    : null;

                            @endphp

                            <div class="card card-info mb-4" id="invoice-1626">
                                <div class="container-fluid">
                                    <div class="row mt-2" id="section-not-to-print">
                                        <div class="col-md-4"></div>
                                        <div class="col-md-4 text-center">
                                            <h3><strong>Invoice</strong></h3>
                                        </div>
                                        <div class="col-md-4"></div>
                                    </div>
                                    <div class="print-section">
                                        <table class="table">
                                            <tr>
                                                <td class="text-left"><img src="{{ $seller_logo }}" alt="logo"
                                                        height="80"></td>
                                                <td class="text-right">
                                                    <b>Order No : </b>#
                                                    {{ $invoice->buyer->custom_fields['order_id'] }} <br>
                                                    <b>Order Date: </b>
                                                    {{ \Carbon\Carbon::parse($invoice->buyer->custom_fields['date_added'])->format('j F Y') }}

                                                </td>
                                            </tr>
                                        </table>

                                        <table class="table">
                                            <tr>
                                                <td>
                                                    <strong>
                                                        <p>Sold By</p>
                                                    </strong>

                                                    <!-- Check if seller store data exists -->
                                                    {{ isset($seller_store_data[0]) ? ucfirst($seller_store_data[0]->store_name) : 'N/A' }}<br>

                                                    <!-- Check if user data exists -->
                                                    {{ isset($s_user_data[0]) ? ucfirst($s_user_data[0]->address) : 'Address not available' }}
                                                    <br>

                                                    <p>Email:
                                                        {{ isset($s_user_data[0]) ? $s_user_data[0]->email : 'Email not available' }}<br>
                                                        Customer Care :
                                                        {{ isset($s_user_data[0]) ? $s_user_data[0]->mobile : 'Mobile not available' }}
                                                    </p>

                                                    <strong></strong>
                                                    <p><strong>Pan Number :</strong>
                                                        {{ isset($seller_data[0]) ? $seller_data[0]->pan_number : 'Pan Number not available' }}
                                                    </p>
                                                </td>


                                                <td class="text-left">
                                                    <strong>
                                                        <p>Shipping Address</p>
                                                    </strong>
                                                    <span>
                                                        {{ $invoice->buyer->name }}<br>
                                                        {{ $invoice->buyer->custom_fields['address'] }}<br>
                                                        {{ $invoice->seller->custom_fields['mobile_number'] }}
                                                    </span>
                                                    <br>
                                                </td>
                                            </tr>

                                        </table>
                                        <div class="row m-3">
                                            <p>Product Details:</p>
                                        </div>
                                        <div class="row m-3">
                                            <div class="col-md-12 table-responsive">
                                                <table class="table borderless text-center text-sm">
                                                    <thead class="">
                                                        <tr>
                                                            <th>Sr No.</th>
                                                            <!-- <th>Product Code</th> -->
                                                            <th>Name</th>
                                                            <th>variants</th>

                                                            <th>Price (Tax Already Included)</th>
                                                            <th></th>
                                                            <th></th>
                                                            {{-- <th>Tax (%)</th>
                                                            <th>Tax Amount (₹)</th> --}}
                                                            <th>Qty</th>
                                                            <th>SubTotal (₹)</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @php
                                                            $j = 1;
                                                            $total = $quantity = $total_tax = $total_discount = $final_sub_total = 0;
                                                        @endphp

                                                        @foreach ($invoice->getCustomData() as $row)
                                                            @if ($invoice->seller->custom_fields['seller_ids'][$i] == $row['seller_id'])
                                                                @php
                                                                    $product_variants = getVariantsValuesById(
                                                                        $row['product_variant_id'],
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
                                                                    $price =
                                                                        isset($row['product_special_price']) &&
                                                                        $row['product_special_price'] > 0
                                                                            ? $row['product_special_price']
                                                                            : $row['product_price'];

                                                                    if (
                                                                        isset($row['is_prices_inclusive_tax']) &&
                                                                        $row['is_prices_inclusive_tax'] == 1
                                                                    ) {
                                                                        $tax_amount =
                                                                            $price -
                                                                            $price *
                                                                                (100 / (100 + $row['tax_percent']));
                                                                    } else {
                                                                        $tax_amount =
                                                                            $price * ($row['tax_percent'] / 100);
                                                                    }

                                                                    $total +=
                                                                        floatval($row['price'] + $tax_amount) *
                                                                        floatval($row['quantity']);
                                                                    $hsn_code = $row['hsn_code']
                                                                        ? $row['hsn_code']
                                                                        : '-';
                                                                    $quantity += floatval($row['quantity']);
                                                                    $total_tax += floatval($row['tax_amount']);
                                                                    $price_without_tax = $row['price'] - $tax_amount;
                                                                    $sub_total =
                                                                        floatval($row['price']) * $row['quantity'];
                                                                    // dd($row['price']);
                                                                    $final_sub_total += $sub_total;
                                                                    // dd($final_sub_total);

                                                                    // dd($row);
                                                                    // dd($tax_amount);
                                                                    $product_name = json_decode($row['pname'], true);
                                                                    $product_name = $product_name['en'] ?? '';
                                                                @endphp

                                                                <tr>
                                                                    <td>{{ $j }}<br></td>
                                                                    <td class="w-25">{{ $product_name }}<br></td>
                                                                    <td class="w-25">{{ $product_variants }}<br></td>
                                                                    <td>{{ formateCurrency(formatePriceDecimal($row['price'])) }}<br>
                                                                    </td>
                                                                    <td></td>
                                                                    <td></td>
                                                                    {{-- <td>{{ $row['tax_percent'] ? $row['tax_percent'] : '0' }}<br> </td>
                                                                    <td>{{ number_format($tax_amount, 2) }}<br></td> --}}
                                                                    <td>{{ $row['quantity'] }}<br></td>
                                                                    <td>{{ formateCurrency(formatePriceDecimal($sub_total)) }}<br>
                                                                    </td>
                                                                </tr>

                                                                @php $j++; @endphp
                                                            @endif
                                                        @endforeach
                                                    </tbody>

                                                    <tbody>
                                                        <tr>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th>Total</th>
                                                            <th>{{ $quantity }} <br>
                                                            </th>
                                                            <th> {{ formateCurrency(formatePriceDecimal($final_sub_total)) }}<br>
                                                            </th>
                                                        </tr>
                                                        <!--  -->
                                                    </tbody>
                                                </table>

                                                {{-- @dd($final_sub_total); --}}
                                            </div>
                                            <!-- /.col -->
                                        </div>
                                        <table class="table">
                                            <tr>
                                                <td class="text-left">
                                                    <b>Payment Method : </b>
                                                    {{ $invoice->buyer->custom_fields['payment_method'] }}
                                                </td>
                                                <td>
                                                    {{-- @dd($tax_amount); --}}
                                                    <table class="table borderless text-sm text-right">
                                                        @php $item_total = (floatval($final_sub_total) + $invoice->buyer->custom_fields['discount']); @endphp
                                                        <tr>
                                                            <td>Total Order Price ({{ $currency_symbol }})</td>
                                                            <td>+ {{ formatePriceDecimal($item_total) }} </td>
                                                        </tr>
                                                        <tr>
                                                            <td>Delivery Charge ({{ $currency_symbol }})</td>
                                                            <td>+
                                                                {{ formatePriceDecimal($order_caharges_data[0]->delivery_charge) }}
                                                            </td>
                                                            @php $total += $order_caharges_data[0]->delivery_charge; @endphp
                                                        </tr>
                                                        @if (isset($invoice->buyer->custom_fields['promo_code']))
                                                            <tr>
                                                                <th>
                                                                    Promo
                                                                    ({{ $invoice->buyer->custom_fields['promo_code'] }})
                                                                    Discount
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


                                                        @if (isset($invoice->buyer->custom_fields['discount']) && $invoice->buyer->custom_fields['discount'] > 0)
                                                            <tr>
                                                                <th>
                                                                    Special Discount {{ $currency_symbol }}
                                                                    {{-- ({{ $invoice->buyer->custom_fields['discount'] }}%) --}}
                                                                </th>
                                                                <td> - {{ $invoice->buyer->custom_fields['discount'] }}
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
                                                                        // dd($total);
                                                                    @endphp
                                                                </td>
                                                            </tr>
                                                        @endif

                                                        <tr>
                                                            <td>Final Total ({{ $currency_symbol }})</td>
                                                            {{-- @php $final_total = $final_sub_total - $invoice->buyer->custom_fields['discount'] - $order_caharges_data[0]->promo_discount + $order_caharges_data[0]->delivery_charge; @endphp --}}
                                                            @php $final_total = $final_sub_total - $order_caharges_data[0]->promo_discount + $order_caharges_data[0]->delivery_charge; @endphp
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
                                                    <p class="mt-3">Authorized Signatory</p>
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
