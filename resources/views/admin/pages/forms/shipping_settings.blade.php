@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.shipping_methods', 'Shipping Methods') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.shipping_methods', 'Shipping Methods')" :subtitle="labels(
        'admin_labels.optimize_and_manage_your_shipping_channels_with_ease',
        'Optimize and Manage Your Shipping Channels with Ease',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.shipping_methods', 'Shipping Methods')],
    ]" />

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <form id="" action="{{ route('shipping_settings.store') }}" class="submit_form"
                    enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="card-body">
                        <h5 class="mb-3">
                            {{ labels('admin_labels.shipping_methods', 'Shipping Methods') }}
                        </h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="form-group">
                                        <label class="mb-3"
                                            for="local_shipping_method">{{ labels('admin_labels.enable_local_shipping', 'Enable Local Shipping') }}
                                            <small>(Use Local Delivery
                                                Boy
                                                For Shipping)</small></label>
                                    </div>
                                    <div class="form-group card-body d-flex justify-content-end">
                                        <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                            href="javascript:void(0)">
                                            <input type="checkbox" class="form-check-input" role="switch"
                                                name="local_shipping_method"
                                                <?= @$settings['local_shipping_method'] == '1' ? 'checked' : '' ?>>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-none">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <label class="mb-3"
                                            for="shiprocket_shipping_method">{{ labels('admin_labels.standard_delivery_method', 'Standard Delivery Method') }}
                                            (Shiprocket)
                                            <a href="https://app.shiprocket.in/api-user" target="_blank">Click
                                                here</a></small>
                                            to
                                            get credentials. <small><a href="https://www.shiprocket.in/"
                                                    target="_blank">What is
                                                    shiprocket?</a></small></label>
                                        <br>
                                        <div class="card-body d-flex justify-content-end">
                                            <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                                href="javascript:void(0)">
                                                <input type="checkbox" class="form-check-input" role="switch"
                                                    name="shiprocket_shipping_method"
                                                    <?= @$settings['shiprocket_shipping_method'] == '1' ? 'checked' : '' ?>>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="form-group">
                                    <label class="mb-3" for="email">{{ labels('admin_labels.email', 'Email') }}<span
                                            class="text-asterisks text-sm">*</span></label>
                                    <input type="email" class="form-control" name="email" id="email"
                                        value="<?= isKeySetAndNotEmpty($settings, 'email') ? $settings['email'] : '' ?>"
                                        placeholder="Shiprocket account email" />
                                </div>
                                <div class="form-group">
                                    <label class="mb-3"
                                        for="password">{{ labels('admin_labels.password', 'Password') }}<span
                                            class="text-asterisks text-sm">*</span></label>
                                    <input type="password" class="form-control" name="password" id=""
                                        value="<?= isKeySetAndNotEmpty($settings, 'password') ? $settings['password'] : '' ?>"
                                        placeholder="Shiprocket account Password" />
                                </div>
                                <div class="form-group">
                                    <label class="mb-3"
                                        for="webhook_url">{{ labels('admin_labels.enable_local_shipping', 'Enable Local Shipping') }}<span
                                            class="text-asterisks text-sm">*</span></label>
                                    <input type="text" class="form-control" name="webhook_url" id=""
                                        value="<?= 'admin/webhook/spr_webhook' ?>" disabled />
                                </div>
                                <div class="form-group">
                                    <label class="mb-3"
                                        for="webhook_token">{{ labels('admin_labels.shiprocket_webhook_token', 'Shiprocket Webhook Token') }}<span
                                            class="text-asterisks text-sm">*</span></label>
                                    <input type="text" class="form-control" name="webhook_token" id=""
                                        value="<?= isKeySetAndNotEmpty($settings, 'webhook_token') ? $settings['webhook_token'] : '' ?>" />
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <span class="text-danger"><b>Note:</b> You can give free delivery charge only when
                                        <b>Standard delivery method</b> is enabled.</span>
                                </div>
                            </div>


                            <div class="col-md-12">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label class="mb-3"
                                        for="shiprocket_shipping_method">{{ labels('admin_labels.enable_free_delivery', 'Enable Free Delivery') }}</label>
                                    <div class="card-body d-flex justify-content-end">
                                        <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                            href="javascript:void(0)">
                                            <input type="checkbox" class="form-check-input" role="switch"
                                                name="standard_shipping_free_delivery"
                                                <?= @$settings['standard_shipping_free_delivery'] == '1' ? 'checked' : '' ?>>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label for="local_shipping_method">Minimum free delivery order amount </label>
                                <div>
                                    <input type="number" min=1 class="form-control"
                                        name="minimum_free_delivery_order_amount" id=""
                                        value="<?= @$settings['minimum_free_delivery_order_amount'] ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                        </div>
                    </div>
            </div>
        </div>
    </div>
@endsection
