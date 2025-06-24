@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.sliders', 'Sliders') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.sliders', 'Sliders')" :subtitle="labels(
        'admin_labels.enhance_visual_appeal_with_effortless_slider_integration',
        'Enhance Visual Appeal with Effortless Slider Integration',
    )" :breadcrumbs="[['label' => labels('admin_labels.sliders', 'Sliders')]]" />

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <form class="form-horizontal form-submit-event submit_form" action="{{ route('sliders.store') }}"
                    method="POST" id="" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <h5 class="mb-4">
                            {{ labels('admin_labels.add_slider', 'Manage Sliders') }}
                        </h5>
                        <div class="form-group">
                            <label for="slider_type" class="mb-2">{{ labels('admin_labels.type', 'Type') }}
                                <span class='text-asterisks text-sm'>*</span> </label>
                            <select name="type" id="slider_type" class="form-control form-select type_event_trigger mb-2"
                                required="">
                                <option value=" ">
                                    {{ labels('admin_labels.select_type', 'Select Type') }}
                                </option>
                                <option value="default">Default</option>
                                <option value="categories">Category</option>
                                <option value="products">Product</option>
                                <option value="combo_products">Combo Product</option>
                                <option value="slider_url">Slider URL</option>
                            </select>
                        </div>
                        <div id="type_add_html">
                            <div class="form-group slider-categories d-none">
                                <label for="category_id" class="mb-2">
                                    {{ labels('admin_labels.categories', 'Categories') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="category_id" class="form-control form-select">
                                    {!! renderCategories($categories, 0, 0, null) !!}
                                </select>
                            </div>
                            <div class="form-group slider-url d-none">
                                <label for="slider_url" class="mb-2">
                                    {{ labels('admin_labels.link', 'Link') }} <span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control" placeholder="https://example.com" name="link"
                                    value="">
                            </div>
                            <div class="form-group row slider-products d-none">
                                <label for="product_id" class="mb-2">{{ labels('admin_labels.products', 'Products') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <div class="col-md-12 search_admin_product_parent">
                                    <select name="product_id" class="search_admin_product w-100"
                                        data-placeholder=" Type to search and select products" onload="multiselect()">
                                        <option value="" selected></option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group row slider-combo-products d-none">
                                <label for="product_id"
                                    class="mb-2">{{ labels('admin_labels.combo_products', 'Combo Products') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <div class="col-md-12">
                                    <select name="combo_product_id" class="search_admin_combo_product w-100"
                                        data-placeholder=" Type to search and select products" onload="multiselect()">
                                        <option value="" selected></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group col-md-12 mt-2 mb-4">
                            <label for="image" class="mb-2">{{ labels('admin_labels.image', 'Slider Image') }}
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

                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.add_slider', 'Add Sliders') }}</button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- table --}}
        <div
            class="col-md-8 mt-md-0 mt-sm-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view slider_images') ? '' : 'd-none' }}">
            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4> {{ labels('admin_labels.manage_sliders', 'Manage Sliders') }}
                                    </h4>
                                </div>
                                <div class="col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                                    <div class="input-group me-2 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="admin_slider_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span
                                            class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="admin_slider_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh"data-table="admin_slider_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button"
                                            id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_slider_table','csv')">CSV</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_slider_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_slider_table','sql')">SQL</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_slider_table','excel')">Excel</button>
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
                                data-table-id="admin_slider_table"
                                data-delete-url="{{ route('sliders.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                        </div>
                        <div class="col-md-12">
                            <div class="pt-0">
                                <div class="table-responsive">
                                    <table class='table' id="admin_slider_table" data-toggle="table"
                                        data-loading-template="loadingTemplate" data-url="{{ route('sliders.list') }}"
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
                                                <th data-field="type" data-sortable="false">
                                                    {{ labels('admin_labels.type', 'Type') }}
                                                </th>

                                                <th class="d-flex justify-content-center" data-field="image"
                                                    data-sortable="false" class="col-md-12">
                                                    {{ labels('admin_labels.image', 'Image') }}
                                                </th>
                                                <th data-field="link" data-sortable="false">
                                                    {{ labels('admin_labels.link', 'Link') }}
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
@endsection
