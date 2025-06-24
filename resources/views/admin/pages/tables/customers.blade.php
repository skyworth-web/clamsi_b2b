@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.customers', 'Customers') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.customers', 'Customers')" :subtitle="labels('admin_labels.optimize_and_manage_customers', 'Optimize and Manage Customers')" :breadcrumbs="[['label' => labels('admin_labels.customers', 'Customers')]]" />


    {{-- table --}}
    <div
        class="col-md-12 mt-4 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view customers') ? '' : 'd-none' }}">
        <section class="overview-data">
            <div class="card content-area p-4 ">
                <div class="row align-items-center d-flex heading mb-5">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6">
                                <h4> {{ labels('admin_labels.manage_customers', 'Manage Customers') }}
                                </h4>
                            </div>

                            <div class="col-md-6 d-flex justify-content-end ">
                                <div class="input-group me-2 search-input-grp ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="admin_customer_table" class="form-control searchInput"
                                        placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="admin_customer_table"
                                    StatusFilter='true'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh" data-table="admin_customer_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_customer_table','csv')">CSV</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_customer_table','json')">JSON</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_customer_table','sql')">SQL</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_customer_table','excel')">Excel</button>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                            data-table-id="admin_customer_table"
                            data-delete-url="{{ route('customers.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                    </div>
                    <div class="col-md-12">
                        <div class="pt-0">
                            <div class="table-responsive">
                                <table class='table' id="admin_customer_table" data-toggle="table"
                                    data-loading-template="loadingTemplate" data-url="{{ route('customers.list') }}"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                    data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                    data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                    data-show-export="false" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="queryParams">
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
                                            <th data-field="mobile" data-disabled="1" data-sortable="false">mobile
                                            </th>
                                            <th data-field="address" data-sortable="false" data-visible="false">
                                                {{ labels('admin_labels.address', 'Address') }}
                                            </th>
                                            <th data-field="email" data-sortable="false">
                                                {{ labels('admin_labels.email', 'Email') }}
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

    <input type='hidden' id='address_user_id' value=''>

    <div class="modal fade" id="customer-address-modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog  modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ labels('admin_labels.view_address_details', 'View Address Details') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <div class="modal-body">
                    <section class="overview-data">
                        <div class="card content-area p-4 ">
                            <div class="row align-items-center d-flex heading mb-5">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12 d-flex justify-content-end ">
                                            <div class="input-group me-2 search-input-grp ">
                                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                                <input type="text" data-table="customer-address-table"
                                                    class="form-control searchInput" placeholder="Search...">
                                                <span
                                                    class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                            </div>

                                            <a class="btn me-2" id="tableRefresh" data-table="customer-address-table"><i
                                                    class='bx bx-refresh'></i></a>
                                            <div class="dropdown">
                                                <a class="btn dropdown-toggle export-btn" type="button"
                                                    id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                    <i class='bx bx-download'></i>
                                                </a>
                                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="exportTableData('customer-address-table','csv')">CSV</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="exportTableData('customer-address-table','json')">JSON</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="exportTableData('customer-address-table','sql')">SQL</a>
                                                    </li>
                                                    <li><a class="dropdown-item" href="#"
                                                            onclick="exportTableData('customer-address-table','excel')">Excel</a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="pt-0">
                                                <div class="table-responsive">
                                                    <table class='table' id='customer-address-table' data-toggle="table"
                                                        data-loading-template="loadingTemplate"
                                                        data-url="{{ route('admin.customers.getCustomersAddressesList') }}"
                                                        data-click-to-select="true" data-side-pagination="server"
                                                        data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                        data-search="false" data-show-columns="false"
                                                        data-show-refresh="false" data-trim-on-search="false"
                                                        data-sort-name="id" data-sort-order="desc"
                                                        data-mobile-responsive="true" data-toolbar=""
                                                        data-show-export="false" data-maintain-selected="true"
                                                        data-export-types='["txt","excel"]'
                                                        data-query-params="address_query_params">
                                                        <thead>
                                                            <tr>
                                                                <th data-field="id" data-sortable="true">
                                                                    {{ labels('admin_labels.id', 'ID') }}
                                                                </th>
                                                                <th data-field="name" data-sortable="false">
                                                                    {{ labels('admin_labels.user_name', 'User Name') }}
                                                                </th>
                                                                <th data-field="type" data-sortable="false">
                                                                    {{ labels('admin_labels.type', 'Type') }}
                                                                </th>
                                                                <th data-field="mobile" data-sortable="false">
                                                                    {{ labels('admin_labels.mobile', 'Mobile') }}
                                                                </th>
                                                                <th data-field="alternate_mobile" data-sortable="false">
                                                                    {{ labels('admin_labels.alternate_mobile', 'Alternate Mobile') }}
                                                                </th>
                                                                <th data-field="address" data-sortable="false"
                                                                    data-visible="false">
                                                                    {{ labels('admin_labels.address', 'Address') }}
                                                                </th>
                                                                <th data-field="landmark" data-sortable="false">
                                                                    {{ labels('admin_labels.landmark', 'Landmark') }}
                                                                </th>
                                                                <th data-field="area" data-sortable="false">
                                                                    {{ labels('admin_labels.area', 'Area') }}
                                                                </th>
                                                                <th data-field="city" data-sortable="false">
                                                                    {{ labels('admin_labels.city', 'City') }}
                                                                </th>
                                                                </th>
                                                                <th data-field="state" data-sortable="false">
                                                                    {{ labels('admin_labels.state', 'State') }}
                                                                </th>
                                                                <th data-field="pincode" data-sortable="false">
                                                                    {{ labels('admin_labels.zipcodes', 'ZipCode') }}
                                                                </th>
                                                                <th data-field="country" data-sortable="false">
                                                                    {{ labels('admin_labels.country', 'Country') }}
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


            </div>
        </div>
    </div>
@endsection
