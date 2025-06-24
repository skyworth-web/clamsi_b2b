@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.categories_sliders', 'Categories Sliders') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.categories_sliders', 'Categories Sliders')" :subtitle="labels(
        'admin_labels.dynamic_category_display_with_seamless_slider_management',
        'Dynamic Category Display with Seamless Slider Management',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.categories', 'Categories'), 'url' => route('categories.index')],
        ['label' => labels('admin_labels.categories_sliders', 'Categories Sliders')],
    ]" />

    <!-- Basic Layout -->
    <div class="col-md-12">
        <div class="row">
            <div class="col-xxl-6 col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3">
                            {{ labels('admin_labels.add_category_slider', 'Add Category Slider') }}
                        </h5>
                        <div class="row">
                            <div class="form-group">
                                <form id="" action="{{ route('category_sliders.store') }}" class="submit_form"
                                    enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="language-nav-link nav-link active" id="tab-en"
                                                data-bs-toggle="tab" data-bs-target="#content-en" type="button"
                                                role="tab" aria-controls="content-en" aria-selected="true">
                                                {{ labels('admin_labels.default', 'Default') }}
                                            </button>
                                        </li>
                                        {!! generateLanguageTabsNav($languages) !!}
                                    </ul>

                                    <div class="tab-content mt-3" id="brandTabsContent">
                                        <!-- Default 'en' tab content -->
                                        <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                            aria-labelledby="tab-en">
                                            <div class="mb-3">
                                                <label for="brand_name"
                                                    class="form-label">{{ labels('admin_labels.title', 'Title') }}<span
                                                        class="text-asterisks text-sm">*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="Popular Categories" name="title"
                                                    value="{{ old('title') }}">
                                            </div>
                                        </div>

                                        {!! generateLanguageTabs($languages, 'admin_labels.title', 'Title', 'translated_category_slider_title') !!}
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="basic-default-fullname">{{ labels('admin_labels.select_category', 'Select Category') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <select name="category_ids[]" required id="category_sliders_category"
                                            class="category_sliders_category w-100" multiple
                                            data-placeholder=" Type to search and select categories" onload="multiselect()">
                                        </select>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="category_slider_color_picker" class="d-block mb-2">Choose
                                                    Background Color<span class='text-asterisks text-sm'>*</span></label>
                                                <input type="color" value="#e0ffee" id="category_slider_color_picker"
                                                    class="form-control d-block mx-auto"
                                                    onchange="updateColorCode('category_slider_color_picker')">
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-4 mb-2">
                                            <div class="form-group">
                                                <input type="text" id="category_slider_color_picker_code"
                                                    name="background_color" class="form-control d-block mx-auto"
                                                    oninput="updateColorPicker('category_slider_color_picker', this.value)">
                                            </div>
                                        </div>

                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="category_style_select">
                                            {{ labels('admin_labels.select_style', 'Select Slider Style') }}
                                        </label>
                                        <select class="category_slider_style form-select form-control"
                                            name="category_slider_style">

                                            <option value="style_1">Style 1</option>
                                            <option value="style_2">Style 2</option>
                                        </select>
                                    </div>

                                    <div class="category_slider_style_images category_card_style_box">
                                        <img src="{{ getimageurl('system_images/category_slider_style_1.png') }}"
                                            alt="" class="style_1" />
                                        <img src="{{ getimageurl('system_images/category_slider_style_2.png') }}"
                                            alt="" class="style_2" />

                                    </div>

                                    <div class="row">
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
                                                                    data-isremovable="0"
                                                                    data-is-multiple-uploads-allowed="0"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#media-upload-modal"
                                                                    value="Upload Photo">
                                                                    <h4><i class='bx bx-upload'></i> Upload
                                                                </a></h4>
                                                                <p class="image_recommendation">Recommended Size: 180 x 180
                                                                    pixels</p>
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
                                            class="btn btn-primary submit_button">{{ labels('admin_labels.add_slider', 'Add Slider') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="col-xxl-6 col-lg-12 mt-xxl-0 mt-lg-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view category_sliders') ? '' : 'd-none' }}">
                <div class="card content-area p-4">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-lg-6">
                                    <h4>{{ labels('admin_labels.manage_sliders', 'Manage Sliders') }}
                                    </h4>
                                </div>

                                <div class="col-md-12 col-lg-6 d-flex justify-content-end mt-md-0 mt-sm-2">
                                    <div class="input-group me-3 search-input-grp">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="admin_category_slider_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">Search</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" StatusFilter='true'
                                        data-table="admin_category_slider_table"><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="admin_category_slider_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button"
                                            id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_category_slider_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_category_slider_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_category_slider_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_category_slider_table','excel')">Excel</button>
                                            </li>
                                        </ul>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="pt-0">
                                <div class="card-body" id="">
                                    <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                                        data-table-id="admin_category_slider_table"
                                        data-delete-url="{{ route('categories_sliders.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                                    <table class='table-responsive' id='admin_category_slider_table' data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('category_sliders.list') }}" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel","csv"]'
                                        data-export-options='{
                                "fileName": "category-list",
                                "ignoreColumn": ["state"]
                                }'
                                        data-query-params="category_query_params">
                                        <thead>
                                            <tr>
                                                <th data-checkbox="true" data-field="delete-checkbox">
                                                    <input name="select_all" type="checkbox">
                                                </th>
                                                <th data-field="id" data-sortable="true" data-visible='true'>
                                                    {{ labels('admin_labels.id', 'ID') }}
                                                </th>
                                                </th>
                                                <th data-field="title" data-disabled="1" data-sortable="false">
                                                    {{ labels('admin_labels.title', 'Title') }}
                                                </th>
                                                <th data-field="categories" data-disabled="1" data-sortable="false">
                                                    {{ labels('admin_labels.categories', 'Categories') }}
                                                </th>
                                                <th data-field="status" data-sortable="false">
                                                    {{ labels('admin_labels.status', 'Status') }}
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
            </div>
        </div>
    </div>
@endsection
