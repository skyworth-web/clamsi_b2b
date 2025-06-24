@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.city', 'City') }}
@endsection
@section('content')
    <section class="main-content">

        <div class="row">
            <x-seller.breadcrumb :title="labels('admin_labels.city', 'City')" :subtitle="labels(
                'admin_labels.efficiently_organize_and_govern_city_data',
                'Efficiently Organize and Govern City Data',
            )" :breadcrumbs="[
                ['label' => labels('admin_labels.location_management', 'Location Management')],
                ['label' => labels('admin_labels.location', 'Locations')],
                ['label' => labels('admin_labels.city', 'City')],
            ]" />

            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-lg-6">
                                    <h4>{{ labels('admin_labels.city', 'City') }} </h4>
                                </div>
                                <div class="col-md-12 col-lg-6 d-flex justify-content-end">
                                    <div class="input-group me-3 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="seller_city_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="seller_city_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="seller_city_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_city_table','csv')">CSV</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_city_table','json')">JSON</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_city_table','sql')">SQL</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_city_table','excel')">Excel</button>
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
                                    <table class='table' id="seller_city_table" data-toggle="table"
                                        data-loading-template="loadingTemplate" data-url="{{ route('seller.city.list') }}"
                                        data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel"]' data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true">
                                                    {{ labels('admin_labels.id', 'ID') }}</th>
                                                <th data-field="name" data-disabled="1" data-sortable="false">
                                                    {{ labels('admin_labels.name', 'Name') }}</th>
                                                <th data-field="minimum_free_delivery_order_amount" data-sortable="false">
                                                    {{ labels('admin_labels.minimum_free_delivery_order_amount', 'Minimum Free Delivery Order Amount') }}
                                                </th>
                                                <th data-field="delivery_charges" data-sortable="false">
                                                    {{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}
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

    </section>
@endsection
