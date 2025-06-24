@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.settings', 'Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.settings', 'Settings')" :subtitle="labels(
        'admin_labels.customize_and_manage_platform_settings_with_ease',
        'Customize and Manage Platform Settings with Ease',
    )" :breadcrumbs="[['label' => labels('admin_labels.settings', 'Settings')]]" />

    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-ms-12">
                <div class="row">
                    <div class="form-group">
                        <div class="row col-12 d-flex">
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/system_settings" target="" class="text-decoration-none">
                                    <div class="card mt-4 rotate_icon_card">
                                        <div class="p-4 pb-0">
                                            <div
                                                class="setting_icons_div test d-flex justify-content-center align-items-center">
                                                <i class='bx bx-cog setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.system_settings', 'System Settings') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>

                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/email_settings" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-mail-send setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.email_settings', 'Email SMTP Settings') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/payment_settings" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div class="setting_icons_div d-flex justify-content-center align-items-center">

                                                <i class='bx bx-credit-card setting_icons'></i>

                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.payment_settings', 'Payment Methods Settings') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/shipping_settings" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-car setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.shipping_settings', 'Shipping Methods Settings') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <div class="row col-12 d-flex">
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('sms_gateway') }}" target="" class="text-decoration-none">
                                    <div class="card mt-4 jingle_icon_card">
                                        <div class="p-4 pb-0">
                                            <div class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-message-dots setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.sms_gateway_setting', 'SMS Gateway Setting') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/currency_settings" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-coin-stack setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.currency_settings', 'Currency Settings') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/notification_and_contact_settings" target=""
                                    class="text-decoration-none">
                                    <div class="card mt-4 jingle_icon_card">
                                        <div class="p-4 pb-0">
                                            <div class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-bell setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.notification_settings', 'Notification & Contact') }}

                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>

                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/pusher_setting" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-message-dots setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.pusher_setting', 'Pusher Setting (For Live Chat)') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="form-group">
                        <div class="row col-12 d-flex">
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/s3_storage_setting" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div
                                                class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-coin-stack setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.s3_storage_setting', 'S3 Storage Setting (For Media Storage)') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/system_policies" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div
                                                class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-detail setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.system_policies', 'System Policies') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/admin_and_seller_policies" target=""
                                    class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div
                                                class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-shield setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.admin_and_seller_policies', 'Admin & Seller Policies') }}

                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/delivery_boy_policies" target=""
                                    class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div
                                                class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-shield setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.delivery_boy_policies', 'Delivery Boy Policies') }}

                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="{{ route('admin.system_registration') }}" target=""
                                    class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div
                                                class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-registered setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.settings_update', 'System Registration') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <a href="/admin/settings/updater" target="" class="text-decoration-none">
                                    <div class="card mt-4 flip_icon_card">
                                        <div class="p-4 pb-0">
                                            <div
                                                class="setting_icons_div d-flex justify-content-center align-items-center">
                                                <i class='bx bx-time setting_icons'></i>
                                            </div>
                                        </div>
                                        <div class="card-body d-flex align-items-center">
                                            <h5 class="card-title m-0 mx-2 setting_card_title">
                                                {{ labels('admin_labels.settings_update', 'System Update') }}
                                            </h5>
                                            <i class='bx bx-chevron-right'></i>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
