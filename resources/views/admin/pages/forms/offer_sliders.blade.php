@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.offer_slider', 'Offer Slider') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.offer_slider', 'Offer Slider')" :subtitle="labels(
        'admin_labels.captivate_audiences_with_eye_catching_deal_showcases',
        'Captivate Audiences with Eye-Catching Deal Showcases',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.offers', 'Offers')],
        ['label' => labels('admin_labels.offer_slider', 'Offer Slider')],
    ]" />

    <!-- Basic Layout -->
    <div>
        <div>
            <form id="" action="{{ route('offer_sliders.store') }}" class="submit_form" enctype="multipart/form-data"
                method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12 col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group">
                                        <div class="row">
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
                                                <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                                    aria-labelledby="tab-en">
                                                    <div class="mb-3">
                                                        <label for="title"
                                                            class="form-label">{{ labels('admin_labels.title', 'Title') }}<span
                                                                class="text-asterisks text-sm">*</span></label>
                                                        <input type="text" placeholder="Best Deals" name="title"
                                                            class="form-control" value="{{ old('title') }}">
                                                    </div>
                                                </div>

                                                {!! generateLanguageTabs($languages, 'admin_labels.title', 'Title', 'translated_offer_slider_title') !!}
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.select_offer', 'Select Offer') }}</label>
                                            <select name="offer_ids[]" required id="offer_sliders_offer"
                                                class="offer_sliders_offer w-100" multiple
                                                data-placeholder=" Type to search and select offers" onload="multiselect()">


                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <label
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
                                                                <p class="image_recommendation">Recommended Size: 131 x 131
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
                                            class="btn btn-primary submit_button">{{ labels('admin_labels.add_offer_slider', 'Add Offer Slider') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- table --}}
        <div
            class="col-md-12 mt-4 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view offer_slider') ? '' : 'd-none' }}">
            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-lg-6">
                                    <h4> {{ labels('admin_labels.manage_offer_sliders', 'Manage Offer Sliders') }}
                                    </h4>
                                </div>
                                <div class="col-md-12 col-lg-6 d-flex justify-content-end ">
                                    <div class="input-group me-2 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="admin_offer_slider_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="admin_offer_slider_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        StatusFilter='true' orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh"data-table="admin_offer_slider_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button"
                                            id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_offer_slider_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_offer_slider_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_offer_slider_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_offer_slider_table','excel')">Excel</button>
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
                                data-table-id="admin_offer_slider_table"
                                data-delete-url="{{ route('offer_sliders.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                        </div>
                        <div class="col-md-12">
                            <div class="pt-0">
                                <div class="table-responsive">
                                    <table class='table' id="admin_offer_slider_table" data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('admin.offer_sliders.list') }}" data-click-to-select="true"
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
                                                <th data-field="id" data-sortable="true" data-visible='true'>
                                                    {{ labels('admin_labels.id', 'ID') }}
                                                <th data-field="title" data-sortable="false" data-disabled="1">
                                                    {{ labels('admin_labels.title', 'Title') }}
                                                </th>
                                                <th data-field="offer_ids" data-sortable="false">
                                                    {{ labels('admin_labels.offers', 'Offers') }}
                                                    </li>
                                                </th>
                                                <th data-field="banner" data-sortable="false"
                                                    class="d-flex justify-content-center">
                                                    {{ labels('admin_labels.banner_image', 'Banner Image') }}
                                                    </li>
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
            </section>
        </div>
    </div>
@endsection
