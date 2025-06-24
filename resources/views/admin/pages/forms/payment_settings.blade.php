@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.payment_method_setting', 'Payment Method Setting') }}
@endsection
@php
    $hide_unwanted_payment_gateway = false;
@endphp
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.payment_methods', 'Payment Methods')" :subtitle="labels(
        'admin_labels.control_and_optimize_payment_channels_with_precision',
        'Control and Optimize Payment Channels with Precision',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.payment_method_setting', 'Payment Method Setting')],
    ]" />

    <div class="row">
        <div class="col-md-12 col-xl-4">
            <div class="card">
                <div class="tab-content p-4" id="pills-tabContent-vertical-pills">
                    <div class="tab-pane tab-example-design fade show active" id="pills-vertical-pills-design" role="tabpanel"
                        aria-labelledby="pills-vertical-pills-design-tab">
                        <div class="row">

                            <div class="nav flex-column nav-pills p-2" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <a class="nav-link active border payment_method_title" data-bs-toggle="pill" href="#paypal"
                                    role="tab" aria-selected="true">{{ labels('admin_labels.paypal', 'Paypal') }}</a>
                                <a class="nav-link border payment_method_title" data-bs-toggle="pill" href="#phonepe"
                                    role="tab" aria-selected="true">{{ labels('admin_labels.phonepe', 'phonepe') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#razorpay" role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.razorpay', 'Razorpay') }}</a>

                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#paystack" role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.paystack', 'Paystack') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#stripe" role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.stripe', 'Stripe') }}</a>

                                @if ($hide_unwanted_payment_gateway == true)
                                    <a class="nav-link border payment_method_title mt-2" id=""
                                        data-bs-toggle="pill" href="#midtrans" role="tab"
                                        aria-controls="v-pills-profile" aria-selected="false"
                                        tabindex="-1">{{ labels('admin_labels.midtrans', 'Midtrans') }}</a>
                                    <a class="nav-link border payment_method_title mt-2" id=""
                                        data-bs-toggle="pill" href="#flutterwave" role="tab"
                                        aria-controls="v-pills-profile" aria-selected="false"
                                        tabindex="-1">{{ labels('admin_labels.flutterwave', 'Flutterwave') }}</a>
                                    <a class="nav-link border payment_method_title mt-2" id=""
                                        data-bs-toggle="pill" href="#fatoorah" role="tab"
                                        aria-controls="v-pills-profile" aria-selected="false"
                                        tabindex="-1">{{ labels('admin_labels.myfatoorah', 'Myfatoorah') }}</a>
                                    <a class="nav-link border payment_method_title mt-2" id=""
                                        data-bs-toggle="pill" href="#direct_bank_transfer" role="tab"
                                        aria-controls="v-pills-profile" aria-selected="false"
                                        tabindex="-1">{{ labels('admin_labels.direct_bank_transfer', 'Direct Bank Transfer') }}</a>
                                @endif
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#cash_on_delivery" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.cash_on_delivery', 'Cash On Delivery') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-xl-8 mt-md-2 mt-xl-0">
            <form class="form-horizontal submit_form" action="{{ route('payment_setting.store') }}" method="POST"
                id="">
                @csrf

                <div class="card">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade active show" id="paypal" role="tabpanel">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0">
                                            {{ labels('admin_labels.paypal', 'Paypal') }}
                                        </h5>
                                    </div>
                                    <select name="paypal_method"
                                        class="form-select status_dropdown <?= isset($settings['paypal_method']) && $settings['paypal_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                        <option value="1"
                                            <?= isset($settings['paypal_method']) && $settings['paypal_method'] == '1' ? 'selected' : '' ?>>
                                            Active
                                        </option>
                                        <option value="0"
                                            <?= isset($settings['paypal_method']) && $settings['paypal_method'] == '0' ? 'selected' : '' ?>>
                                            Deactive
                                        </option>
                                    </select>

                                </div>

                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group mb-2">

                                                <label class="form-label"
                                                    for="">{{ labels('admin_labels.payment_mode', 'Payment Mode') }}
                                                    <small>[ sandbox / live ]</small><span
                                                        class="text-asterisks text-sm">*</span>
                                                </label>

                                            </div>
                                            <div class="form-group col-md-12 mt-2">
                                                <select name="paypal_mode" class="form-control form-select">
                                                    <option value="">
                                                        {{ labels('admin_labels.select_mode', 'Select Mode') }}
                                                    </option>
                                                    <option value="sandbox"
                                                        <?= isset($settings['paypal_mode']) && $settings['paypal_mode'] == 'sandbox' ? 'selected' : '' ?>>
                                                        Sandbox (Testing)
                                                    </option>
                                                    <option value="production"
                                                        <?= isset($settings['paypal_mode']) && $settings['paypal_mode'] == 'production' ? 'selected' : '' ?>>
                                                        Production (Live)
                                                    </option>
                                                </select>

                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="paypal_business_email">{{ labels('admin_labels.paypal_business_email', 'Paypal Business Email') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="paypal_business_email"
                                                placeholder="Paypal Business Email"
                                                value="<?= isKeySetAndNotEmpty($settings, 'paypal_business_email') ? $settings['paypal_business_email'] : '' ?>" />

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">


                                            <label class="form-label mt-3"
                                                for="paypal_client_id">{{ labels('admin_labels.paypal_client_id', 'Paypal Client ID') }}
                                            </label><span class="text-asterisks text-sm">*</span>


                                            <input type="text" class="form-control" name="paypal_client_id"
                                                placeholder="Paypal Client Id"
                                                value="<?= isKeySetAndNotEmpty($settings, 'paypal_client_id') ? $settings['paypal_client_id'] : '' ?>" />

                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label mt-3"
                                                for="currency_code">{{ labels('admin_labels.currency', 'Currency') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <select class="form-control form-select" name="currency_code">
                                                @php
                                                    $currencyCodes = [
                                                        'AUD',
                                                        'BRL',
                                                        'CAD',
                                                        'CNY',
                                                        'CZK',
                                                        'DKK',
                                                        'EUR',
                                                        'HKD',
                                                        'HUF',
                                                        'INR',
                                                        'ILS',
                                                        'JPY',
                                                        'MYR',
                                                        'MXN',
                                                        'TWD',
                                                        'NZD',
                                                        'NOK',
                                                        'PHP',
                                                        'PLN',
                                                        'GBP',
                                                        'RUB',
                                                        'SGD',
                                                        'SEK',
                                                        'CHF',
                                                        'THB',
                                                        'USD',
                                                    ];
                                                @endphp

                                                @foreach ($currencyCodes as $code)
                                                    <option value="{{ $code }}"
                                                        {{ isset($settings['currency_code']) && $settings['currency_code'] == $code ? 'selected' : '' }}>
                                                        {{ $code }}</option>
                                                @endforeach

                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-12 col-xl-6">
                                            <label for=""
                                                class="form-label">{{ labels('admin_labels.notification_url', 'Notification URL') }}
                                                <small>(Set this as Webhook URL in your
                                                    PayPal
                                                    account)</small></label>
                                            <input type="text" class="form-control" readonly
                                                value="{{ url('admin/webhook/paypal_webhook') }}" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="phonepe" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0">
                                        {{ labels('admin_labels.phonepe', 'Phonepe') }}
                                    </h5>
                                </div>
                                <select name="phonepe_method"
                                    class="form-select status_dropdown <?= isset($settings['phonepe_method']) && $settings['phonepe_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                    <option value="1"
                                        <?= isset($settings['phonepe_method']) && $settings['phonepe_method'] == '1' ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="0"
                                        <?= isset($settings['phonepe_method']) && $settings['phonepe_method'] == '0' ? 'selected' : '' ?>>
                                        Deactive
                                    </option>
                                </select>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="">{{ labels('admin_labels.payment_mode', 'Payment Mode') }}
                                            <small>[ sandbox / UTA / live ]</small><span
                                                class="text-asterisks text-sm">*</span>
                                        </label>
                                        <div class="form-group col-md-12">
                                            <select name="phonepe_mode" class="form-control form-select">
                                                <option value="">
                                                    {{ labels('admin_labels.select_mode', 'Select Mode') }}
                                                </option>
                                                <option value="sandbox"
                                                    <?= isset($settings['phonepe_mode']) && $settings['phonepe_mode'] != null && $settings['phonepe_mode'] == 'sandbox' ? 'selected' : '' ?>>
                                                    Sandbox ( Testing )</option>
                                                <option value="production"
                                                    <?= isset($settings['phonepe_mode']) && $settings['phonepe_mode'] != null && $settings['phonepe_mode'] == 'production' ? 'selected' : '' ?>>
                                                    UTA</option>
                                                <option value="production"
                                                    <?= isset($settings['phonepe_mode']) && $settings['phonepe_mode'] != null && $settings['phonepe_mode'] == 'production' ? 'selected' : '' ?>>
                                                    Production ( Live )</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="phonepe_marchant_id">{{ labels('admin_labels.phonepe_marchant_id', 'Phonepe Marchant ID') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control" name="phonepe_marchant_id"
                                            placeholder="Phonepe Marchant ID"
                                            value="{{ $settings['phonepe_marchant_id'] ?? '' }}" />
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="phonepe_salt_index">{{ labels('admin_labels.phonepe_salt_index', 'Salt index') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control width" name="phonepe_salt_index"
                                            placeholder="Salt Index"
                                            value="{{ $settings['phonepe_salt_index'] ?? '' }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="phonepe_salt_key">{{ labels('admin_labels.phonepe_salt_key', 'Salt key') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control width" name="phonepe_salt_key"
                                            placeholder="Salt Key" value="{{ $settings['phonepe_salt_key'] ?? '' }}" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="razorpay_webhook_secret_key">{{ labels('admin_labels.payment_endpoint_url', 'Payment Endpoint URL') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control width" readonly name=""
                                            value="{{ url('admin/webhook/phonepe_webhook') }}" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="razorpay" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0">
                                        {{ labels('admin_labels.razorpay', 'Razorpay') }}
                                    </h5>
                                </div>
                                <select name="razorpay_method"
                                    class="form-select status_dropdown <?= isset($settings['razorpay_method']) && $settings['razorpay_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                    <option value="1"
                                        <?= isset($settings['razorpay_method']) && $settings['razorpay_method'] == '1' ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="0"
                                        <?= isset($settings['razorpay_method']) && $settings['razorpay_method'] == '0' ? 'selected' : '' ?>>
                                        Deactive
                                    </option>
                                </select>

                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">

                                        <label class="form-label"
                                            for="razorpay_key_id">{{ labels('admin_labels.razorpay_key_id', 'Razorpay Key ID') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control" name="razorpay_key_id"
                                            placeholder="Razorpay Key ID"
                                            value="<?= isKeySetAndNotEmpty($settings, 'razorpay_key_id') ? $settings['razorpay_key_id'] : '' ?>" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="razorpay_secret_key">{{ labels('admin_labels.razorpay_secret_key', 'Razorpay Secret Key') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control" name="razorpay_secret_key"
                                            placeholder="Razorpay Key ID"
                                            value="<?= isKeySetAndNotEmpty($settings, 'razorpay_secret_key') ? $settings['razorpay_secret_key'] : '' ?>" />
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="razorpay_webhook_secret_key">{{ labels('admin_labels.webhook_secret_key', 'Webhook Secret Key') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control width"
                                            name="razorpay_webhook_secret_key"
                                            value="<?= isKeySetAndNotEmpty($settings, 'razorpay_webhook_secret_key') ? $settings['razorpay_webhook_secret_key'] : '' ?>" />
                                    </div>


                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="razorpay_webhook_secret_key">{{ labels('admin_labels.payment_endpoint_url', 'Payment Endpoint URL') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control width" readonly name=""
                                            value="{{ url('admin/webhook/razorpay_webhook') }}" />
                                    </div>



                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="paystack" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0">
                                        {{ labels('admin_labels.paystack', 'Paystack') }}
                                    </h5>
                                </div>
                                <select name="paystack_method"
                                    class="form-select status_dropdown <?= @$settings['paystack_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                    <option value="1" <?= @$settings['paystack_method'] == '1' ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="0" <?= @$settings['paystack_method'] == '0' ? 'selected' : '' ?>>
                                        Deactive
                                    </option>
                                </select>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="paystack_key_id">{{ labels('admin_labels.paystack_key_id', 'Paystack key ID') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control" name="paystack_key_id"
                                            value="<?= isKeySetAndNotEmpty($settings, 'paystack_key_id') ? $settings['paystack_key_id'] : '' ?>"
                                            placeholder="Paystack Public Key" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="paystack_secret_key">{{ labels('admin_labels.secret_key', 'Secret key') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control" name="paystack_secret_key"
                                            value="<?= isKeySetAndNotEmpty($settings, 'paystack_secret_key') ? $settings['paystack_secret_key'] : '' ?>"
                                            placeholder="Paystack Secret Key" />
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-12 col-xl-8">
                                        <label class="form-label"
                                            for="paystack_webhook_url">{{ labels('admin_labels.payment_endpoint_url', 'Payment Endpoint URL') }}
                                            <small>(Set this as
                                                Endpoint URL in your Paystack account)</small></label>
                                        <input type="text" class="form-control" name="paystack_webhook_url"
                                            value="{{ url('admin/webhook/paystack_webhook') }}" disabled />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="stripe" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0">
                                        {{ labels('admin_labels.stripe', 'Stripe') }}
                                    </h5>
                                </div>
                                <select name="stripe_method"
                                    class="form-select status_dropdown <?= @$settings['stripe_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                    <option value="1" <?= @$settings['stripe_method'] == '1' ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="0" <?= @$settings['stripe_method'] == '0' ? 'selected' : '' ?>>
                                        Deactive
                                    </option>
                                </select>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="stripe_payment_mode">{{ labels('admin_labels.payment_mode', 'Payment Mode') }}
                                            <small>[
                                                sandbox
                                                / live
                                                ]</small></label><span class="text-asterisks text-sm">*</span>
                                        <select name="stripe_payment_mode" class="form-select">
                                            <option value="">
                                                {{ labels('admin_labels.select_mode', 'Select Mode') }}
                                            </option>
                                            <option value="test"
                                                <?= isset($settings['stripe_payment_mode']) && $settings['stripe_payment_mode'] == 'test' ? 'selected' : '' ?>>
                                                Test</option>
                                            <option value="live"
                                                <?= isset($settings['stripe_payment_mode']) && $settings['stripe_payment_mode'] == 'live' ? 'selected' : '' ?>>
                                                Live</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="stripe_webhook_url">{{ labels('admin_labels.payment_endpoint_url', 'Payment Endpoint URL') }}
                                            <small>(Set this as
                                                Endpoint URL in your Stripe account)</small></label>
                                        <input type="text" class="form-control" name="stripe_webhook_url"
                                            value="{{ url('admin/webhook/stripe_webhook') }}" disabled />
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="stripe_publishable_key">{{ labels('admin_labels.stripe_publishable_key', 'Stripe Publishable Key') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control mt-2" name="stripe_publishable_key"
                                            value="<?= isKeySetAndNotEmpty($settings, 'stripe_publishable_key') ? $settings['stripe_publishable_key'] : '' ?>"
                                            placeholder="Stripe Publishable Key" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="stripe_secret_key">{{ labels('admin_labels.stripe_secret_key', 'Stripe Secret Key') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control mt-2" name="stripe_secret_key"
                                            value="<?= isKeySetAndNotEmpty($settings, 'stripe_secret_key') ? $settings['stripe_secret_key'] : '' ?>"
                                            placeholder="Stripe Secret Key" />
                                    </div>
                                </div>
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="stripe_webhook_secret_key">{{ labels('admin_labels.webhook_secret_key', 'Webhook Secret Key') }}</label><span
                                            class="text-asterisks text-sm">*</span>
                                        <input type="text" class="form-control mt-2" name="stripe_webhook_secret_key"
                                            value="<?= isKeySetAndNotEmpty($settings, 'stripe_webhook_secret_key') ? $settings['stripe_webhook_secret_key'] : '' ?>"
                                            placeholder="Stripe Webhook Secret Key" />
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label"
                                            for="">{{ labels('admin_labels.currency', 'Currency') }}
                                            <small>[ Stripe
                                                supported
                                                ]</small> <a href="https://stripe.com/docs/currencies" target="_BLANK"><i
                                                    class="fa fa-link"></i></a></label><span
                                            class="text-asterisks text-sm">*</span>
                                        <select name="stripe_currency_code" class="form-control form-select mt-2">
                                            <option value="">Select Currency Code</option>
                                            @php
                                                $currencyOptions = [
                                                    'INR' => 'Indian rupee',
                                                    'USD' => 'United States dollar',
                                                    'AED' => 'United Arab Emirates Dirham',
                                                    'AFN' => 'Afghan Afghani',
                                                    'ALL' => 'Albanian Lek',
                                                    'AMD' => 'Armenian Dram',
                                                    'ANG' => 'Netherlands Antillean Guilder',
                                                    'AOA' => 'Angolan Kwanza',
                                                    'ARS' => 'Argentine Peso',
                                                    'AUD' => 'Australian Dollar',
                                                    'AWG' => 'Aruban Florin',
                                                    'AZN' => 'Azerbaijani Manat',
                                                    'BAM' => 'Bosnia-Herzegovina Convertible Mark',
                                                    'BBD' => 'Bajan dollar',
                                                    'BDT' => 'Bangladeshi Taka',
                                                    'BGN' => 'Bulgarian Lev',
                                                    'BIF' => 'Burundian Franc',
                                                    'BMD' => 'Bermudan Dollar',
                                                    'BND' => 'Brunei Dollar',
                                                    'BOB' => 'Bolivian Boliviano',
                                                    'BRL' => 'Brazilian Real',
                                                    'BSD' => 'Bahamian Dollar',
                                                    'BWP' => 'Botswanan Pula',
                                                    'BZD' => 'Belize Dollar',
                                                    'CAD' => 'Canadian Dollar',
                                                    'CDF' => 'Congolese Franc',
                                                    'CHF' => 'Swiss Franc',
                                                    'CLP' => 'Chilean Peso',
                                                    'CNY' => 'Chinese Yuan',
                                                    'COP' => 'Colombian Peso',
                                                    'CRC' => 'Costa Rican Colón',
                                                    'CVE' => 'Cape Verdean Escudo',
                                                    'CZK' => 'Czech Koruna',
                                                    'DJF' => 'Djiboutian Franc',
                                                    'DKK' => 'Danish Krone',
                                                    'DOP' => 'Dominican Peso',
                                                    'DZD' => 'Algerian Dinar',
                                                    'EGP' => 'Egyptian Pound',
                                                    'ETB' => 'Ethiopian Birr',
                                                    'EUR' => 'Euro',
                                                    'FJD' => 'Fijian Dollar',
                                                    'FKP' => 'Falkland Island Pound',
                                                    'GBP' => 'Pound sterling',
                                                    'GEL' => 'Georgian Lari',
                                                    'GIP' => 'Gibraltar Pound',
                                                    'GMD' => 'Gambian dalasi',
                                                    'GNF' => 'Guinean Franc',
                                                    'GTQ' => 'Guatemalan Quetzal',
                                                    'GYD' => 'Guyanaese Dollar',
                                                    'HKD' => 'Hong Kong Dollar',
                                                    'HNL' => 'Honduran Lempira',
                                                    'HRK' => 'Croatian Kuna',
                                                    'HTG' => 'Haitian Gourde',
                                                    'HUF' => 'Hungarian Forint',
                                                    'IDR' => 'Indonesian Rupiah',
                                                    'ILS' => 'Israeli New Shekel',
                                                    'ISK' => 'Icelandic Króna',
                                                    'JMD' => 'Jamaican Dollar',
                                                    'JPY' => 'Japanese Yen',
                                                    'KES' => 'Kenyan Shilling',
                                                    'KGS' => 'Kyrgystani Som',
                                                    'KHR' => 'Cambodian riel',
                                                    'KMF' => 'Comorian franc',
                                                    'KRW' => 'South Korean won',
                                                    'KYD' => 'Cayman Islands Dollar',
                                                    'KZT' => 'Kazakhstani Tenge',
                                                    'LAK' => 'Laotian Kip',
                                                    'LBP' => 'Lebanese pound',
                                                    'LKR' => 'Sri Lankan Rupee',
                                                    'LRD' => 'Liberian Dollar',
                                                    'LSL' => 'Lesotho loti',
                                                    'MAD' => 'Moroccan Dirham',
                                                    'MDL' => 'Moldovan Leu',
                                                    'MGA' => 'Malagasy Ariary',
                                                    'MKD' => 'Macedonian Denar',
                                                    'MMK' => 'Myanmar Kyat',
                                                    'MNT' => 'Mongolian Tugrik',
                                                    'MOP' => 'Macanese Pataca',
                                                    'MRO' => 'Mauritanian Ouguiya',
                                                    'MUR' => 'Mauritian Rupee',
                                                    'MVR' => 'Maldivian Rufiyaa',
                                                    'MWK' => 'Malawian Kwacha',
                                                    'MXN' => 'Mexican Peso',
                                                    'MYR' => 'Malaysian Ringgit',
                                                    'MZN' => 'Mozambican metical',
                                                    'NAD' => 'Namibian dollar',
                                                    'NGN' => 'Nigerian Naira',
                                                    'NIO' => 'Nicaraguan Córdoba',
                                                    'NOK' => 'Norwegian Krone',
                                                    'NPR' => 'Nepalese Rupee',
                                                    'NZD' => 'New Zealand Dollar',
                                                    'PAB' => 'Panamanian Balboa',
                                                    'PEN' => 'Sol',
                                                    'PGK' => 'Papua New Guinean Kina',
                                                    'PHP' => 'Philippine peso',
                                                    'PKR' => 'Pakistani Rupee',
                                                    'PLN' => 'Poland złoty',
                                                    'PYG' => 'Paraguayan Guarani',
                                                    'QAR' => 'Qatari Rial',
                                                    'RON' => 'Romanian Leu',
                                                    'RSD' => 'Serbian Dinar',
                                                    'RUB' => 'Russian Ruble',
                                                    'RWF' => 'Rwandan franc',
                                                    'SAR' => 'Saudi Riyal',
                                                    'SBD' => 'Solomon Islands Dollar',
                                                    'SCR' => 'Seychellois Rupee',
                                                    'SEK' => 'Swedish Krona',
                                                    'SGD' => 'Singapore Dollar',
                                                    'SHP' => 'Saint Helenian Pound',
                                                    'SLL' => 'Sierra Leonean Leone',
                                                    'SOS' => 'Somali Shilling',
                                                    'SRD' => 'Surinamese Dollar',
                                                    'STD' => 'Sao Tome Dobra',
                                                    'SZL' => 'Swazi Lilangeni',
                                                    'THB' => 'Thai Baht',
                                                    'TJS' => 'Tajikistani Somoni',
                                                    'TOP' => 'Tongan Paʻanga',
                                                    'TRY' => 'Turkish lira',
                                                    'TTD' => 'Trinidad & Tobago Dollar',
                                                    'TWD' => 'New Taiwan dollar',
                                                    'TZS' => 'Tanzanian Shilling',
                                                    'UAH' => 'Ukrainian hryvnia',
                                                    'UGX' => 'Ugandan Shilling',
                                                    'UYU' => 'Uruguayan Peso',
                                                    'UZS' => 'Uzbekistani Som',
                                                    'VND' => 'Vietnamese dong',
                                                    'VUV' => 'Vanuatu Vatu',
                                                    'WST' => 'Samoa Tala',
                                                    'XAF' => 'Central African CFA franc',
                                                    'XCD' => 'East Caribbean Dollar',
                                                    'XOF' => 'West African CFA franc',
                                                    'XPF' => 'CFP Franc',
                                                    'YER' => 'Yemeni Rial',
                                                    'ZAR' => 'South African Rand',
                                                    'ZMW' => 'Zambian Kwacha',
                                                ];

                                                foreach ($currencyOptions as $code => $name) {
                                                    $selected =
                                                        isset($settings['stripe_currency_code']) &&
                                                        $settings['stripe_currency_code'] == $code
                                                            ? 'selected'
                                                            : '';
                                                    echo "<option value=\"$code\" $selected>$name</option>";
                                                }
                                            @endphp
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @if ($hide_unwanted_payment_gateway == true)
                            <div class="tab-pane fade" id="flutterwave" role="tabpanel"
                                aria-labelledby="v-pills-profile-tab">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0">
                                            {{ labels('admin_labels.flutterwave', 'Flutterwave Payments') }}
                                        </h5>
                                    </div>
                                    <select name="flutterwave_method"
                                        class="form-select status_dropdown <?= @$settings['flutterwave_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                        <option value="1"
                                            <?= @$settings['flutterwave_method'] == '1' ? 'selected' : '' ?>>
                                            Active
                                        </option>
                                        <option value="0"
                                            <?= @$settings['flutterwave_method'] == '0' ? 'selected' : '' ?>>
                                            Deactive
                                        </option>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="flutterwave_public_key">{{ labels('admin_labels.flutterwave_public_key', 'Flutterwave Public Key') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="flutterwave_public_key"
                                                value="<?= isKeySetAndNotEmpty($settings, 'flutterwave_public_key') ? $settings['flutterwave_public_key'] : '' ?>"
                                                placeholder="Flutterwave Public Key" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="flutterwave_secret_key">{{ labels('admin_labels.secret_key', 'Secret key') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="flutterwave_secret_key"
                                                value="<?= isKeySetAndNotEmpty($settings, 'flutterwave_secret_key') ? $settings['flutterwave_secret_key'] : '' ?>"
                                                placeholder="Flutterwave Secret Key" />
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="flutterwave_encryption_key">{{ labels('admin_labels.flutterwave_encryption_key', 'Flutterwave Encryption Key') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="flutterwave_encryption_key"
                                                value="<?= isKeySetAndNotEmpty($settings, 'flutterwave_encryption_key') ? $settings['flutterwave_encryption_key'] : '' ?>"
                                                placeholder="Flutterwave Encryption Key" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="flutterwave_currency_code">{{ labels('admin_labels.currency', 'Currency') }}
                                                <small>[
                                                    Flutterwave
                                                    supported ]</small> </label><span
                                                class="text-asterisks text-sm">*</span>
                                            <select name="flutterwave_currency_code" class="form-control form-select">
                                                <option value="">Select Currency Code </option>
                                                <option value="NGN"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'NGN' ? 'selected' : '' ?>>
                                                    Nigerian Naira</option>
                                                <option value="USD"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'USD' ? 'selected' : '' ?>>
                                                    United States dollar</option>
                                                <option value="TZS"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'TZS' ? 'selected' : '' ?>>
                                                    Tanzanian Shilling</option>
                                                <option value="SLL"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'SLL' ? 'selected' : '' ?>>
                                                    Sierra Leonean Leone</option>
                                                <option value="MUR"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'MUR' ? 'selected' : '' ?>>
                                                    Mauritian Rupee</option>
                                                <option value="MWK"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'MWK' ? 'selected' : '' ?>>
                                                    Malawian Kwacha </option>
                                                <option value="GBP"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'GBP' ? 'selected' : '' ?>>
                                                    UK Bank Accounts</option>
                                                <option value="GHS"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'GHS' ? 'selected' : '' ?>>
                                                    Ghanaian Cedi</option>
                                                <option value="RWF"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'RWF' ? 'selected' : '' ?>>
                                                    Rwandan franc</option>
                                                <option value="UGX"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'UGX' ? 'selected' : '' ?>>
                                                    Ugandan Shilling</option>
                                                <option value="ZMW"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'ZMW' ? 'selected' : '' ?>>
                                                    Zambian Kwacha</option>
                                                <option value="KES"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'KES' ? 'selected' : '' ?>>
                                                    Mpesa</option>
                                                <option value="ZAR"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'ZAR' ? 'selected' : '' ?>>
                                                    South African Rand</option>
                                                <option value="XAF"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'XAF' ? 'selected' : '' ?>>
                                                    Central African CFA franc</option>
                                                <option value="XOF"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'XOF' ? 'selected' : '' ?>>
                                                    West African CFA franc</option>
                                                <option value="AUD"
                                                    <?= isset($settings['flutterwave_currency_code']) && $settings['flutterwave_currency_code'] == 'AUD' ? 'selected' : '' ?>>
                                                    Australian Dollar</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="flutterwave_webhook_secret_key">{{ labels('admin_labels.webhook_secret_key', 'Webhook Secret Key') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control"
                                                name="flutterwave_webhook_secret_key"
                                                value="<?= isKeySetAndNotEmpty($settings, 'flutterwave_webhook_secret_key') ? $settings['flutterwave_webhook_secret_key'] : '' ?>"
                                                placeholder="Flutterwave Webhook Secret Key" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="flutterwave_webhook_url">{{ labels('admin_labels.payment_endpoint_url', 'Payment Endpoint URL') }}
                                                <small>(Set this
                                                    as
                                                    Endpoint URL in your flutterwave account)</small></label>
                                            <input type="text" class="form-control" name="flutterwave_webhook_url"
                                                value="" disabled />
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane fade" id="fatoorah" role="tabpanel"
                                aria-labelledby="v-pills-profile-tab">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0">
                                            {{ labels('admin_labels.myfatoorah', 'MyFatoorah Payments') }}
                                        </h5>
                                    </div>
                                    <select name="fatoorah_method"
                                        class="form-select status_dropdown <?= @$settings['fatoorah_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                        <option value="1"
                                            <?= @$settings['fatoorah_method'] == '1' ? 'selected' : '' ?>>
                                            Active
                                        </option>
                                        <option value="0"
                                            <?= @$settings['fatoorah_method'] == '0' ? 'selected' : '' ?>>
                                            Deactive
                                        </option>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="myfatoorah_token">{{ labels('admin_labels.myfatoorah_token', 'MyFatoorah Token') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <textarea rows="5" name="myfatoorah_token" class="form-control"><?= isKeySetAndNotEmpty($settings, 'myfatoorah_token') ? $settings['myfatoorah_token'] : '' ?></textarea>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="">{{ labels('admin_labels.payment_mode', 'Payment Mode') }}
                                                <small>[ test / live
                                                    ]</small></label><span class="text-asterisks text-sm">*</span>
                                            <select name="myfatoorah_payment_mode" class="form-control form-select">
                                                <option value="">
                                                    {{ labels('admin_labels.select_mode', 'Select Mode') }}
                                                </option>
                                                <option value="test"
                                                    <?= isset($settings['myfatoorah_payment_mode']) && $settings['myfatoorah_payment_mode'] == 'test' ? 'selected' : '' ?>>
                                                    Test</option>
                                                <option value="live"
                                                    <?= isset($settings['myfatoorah_payment_mode']) && $settings['myfatoorah_payment_mode'] == 'live' ? 'selected' : '' ?>>
                                                    Live</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="myfatoorah_language">{{ labels('admin_labels.language', 'Language') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <select name="myfatoorah_language" class="form-control form-select">
                                                <option value="">Select Language</option>
                                                <option value="english"
                                                    <?= isset($settings['myfatoorah_language']) && $settings['myfatoorah_language'] == 'english' ? 'selected' : '' ?>>
                                                    English</option>
                                                <option value="arabic"
                                                    <?= isset($settings['myfatoorah_language']) && $settings['myfatoorah_language'] == 'arabic' ? 'selected' : '' ?>>
                                                    Arabic</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="myfatoorah_webhook_url">{{ labels('admin_labels.payment_endpoint_url', 'Payment Endpoint URL') }}
                                                <small>(Set
                                                    this as Endpoint URL in your account)</small></label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="myfatoorah__webhook_url"
                                                value="" readonly />
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="myfatoorah_country">{{ labels('admin_labels.country', 'Myfatoorah Country') }}
                                                <small>[
                                                    test / live
                                                    ]</small></label><span class="text-asterisks text-sm">*</span>
                                            <select name="myfatoorah_country" class="form-control form-select">
                                                <option value="">Select country</option>
                                                <option value="Kuwait"
                                                    <?= isset($settings['myfatoorah_country']) && $settings['myfatoorah_country'] == 'Kuwait' ? 'selected' : '' ?>>
                                                    Kuwait</option>
                                                <option value="SaudiArabia"
                                                    <?= isset($settings['myfatoorah_country']) && $settings['myfatoorah_country'] == 'SaudiArabia' ? 'selected' : '' ?>>
                                                    Saudi Arabia</option>
                                                <option value="Bahrain"
                                                    <?= isset($settings['myfatoorah_country']) && $settings['myfatoorah_country'] == 'Bahrain' ? 'selected' : '' ?>>
                                                    Bahrain</option>
                                                <option value="UAE"
                                                    <?= isset($settings['myfatoorah_country']) && $settings['myfatoorah_country'] == 'UAE' ? 'selected' : '' ?>>
                                                    UAE</option>
                                                <option value="Qatar"
                                                    <?= isset($settings['myfatoorah_country']) && $settings['myfatoorah_country'] == 'Qatar' ? 'selected' : '' ?>>
                                                    Qatar</option>
                                                <option value="Oman"
                                                    <?= isset($settings['myfatoorah_country']) && $settings['myfatoorah_country'] == 'Oman' ? 'selected' : '' ?>>
                                                    Oman</option>
                                                <option value="Jordan"
                                                    <?= isset($settings['myfatoorah_country']) && $settings['myfatoorah_country'] == 'Jordan' ? 'selected' : '' ?>>
                                                    Jordan</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="myfatoorah__successUrl">{{ labels('admin_labels.payment_success_url', 'Payment Success URL') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="myfatoorah__successUrl"
                                                value="" readonly />
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="myfatoorah__errorUrl">{{ labels('admin_labels.payment_error_url', 'Payment Error URL') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="myfatoorah__errorUrl"
                                                value="" readonly />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="myfatoorah__secret_key">{{ labels('admin_labels.secret_key', 'Secret Key') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="myfatoorah__secret_key"
                                                value="<?= isKeySetAndNotEmpty($settings, 'myfatoorah__secret_key') ? $settings['myfatoorah__secret_key'] : '' ?>" />
                                        </div>
                                    </div>

                                </div>
                            </div>
                            <div class="tab-pane fade" id="midtrans" role="tabpanel"
                                aria-labelledby="v-pills-profile-tab">
                                <div>
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <h5 class="mb-0">
                                                {{ labels('admin_labels.midtrans', 'Midtrans') }}
                                            </h5>
                                        </div>
                                        <select name="midtrans_method"
                                            class="form-select status_dropdown <?= @$settings['midtrans_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                            <option value="1"
                                                <?= @$settings['midtrans_method'] == '1' ? 'selected' : '' ?>>
                                                Active
                                            </option>
                                            <option value="0"
                                                <?= @$settings['midtrans_method'] == '0' ? 'selected' : '' ?>>
                                                Deactive
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="midtrans_payment_mode">{{ labels('admin_labels.midtrans_mode', 'Midtrans Mode') }}
                                                <small>[
                                                    sandbox / live
                                                    ]</small></label><span class="text-asterisks text-sm">*</span>
                                            <select name="midtrans_payment_mode" class="form-select">
                                                <option value="">
                                                    {{ labels('admin_labels.select_mode', 'Select Mode') }}
                                                </option>
                                                <option value="sandbox"
                                                    <?= isset($settings['midtrans_payment_mode']) && @$settings['midtrans_payment_mode'] == 'sandbox' ? 'selected' : '' ?>>
                                                    Sandbox</option>
                                                <option value="production"
                                                    <?= isset($settings['midtrans_payment_mode']) && @$settings['midtrans_payment_mode'] == 'live' ? 'selected' : '' ?>>
                                                    Live</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="midtrans_client_key">{{ labels('admin_labels.midtrans_client_key', 'Midtrans Client Key') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="midtrans_client_key"
                                                value="<?= isKeySetAndNotEmpty($settings, 'midtrans_client_key') ? $settings['midtrans_client_key'] : '' ?>"
                                                placeholder="Midtrans Client Key" />

                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="midtrans_merchant_id">{{ labels('admin_labels.midtrans_merchant_id', 'Midtrans Merchant ID') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="midtrans_merchant_id"
                                                value="<?= isKeySetAndNotEmpty($settings, 'midtrans_merchant_id') ? $settings['midtrans_merchant_id'] : '' ?>"
                                                placeholder="Midtrans Merchant ID" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="midtrans_server_key">{{ labels('admin_labels.midtrans_server_key', 'Midtrans Server Key') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="midtrans_server_key"
                                                value="<?= isKeySetAndNotEmpty($settings, 'midtrans_server_key') ? $settings['midtrans_server_key'] : '' ?>"
                                                placeholder="Midtrans Server Key" />
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label for="notification_url"
                                                class="form-label">{{ labels('admin_labels.notification_url', 'Notification URL') }}
                                                <small>(Set this as Webhook URL in
                                                    your
                                                    Midtrans
                                                    account)</small></label></span>
                                            <input type="text" class="form-control mt-2" readonly value="">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="payment_return_url"
                                                class="form-label">{{ labels('admin_labels.payment_return_url', 'Payment Return URL') }}
                                                <small>(Set this as Finish URL in
                                                    your
                                                    Midtrans
                                                    account)</small></label>
                                            <input type="text" class="form-control mt-2" readonly value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="direct_bank_transfer" role="tabpanel"
                                aria-labelledby="v-pills-profile-tab">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <h5 class="mb-0">
                                            {{ labels('admin_labels.direct_bank_transfer', 'Direct Bank Transfer') }}
                                        </h5>
                                    </div>
                                    <select name="direct_bank_transfer_method"
                                        class="form-select status_dropdown <?= @$settings['direct_bank_transfer_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                        <option value="1"
                                            <?= @$settings['direct_bank_transfer_method'] == '1' ? 'selected' : '' ?>>
                                            Active
                                        </option>
                                        <option value="0"
                                            <?= @$settings['direct_bank_transfer_method'] == '0' ? 'selected' : '' ?>>
                                            Deactive
                                        </option>
                                    </select>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="account_name">{{ labels('admin_labels.account_name', 'Account Name') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="account_name"
                                                value="<?= isKeySetAndNotEmpty($settings, 'account_name') ? $settings['account_name'] : '' ?>"
                                                placeholder="Account Name" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="account_number">{{ labels('admin_labels.account_number', 'Account Number') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="number" step="any" class="form-control"
                                                name="account_number"
                                                value="<?= isKeySetAndNotEmpty($settings, 'account_number') ? $settings['account_number'] : '' ?>"
                                                placeholder="Account Number" />
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="bank_name">{{ labels('admin_labels.bank_name', 'Bank Name') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="bank_name"
                                                value="<?= isKeySetAndNotEmpty($settings, 'bank_name') ? $settings['bank_name'] : '' ?>"
                                                placeholder="Bank Name" />
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label"
                                                for="bank_code">{{ labels('admin_labels.bank_code', 'Bank Code') }}</label><span
                                                class="text-asterisks text-sm">*</span>
                                            <input type="text" class="form-control" name="bank_code"
                                                value="<?= isKeySetAndNotEmpty($settings, 'bank_code') ? $settings['bank_code'] : '' ?>"
                                                placeholder="Bank Code" />
                                        </div>
                                    </div>
                                    <div class="col-md-12 mt-4">
                                        <label class="form-label"
                                            for="notes">{{ labels('admin_labels.extra_notes', 'Extra Notes') }}<span
                                                class="text-asterisks text-sm">*</span></label>
                                        <textarea name="notes" class="form-control" placeholder="Extra Notes"><?= @$settings['notes'] ?></textarea>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="tab-pane fade" id="cash_on_delivery" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <h5 class="mb-0">
                                        {{ labels('admin_labels.cash_on_delivery', 'Cash On Delivery') }}
                                    </h5>
                                </div>
                                <select name="cod_method"
                                    class="form-select status_dropdown <?= @$settings['cod_method'] == 1 ? 'active_status' : 'inactive_status' ?>">
                                    <option value="1" <?= @$settings['cod_method'] == '1' ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="0" <?= @$settings['cod_method'] == '0' ? 'selected' : '' ?>>
                                        Deactive
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary submit_button"
                                id="submit_btn">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
