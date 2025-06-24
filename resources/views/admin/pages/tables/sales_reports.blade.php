@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.sales_reports', 'Sales Reports') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.sales_reports', 'Sales Reports')" :subtitle="labels('admin_labels.manage_sales_reports', 'Manage Sales Reports')" :breadcrumbs="[
        ['label' => labels('admin_labels.reports', 'Reports')],
        ['label' => labels('admin_labels.sales_reports', 'Sales Reports')],
    ]" />
    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-xxl-6">
                            <h4>{{ labels('admin_labels.sales_report', 'Sales Report') }} </h4>
                        </div>
                        <div class="col-md-12 col-xxl-6 d-flex justify-content-end ">

                            <div class="input-group me-3 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_sales_report_table" class="form-control searchInput"
                                    placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_sales_report_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_sales_report_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_sales_report_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_sales_report_table','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_sales_report_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_sales_report_table','excel')">Excel</button>
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
                            <table id="admin_sales_report_table" data-toggle="table" data-loading-template="loadingTemplate"
                                data-url="{{ route('admin.sales_reports.list') }}" data-detail-view="true"
                                data-detail-formatter="salesReport" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true"
                                data-export-types='["txt","excel","csv"]' data-query-params="sales_report_query_params"
                                data-export-options='{
                                    "fileName": "sales-report",
                                    "ignoreColumn": ["state"]
                                }'>

                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'Order ID') }}
                                        </th>
                                        <th data-field="product_name" data-sortable="true">
                                            {{ labels('admin_labels.product_name', 'Product Name') }}
                                        </th>
                                        <th data-field="total" data-sortable="true">
                                            {{ labels('admin_labels.total', 'Total') }}
                                        </th>
                                        <th data-field="net_revenue" data-sortable="true">
                                            {{ labels('admin_labels.net_revenue', 'Net Revenue') }}
                                        </th>
                                        <th data-field="admin_commission" data-sortable="false">
                                            {{ labels('admin_labels.admin_commission', 'Admin Commission') }}
                                        </th>
                                        <th data-field="delivery_charge" data-sortable="true">
                                            {{ labels('admin_labels.delivery_charge', 'Delivery Charge') }}
                                        </th>
                                        <th data-field="promo_discount" data-sortable="true">
                                            {{ labels('admin_labels.promo_discount', 'Promo Discount') }}
                                        </th>
                                        <th data-field="seller_commission" data-sortable="true">
                                            {{ labels('admin_labels.seller_commission', 'Seller Commission') }}
                                        </th>
                                        <th data-field="total_commissions" data-sortable="true">
                                            {{ labels('admin_labels.total_commissions', 'Total Commissions') }}
                                        </th>
                                        <th data-field="loss" data-sortable="true">
                                            {{ labels('admin_labels.loss', 'Loss') }}
                                        </th>
                                        <th data-field="profit" data-sortable="true">
                                            {{ labels('admin_labels.profit', 'Profit') }}
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
