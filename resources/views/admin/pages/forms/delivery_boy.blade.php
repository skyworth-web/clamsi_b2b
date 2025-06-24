@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.delivery_boys', 'Delivery Boys') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.delivery_boys', 'Delivery Boys')" :subtitle="labels(
        'admin_labels.optimize_and_control_your_fleet_of_delivery_personnel',
        'Optimize and Control Your Fleet of Delivery Personnel',
    )" :breadcrumbs="[['label' => labels('admin_labels.delivery_boys', 'Delivery Boys')]]" />

    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">
                        {{ !trans()->has('admin_labels.add_delivery_boy') ? 'Add Delivery Boy' : trans('admin_labels.add_delivery_boy') }}
                    </h5>
                    <div class="row">
                        <div class="form-group">
                            <form class="form-horizontal form-submit-event submit_form"
                                action="{{ route('delivery_boys.store') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="mb-3 col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.name') ? 'Name' : trans('admin_labels.name') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <input type="text" class="form-control" placeholder="" name="name"
                                            value="{{ old('name') }}">

                                    </div>
                                    <div class="mb-3 col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.mobile') ? 'Mobile' : trans('admin_labels.mobile') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <input type="text" maxlength="16" oninput="validateNumberInput(this)"
                                            class="form-control" placeholder="" name="mobile">

                                    </div>
                                    <div class="mb-3 col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.email') ? 'Email' : trans('admin_labels.email') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <input type="text" class="form-control" placeholder="" name="email"
                                            value="{{ old('email') }}">

                                    </div>
                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.password') ? 'Password' : trans('admin_labels.password') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control show_seller_password" name="password"
                                                placeholder="Enter Your Password">
                                            <span class="input-group-text cursor-pointer toggle_password"><i
                                                    class="bx bx-hide"></i></span>
                                        </div>
                                    </div>

                                </div>
                                <div class="row">
                                    <div class="mb-3 col-lg-6 col-md-12">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.confirm_password') ? 'Confirm Password' : trans('admin_labels.confirm_password') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="confirm_password"
                                                placeholder="Enter your password" aria-describedby="password" />
                                            <span class="input-group-text cursor-pointer toggle_confirm_password"><i
                                                    class="bx bx-hide"></i></span>
                                        </div>
                                    </div>

                                    <div class="mb-3 col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.serviceable_zones') ? 'Serviceable zones' : trans('admin_labels.serviceable_zones') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <select name="serviceable_zones[]" class="form-control search_zone w-100" multiple
                                            onload="multiselect()" id="zone_list">
                                            <option value="">
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="mb-3 col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.address') ? 'Address' : trans('admin_labels.address') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <textarea type="text" class="form-control" placeholder="" name="address" value="">{{ old('address') }}</textarea>

                                    </div>
                                    <div class="mb-3 col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.bonus_type') ? 'Bonus Type' : trans('admin_labels.bonus_type') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <select class="form-select form-select-md mb-3 bonus_type"
                                            aria-label=".form-select-md example" name="bonus_type">
                                            <option value="0">
                                                {{ !trans()->has('admin_labels.select_type') ? 'Select Type' : trans('admin_labels.select_type') }}
                                            </option>
                                            <option value="fixed_amount_per_order_item">Fixed Amount Per Order Item</option>
                                            <option value="percentage_per_order_item">Percentage Per Order Item</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-lg-6 col-md-12  fixed_amount_per_order_item d-none">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.bonus_amount') ? 'Bonus Amount' : trans('admin_labels.bonus_amount') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <input type="number" min=0 class="form-control"
                                            placeholder="Enter amount to be given to the delivery boy on successful order item delivery"
                                            placeholder="" name="bonus_amount" value="{{ old('bonus_amount') }}">

                                    </div>
                                    <div class="mb-3 col-lg-6 col-md-12  percentage_per_order_item d-none">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.bonus_percentage') ? 'Bonus Percentage' : trans('admin_labels.bonus_percentage') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <input type="number" class="form-control" min=1 max=100
                                            placeholder="Enter Bonus(%) to be given to the delivery boy on successful order item delivery"
                                            placeholder="" name="bonus_percentage"
                                            value="{{ old('bonus_percentage') }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.driving_licence_front_image') ? 'Driving Licence Front Image' : trans('admin_labels.driving_licence_front_image') }}<span
                                                class="text-asterisks text-sm">*</span></label>

                                        <div class="col-md-12  text-center form-group">
                                            <input type="file" class="filepond" name="front_licence_image"
                                                data-max-file-size="300MB" data-max-files="20" accept="image/*,.webp" />
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-12 ">
                                        <label
                                            class="form-label">{{ !trans()->has('admin_labels.driving_licence_back_image') ? 'Driving Licence Back Image' : trans('admin_labels.driving_licence_back_image') }}<span
                                                class="text-asterisks text-sm">*</span></label>

                                        <div class="col-md-12  text-center form-group">
                                            <input type="file" class="filepond" name="back_licence_image"
                                                data-max-file-size="300MB" data-max-files="20" accept="image/*,.webp" />
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="reset"
                                        class="btn mx-2 reset_button">{{ !trans()->has('admin_labels.reset') ? 'Reset' : trans('admin_labels.reset') }}</button>
                                    <button type="submit"
                                        class="btn btn-primary submit_button">{{ !trans()->has('admin_labels.add_delivery_boy') ? 'Add Delivery Boy' : trans('admin_labels.add_delivery_boy') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- ======================= modal for fund transfer ====================== -->


    <div class="modal fade" id="fund_transfer_delivery_boy" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ labels('admin_labels.fund_transfer', 'Fund Transfer') }}</h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal submit_form" action="/admin/fund_transfer/add_fund_transfer" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body row">
                        <input type="hidden" name='delivery_boy_id' id="delivery_boy_id">
                        <div class="form-group col-md-6">
                            <label for="name"
                                class="col-sm-2 col-form-label">{{ labels('admin_labels.name', 'Name') }}</label>

                            <input type="text" class="form-control" id="name" name="name" readonly>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="mobile"
                                class="col-sm-2 col-form-label">{{ labels('admin_labels.mobile', 'Mobile') }}</label>

                            <input type="text" maxlength="16" oninput="validateNumberInput(this)"
                                class="form-control" id="mobile" name="mobile" readonly>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="balance"
                                class="col-sm-2 col-form-label">{{ labels('admin_labels.balance', 'Balance') }}</label>

                            <input type="number" class="form-control" id="balance" min=1 name="balance" readonly>

                        </div>
                        <div class="form-group col-md-6">
                            <label for="transfer_amt"
                                class="col-sm-6 col-form-label">{{ labels('admin_labels.amount', 'Amount') }}</label>

                            <input type="number" min='1' class="form-control" id="transfer_amt"
                                name="transfer_amt">

                        </div>
                        <div class="form-group col-md-12">
                            <label for="message"
                                class="col-sm-2 col-form-label">{{ labels('admin_labels.message', 'Message') }}</label>

                            <input type="text" class="form-control" id="message" name="message">

                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit"
                                class="btn btn-primary">{{ labels('admin_labels.fund_transfer', 'Fund Transfer') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- ========================= modal for update delivery boy =========================== -->

    <div class="modal fade" id="edit_delivery_boy" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ labels('admin_labels.update_delivery_boy', 'Update Delivery Boy') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <div class="modal-body p-4">
                    <form class="form-horizontal form-submit-event submit_form" action="" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_id" value="">
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control" id="name" placeholder="" name="name"
                                    value="">

                            </div>
                            <div class="mb-3 col-md-6">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.mobile', 'Mobile') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" maxlength="16" oninput="validateNumberInput(this)"
                                    class="form-control" id="mobile" placeholder="" name="mobile" value="">

                            </div>

                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="" class="form-label">{{ labels('admin_labels.email', 'Email') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control" id="email" placeholder="" name="email"
                                    value="">

                            </div>
                            <div class="mb-3 col-md-6">
                                <label
                                    class="form-label">{{ labels('admin_labels.serviceable_zones', 'Serviceable Zones') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <select name="serviceable_zones[]"
                                    class="form-control edit_serviceable_zones search_zone w-100" multiple
                                    onload="multiselect()">
                                    <option value="">
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.address', 'Address') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <textarea type="text" class="form-control" placeholder="" name="address" value=""></textarea>

                            </div>
                        </div>
                        <div class="row">

                            <div class="mb-3 col-md-6">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.bonus_type', 'Bonus Type') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <select class="form-select form-select-md mb-3 bonus_type"
                                    aria-label=".form-select-md example" name="bonus_type">
                                    <option value="0">Select Type</option>
                                    <option value="fixed_amount_per_order_item">Fixed Amount Per Order</option>
                                    <option value="percentage_per_order_item">Percentage Per Order</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-6 edit_fixed_amount_per_order_item fixed_amount_per_order_item d-none">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.bonus_amount', 'Bonus Amount') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control edit_bonus_amount"
                                    placeholder="Enter amount to be given to the delivery boy on successful order delivery"
                                    placeholder="" name="bonus_amount" value="">

                            </div>
                            <div class="mb-3 col-md-6 edit_percentage_per_order_item percentage_per_order_item d-none">
                                <label
                                    class="form-label">{{ labels('admin_labels.bonus_percentage', 'Bonus Percentage') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control edit_bonus_percentage"
                                    placeholder="Enter Bonus(%) to be given to the delivery boy on successful order delivery"
                                    placeholder="" name="bonus_percentage" value="">

                            </div>

                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label
                                        for="image">{{ labels('admin_labels.driving_licence_front_image', 'Driving Licence Front Image') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <div class="col-sm-10">

                                        <div class="col-md-12  text-center form-group">
                                            <input type="file" class="filepond" name="front_licence_image"
                                                data-max-file-size="300MB" data-max-files="20" accept="image/*,.webp" />
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label
                                        for="image">{{ labels('admin_labels.driving_licence_back_image', 'Driving Licence Back Image') }}<span
                                            class='text-asterisks text-sm'>*</span></label>

                                    <div class="col-md-12  text-center form-group">
                                        <input type="file" class="filepond" name="back_licence_image"
                                            data-max-file-size="300MB" data-max-files="20" accept="image/*,.webp" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="" class="text-danger mt-3">*Only Choose When Update is
                                    necessary</label>
                                <div class="container-fluid row image-upload-section">
                                    <div
                                        class="col-md-8 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                        <div class='image-upload-div'><img class="img-fluid edit_front_licence_image mb-2"
                                                src="" alt="Not Found"></div>
                                        <input type="hidden" name="image" value=''>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="" class="text-danger mt-3">*Only Choose When Update is
                                    necessary</label>
                                <div class="container-fluid row image-upload-section">
                                    <div
                                        class="col-md-8 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                        <div class='image-upload-div'><img class="img-fluid edit_back_licence_image mb-2"
                                                src="" alt="Not Found"></div>
                                        <input type="hidden" name="image" value=''>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end ">
                            <button type="reset"
                                class="btn reset-btn mx-2">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary">{{ labels('admin_labels.update_delivery_boy', 'Update Delivery Boy') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    {{-- table --}}

    <section
        class="overview-data {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view delivery_boy') ? '' : 'd-none' }}">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-6">
                            <h4> {{ labels('admin_labels.manage_delivery_boys', 'Manage Delivery Boys') }}
                            </h4>
                        </div>
                        <div class="col-md-6 d-flex justify-content-end ">
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_delivery_boys_table"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_delivery_boys_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_delivery_boys_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boys_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boys_table','json')">JSON</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boys_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boys_table','excel')">Excel</button>
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
                        data-table-id="admin_delivery_boys_table"
                        data-delete-url="{{ route('delivery_boys.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                </div>
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="admin_delivery_boys_table" data-loading-template="loadingTemplate"
                                data-toggle="table" data-url="{{ route('delivery_boys.list') }}"
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
                                        <th data-field="username" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.name', 'Name') }}
                                        </th>
                                        <th data-field="email" data-sortable="false">
                                            {{ labels('admin_labels.email', 'Email') }}
                                        </th>
                                        <th data-field="mobile" data-sortable="false">
                                            {{ labels('admin_labels.mobile', 'Mobile') }}
                                        </th>
                                        <th data-field="address" data-sortable="false" data-visible='false'>
                                            {{ labels('admin_labels.address', 'Address') }}
                                        </th>
                                        <th data-field="bonus_type" data-sortable="false">
                                            {{ labels('admin_labels.bonus_type', 'Bonus Type') }}
                                        </th>
                                        <th data-field="bonus" data-sortable="false">
                                            {{ labels('admin_labels.bonus', 'Bonus') }}
                                        </th>
                                        <th data-field="balance" data-sortable="false">
                                            {{ labels('admin_labels.balance', 'Balance') }}
                                        </th>
                                        <th data-field="serviceable_zones" data-sortable="false" data-visible="true">
                                            {{ labels('admin_labels.serviceable_zones', 'Serviceable Zones') }}
                                        </th>
                                        <th data-field="front_licence_image" data-sortable="true" data-visible="false">
                                            {{ labels('admin_labels.driving_licence_front_image', 'Driving Licence Front Image') }}
                                        </th>
                                        <th data-field="back_licence_image" data-sortable="true" data-visible="false">
                                            {{ labels('admin_labels.driving_licence_back_image', 'Driving Licence Back Image') }}
                                        </th>
                                        <th data-field="status">
                                            {{ labels('admin_labels.status', 'Status') }}
                                        </th>
                                        <th data-field="action">
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
