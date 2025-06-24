@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.sellers', 'Sellers') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.sellers', 'Sellers')" :subtitle="labels(
        'admin_labels.efficiently_organize_and_manage_sellers',
        'Efficiently Organize and Manage Sellers',
    )" :breadcrumbs="[['label' => labels('admin_labels.sellers', 'Sellers')]]" />


    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-sm-12">
                            <h4>{{ labels('admin_labels.manage_sellers', 'Manage Sellers') }}
                            </h4>
                        </div>
                        <div class="col-sm-12 d-flex justify-content-end mt-md-2 mt-sm-2">
                            <a href="#" class="btn btn-dark me-3 update-seller-commission"
                                title="If you found seller commission not crediting using cron job you can update seller commission from here!">{{ labels('admin_labels.update_seller_commission', 'Update Seller Commission') }}</a>
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_manage_sellers" class="form-control searchInput"
                                    placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_manage_sellers"
                                productStatusFilter='true'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_manage_sellers"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_manage_sellers','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_manage_sellers','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_manage_sellers','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_manage_sellers','excel')">Excel</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                        data-table-id="admin_manage_sellers"
                        data-delete-url="{{ route('sellers.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                </div>
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="admin_manage_sellers" data-toggle="table"
                                data-loading-template="loadingTemplate" data-url="{{ route('sellers.list') }}"
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
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.name', 'Name') }}
                                        </th>
                                        <th data-field="email" data-sortable="false">
                                            {{ labels('admin_labels.email', 'Email') }}
                                        </th>
                                        <th data-field="mobile" data-sortable="false">
                                            {{ labels('admin_labels.mobile', 'Mobile') }}
                                        </th>
                                        <th data-field="address" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.address', 'Address') }}
                                        </th>
                                        <th data-field="balance" data-sortable="false">
                                            {{ labels('admin_labels.balance', 'Balance') }}
                                        </th>
                                        <th data-field="rating" data-sortable="false">
                                            {{ labels('admin_labels.rating', 'Rating') }}
                                        </th>
                                        <th data-field="store_name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.store_name', 'Store Name') }}
                                        </th>
                                        <th data-field="store_url" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.store_url', 'Store URL') }}
                                        </th>
                                        <th data-field="store_description" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.description', 'Description') }}
                                        </th>
                                        <th data-field="account_number" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.account_number', 'Account Number') }}
                                        </th>
                                        <th data-field="account_name" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.account_name', 'Account Name') }}
                                        </th>
                                        <th data-field="bank_code" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.bank_code', 'Bank Code') }}
                                        </th>
                                        <th data-field="bank_name" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.bank_name', 'Bank Name') }}
                                        </th>
                                        <th data-field="tax_name" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.tax_name', 'Tax Name') }}
                                        </th>
                                        <th data-field="tax_number" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.tax_number', 'Tax Number') }}
                                        </th>
                                        <th data-field="pan_number" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.pan_number', 'Pan Number') }}
                                        </th>
                                        <th data-field="status" data-sortable="false">
                                            {{ labels('admin_labels.status', 'Status') }}
                                        </th>
                                        <th data-field="logo" data-sortable="false">
                                            {{ labels('admin_labels.logo', 'Logo') }}
                                        </th>
                                        <th data-field="store_thumbnail" data-sortable="false">
                                            {{ labels('admin_labels.store_thumbnail', 'Store Thumbnail') }}
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
    </section>
@endsection
