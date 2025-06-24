@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.sales_report', 'Sales Report') }}
@endsection
@section('content')
    <section class="main-content">
        <div class="row">
            <x-seller.breadcrumb :title="labels('admin_labels.sales_report', 'Sales Report')" :subtitle="labels(
                'admin_labels.empower_seller_with_comprehrnsive_and_actionable_sales_insight',
                'Empower Sellers with Comprehensive and Actionable Sales Insights',
            )" :breadcrumbs="[
                ['label' => labels('admin_labels.reports', 'Reports')],
                ['label' => labels('admin_labels.sales_report', 'Sales Report')],
            ]" />
            {{-- table --}}
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
                                        <input type="text" data-table="seller_sales_report_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="seller_sales_report_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="seller_sales_report_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_sales_report_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_sales_report_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_sales_report_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_sales_report_table','excel')">Excel</button>
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

                                    <table id='seller_sales_report_table' data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('seller.sales.report.list') }}" data-detail-view="true"
                                        data-detail-formatter="salesReport" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel","csv"]'
                                        data-query-params="sales_report_query_params"
                                        data-export-types='["txt","excel","csv"]'
                                        data-export-options='{
                        "fileName": "products-list",
                        "ignoreColumn": ["state"]
                        }'>


                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable='true'>
                                                    {{ labels('admin_labels.order_item_id', 'Order Item ID') }}
                                                </th>
                                                <th data-field="product_name" data-sortable='false'>
                                                    {{ labels('admin_labels.product_name', 'Product Name') }}
                                                </th>
                                                <th data-field="final_total" data-sortable='true'>
                                                    {{ labels('admin_labels.final_total', 'Final Total') }}
                                                </th>
                                                <th data-field="payment_method" data-sortable='true'>
                                                    {{ labels('admin_labels.payment_method', 'Payment Method') }}
                                                </th>
                                                <th data-field="store_name" data-sortable='true'>
                                                    {{ labels('admin_labels.store_name', 'Store Name') }}</th>
                                                <th data-field="seller_name" data-sortable='true'>
                                                    {{ labels('admin_labels.seller', 'Seller Name') }}</th>
                                                <th data-field="date_added" data-sortable='true'>
                                                    {{ labels('admin_labels.date', 'Date Added') }}</th>
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
    </section>
@endsection
