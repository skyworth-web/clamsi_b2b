@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.manage_orders', 'Order Manage') }}
@endsection
@section('content')
    <section class="main-content">
        <x-seller.breadcrumb :title="labels('admin_labels.manage_orders', 'Order Manage')" :subtitle="labels('admin_labels.all_information_about_orders', 'All Information About Orders')" :breadcrumbs="[
            ['label' => labels('admin_labels.manage_orders', 'Order Manage')],
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
                                <img src="{{ asset('storage/dashboard_icon/dashboard_overview.svg') }}"
                                    class="dashboard-icon" alt="">
                            </div>
                            <div class="content">
                                <p class="body-default">{{ labels('admin_labels.received', 'Received') }}
                                </p>
                                <h5>{{ ordersCount('received', $seller_id, '', $store_id) }}</h5>
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
                                <h5>{{ ordersCount('processed', $seller_id, '', $store_id) }}</h5>
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
                                <h5>{{ ordersCount('shipped', $seller_id, '', $store_id) }}</h5>
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
                                <h5>{{ ordersCount('delivered', $seller_id, '', $store_id) }}</h5>
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
                                <h5>{{ ordersCount('cancelled', $seller_id, '', $store_id) }}</h5>
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
                                <h5>{{ ordersCount('returned', $seller_id, '', $store_id) }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
        <section class="overview-data">
            <div class="card content-area p-4 ">

                <div class="row align-items-center d-flex heading mb-5">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12 col-lg-6">
                                <h4>{{ labels('admin_labels.orders', 'Orders') }} </h4>
                            </div>
                            <div class="col-md-12 col-lg-6 d-flex justify-content-end">
                                <div class="input-group me-2 search-input-grp">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="seller_order_item_table"
                                        class="form-control searchInput" placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="seller_order_item_table"
                                    dateFilter='true' orderStatusFilter='true' paymentMethodFilter='true'
                                    orderTypeFilter='true'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh" data-table="seller_order_item_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('seller_order_item_table','csv')">CSV</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('seller_order_item_table','json')">JSON</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('seller_order_item_table','sql')">SQL</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('seller_order_item_table','excel')">Excel</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="pt-0">
                                    <div id="order_items_table">
                                        <div class="table-responsive">
                                            <table id="seller_order_item_table" data-toggle="table"
                                                data-loading-template="loadingTemplate"
                                                data-url="{{ route('seller.orders.item_list') }}"
                                                data-click-to-select="true" data-side-pagination="server"
                                                data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                data-search="false" data-show-columns="false" data-show-refresh="false"
                                                data-trim-on-search="false" data-sort-name="o.id" data-sort-order="desc"
                                                data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                                data-maintain-selected="true" data-query-params="queryParams">
                                                <thead>
                                                    <tr>
                                                        <th data-field="id" data-sortable='true'
                                                            data-footer-formatter="totalFormatter">
                                                            {{ labels('admin_labels.id', 'ID') }}</th>
                                                        <th data-field="order_item_id" data-sortable='true'>
                                                            {{ labels('admin_labels.order_item_id', 'Order Item ID') }}
                                                        </th>
                                                        <th data-field="order_id" data-sortable='true'>
                                                            {{ labels('admin_labels.order_id', 'Order ID') }}</th>
                                                        <th data-field="user_id" data-sortable='true'
                                                            data-visible="false">
                                                            {{ labels('admin_labels.user_id', 'User ID') }}</th>
                                                        <th data-field="seller_id" data-sortable='true'
                                                            data-visible="false">
                                                            {{ labels('admin_labels.seller_id', 'Seller ID') }}</th>
                                                        <th data-field="is_credited" data-sortable='false'
                                                            data-visible="false">
                                                            {{ labels('admin_labels.comission', 'Commission') }}</th>
                                                        <th data-field="quantity" data-sortable='false'
                                                            data-visible="false">
                                                            {{ labels('admin_labels.quantity', 'Quantity') }}</th>
                                                        <th data-field="username" data-sortable='false'>
                                                            {{ labels('admin_labels.user_name', 'User Name') }}</th>
                                                        <th data-field="product_name" data-sortable='false'>
                                                            {{ labels('admin_labels.product_name', 'Product Name') }}
                                                        </th>
                                                        <th data-field="mobile" data-sortable='false'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.mobile', 'Mobile') }}</th>
                                                        <th data-field="sub_total" data-sortable='false'
                                                            data-visible="true">
                                                            {{ labels('admin_labels.total', 'Total') }}(<?= $currency ?>)
                                                        </th>
                                                        <th data-field="delivery_boy" data-sortable='false'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.deliver_by', 'Deliver By') }}</th>
                                                        <th data-field="delivery_boy_id" data-sortable='true'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.delivery_boy_id', 'Delivery Boy ID') }}
                                                        </th>
                                                        <th data-field="product_variant_id" data-sortable='true'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.product_variant_id', 'Product Variant ID') }}
                                                        </th>
                                                        <th data-field="delivery_date" data-sortable='true'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.delivery_date', 'Delivery Date') }}
                                                        </th>
                                                        <th data-field="delivery_time" data-sortable='false'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.delivery_time', 'Delivery Time') }}
                                                        </th>
                                                        <th data-field="updated_by" data-sortable='false'
                                                            data-visible="false">
                                                            {{ labels('admin_labels.updated_by', 'Updated By') }}</th>
                                                        <th data-field="active_status" data-sortable='false'
                                                            data-visible='true'>
                                                            {{ labels('admin_labels.active_status', 'Active Status') }}
                                                        </th>
                                                        <th data-field="transaction_status" data-sortable='false'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.transaction_status', 'Transaction Status') }}
                                                        </th>
                                                        <th data-field="date_added" data-sortable='true'>
                                                            {{ labels('admin_labels.order_date', 'Order Date') }}</th>
                                                        <th data-field="mail_status" data-sortable='false'
                                                            data-visible='false'>
                                                            {{ labels('admin_labels.mail_status', 'Mail Status') }}
                                                        </th>
                                                        <th data-field="operate" data-sortable='false'>
                                                            {{ labels('admin_labels.action', 'Action') }}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
        </section>

        {{-- order tracking modal --}}

        <div class="modal fade" id="edit_order_tracking_modal" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form class="form-horizontal " id="order_tracking_form"
                        action="{{ route('seller.orders.update_order_tracking') }}" method="POST"
                        enctype="multipart/form-data">
                        @method('POST')
                        @csrf
                        <input type="hidden" name="order_id" id="order_id">
                        <input type="hidden" name="order_item_id" id="order_item_id">
                        <input type="hidden" name="seller_id" id="seller_id">
                        <div class="modal-header">
                            <h5 class="modal-title" id="user_name">
                                {{ labels('admin_labels.order_tracking', 'Order Tracking') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group ">
                                    <label
                                        for="courier_agency">{{ labels('admin_labels.courier_agency', 'Courier Agency') }}</label>
                                    <input type="text" class="form-control" name="courier_agency" id="courier_agency"
                                        placeholder="Courier Agency" />
                                </div>
                                <div class="form-group ">
                                    <label
                                        for="tracking_id">{{ labels('admin_labels.tracking_id', 'Tracking ID') }}</label>
                                    <input type="text" class="form-control" name="tracking_id" id="tracking_id"
                                        placeholder="Tracking Id" />
                                </div>
                                <div class="form-group ">
                                    <label for="url">{{ labels('admin_labels.url', 'URL') }}</label>
                                    <input type="text" class="form-control" name="url" id="url"
                                        placeholder="URL" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                                {{ labels('admin_labels.close', 'Close') }}
                            </button>
                            <button type="submit" class="btn btn-primary submit_button"
                                id="save_changes_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </section>
@endsection
