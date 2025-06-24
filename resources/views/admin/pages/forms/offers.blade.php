@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.offers', 'Offers') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.offers', 'Offers')" :subtitle="labels(
        'admin_labels.boost_sales_with_captivating_and_profitable_promotions',
        'Boost Sales with Captivating and Profitable Promotions',
    )" :breadcrumbs="[['label' => labels('admin_labels.offers', 'Offers')]]" />

    <div>
        <form class="form-horizontal form-submit-event submit_form" action="{{ route('offers.store') }}" method="POST"
            id="" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12 col-xxl-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.add_offer', 'Add Offer') }}
                            </h5>
                            <ul class="nav nav-tabs" id="brandTabs" role="tablist">
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
                                        <label for="title"
                                            class="form-label">{{ labels('admin_labels.title', 'Title') }}<span
                                                class="text-asterisks text-sm">*</span></label>
                                        <input type="text" placeholder="Best Deals" name="title" class="form-control"
                                            value="{{ old('title') }}">
                                    </div>
                                </div>

                                {!! generateLanguageTabs($languages, 'admin_labels.title', 'Title', 'translated_offer_title') !!}
                            </div>
                            <div class="form-group">
                                <label for="offer_type" class="mb-2">{{ labels('admin_labels.type', 'Type') }}
                                    <span class='text-asterisks text-sm'>*</span>
                                </label>
                                <select name="type" id="offer_type" class="form-select form-control type_event_trigger"
                                    required="">
                                    <option value=" ">
                                        {{ labels('admin_labels.select_type', 'Select Type') }}
                                    </option>
                                    <option value="default">Default</option>
                                    <option value="categories">Category</option>
                                    <option value="all_products">All Products</option>
                                    <option value="all_combo_products">Combo Products</option>
                                    <option value="products">Specific Product</option>
                                    <option value="combo_products">Specific Combo Product</option>
                                    <option value="brand">Brand</option>
                                    <option value="offer_url">Offer URL</option>
                                </select>
                            </div>
                            <div id="type_add_html">
                                <div class="form-group slider-categories d-none mt-4">
                                    <label for="category_id" class="mb-2">
                                        {{ labels('admin_labels.categories', 'Categories') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <select name="category_id" class="form-control form-select">
                                        {!! renderCategories($categories, 0, 0, null) !!}
                                    </select>
                                </div>
                                <div class="form-group slider-brand d-none mt-4">
                                    <label for="category_id" class="mb-2">
                                        {{ labels('admin_labels.brands', 'Brands') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <select name="brand_id" class="form-control admin_brand_list">
                                        <option value="">
                                            {{ labels('admin_labels.select_brand', 'Select Brand') }}
                                        </option>

                                    </select>

                                </div>
                                <div class="form-group offer-url d-none mt-4">
                                    <label for="slider_url" class="mb-2">
                                        {{ labels('admin_labels.link', 'Link') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" placeholder="https://example.com"
                                        name="link" value="">
                                </div>
                                <div class="form-group row slider-products d-none mt-4">
                                    <label for="product_id"
                                        class="control-label mb-2">{{ labels('admin_labels.products', 'Products') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <div class="col-md-12 search_admin_product_parent">
                                        <select name="product_id" class="search_admin_product w-100"
                                            data-placeholder=" Type to search and select products" onload="multiselect()">
                                            <option value="" selected></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row slider-combo-products d-none mt-4">
                                    <label for="product_id"
                                        class="control-label mb-2">{{ labels('admin_labels.combo_products', 'Combo Products') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <div class="col-md-12">
                                        <select name="combo_product_id" class="search_admin_combo_product w-100"
                                            data-placeholder=" Type to search and select products" onload="multiselect()">
                                            <option value="" selected></option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row offer_discount d-none mt-4" id="min_max_section">
                                <div class="form-group col-md-6">
                                    <label
                                        for="">{{ labels('admin_labels.minimum_offer_discount', 'Minimum offer Discount(%)') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="number" class="form-control" name="min_discount" id="min_discount"
                                        min=1 max=100 value="">
                                </div>
                                <div class="form-group col-md-6">
                                    <label
                                        for="">{{ labels('admin_labels.maximum_offer_discount', 'Maximum offer Discount(%)') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="number" class="form-control" name="max_discount" max=100
                                        id="max_discount" min=1 max=100 value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 col-xxl-6 mt-md-2 mt-xxl-0">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.offer_images', 'Offer Images') }}
                            </h5>
                            <div class="form-group col-md-12 mb-4">
                                <label for="image" class="mb-2">{{ labels('admin_labels.image', 'Image') }}
                                    <span class='text-asterisks text-sm'>*</span>
                                </label>

                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <div class="col-md-6 file_upload_box border file_upload_border mt-2">
                                            <div class="mt-2">
                                                <div class="col-md-12  text-center">
                                                    <div>
                                                        <a class="media_link" data-input="image" data-isremovable="0"
                                                            data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                            data-bs-target="#media-upload-modal" value="Upload Photo">
                                                            <h4><i class='bx bx-upload'></i> Upload
                                                        </a></h4>
                                                        <p class="image_recommendation">Recommended Size: 1648 x 610 pixels
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
                            <div class="form-group col-md-12">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.banner_image', 'Banner Image') }}<span
                                        class="text-asterisks text-sm">*</span></label>
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <div class="col-md-6 file_upload_box border file_upload_border mt-2">
                                            <div class="mt-2">
                                                <div class="col-md-12  text-center">
                                                    <div>
                                                        <a class="media_link" data-input="banner_image"
                                                            data-isremovable="0" data-is-multiple-uploads-allowed="0"
                                                            data-bs-toggle="modal" data-bs-target="#media-upload-modal"
                                                            value="Upload Photo">
                                                            <h4><i class='bx bx-upload'></i> Upload
                                                        </a></h4>
                                                        <p class="image_recommendation">Recommended Size: 1648 x 610 pixels
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
                            <div class="d-flex justify-content-end">
                                <button type="reset"
                                    class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.add_offer', 'Add Offer') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    {{-- table --}}
    <div
        class="col-md-12 mt-4 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view offer_images') ? '' : 'd-none' }}">
        <section class="overview-data">
            <div class="card content-area p-4 ">
                <div class="row align-items-center d-flex heading mb-5">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12 col-lg-6">
                                <h4> {{ labels('admin_labels.manage_offers', 'Manage Offers') }}
                                </h4>
                            </div>
                            <div class="col-md-12 col-lg-6 d-flex justify-content-end ">
                                <div class="input-group me-2 search-input-grp ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="admin_offer_table" class="form-control searchInput"
                                        placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="admin_offer_table"
                                    dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                    orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh"data-table="admin_offer_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_offer_table','csv')">CSV</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_offer_table','json')">JSON</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_offer_table','sql')">SQL</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_offer_table','excel')">Excel</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                            data-table-id="admin_offer_table"
                            data-delete-url="{{ route('offers.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                    </div>
                    <div class="col-md-12">
                        <div class="pt-0">
                            <div class="table-responsive">
                                <table class='table' id="admin_offer_table" data-toggle="table"
                                    data-loading-template="loadingTemplate" data-url="{{ route('offers.list') }}"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
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
                                            <th data-field="title" data-disabled="1" data-sortable="false">
                                                {{ labels('admin_labels.title', 'Title') }}
                                            </th>
                                            <th data-field="type" data-sortable="false">
                                                {{ labels('admin_labels.type', 'Type') }}
                                            </th>
                                            <th class="d-flex justify-content-center" data-field="image"
                                                data-sortable="false" class="">
                                                {{ labels('admin_labels.image', 'Image') }}
                                            </th>
                                            <th data-field="banner_image" data-sortable="false">
                                                {{ labels('admin_labels.banner_image', 'Banner Image') }}
                                            </th>
                                            <th data-field="link" data-sortable="false">
                                                {{ labels('admin_labels.link', 'Link') }}
                                            </th>
                                            <th data-field="min_discount" data-sortable="false">
                                                {{ labels('admin_labels.min_discount', 'Minimum Discount') }}
                                            </th>
                                            <th data-field="max_discount" data-sortable="false">
                                                {{ labels('admin_labels.max_discount', 'Maximum Discount') }}
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
@endsection
