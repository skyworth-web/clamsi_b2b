@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.categories', 'Categories') }}
@endsection
@section('content')
    <section class="main-content">

        <div class="row">
            <x-seller.breadcrumb :title="labels('admin_labels.categories', 'Categories')" :subtitle="'All information about categories'" :breadcrumbs="[
                ['label' => labels('admin_labels.manage', 'Manage')],
                ['label' => labels('admin_labels.categories', 'Categories')],
            ]" />

            <section class="overview-data">
                <div class="card content-area p-4 ">

                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-lg-6">
                                    <h4>{{ labels('admin_labels.categories', 'Categories') }} </h4>
                                </div>
                                <div class="col-md-12 col-lg-6 d-flex justify-content-end">
                                    <div class="input-group me-2 search-input-grp">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="seller_category_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="seller_category_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="seller_category_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_category_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_category_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_category_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_category_table','excel')">Excel</button>
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

                                <div class="card-body p-0 list_view_html" id="">
                                    <div class="gaps-1-5x"></div>

                                    <div class="table-responsive">
                                        <table id='seller_category_table' data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="{{ route('seller_categories.list') }}" data-click-to-select="true"
                                            data-side-pagination="server" data-pagination="true"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                            data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                            data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                            data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                            data-export-types='["txt","excel","pdf","csv"]'
                                            data-export-options='{
                                "fileName": "categories-list",
                                "ignoreColumn": ["action"]
                            }'
                                            data-query-params="category_query_params">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" data-sortable="true" data-visible='true'>
                                                        {{ labels('admin_labels.id', 'ID') }}
                                                    </th>
                                                    <th data-field="name" data-disabled="1" data-sortable="false">
                                                        {{ labels('admin_labels.name', 'Name') }}
                                                    </th>
                                                    <th class="d-flex justify-content-center" data-field="image"
                                                        data-sortable="false">
                                                        {{ labels('admin_labels.image', 'Image') }}
                                                    </th>
                                                    <th data-field="banner" data-sortable="false">
                                                        {{ labels('admin_labels.banner_image', 'Banner Image') }}
                                                    </th>
                                                    <th data-field="status" data-sortable="false">
                                                        {{ labels('admin_labels.status', 'Status') }}
                                                    </th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                                <div id="" class="d-none tree_view_html">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>


        </div>

    </section>
@endsection
