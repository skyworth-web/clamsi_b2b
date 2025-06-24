@extends('delivery_boy/layout')
@section('title')
    {{ labels('admin_labels.manage_orders', 'Order Manage') }}
@endsection
@section('content')
    {{-- table --}}
    <section class="main-content">
        <x-delivery_boy.breadcrumb :title="labels('admin_labels.manage_orders', 'Order Manage')" :subtitle="labels('admin_labels.all_information_about_orders', 'All Information About Orders')" :breadcrumbs="[
            ['label' => labels('admin_labels.orders', 'Manage')],
            ['label' => labels('admin_labels.orders', 'Orders')],
        ]" />
        <section class="overview-data">
            <div class="card content-area p-4 ">
                <div class="row align-items-center d-flex heading mb-5">
                    <div class="col-md-6">
                        <h4> {{ labels('admin_labels.manage_orders', 'Manage Orders') }}
                        </h4>
                    </div>
                    <div class="col-md-6 d-flex justify-content-end ">
                        <div class="input-group me-3 search-input-grp ">
                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                            <input type="text" data-table="delivery_boy_order_table" class="form-control searchInput"
                                placeholder="Search...">
                            <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                        </div>
                        <a class="btn me-3" id="tableFilter" data-bs-toggle="offcanvas"
                            data-bs-target="#columnFilterOffcanvas" data-table="delivery_boy_order_table" dateFilter='true'
                            orderStatusFilter='true' paymentMethodFilter='true' orderTypeFilter='false'><i
                                class='bx bx-filter-alt'></i></a>
                        <a class="btn me-3" id="tableRefresh" data-table="delivery_boy_order_table"><i
                                class='bx bx-refresh'></i></a>
                        <div class="dropdown">
                            <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class='bx bx-download'></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_order_table','csv')">CSV</button></li>
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_order_table','json')">JSON</button></li>
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_order_table','sql')">SQL</button></li>
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_order_table','excel')">Excel</button></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="pt-0">
                            <div class="table-responsive">
                                <table class='table' id="delivery_boy_order_table" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('delivery_boy.view_parcels') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                    data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                    data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                    data-show-export="false" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            <th data-field="order_id" data-sortable='true'>
                                                {{ labels('admin_labels.order_id', 'Order ID') }}
                                            </th>
                                            <th data-field="user_id" data-sortable='true' data-visible="false">
                                                {{ labels('admin_labels.user_id', 'User ID') }}
                                            </th>
                                            <th data-field="quantity" data-sortable='false' data-visible="false">
                                                {{ labels('admin_labels.quantity', 'Quantity') }}
                                            </th>
                                            <th data-field="username" data-sortable='false'>
                                                {{ labels('admin_labels.user_name', 'User Name') }}
                                            </th>
                                            <th data-field="product_name" data-sortable='false'>
                                                {{ labels('admin_labels.product_name', 'Product Name') }}
                                            </th>
                                            <th data-field="mobile" data-sortable='false' data-visible='false'>
                                                {{ labels('admin_labels.mobile', 'Mobile') }}
                                            </th>
                                            <th data-field="payment_method" data-sortable='false' data-visible="true">
                                                {{ labels('admin_labels.payment_method', 'Payment Method') }}
                                            </th>
                                            <th data-field="status" data-sortable='false' data-visible='true'>
                                                {{ labels('admin_labels.active_status', 'Active Status') }}
                                            </th>

                                            <th data-field="created_at" data-sortable='true'>
                                                {{ labels('admin_labels.created_at', 'Order Date') }}
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
    </section>
@endsection
