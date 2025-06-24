@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.manage_products', 'Manage Products') }}
@endsection
@section('content')
    <section class="main-content">
        <div class="row">
            <x-seller.breadcrumb :title="labels('admin_labels.manage_products', 'Manage Products')" :subtitle="labels('admin_labels.track_and_manage_products', 'Track And Manage Products')" :breadcrumbs="[
                ['label' => labels('admin_labels.products', 'Products')],
                ['label' => labels('admin_labels.manage_products', 'Manage Products')],
            ]" />

            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-xl-4">
                                    <h4>{{ labels('admin_labels.products', 'Products') }} </h4>
                                </div>
                                <div class="col-md-12 col-xl-8 d-flex justify-content-end">
                                    <a href="{{ route('seller.products.index') }}" class="btn btn-dark me-3"><i
                                            class='bx bx-plus-circle me-1'></i>
                                        {{ labels('admin_labels.add_product', 'Add Product') }}</a>

                                    <div class="input-group me-2 search-input-grp">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="seller_product_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="seller_product_table"
                                        productTypeFilter='true' brandFilter='true' StatusFilter='true'
                                        categoryFilter='true'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="seller_product_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_product_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_product_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_product_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_product_table','excel')">Excel</button>
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


                                    <table id='seller_product_table' data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('seller.products.list') }}" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel"]' data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true" data-visible='true'>
                                                    {{ labels('admin_labels.id', 'ID') }}
                                                </th>

                                                <th class="d-flex justify-content-center" data-field="image"
                                                    data-sortable="false">
                                                    {{ labels('admin_labels.image', 'Image') }}
                                                </th>
                                                <th data-field="name" data-sortable="false" data-disabled="1">
                                                    {{ labels('admin_labels.name', 'Name') }}
                                                </th>
                                                <th data-field="brand" data-sortable="false">
                                                    {{ labels('admin_labels.brand', 'Brand') }}
                                                </th>
                                                <th data-field="category_name" data-sortable="false">
                                                    {{ labels('admin_labels.category', 'Category Name') }}
                                                </th>
                                                <th data-field="rating" data-sortable="false">
                                                    {{ labels('admin_labels.rating', 'Rating') }}
                                                </th>
                                                <th data-field="variations" data-sortable="false" data-visible='false'>
                                                    {{ labels('admin_labels.variations', 'Variations') }}
                                                </th>
                                                <th data-field="status" data-sortable="false">
                                                    {{ labels('admin_labels.status', 'Status') }}
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
    </section>
@endsection
