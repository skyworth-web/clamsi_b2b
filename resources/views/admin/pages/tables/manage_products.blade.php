@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.manage_products', 'Manage Products') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.manage_products', 'Manage Products')" :subtitle="labels('admin_labels.track_and_manage_products', 'Track And Manage Products')" :breadcrumbs="[
        ['label' => labels('admin_labels.products', 'Products'), 'url' => route('admin.products.index')],
        ['label' => labels('admin_labels.manage_products', 'Manage Products')],
    ]" />

    {{-- table  --}}
    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{{ labels('admin_labels.manage_products', 'Manage Products') }}
                            </h4>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end ">
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_product_table" class="form-control searchInput"
                                    placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_product_table" sellerFilter='true'
                                categoryFilter='true' StatusFilter='true' productTypeFilter='true' brandFilter='true'><i
                                    class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_product_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_table','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_table','excel')">Excel</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                        data-table-id="admin_product_table"
                        data-delete-url="{{ route('products.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                </div>
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="admin_product_table" data-toggle="table"
                                data-loading-template="loadingTemplate" data-url="{{ route('admin.products.list') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true" data-export-types='["txt","excel"]'
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-checkbox="true" data-field="delete-checkbox">
                                            <input name="select_all" type="checkbox">
                                        </th>
                                        <th data-field="id" data-sortable="true" data-visible='true'>
                                            {{ labels('admin_labels.id', 'ID') }}
                                        </th>
                                        <th class="d-flex justify-content-center" data-field="image" data-sortable="false">
                                            {{ labels('admin_labels.image', 'Image') }}
                                        </th>
                                        <th data-field="name" data-sortable="false" data-disabled="1">
                                            {{ labels('admin_labels.name', 'Name') }}
                                        </th>
                                        <th data-field="brand" data-sortable="false">
                                            {{ labels('admin_labels.brand', 'Brand') }}
                                        </th>
                                        <th data-field="category_name" data-sortable="false">
                                            {{ labels('admin_labels.category', 'Category') }}
                                        </th>
                                        <th data-field="rating" data-sortable="false">
                                            {{ labels('admin_labels.rating', 'Rating') }}
                                        </th>
                                        <th data-field="status" data-sortable="false">
                                            {{ labels('admin_labels.status', 'Status') }}
                                        </th>
                                        <th data-field="variations" data-sortable="false" data-visible='false'>
                                            {{ labels('admin_labels.variations', 'Variations') }}
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
