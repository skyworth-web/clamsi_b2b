@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.zipcodes', 'ZipCodes') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.zipcodes', 'ZipCodes')" :subtitle="labels(
        'admin_labels.effortlessly_organize_and_control_zipcode_data',
        'Effortlessly Organize and Control Zip Code Data',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.location', 'Location')],
        ['label' => labels('admin_labels.zipcodes', 'ZipCodes')],
    ]" />

    {{-- add model  --}}

    <div class="modal fade" id="add_zipcode" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myLargeModalLabel">
                        {{ labels('admin_labels.add_zipcode', 'Add ZipCode') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal form-submit-event submit_form" action="{{ route('admin.zipcodes.store') }}"
                    method="POST" id="" enctype="multipart/form-data">
                    @csrf

                    <div class="modal-body">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zipcode"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.zipcode', 'ZipCode') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="zipcode" id="zipcode" value="">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group city_list_parent">
                                    <label for="city"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.city', 'City') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <select class="form-select city_list" name="city" id="">
                                        <option value=" ">
                                            {{ labels('admin_labels.select_city', 'Select City') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="minimum_free_delivery_order_amount"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.minimum_free_delivery_order_amount', 'Minimum Free Delivery Order Amount') }}<span
                                            class='text-danger text-xs'>*</span></label>
                                    <input type="number" class="form-control" name="minimum_free_delivery_order_amount"
                                        id="minimum_free_delivery_order_amount" min="0" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="delivery_charges"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="number" class="form-control" name="delivery_charges" id="delivery_charges"
                                        min="0" value="">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.add_zipcode', 'Add ZipCode') }}</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- edit model  --}}

    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        {{ labels('admin_labels.edit_zipcode', 'Edit ZipCode') }}
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zipcode"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.zipcode', 'ZipCode') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control zipcode" name="zipcode" id="zipcode"
                                        value="">

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.city', 'City') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <select class="form-select mb-0" name="city_id" id="city">
                                        @foreach ($cities as $city)
                                            <option value="{{ $city->id }}">
                                                {{ getDynamicTranslation('cities', 'name', $city->id, $language_code) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="minimum_free_delivery_order_amount"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.minimum_free_delivery_order_amount', 'Minimum Free Delivery Order Amount') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="number" class="form-control minimum_free_delivery_order_amount"
                                        name="minimum_free_delivery_order_amount" id="minimum_free_delivery_order_amount"
                                        min="0" value="">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="delivery_charges"
                                        class="control-label mb-2 mt-2">{{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="number" class="form-control delivery_charges" name="delivery_charges"
                                        id="delivery_charges" min="0" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{ labels('admin_labels.close', 'Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary submit_button"
                            id="save_changes_btn">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- table  --}}
    <section
        class="overview-data {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view zipcodes') ? '' : 'd-none' }}">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-lg-12 col-xxl-6">
                            <h4>{{ labels('admin_labels.manage_zipcodes', 'Manage ZipCodes') }}
                            </h4>
                        </div>
                        <div class="col-md-12 col-lg-12 col-xxl-6 d-flex justify-content-end ">
                            <button type="button" class="btn btn-dark me-3" data-bs-target="#add_zipcode"
                                data-bs-toggle="modal"><i
                                    class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.add_zipcode', 'Add ZipCode') }}</button>

                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_zipcode_table" class="form-control searchInput"
                                    placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_zipcode_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_zipcode_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_zipcode_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_zipcode_table','json')">JSON</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_zipcode_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_zipcode_table','excel')">Excel</button></li>
                                </ul>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                        data-table-id="admin_zipcode_table"
                        data-delete-url="{{ route('zipcodes.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                </div>
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="admin_zipcode_table" data-toggle="table"
                                data-loading-template="loadingTemplate" data-url="{{ route('admin.zipcodes.list') }}"
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
                                        <th data-field="zipcode" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.zipcodes', 'ZipCode') }}
                                        </th>
                                        <th data-field="city_name" data-sortable="false">
                                            {{ labels('admin_labels.city_name', 'City Name') }}
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
