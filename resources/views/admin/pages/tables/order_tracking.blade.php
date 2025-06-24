@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.order_tracking', 'Order Tracking') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.order_tracking', 'Order Tracking')" :subtitle="labels('admin_labels.track_and_manage_order_with_precision', 'Track and Manage Order with Precision')" :breadcrumbs="[
        ['label' => labels('admin_labels.orders', 'Orders'), 'url' => route('admin.orders.index')],
        ['label' => labels('admin_labels.order_tracking', 'Order Tracking')],
    ]" />


    {{-- table  --}}
    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <h4>{{ labels('admin_labels.manage_order_tracking', 'Manage Order Tracking') }}
                            </h4>
                        </div>
                        <div class="col-md-12 col-lg-6 d-flex justify-content-end ">
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_order_tracking_table"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_order_tracking_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_order_tracking_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_order_tracking_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_order_tracking_table','json')">JSON</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_order_tracking_table','sql')">SQL</button></li>
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
                                data-url="{{ route('admin.orders.get_order_tracking') }}" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true" data-export-types='["txt","excel"]'
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="order_id" data-disabled="1" data-sortable="true">
                                            {{ labels('admin_labels.order_id', 'Order ID') }}
                                        </th>
                                        <th data-field="order_item_id" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.order_item_id', 'Order Item ID') }}
                                        </th>
                                        <th data-field="courier_agency" data-disabled="1" data-sortable="false">
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
@endsection
