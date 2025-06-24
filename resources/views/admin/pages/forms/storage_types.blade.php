@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.storage_types', 'Storage Types') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.storage_types', 'Storage Types')" :subtitle="labels(
        'admin_labels.efficiently_organize_and_govern_storage_types',
        'Efficiently Organize and Govern Storage Types',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.media', 'Media')],
        ['label' => labels('admin_labels.storage_types', 'Storage Types')],
    ]" />


    {{-- add model  --}}

    <div class="modal fade" id="add_storage_type" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myLargeModalLabel">
                        {{ labels('admin_labels.add_storage_type', 'Add Storage Type') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>

                    </button>
                </div>
                <form class="form-horizontal form-submit-event submit_form" action="{{ route('admin.storage_type.store') }}"
                    method="POST" id="" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <label for="city_name" class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <input type="text" class="form-control" name="name" id="" value=""
                                placeholder="public,s3">
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.add_storage_type', 'Add Storage Type') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- edit modal  --}}

    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        {{ labels('admin_labels.edit_storage_type', 'Edit Storage Type') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form enctype="multipart/form-data" method="POST" class="submit_form">
                    @method('PUT')
                    @csrf

                    <div class="modal-body">
                        <div class="form-group">
                            <label for="city">{{ labels('admin_labels.name', 'Name') }}</label>
                            <input type="text" class="form-control name" id="" name="name">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{ labels('admin_labels.close', 'Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary submit_button"
                            id="submit_button">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
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
                        <div class="col-sm-12">
                            <h4>{{ labels('admin_labels.manage_storage_type', 'Manage Storage Type') }}
                            </h4>
                        </div>
                        <div class="col-sm-12 d-flex justify-content-end mt-md-2 mt-sm-2">

                            <button type="button" class="btn btn-dark me-3" data-bs-target="#add_storage_type"
                                data-bs-toggle="modal"><i
                                    class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.add_storage_type', 'Add Storage Type') }}</button>
                            @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view storage_type'))
                                <div class="input-group me-2 search-input-grp ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="admin_storage_type_table"
                                        class="form-control searchInput" placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="admin_storage_type_table"
                                    dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                    orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh"data-table="admin_storage_type_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_storage_type_table','csv')">CSV</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_storage_type_table','json')">JSON</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_storage_type_table','sql')">SQL</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_storage_type_table','excel')">Excel</button>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="row {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view storage_type') ? '' : 'd-none' }}">
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="admin_storage_type_table" data-toggle="table"
                                data-loading-template="loadingTemplate" data-url="{{ route('admin.storage_type.list') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true"
                                data-export-types='["txt","excel"]' data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true" class="col-md-4">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="name" data-disabled="1" data-sortable="false" class="col-md-4">
                                            {{ labels('admin_labels.name', 'Name') }}
                                        </th>
                                        <th data-field="operate" data-sortable="false" class="col-md-4">
                                            {{ labels('admin_labels.set_as_default', 'Set as Default') }}
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
