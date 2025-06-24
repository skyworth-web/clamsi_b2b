@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.stock_manage', 'Combo Stock Manage') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.stock_manage', 'Combo Stock Manage')" :subtitle="labels('admin_labels.know_your_stock_grow_your_business', 'Know your stock, grow your business')" :breadcrumbs="[['label' => labels('admin_labels.stock_manage', 'Combo Stock Manage')]]" />

    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        {{ labels('admin_labels.update_stock', 'Update Stock') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form enctype="multipart/form-data" method="POST" class="submit_form">
                    @method('PUT')
                    @csrf
                    <input type="hidden" id="edit_zipcode_id" name="edit_zipcode_id">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label
                                        for="current_stock">{{ labels('admin_labels.current_stock', 'Current Stock') }}</label>
                                    <input type="text" class="form-control stock" name="stock" id=""
                                        value="" readonly>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="quantity">{{ labels('admin_labels.quantity', 'Quantity') }}</label>
                                    <span class="asterisk text-danger">*</span>
                                    <input type="number" class="form-control quantity" name="quantity" id=""
                                        min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="type">{{ labels('admin_labels.type', 'Type') }}</label>
                                    <select class='form-control form-select' name='type' id='type'>
                                        <option value="add">Add</option>
                                        <option value="subtract">Subtract</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{ labels('admin_labels.close', 'Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary"
                            id="save_changes_btn">{{ labels('admin_labels.update_stock', 'Update Stock') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <section class="overview-data">

        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <h5>{{ labels('admin_labels.stocks', 'Stocks') }} </h4>
                        </div>
                        <div class="col-md-12 col-lg-6 d-flex justify-content-end">
                            <div class="input-group me-3 search-input-grp">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_product_stock_table"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_product_stock_table"
                                sellerFilter='true'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_product_stock_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_stock_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_stock_table','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_stock_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_product_stock_table','excel')">Excel</button>
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
                            <table id='admin_product_stock_table' data-toggle="table"
                                data-loading-template="loadingTemplate"
                                data-url="{{ route('admin.manage_combo_stock.list') }}" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true"
                                data-export-types='["txt","excel","csv"]'
                                data-export-options='{"fileName": "products-list","ignoreColumn": ["state"] }'
                                data-query-params="stock_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.product_id', 'Product ID') }}
                                        </th>
                                        <th data-field="name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.product', 'Product') }}
                                        </th>
                                        <th data-field="price" data-sortable="false">
                                            {{ labels('admin_labels.price', 'Price') }}
                                        </th>
                                        <th data-field="stock_count" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.stock_count', 'Stock Count') }}
                                        </th>
                                        <th data-field="stock_status" data-sortable="false">
                                            {{ labels('admin_labels.status', 'Stock Status') }}
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
