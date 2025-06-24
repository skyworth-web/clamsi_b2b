@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.city', 'City') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.city', 'City')" :subtitle="labels(
        'admin_labels.efficiently_organize_and_govern_city_data',
        'Efficiently Organize and Govern City Data',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.location', 'Location')],
        ['label' => labels('admin_labels.cities', 'Cities')],
    ]" />



    {{-- add model  --}}

    <div class="modal fade" id="add_city" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myLargeModalLabel">
                        {{ labels('admin_labels.add_city', 'Add City') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>

                    </button>
                </div>
                <form class="form-horizontal form-submit-event submit_form" action="{{ route('admin.city.store') }}"
                    method="POST" id="" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label for="city_name" class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control" name="name" value="">
                            </div>
                        </div>
                        @foreach ($languages as $lang)
                            @if ($lang->code !== 'en')
                                <div class="mb-3 col-md-12">
                                    <label for="translated_name_{{ $lang->code }}" class="form-label">
                                        {{ labels('admin_labels.name', 'Name') }} ({{ $lang->language }})
                                    </label>
                                    <input type="text" class="form-control translated_name_{{ $lang->code }}"
                                        id="translated_name_{{ $lang->code }}"
                                        name="translated_city_name[{{ $lang->code }}]" value="">
                                </div>
                            @endif
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="minimum_free_delivery_order_amount"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.minimum_free_delivery_order_amount', 'Minimum Free Delivery Order Amount') }}<span
                                            class='text-danger text-xs'>*</span></label>
                                    <input type="text" class="form-control" oninput="validateNumberInput(this)"
                                        name="minimum_free_delivery_order_amount" id="minimum_free_delivery_order_amount"
                                        min="0" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="delivery_charges"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control" oninput="validateNumberInput(this)"
                                        name="delivery_charges" id="delivery_charges" min="0" value="">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.add_city', 'Add City') }}</button>
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
                        {{ labels('admin_labels.edit_city', 'Edit City') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form enctype="multipart/form-data" method="POST" class="submit_form">
                    @method('PUT')
                    @csrf
                    <input type="hidden" id="edit_area_id" name="edit_area_id">

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group">
                                <label for="city">{{ labels('admin_labels.name', 'Name') }}</label>
                                <input type="text" class="form-control name" id="" name="name">
                            </div>
                        </div>
                        @foreach ($languages as $lang)
                            @if ($lang->code !== 'en')
                                <div class="mb-3 col-md-12">
                                    <label for="translated_name_{{ $lang->code }}" class="form-label">
                                        {{ labels('admin_labels.name', 'Name') }} ({{ $lang->language }})
                                    </label>
                                    <input type="text" class="form-control translated_name_{{ $lang->code }}"
                                        id="translated_name_{{ $lang->code }}"
                                        name="translated_city_name[{{ $lang->code }}]" value="">
                                </div>
                            @endif
                        @endforeach
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="minimum_free_delivery_order_amount"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.minimum_free_delivery_order_amount', 'Minimum Free Delivery Order Amount') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control minimum_free_delivery_order_amount"
                                        oninput="validateNumberInput(this)" name="minimum_free_delivery_order_amount"
                                        id="minimum_free_delivery_order_amount" min="0" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="delivery_charges"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control delivery_charges"
                                        oninput="validateNumberInput(this)" name="delivery_charges" id="delivery_charges"
                                        min="0" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{ !trans()->has('admin_labels.close') ? 'Close' : trans('admin_labels.close') }}
                        </button>
                        <button type="submit" class="btn btn-primary submit_button"
                            id="submit_button">{{ !trans()->has('admin_labels.save_changes') ? 'Save Changes' : trans('admin_labels.save_changes') }}</button>
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
                        <div class="ol-md-12 col-lg-12 col-xxl-6">
                            <h4>{{ labels('admin_labels.manage_cities', 'Manage Cities') }}
                            </h4>
                        </div>
                        <div class="ol-md-12 col-lg-12 col-xxl-6 d-flex justify-content-end ">
                            <button type="button" class="btn btn-dark me-3" data-bs-target="#add_city"
                                data-bs-toggle="modal"><i
                                    class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.add_city', 'Add City') }}</button>
                            <div
                                class="input-group me-2 search-input-grp {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view city') ? '' : 'd-none' }}">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_city_table" class="form-control searchInput"
                                    placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view city') ? '' : 'd-none' }}"
                                id="tableFilter" data-bs-toggle="offcanvas" data-bs-target="#columnFilterOffcanvas"
                                data-table="admin_city_table" dateFilter='false' orderStatusFilter='false'
                                paymentMethodFilter='false' orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view city') ? '' : 'd-none' }}"
                                id="tableRefresh" data-table="admin_city_table"><i class='bx bx-refresh'></i></a>
                            <div
                                class="dropdown {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view city') ? '' : 'd-none' }}">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_city_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_city_table','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_city_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_city_table','excel')">Excel</button></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="row {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view city') ? '' : 'd-none' }}">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                        data-table-id="admin_city_table"
                        data-delete-url="{{ route('cities.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                </div>
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="admin_city_table" data-toggle="table"
                                data-loading-template="loadingTemplate" data-url="{{ route('admin.city.list') }}"
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
                                        <th data-field="minimum_free_delivery_order_amount" data-sortable="false">
                                            {{ labels('admin_labels.minimum_free_delivery_order_amount', 'Minimum Free Delivery Order Amount') }}
                                        </th>
                                        <th data-field="delivery_charges" data-sortable="false">
                                            {{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}
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
