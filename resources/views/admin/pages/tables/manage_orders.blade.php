@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.manage_orders', 'Manage Orders') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.manage_orders', 'Manage Orders')" :subtitle="labels('admin_labels.all_information_about_orders', 'All Information About Orders')" :breadcrumbs="[
        ['label' => labels('admin_labels.manage_orders', 'Manage Orders')],
        ['label' => labels('admin_labels.orders', 'Orders')],
    ]" />


    <section class="overview-data">
        <div class="card content-area p-4 mb-5">
            <div class="row mb-5">
                <div class="col-md-12">
                    <div class="heading">
                        <h4>{{ labels('admin_labels.orders_review', 'Orders Review') }}</h4>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xxl-4 col-lg-4 col-md-6 col-sm-12 col mb-6">
                    <div class="info-box align-items-center">
                        <div class="primary-icon">
                            <img src="{{ asset('storage/dashboard_icon/dashboard_overview.svg') }}" class="dashboard-icon"
                                alt="">
                        </div>
                        <div class="content">
                            <p class="body-default">{{ labels('admin_labels.received', 'Received') }}
                            </p>
                            <h5>{{ ordersCount('received', '', '', $store_id) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-6 col-sm-12 col mb-6">
                    <div class="info-box align-items-center">
                        <div class="success-icon">
                            <img src="{{ asset('storage/dashboard_icon/processed.svg') }}" class="dashboard-icon"
                                alt="">
                        </div>
                        <div class="content">
                            <p class="body-default">{{ labels('admin_labels.processed', 'Processed') }}</p>
                            <h5>{{ ordersCount('processed', '', '', $store_id) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-6 col-sm-12 col mb-6">
                    <div class="info-box align-items-center">
                        <div class="danger-icon">
                            <img src="{{ asset('storage/dashboard_icon/shipped.svg') }}" class="dashboard-icon"
                                alt="">
                        </div>
                        <div class="content">
                            <p class="body-default">{{ labels('admin_labels.shipped', 'Shipped') }}</p>
                            <h5>{{ ordersCount('shipped', '', '', $store_id) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-6 col-sm-12 col">
                    <div class="info-box align-items-center">
                        <div class="warning-icon">
                            <img src="{{ asset('storage/dashboard_icon/delivered.svg') }}" class="dashboard-icon"
                                alt="">
                        </div>
                        <div class="content">
                            <p class="body-default">{{ labels('admin_labels.delivered', 'Delivered') }}</p>
                            <h5>{{ ordersCount('delivered', '', '', $store_id) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-6 col-sm-12 col">
                    <div class="info-box align-items-center">
                        <div class="info-icon">
                            <img src="{{ asset('storage/dashboard_icon/cancelled.svg') }}" class="dashboard-icon"
                                alt="">
                        </div>
                        <div class="content">
                            <p class="body-default">{{ labels('admin_labels.cancelled', 'Cancelled') }}</p>
                            <h5>{{ ordersCount('cancelled', '', '', $store_id) }}</h5>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-4 col-lg-4 col-md-6 col-sm-12 col">
                    <div class="info-box align-items-center">
                        <div class="pink-icon">
                            <img src="{{ asset('storage/dashboard_icon/returned.svg') }}" class="dashboard-icon"
                                alt="">
                        </div>
                        <div class="content">
                            <p class="body-default">{{ labels('admin_labels.returned', 'Returned') }}</p>
                            <h5>{{ ordersCount('returned', '', '', $store_id) }}</h5>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>


    {{-- order tracking model  --}}

    <div class="modal fade" id="order-tracking-modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog  modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ labels('admin_labels.view_order_tracking', 'View Order Tracking') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <div class="modal-body">


                    <section class="overview-data">
                        <div class="card content-area p-4 ">
                            <div class="row align-items-center d-flex heading mb-5">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12 d-flex justify-content-end ">
                                            <div class="input-group me-2 search-input-grp ">
                                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                                <input type="text" data-table="admin_order_tracking_table"
                                                    class="form-control searchInput" placeholder="Search...">
                                                <span
                                                    class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                            </div>

                                            <a class="btn me-2" id="tableRefresh" data-table="admin_order_tracking_table"><i
                                                    class='bx bx-refresh'></i></a>
                                            <div class="dropdown">
                                                <a class="btn dropdown-toggle export-btn" type="button"
                                                    id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class='bx bx-download'></i>
                                                </a>
                                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                    <li><button class="dropdown-item" type="button"
                                                            onclick="exportTableData('admin_order_tracking_table','csv')">CSV</button>
                                                    </li>
                                                    <li><button class="dropdown-item" type="button"
                                                            onclick="exportTableData('admin_order_tracking_table','json')">JSON</button>
                                                    </li>
                                                    <li><button class="dropdown-item" type="button"
                                                            onclick="exportTableData('admin_order_tracking_table','sql')">SQL</button>
                                                    </li>
                                                    <li><button class="dropdown-item" type="button"
                                                            onclick="exportTableData('admin_order_tracking_table','excel')">Excel</button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pt-0">
                                        <div class="table-responsive">
                                            <table class='table' id="admin_order_tracking_table" data-toggle="table"
                                                data-loading-template="loadingTemplate"
                                                data-url="{{ route('admin.orders.get_order_tracking') }}"
                                                data-click-to-select="true" data-side-pagination="server"
                                                data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                data-search="false" data-show-columns="false" data-show-refresh="false"
                                                data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                                data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                                data-maintain-selected="true" data-export-types='["txt","excel"]'
                                                data-query-params="orderTrackingQueryParams">
                                                <thead>
                                                    <tr>
                                                        <th data-field="id" data-sortable="true">
                                                            {{ labels('admin_labels.id', 'ID') }}
                                                        <th data-field="order_id" data-sortable="true">
                                                            {{ labels('admin_labels.order_id', 'Order ID') }}
                                                        </th>
                                                        <th data-field="order_item_id" data-sortable="false">
                                                            {{ labels('admin_labels.order_item_id', 'Order Item ID') }}
                                                        </th>
                                                        <th data-field="courier_agency" data-sortable="false">
                                                            {{ labels('admin_labels.courier_agency', 'Courier Agency') }}
                                                        </th>
                                                        <th data-field="tracking_id" data-sortable="false">
                                                            {{ labels('admin_labels.tracking_id', 'Tracking ID') }}
                                                        </th>
                                                        <th data-field="url" data-sortable="false">
                                                            {{ labels('admin_labels.url', 'URL') }}
                                                        </th>
                                                        <th data-field="date" data-sortable="true">
                                                            {{ labels('admin_labels.date', 'Date') }}
                                                        </th>
                                                        <th data-field="operate" data-sortable="false">
                                                            {{ labels('admin_labels.action', 'Action') }}
                                                        </th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <input type='hidden' id='order_user_id' value='<?= isset($user_id) && !empty($user_id) ? $user_id : '' ?>'>

    <!-- modal for assign tracking data for order -->


    <div class="modal fade" id="edit_order_tracking_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="user_name">
                        {{ labels('admin_labels.order_tracking', 'Order Tracking') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal " id="order_tracking_form"
                    action="{{ route('admin.orders.update_order_tracking') }}" method="POST"
                    enctype="multipart/form-data">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="order_id" id="order_id">
                    <input type="hidden" name="order_item_id" id="order_item_id">
                    <input type="hidden" name="seller_id" id="seller_id">
                    <div class="modal-body">
                        <div class="form-group ">
                            <label
                                for="courier_agency">{{ labels('admin_labels.courier_agency', 'Courier Agency') }}</label>
                            <input type="text" class="form-control" name="courier_agency" id="courier_agency"
                                placeholder="Courier Agency" />
                        </div>
                        <div class="form-group ">
                            <label for="tracking_id">{{ labels('admin_labels.tracking_id', 'Tracking ID') }}</label>
                            <input type="text" class="form-control" name="tracking_id" id="tracking_id"
                                placeholder="Tracking Id" />
                        </div>
                        <div class="form-group ">
                            <label for="url">{{ labels('admin_labels.url', 'URL') }}</label>
                            <input type="text" class="form-control" name="url" id="url"
                                placeholder="URL" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset"
                            class="btn btn-warning">{{ labels('admin_labels.reset', 'Reset') }}</button>
                        <button type="submit" class="btn btn-success"
                            id="submit_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- table --}}

    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">

                        <div class="col-md-12 d-flex justify-content-end ">
                            <a href="#" class="btn btn-dark me-3 add_promo_code_discount"
                                title="If you found Promo Code Discount not crediting using cron job you can update Promo Code Discount from here!">{{ labels('admin_labels.settle_promo_code_discount', 'Settle Promo Code Discount') }}</a>
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_order_table" class="form-control searchInput"
                                    placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_order_table" dateFilter='true'
                                paymentMethodFilter='true' orderTypeFilter='true'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_order_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_order_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_order_table','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_order_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_order_table','excel')">Excel</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="pt-0">
                                <div class="table-responsive">
                                    <table class='table' id="admin_order_table" data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('admin.orders.list') }}" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel"]' data-query-params="orders_query_params">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable='true'
                                                    data-footer-formatter="totalFormatter">
                                                    {{ labels('admin_labels.order_id', 'Order ID') }}
                                                </th>
                                                <th data-field="user_id" data-sortable='true' data-visible="false">
                                                    {{ labels('admin_labels.user_id', 'User ID') }}
                                                </th>
                                                <th data-field="qty" data-sortable='false' data-visible="false">
                                                    {{ labels('admin_labels.quantity', 'Quantity') }}
                                                </th>
                                                <th data-field="name" data-disabled="1" data-sortable='false'>
                                                    {{ labels('admin_labels.user_name', 'User Name') }}
                                                </th>
                                                <th data-field="sellers" data-disabled="1" data-sortable='false'>
                                                    {{ labels('admin_labels.sellers', 'Sellers') }}
                                                </th>
                                                <th data-field="mobile" data-sortable='false' data-visible='false'>
                                                    {{ labels('admin_labels.mobile', 'Mobile') }}
                                                </th>
                                                <th data-field="notes" data-sortable='false' data-visible='true'>
                                                    {{ labels('admin_labels.order_notes', 'Order Notes') }}
                                                </th>
                                                <th data-field="items" data-sortable='false' data-visible="false">
                                                    {{ labels('admin_labels.items', 'Items') }}
                                                </th>
                                                <th data-field="total" data-sortable='false' data-visible="true">
                                                    {{ labels('admin_labels.total', 'Total') }}(<?= $currency ?>)
                                                </th>
                                                <th data-field="delivery_charge" data-sortable='false'
                                                    data-footer-formatter="delivery_chargeFormatter">
                                                    {{ labels('admin_labels.delivery_charge', 'Delivery Charge') }}
                                                </th>
                                                <th data-field="wallet_balance" data-sortable='false'
                                                    data-visible="true">
                                                    {{ labels('admin_labels.wallet_used', 'Wallet Used') }}(<?= $currency ?>)
                                                </th>
                                                <th data-field="promo_code" data-sortable='false' data-visible="false">
                                                    {{ labels('admin_labels.promo_codes', 'Promo Code') }}
                                                </th>
                                                <th data-field="promo_discount" data-sortable='false'data-visible="true">
                                                    {{ labels('admin_labels.promo_dicsount', 'Promo Discount') }}(<?= $currency ?>)
                                                </th>
                                                <th data-field="final_total" data-sortable='false'>
                                                    {{ labels('admin_labels.final_total', 'Final Total') }}(<?= $currency ?>)
                                                </th>
                                                <th data-field="payment_method" data-sortable='false'
                                                    data-visible="true">
                                                    {{ labels('admin_labels.payment_method', 'Payment Method') }}
                                                </th>
                                                <th data-field="address" data-sortable='false' data-visible='false'>
                                                    {{ labels('admin_labels.address', 'Address') }}
                                                </th>
                                                <th data-field="delivery_date" data-sortable='false'
                                                    data-visible='false'>
                                                    {{ labels('admin_labels.delivery_date', 'Delivery Date') }}
                                                </th>
                                                <th data-field="delivery_time" data-sortable='false'
                                                    data-visible='false'>
                                                    {{ labels('admin_labels.delivery_time', 'Delivery Time') }}
                                                </th>
                                                <th data-field="date_added" data-sortable='false'>
                                                    {{ labels('admin_labels.order_date', 'Order Date') }}
                                                </th>
                                                <th data-field="operate" data-sortable='false'>
                                                    {{ labels('admin_labels.action', 'Action') }}
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
    </section>
@endsection
