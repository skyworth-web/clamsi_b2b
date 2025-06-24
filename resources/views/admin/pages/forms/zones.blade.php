@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.zones', 'Zones') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.zones', 'Zones')" :subtitle="labels(
        'admin_labels.enhance_visual_appeal_with_effortless_zone_integration',
        'Enhance Visual Appeal with Effortless Zone Integration',
    )" :breadcrumbs="[['label' => labels('admin_labels.zones', 'Zones')]]" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="form-horizontal form-submit-event submit_form" action="{{ route('admin.zones.store') }}"
                    method="POST" id="" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <h5 class="mb-4">
                            {{ labels('admin_labels.add_zone', 'Add Zones') }}
                        </h5>
                        <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="language-nav-link nav-link active" id="tab-en" data-bs-toggle="tab"
                                    data-bs-target="#content-en" type="button" role="tab" aria-controls="content-en"
                                    aria-selected="true">
                                    {{ labels('admin_labels.default', 'Default') }}
                                </button>
                            </li>
                            {!! generateLanguageTabsNav($languages) !!}
                        </ul>

                        <div class="tab-content mt-3" id="brandTabsContent">
                            <div class="tab-pane fade show active" id="content-en" role="tabpanel" aria-labelledby="tab-en">
                                <div class="mb-3">
                                    <label for="title" class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                            class="text-asterisks text-sm">*</span></label>
                                    <input type="text" placeholder="" name="name" class="form-control"
                                        value="{{ old('name') }}">
                                </div>
                            </div>

                            {!! generateLanguageTabs($languages, 'admin_labels.name', 'Name', 'translated_zone_name') !!}
                        </div>
                        <label for="name"
                            class="form-label">{{ labels('admin_labels.serviceable_zipcodes', 'Serviceable Zipcodes') }}<span
                                class="text-asterisks text-sm">*</span></label>
                        <div class="repeater">
                            <div data-repeater-list="zipcode_group">
                                <div data-repeater-item>
                                    <div class="row">
                                        <div class="col-md-5 mt-2">
                                            <select class="form-select zone_zipcode_list" name="serviceable_zipcode_id"
                                                required id="">
                                                <option value=" ">
                                                    {{ labels('admin_labels.select_zipcode', 'Select Zipcode') }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 mt-2">
                                            <input type="text" name="zipcode_delivery_charge" class="form-control"
                                                placeholder="Delivery Charge" value="" />
                                        </div>
                                        <div class="col-md-2">
                                            <input data-repeater-delete type="button" class="btn btn-secondary mt-2"
                                                value="Delete" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input data-repeater-create type="button" class="btn btn-primary mt-2" value="Add" />
                        </div>
                        <label for="name"
                            class="form-label mt-4">{{ labels('admin_labels.serviceable_cities', 'Serviceable Cities') }}<span
                                class="text-asterisks text-sm">*</span></label>
                        <div class="repeater">
                            <div data-repeater-list="city_group">
                                <div data-repeater-item>
                                    <div class="row city_list_parent">
                                        <div class="col-md-5 mt-2">
                                            <select class="form-select city_list" name="serviceable_city_id" id="">
                                                <option value=" ">
                                                    {{ labels('admin_labels.select_city', 'Select City') }}
                                                </option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 mt-2">
                                            <input type="text" name="city_delivery_charge" class="form-control"
                                                placeholder="Delivery Charge" value="" />
                                        </div>
                                        <div class="col-md-2">
                                            <input data-repeater-delete type="button" class="btn btn-secondary mt-2"
                                                value="Delete" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <input data-repeater-create type="button" class="btn btn-primary mt-2" value="Add" />
                        </div>
                        {{-- <div class="mb-3">
                                <label
                                    class="form-label">{{ labels('admin_labels.serviceable_zipcodes', 'Serviceable Zipcodes') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <select name="serviceable_zipcode_ids[]"
                                    class="form-control  search_zipcode w-100" multiple
                                    onload="multiselect()">
                                    <option value="">
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-group">
                                    <label for="cities"
                                        class="form-label">{{ labels('admin_labels.serviceable_cities', 'Serviceable Cities') }}<span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <select name="serviceable_city_ids[]"
                                        class="city_list  form-select w-100" multiple
                                        onload="multiselect()">
                                        <option value="">
                                    </select>
                                </div>
                            </div> --}}
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.add_zone', 'Add Zones') }}</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- table --}}
        <div
            class="col-md-12  mt-4 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view zones') ? '' : 'd-none' }}">
            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4> {{ labels('admin_labels.manage_zones', 'Manage Zones') }}
                                    </h4>
                                </div>
                                <div class="col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                                    <div class="input-group me-2 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="admin_zone_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span
                                            class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="admin_zone_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh"data-table="admin_zone_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button"
                                            id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_zone_table','csv')">CSV</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_zone_table','json')">JSON</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_zone_table','sql')">SQL</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_zone_table','excel')">Excel</button>
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
                                data-table-id="admin_zone_table"
                                data-delete-url="{{ route('zones.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                        </div>
                        <div class="col-md-12">
                            <div class="pt-0">
                                <div class="table-responsive">
                                    <table class='table' id="admin_zone_table" data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('admin.zones.list') }}" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel"]' data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-checkbox="true" data-field="delete-checkbox">
                                                    <input name="select_all" type="checkbox">
                                                </th>
                                                <th data-field="id" data-sortable="true">
                                                    {{ labels('admin_labels.id', 'ID') }}
                                                <th data-field="name" data-sortable="false">
                                                    {{ labels('admin_labels.name', 'Name') }}
                                                </th>
                                                <th data-field="serviceable_zipcodes" data-sortable="false"
                                                    data-visible="true">
                                                    {{ labels('admin_labels.serviceable_zipcodes', 'Serviceable Zipcodes') }}
                                                </th>
                                                <th data-field="serviceable_cities" data-sortable="false"
                                                    data-visible="true">
                                                    {{ labels('admin_labels.serviceable_cities', 'Serviceable Cities') }}
                                                </th>
                                                <th data-field="status" data-sortable="false" data-visible="true">
                                                    {{ labels('admin_labels.status', 'Status') }}
                                                </th>

                                                <th data-field="action" data-sortable="false">
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
    </div>
@endsection
