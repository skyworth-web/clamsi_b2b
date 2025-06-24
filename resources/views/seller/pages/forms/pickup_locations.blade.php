@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.pickup_locations', 'Pickup Locations') }}
@endsection
@section('content')
    <section class="main-content">
        <div class="container-fluid mt-5 mb-5 px-6">
            <x-seller.breadcrumb :title="labels('admin_labels.pickup_locations', 'Pickup Locations')" :subtitle="labels(
                'admin_labels.add_pickup_location_with_power_and_simplicity',
                'Add pickup location with power and simplicity',
            )" :breadcrumbs="[['label' => labels('admin_labels.pickup_locations', 'Pickup Locations')]]" />


            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-lg-6">
                                    <h4>{{ labels('admin_labels.pickup_locations', 'Pickup Locations') }}</h4>
                                </div>
                                <div class="col-md-12 col-lg-6 d-flex justify-content-end ">
                                    <button type="button" class="btn btn-dark me-3" data-bs-target="#add_pickup_location"
                                        data-bs-toggle="modal"><i
                                            class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.add_pickup_location', 'Add PickupLocation') }}</button>

                                    <div class="input-group me-3 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="seller_pickup_location_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="seller_pickup_location_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="seller_pickup_location_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_pickup_location_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_pickup_location_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_pickup_location_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_pickup_location_table','excel')">Excel</button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="pt-0">
                                        <div class="table-responsive">
                                            <table id='seller_pickup_location_table' data-toggle="table"
                                                data-loading-template="loadingTemplate"
                                                data-url="{{ route('pickup_locations.list') }}" data-click-to-select="true"
                                                data-side-pagination="server" data-pagination="true"
                                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                                data-show-columns="false" data-show-refresh="false"
                                                data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                                data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                                data-maintain-selected="true" data-export-types='["txt","excel","csv"]'
                                                data-export-options='{
                                                "fileName": "products-list",
                                                "ignoreColumn": ["state"]
                                                }'
                                                data-query-params="queryParams">
                                                <thead>
                                                    <tr>
                                                        <th data-field="id" data-sortable="true">
                                                            {{ labels('admin_labels.id', 'ID') }}
                                                        </th>
                                                        <th data-field="pickup_location" data-sortable="false">
                                                            {{ labels('admin_labels.pickup_locations', 'Pickup Locations') }}
                                                        </th>
                                                        <th data-field="name" data-disabled="1" data-sortable="false">
                                                            {{ labels('admin_labels.name', 'Name') }}
                                                        </th>
                                                        <th data-field="email" data-sortable="false">
                                                            {{ labels('admin_labels.email', 'Email') }}
                                                        </th>
                                                        <th data-field="phone" data-sortable="false">
                                                            {{ labels('admin_labels.mobile', 'Mobile') }}
                                                        </th>
                                                        <th data-field="address" data-sortable="false">
                                                            {{ labels('admin_labels.address', 'Address') }}
                                                        </th>
                                                        <th data-field="address2" data-sortable="false">
                                                            {{ labels('admin_labels.address', 'Address') }} 2
                                                        </th>
                                                        <th data-field="city" data-sortable="false">
                                                            {{ labels('admin_labels.city', 'City') }}
                                                        </th>
                                                        <th data-field="pincode" data-sortable="false">
                                                            {{ labels('admin_labels.zipcodes', 'Zipcodes') }}
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
    <!-- Modal -->
    <div class="modal fade" id="add_pickup_location" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <form id="" action="{{ route('pickup_locations.store') }}" class="submit_form"
                    enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">
                            {{ labels('admin_labels.add_pickup_location', 'Add Pickup Location') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">


                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pickup_location" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.pickup_locations', 'Pickup Locations') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="pickup_location"
                                        placeholder="The nickname of the new pickup location. Max 36 characters."
                                        id="pickup_location" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name"
                                        class="form-label mb-2 mt-2">{{ labels('admin_labels.name', 'Name') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="name"
                                        placeholder="The shipper's name." id="name" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email"
                                        class="form-label mb-2 mt-2">{{ labels('admin_labels.email', 'Email') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="email"
                                        placeholder="The shipper's email address." id="email" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone"
                                        class="form-label mb-2 mt-2">{{ labels('admin_labels.mobile', 'Mobile') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="phone" maxlength="16"
                                        oninput="validateNumberInput(this)" placeholder="Shipper's phone number."
                                        id="phone" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="city"
                                        class="form-label mb-2 mt-2">{{ labels('admin_labels.city', 'City') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="city"
                                        placeholder="Pickup location city name." id="city" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="state" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.state', 'State') }} <span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="state"
                                        placeholder="Pickup location state name." id="state" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="country" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.country', 'Country') }} <span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="country"
                                        placeholder="Pickup location country." id="country" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pincode" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.zipcode', 'Zipcode') }} <span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" name="pincode"
                                        placeholder="Pickup location pincode." id="pincode" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.address', 'Address') }} <span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <textarea class="form-control" name="address" placeholder="Shipper's primary address. Max 80 characters."
                                        id="address"></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="address2" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.address', 'Address') }} 2 </label>
                                    <textarea class="form-control" name="address2" placeholder="Additional address details." id="address2"><?= isset($fetched_data[0]['address_2']) ? $fetched_data[0]['address_2'] : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitude" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.latitude', 'Latitude') }}</label>
                                    <input type="text" class="form-control" name="latitude"
                                        placeholder="Pickup location Latitude." id="latitude" value="">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitude" class="form-label mb-2 mt-2">
                                        {{ labels('admin_labels.longitude', 'Longitude') }}</label>
                                    <input type="text" class="form-control" name="longitude"
                                        placeholder="Pickup location Longitude." id="longitude" value="">
                                </div>
                            </div>
                        </div>


                    </div>
                    <div class="modal-footer">
                        <button type="reset"
                            class="btn reset-btn mx-2">{{ labels('admin_labels.reset', 'Reset') }}</button>
                        <button type="submit"
                            class="btn btn-primary submit_button">{{ labels('admin_labels.add_pickup_location', 'Add Pickup Location') }}</button>
                    </div>
                </form>
            </div>

        </div>
    </div>
@endsection
