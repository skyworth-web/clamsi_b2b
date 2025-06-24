@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.manage_system_users', 'Manage System Users') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.manage_system_users', 'Manage System Users')" :subtitle="labels(
        'admin_labels.effortlessly_administer_and_control_system_users',
        'Effortlessly Administer and Control System Users',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.system_users', 'System Users')],
        ['label' => labels('admin_labels.manage_system_users', 'Manage System Users')],
    ]" />

    {{-- table --}}

    <section class="overview-data ">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <h4> {{ labels('admin_labels.manage_system_users', 'Manage System Users') }}
                            </h4>
                        </div>
                        <div class="col-md-12 col-lg-6 d-flex justify-content-end ">
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_system_users_table" class="form-control searchInput"
                                    placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_system_users_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh"data-table="admin_system_users_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_system_users_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_system_users_table','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_system_users_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_system_users_table','excel')">Excel</button>
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
                        data-table-id="admin_system_users_table"
                        data-delete-url="{{ route('system_users.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                </div>
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="admin_system_users_table" data-toggle="table"
                                data-loading-template="loadingTemplate" data-url="{{ route('system_users.list') }}"
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
                                        <th data-field="username" data-sortable="false">
                                            {{ labels('admin_labels.user_name', 'User Name') }}
                                        </th>
                                        <th data-field="mobile" data-sortable="false">
                                            {{ labels('admin_labels.mobile', 'Mobile') }}
                                        </th>
                                        <th data-field="email" data-sortable="false">
                                            {{ labels('admin_labels.email', 'Email') }}
                                        </th>
                                        <th data-field="role" data-sortable="false">
                                            {{ labels('admin_labels.role', 'Role') }}
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
