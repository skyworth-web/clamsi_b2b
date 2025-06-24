@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.customer_address', 'Customer Address') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.customer_address', 'Customer Address')" :subtitle="labels('admin_labels.track_and_manage_customer_addresses', 'Track and Manage Customer Addresses')" :breadcrumbs="[
        ['label' => labels('admin_labels.customers', 'Customers')],
        ['label' => labels('admin_labels.address', 'Address')],
    ]" />


    {{-- table  --}}
    <section
        class="overview-data {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view address') ? '' : 'd-none' }}">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>{{ labels('admin_labels.manage_customer_address', 'Manage Customer Address') }}
                            </h4>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end ">
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_customer_address_table"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_customer_address_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh"data-table="admin_customer_address_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_address_table','csv')">CSV</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_address_table','json')">JSON</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_address_table','sql')">SQL</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_address_table','excel')">Excel</button>
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
                            <table class='table' id="admin_customer_address_table" data-toggle="table"
                                data-loading-template="loadingTemplate"
                                data-url="{{ route('admin.customers.getCustomersAddressesList') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true" data-export-types='["txt","excel"]'
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="name" data-disabled="1" data-sortable="false">
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
                                        <th data-field="address" data-sortable="false" data-visible="false">
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
@endsection
