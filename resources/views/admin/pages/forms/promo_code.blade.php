@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.promo_codes', 'PromoCode') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.promo_codes', 'PromoCode')" :subtitle="labels(
        'admin_labels.boost_sales_with_seamless_and_strategic_promocode_management',
        'Boost Sales with Seamless and Strategic Promocode Management',
    )" :breadcrumbs="[['label' => labels('admin_labels.promo_codes', 'PromoCode')]]" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="form-horizontal form-submit-event submit_form" action="{{ route('promo_codes.store') }}"
                    method="POST">
                    @csrf
                    <div class="card-body">
                        <h5 class="mb-3">
                            {{ labels('admin_labels.promo_codes', 'PromoCode') }}
                        </h5>
                        <div class="row mt-2">
                            <ul class="nav nav-tabs mt-4" id="brandTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="language-nav-link nav-link active" id="tab-en" data-bs-toggle="tab"
                                        data-bs-target="#content-en" type="button" role="tab"
                                        aria-controls="content-en" aria-selected="true">
                                        {{ labels('admin_labels.default', 'Default') }}
                                    </button>
                                </li>
                                {!! generateLanguageTabsNav($languages) !!}
                            </ul>

                            <div class="tab-content mt-3" id="brandTabsContent">
                                <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                    aria-labelledby="tab-en">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">
                                            {{ labels('admin_labels.title', 'Title') }}<span
                                                class="text-asterisks text-sm">*</span>
                                        </label>
                                        <input type="text" placeholder="Title" name="title" class="form-control"
                                            value="{{ old('title') }}">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="description" class="control-label mb-2 mt-2">
                                            {{ labels('admin_labels.message', 'Message') }}
                                            <span class='text-asterisks text-sm'>*</span>
                                        </label>
                                        <input type="text" class="form-control" name="message" id="message"
                                            value="" placeholder="Message">
                                    </div>
                                </div>

                                @foreach ($languages as $lang)
                                    @if ($lang->code !== 'en')
                                        <div class="tab-pane fade" id="content-{{ $lang->code }}" role="tabpanel"
                                            aria-labelledby="tab-{{ $lang->code }}">
                                            <div class="mb-3">
                                                <label for="translated_promocode_title_{{ $lang->code }}"
                                                    class="form-label">
                                                    {{ labels('admin_labels.title', 'Title') }} ({{ $lang->language }})
                                                </label>
                                                <input type="text" class="form-control"
                                                    id="translated_promocode_title_{{ $lang->code }}"
                                                    name="translated_promocode_title[{{ $lang->code }}]" value="">
                                            </div>
                                            <div class="mb-3">
                                                <label for="translated_promocode_message_{{ $lang->code }}"
                                                    class="form-label">
                                                    {{ labels('admin_labels.message', 'Message') }}
                                                    ({{ $lang->language }})
                                                </label>
                                                <input type="text" class="form-control"
                                                    id="translated_promocode_message_{{ $lang->code }}"
                                                    name="translated_promocode_message[{{ $lang->code }}]"
                                                    value="">
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.promo_codes', 'PromoCode') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control" name="promo_code" value="">

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.start_date', 'Start Date') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="date" class="form-control" name="start_date" id="start_date" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2" for="">{{ labels('admin_labels.end_date', 'End Date') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="date" class="form-control" name="end_date" id="end_date" value="">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.number_of_users', 'No.Of Users') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="number" min=1 class="form-control" name="no_of_users" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.minimum_order_amount', 'Minimum Order Amount') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="number" min=1 class="form-control" name="minimum_order_amount"
                                    value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.discount_type', 'Discount Type') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="discount_type" class="form-control form-select discount_type">
                                    <option value="">Select</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="amount">
                                        {{ labels('admin_labels.amount', 'Amount') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2" for="">{{ labels('admin_labels.discount', 'Discount') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="number" class="form-control discount" min=1 name="discount" id="discount"
                                    value="">
                                <div class="error"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.max_discount_amount', 'Max Discount Amount') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="number" min=1 class="form-control" min=1 name="max_discount_amount"
                                    id="max_discount_amount" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.repeat_usage', 'Repeat Usage') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="repeat_usage" id="repeat_usage" class="form-control form-select">
                                    <option value="">
                                        {{ labels('admin_labels.select', 'Select') }}
                                    </option>
                                    <option value="1">Allowed</option>
                                    <option value="0">Not Allowed</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-none" id="repeat_usage_html">
                                <label class="mb-2 mt-2" for="">
                                    {{ labels('admin_labels.number_of_repeat_usage', 'Number Of Repeat Usage') }}
                                </label>
                                <input type="number" class="form-control" name="no_of_repeat_usage" min=1
                                    id="no_of_repeat_usage" value="">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2" for="">{{ labels('admin_labels.status', 'Status') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="status" id="status" class="form-control form-select">
                                    <option value="">
                                        {{ labels('admin_labels.select', 'Select') }}
                                    </option>
                                    <option value="1">Active</option>
                                    <option value="0">Deactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-md-6 mb-4">
                                <label class="mb-2 mt-2" for="image"
                                    class="mb-2">{{ labels('admin_labels.image', 'Image') }}
                                    <span class='text-asterisks text-sm'>*</span>
                                </label>
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <div class="col-md-12 col-lg-6 file_upload_box border file_upload_border mt-2">
                                            <div class="mt-2">
                                                <div class="col-md-12  text-center">
                                                    <div>
                                                        <a class="media_link" data-input="image" data-isremovable="0"
                                                            data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                            data-bs-target="#media-upload-modal" value="Upload Photo">
                                                            <h4><i class='bx bx-upload'></i> Upload
                                                        </a></h4>
                                                        <p class="image_recommendation">Recommended Size: 147 x 60 pixels
                                                        </p>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 container-fluid row mt-3 image-upload-section">
                                            <div
                                                class="col-md-12 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-sm-12 col-lg-6">
                                        <label class="mb-2 mt-2"
                                            for="is_cashback">{{ labels('admin_labels.is_cashback', 'Is Cashback?') }}</label>
                                    </div>
                                    <div class="col-sm-12 col-md-6 d-flex justify-content-end">
                                        <div class="form-check form-switch mx-8">
                                            <input class="form-check-input form-switch" type="checkbox" id=""
                                                name="is_cashback">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="row align-items-center">
                                    <div class="col-sm-12 col-lg-6">
                                        <label class="mb-2 mt-2"
                                            for="is_cashback">{{ labels('admin_labels.list_promocode', 'List PromoCode?') }}</label>
                                    </div>
                                    <div class="col-sm-12 col-md-6 d-flex justify-content-end">
                                        <div class="form-check form-switch mx-8">
                                            <input class="form-check-input form-switch" type="checkbox" id=""
                                                name="list_promocode">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="reset"
                                    class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.add_promocode', 'Add PromoCode') }}</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            {{-- table --}}
            <div
                class="col-md-12 mt-4 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view promo_code') ? '' : 'd-none' }}">
                <section class="overview-data">
                    <div class="card content-area p-4 ">
                        <div class="row align-items-center d-flex heading mb-5">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4> {{ labels('admin_labels.manage_promocodes', 'Manage PromoCode') }}
                                        </h4>
                                    </div>
                                    <div class="col-sm-12 d-flex justify-content-end ">
                                        <div class="input-group me-2 search-input-grp ">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" data-table="admin_promocode_table"
                                                class="form-control searchInput" placeholder="Search...">
                                            <span
                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                        </div>
                                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                            data-bs-target="#columnFilterOffcanvas" data-table="admin_promocode_table"
                                            dateFilter='false' StatusFilter='true'><i class='bx bx-filter-alt'></i></a>
                                        <a class="btn me-2" id="tableRefresh" data-table="admin_promocode_table"><i
                                                class='bx bx-refresh'></i></a>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_promocode_table','csv')">CSV</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_promocode_table','json')">JSON</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_promocode_table','sql')">SQL</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_promocode_table','excel')">Excel</button>
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
                                    data-table-id="admin_promocode_table"
                                    data-delete-url="{{ route('promo_codes.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                            </div>
                            <div class="col-md-12">
                                <div class="pt-0">
                                    <div class="table-responsive">
                                        <table class='table' id="admin_promocode_table" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="{{ route('promo_codes.list') }}" data-click-to-select="true"
                                            data-side-pagination="server" data-pagination="true"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                            data-show-columns="false" data-show-refresh="false"
                                            data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                            data-maintain-selected="true" data-export-types='["txt","excel"]'
                                            data-query-params="PromoqueryParams">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true" data-field="delete-checkbox">
                                                        <input name="select_all" type="checkbox">
                                                    </th>
                                                    <th data-field="id" data-sortable="true">
                                                        {{ labels('admin_labels.id', 'ID') }}
                                                    <th data-field="title" data-sortable="true">
                                                        {{ labels('admin_labels.title', 'Title') }}
                                                    <th data-field="promo_code" data-disabled="1" data-sortable="false">
                                                        {{ labels('admin_labels.promo_codes', 'PromoCode') }}
                                                    </th>
                                                    <th class="d-flex justify-content-center" data-field="image"
                                                        data-sortable="false">
                                                        {{ labels('admin_labels.image', 'Image') }}
                                                    </th>
                                                    <th data-field="message" data-sortable="false">
                                                        {{ labels('admin_labels.message', 'Message') }}
                                                    </th>
                                                    <th data-field="start_date" data-sortable="true">
                                                        {{ labels('admin_labels.start_date', 'Start Date') }}
                                                    </th>
                                                    <th data-field="end_date" data-sortable="true">
                                                        {{ labels('admin_labels.end_date', 'End Date') }}
                                                    </th>
                                                    <th data-field="no_of_users" data-sortable="false"
                                                        data-visible='false'>
                                                        {{ labels('admin_labels.number_of_users', 'No.Of Users') }}
                                                    </th>
                                                    <th data-field="min_order_amt" data-sortable="false"
                                                        data-visible='false'>
                                                        {{ labels('admin_labels.minimum_order_amount', 'Minimum Order Amount') }}
                                                    </th>
                                                    <th data-field="discount" data-sortable="false">
                                                        {{ labels('admin_labels.discount', 'Discount') }}
                                                    </th>
                                                    <th data-field="discount_type" data-sortable="false">
                                                        {{ labels('admin_labels.discount_type', 'Discount Type') }}
                                                    </th>
                                                    <th data-field="max_discount_amount" data-sortable="false"
                                                        data-visible='false'>
                                                        {{ labels('admin_labels.max_discount_amount', 'Max Discount Amount') }}
                                                    </th>
                                                    <th data-field="repeat_usage" data-sortable="false"
                                                        data-visible='false'>
                                                        {{ labels('admin_labels.repeat_usage', 'Repeat Usage') }}
                                                    </th>
                                                    <th data-field="no_of_repeat_usage" data-sortable="false"
                                                        data-visible='false'>
                                                        {{ labels('admin_labels.number_of_repeat_usage', 'Number Of Repeat Usage') }}
                                                    </th>
                                                    <th data-field="status" data-sortable="false">
                                                        {{ labels('admin_labels.status', 'Status') }}
                                                    </th>
                                                    <th data-field="is_cashback" data-sortable="false">
                                                        {{ labels('admin_labels.is_cashback', 'Is Cashback') }}
                                                    </th>
                                                    <th data-field="list_promocode" data-sortable="false">
                                                        {{ labels('admin_labels.list_promocode', 'View PromoCode?') }}
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
        </div>
    </div>
@endsection
