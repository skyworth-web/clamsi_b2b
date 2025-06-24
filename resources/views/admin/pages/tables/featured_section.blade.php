@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.featured_section', 'Featured Section') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.featured_section', 'Featured Section')" :subtitle="labels(
        'admin_labels.showcase_top_picks_with_effortless_featured_item_management',
        'Showcase Top Picks with Effortless Featured Item Management',
    )" :breadcrumbs="[['label' => labels('admin_labels.featured_section', 'Featured Section')]]" />

    <form class="form-horizontal form-submit-event submit_form" action="{{ route('feature_section.store') }}" method="POST"
        id="" enctype="multipart/form-data">
        @csrf
        <div class="row">
            <div class="col-md-12 col-xxl-6">
                <div class="card">
                    <div class="card-body ">
                        <h5 class="mb-3">
                            {{ labels('admin_labels.manage_featured_section', 'Manage Featured Section') }}
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
                                    <label for="title" class="form-label">
                                        {{ labels('admin_labels.title', 'Title') }}<span
                                            class="text-asterisks text-sm">*</span>
                                    </label>
                                    <input type="text" placeholder="Best Deals" name="title" class="form-control"
                                        value="{{ old('title') }}">
                                </div>
                                <div class="col-md-12">
                                    <label for="short_description" class="control-label mb-2 mt-2">
                                        {{ labels('admin_labels.short_description', 'Short Description') }}
                                        <span class='text-asterisks text-sm'>*</span>
                                    </label>
                                    <input type="text" class="form-control" name="short_description"
                                        id="short_description" value="" placeholder="Short description">
                                </div>
                            </div>

                            @foreach ($languages as $lang)
                                @if ($lang->code !== 'en')
                                    <div class="tab-pane fade" id="content-{{ $lang->code }}" role="tabpanel"
                                        aria-labelledby="tab-{{ $lang->code }}">
                                        <div class="mb-3">
                                            <label for="translated_title_{{ $lang->code }}" class="form-label">
                                                {{ labels('admin_labels.title', 'Title') }} ({{ $lang->language }})
                                            </label>
                                            <input type="text" class="form-control"
                                                id="translated_title_{{ $lang->code }}"
                                                name="translated_featured_section_title[{{ $lang->code }}]"
                                                value="">
                                        </div>
                                        <div class="mb-3">
                                            <label for="translated_short_description_{{ $lang->code }}"
                                                class="form-label">
                                                {{ labels('admin_labels.short_description', 'Short Description') }}
                                                ({{ $lang->language }})
                                            </label>
                                            <input type="text" class="form-control"
                                                id="translated_short_description_{{ $lang->code }}"
                                                name="translated_featured_section_description[{{ $lang->code }}]"
                                                value="">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="form-group row select-categories">
                            <label for="categories"
                                class="control-label mb-2 mt-2">{{ labels('admin_labels.categories', 'Categories') }}</label>
                            <div class="col-md-12">
                                <select name="categories[]" id="category_sliders_category"
                                    class="category_sliders_category w-100" multiple
                                    data-placeholder=" Type to search and select categories" onload="multiselect()">

                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            @php
                                $product_type = [
                                    'new_added_products',
                                    'products_on_sale',
                                    'top_rated_products',
                                    'most_selling_products',
                                    'custom_products',
                                    'digital_product',
                                    'custom_combo_products',
                                ];
                            @endphp

                            <label for="product_type" class="control-label mb-2 mt-2">
                                {{ labels('admin_labels.product_type', 'Product Types') }}
                                <span class='text-danger text-sm'>* </span>
                            </label>

                            <div class="col-md-12">
                                <select name="product_type" class="form-control product_type form-select">
                                    <option value=" ">
                                        {{ labels('admin_labels.select_type', 'Select Type') }}
                                    </option>
                                    @foreach ($product_type as $row)
                                        <option value="{{ $row }}">{{ ucwords(str_replace('_', ' ', $row)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <!-- for custom product -->

                        <div class="form-group row custom_products d-none">
                            <label for="product_ids"
                                class="control-label mb-2 mt-2">{{ labels('admin_labels.products', 'Products') }}
                                <span class='text-danger text-sm'>
                                    * </span></label>
                            <div class="col-md-12 search_admin_product_parent">
                                <select name="product_ids[]" class="search_admin_product w-100" multiple
                                    data-placeholder=" Type to search and select products" onload="multiselect()">

                                </select>
                            </div>
                        </div>
                        <!-- for custom combo product -->

                        <div class="form-group row custom_combo_products d-none">
                            <label for="product_ids"
                                class="control-label mb-2 mt-2">{{ labels('admin_labels.combo_products', 'Combo Products') }}
                                <span class='text-danger text-sm'>
                                    * </span></label>
                            <div class="col-md-12">
                                <select name="product_ids[]" class="search_admin_combo_product w-100" multiple
                                    data-placeholder=" Type to search and select products" onload="multiselect()">

                                </select>
                            </div>
                        </div>

                        <!-- for digital product -->
                        <div class="form-group row digital_products d-none">
                            <label for="digital_product_ids"
                                class="control-label mb-2 mt-2">{{ labels('admin_labels.products', 'Products') }}
                                *</label>
                            <div class="col-md-12">
                                <select name="digital_product_ids[]" class="search_admin_digital_product w-100" multiple
                                    data-placeholder=" Type to search and select products" onload="multiselect()">

                                </select>
                            </div>
                        </div>

                        <div class="form-group col-md-12 mt-2 mb-4">
                            <label for="image"
                                class="mb-2">{{ labels('admin_labels.banner_image', 'Banner Image') }}
                                <span class='text-asterisks text-sm'>*</span>
                            </label>

                            <div class="col-md-12">
                                <div class="row form-group">
                                    <div class="col-md-6 file_upload_box border file_upload_border mt-2">
                                        <div class="mt-2">
                                            <div class="col-md-12  text-center">
                                                <div>
                                                    <a class="media_link" data-input="banner_image" data-isremovable="0"
                                                        data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                        data-bs-target="#media-upload-modal" value="Upload Photo">
                                                        <h4><i class='bx bx-upload'></i> Upload
                                                    </a></h4>
                                                    <p class="image_recommendation">Recommended Size: 1648 x 610 pixels</p>
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
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xxl-6 mt-md-2 mt-sm-2">
                <div class="card">
                    <div class="card-body ">
                        <h5 class="mb-3">
                            {{ labels('admin_labels.select_style', 'Select Style') }}
                        </h5>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="feature_section_color_picker"
                                        class="d-block">{{ labels('admin_labels.choose_background_color', 'Choose Background Color') }}</label>
                                    <input type="color" value="#e0ffee" id="feature_section_color_picker"
                                        onchange="updateColorCode('feature_section_color_picker')"
                                        class="form-control d-block mx-auto">
                                </div>
                            </div>
                            <div class="col-md-6 mt-4 mb-2">
                                <div class="form-group">
                                    <input type="text" id="feature_section_color_picker_code" name="background_color"
                                        class="form-control d-block mx-auto"
                                        oninput="updateColorPicker('feature_section_color_picker', this.value)">
                                </div>
                            </div>

                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-4">
                                    <label class="form-label" for="category_style_select">
                                        {{ labels('admin_labels.style', 'Select Section Header Style') }}
                                    </label>
                                    <select class="feature_section_header_style form-control form-select"
                                        name="header_style">
                                        <option value="header_style_1">Style 1</option>
                                        <option value="header_style_2">Style 2</option>
                                        <option value="header_style_3">Style 3</option>
                                    </select>
                                </div>

                                <div class="feature_section_header_style_images feature_section_header_style_box">
                                    <img src="{{ getimageurl('system_images/feature_section_heading_style_1.png') }}"
                                        class="header_style_1" alt="Feature Section Heading Style 1">
                                    <img src="{{ getimageurl('system_images/feature_section_heading_style_2.png') }}"
                                        class="header_style_2" alt="Feature Section Heading Style 2">
                                    <img src="{{ getimageurl('system_images/feature_section_heading_style_3.png') }}"
                                        class="header_style_3" alt="Feature Section Heading Style 3">

                                </div>
                            </div>

                            <div class="col-md-6 feature_section_style_main">
                                <div class="mb-4">
                                    <label class="form-label" for="category_style_select">
                                        {{ labels('admin_labels.style', 'Select Section Style') }}
                                    </label>
                                    <select class="feature_section_style form-control form-select" name="style">
                                        <option value="style_1">Style 1</option>
                                        <option value="style_2">Style 2</option>
                                        <option value="style_3">Style 3</option>
                                    </select>
                                </div>

                                <div class="feature_section_style_images feature_section_style_box">
                                    <img src="{{ getimageurl('system_images/featured_section_style_1.png') }}"
                                        class="style_1" alt="Featured Section Style 1">
                                    <img src="{{ getimageurl('system_images/featured_section_style_2.png') }}"
                                        class="style_2" alt="Featured Section Style 2">
                                    <img src="{{ getimageurl('system_images/featured_section_style_3.png') }}"
                                        class="style_3" alt="Featured Section Style 3">

                                </div>
                            </div>
                        </div>


                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.add_featured_section', 'Add Featured Section') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div
        class="col-md-12 mt-4 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view featured_section') ? '' : 'd-none' }}">
        <section class="overview-data">
            <div class="card content-area p-4 ">
                <div class="row align-items-center d-flex heading mb-5">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12 col-lg-6">
                                <h4> {{ labels('admin_labels.manage_section', 'Manage Section') }}
                                </h4>
                            </div>
                            <div class="col-md-12 col-lg-6 d-flex justify-content-end mt-md-2 mt-lg-0">
                                <div class="input-group me-2 search-input-grp ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="admin_featured_section_table"
                                        class="form-control searchInput" placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="admin_featured_section_table"
                                    dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                    orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh"data-table="admin_featured_section_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_featured_section_table','csv')">CSV</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_featured_section_table','json')">JSON</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_featured_section_table','sql')">SQL</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_featured_section_table','excel')">Excel</button>
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
                            data-table-id="admin_featured_section_table"
                            data-delete-url="{{ route('featured_sections.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                    </div>
                    <div class="col-md-12">
                        <div class="pt-0">
                            <div class="table-responsive">
                                <table class='table' id="admin_featured_section_table" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('feature_section.list') }}" data-click-to-select="true"
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
                                            </th>
                                            <th data-field="title" data-sortable="false" data-disabled="1">
                                                {{ labels('admin_labels.title', 'Title') }}
                                            </th>
                                            <th class="d-flex justify-content-center" data-field="banner_image"
                                                data-sortable="false">
                                                {{ labels('admin_labels.banner_image', 'Banner Image') }}
                                            </th>
                                            <th data-field="short_description" data-sortable="false">
                                                {{ labels('admin_labels.short_description', 'Short Description') }}
                                            </th>
                                            <th data-field="style" data-sortable="false">
                                                {{ labels('admin_labels.style', 'Style') }}
                                            </th>
                                            <th data-field="categories" data-sortable="false">
                                                {{ labels('admin_labels.categories', 'Categories') }}
                                            </th>
                                            <th data-field="product_ids" data-sortable="false">
                                                {{ labels('admin_labels.product_id', 'Product ID') }}
                                            </th>
                                            <th data-field="product_type" data-sortable="false">
                                                {{ labels('admin_labels.product_type', 'Product Type') }}
                                            </th>
                                            <th data-field="date">
                                                {{ labels('admin_labels.date', 'Date') }}
                                            </th>
                                            <th data-field="operate" data-sortable='false'>
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
