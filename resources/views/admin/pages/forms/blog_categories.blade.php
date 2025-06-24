@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.blog_categories', 'Blog Categories') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.blog_categories', 'Blog Categories')" :subtitle="labels(
        'admin_labels.organize_and_navigate_blog_content_with_ease',
        'Organize and Navigate Blog Content with Ease',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.blogs', 'Blogs')],
        ['label' => labels('admin_labels.categories', 'Categories')],
    ]" />

    <!-- Basic Layout -->
    <div class="col-md-12">
        <div class="row">
            <div class="col-xxl-4 col-lg-12">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3">
                            {{ labels('admin_labels.add_category', 'Add Blog Category') }}
                        </h5>
                        <div class="row">
                            <div class="form-group">
                                <form id="" action="{{ route('blog_category.store') }}" class="submit_form"
                                    enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="row">
                                        {{-- <div class="mb-3 col-md-12">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.name', 'Name') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="sport" name="name" value="{{ old('name') }}">
                                        </div> --}}
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
                                                    <label for="category_name"
                                                        class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                                            class="text-asterisks text-sm">*</span></label>
                                                    <input type="text" name="name" class="form-control"
                                                        placeholder="Name" value="">
                                                </div>
                                            </div>

                                            {!! generateLanguageTabs($languages, 'admin_labels.name', 'Name', 'translated_category_name') !!}
                                        </div>
                                        <div class="col-md-12">
                                            <label for=""
                                                class="form-label">{{ labels('admin_labels.image', 'Image') }}<span
                                                    class="text-asterisks text-sm">*</span></label>
                                            <div class="col-md-12">
                                                <div class="row form-group">
                                                    <div class="col-md-6 file_upload_box border file_upload_border mt-2">
                                                        <div class="mt-2">
                                                            <div class="col-md-12  text-center">
                                                                <div>
                                                                    <a class="media_link" data-input="image"
                                                                        data-isremovable="0"
                                                                        data-is-multiple-uploads-allowed="0"
                                                                        data-bs-toggle="modal"
                                                                        data-bs-target="#media-upload-modal"
                                                                        value="Upload Photo">
                                                                        <h4><i class='bx bx-upload'></i> Upload
                                                                    </a></h4>
                                                                    <p class="image_recommendation">Recommended Size: 180 x
                                                                        180 pixels</p>
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-12 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="reset"
                                            class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary submit_button">{{ labels('admin_labels.add_category', 'Add Category') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="col-lg-12 col-xxl-8 mt-md-2 mt-lg-0 mt-sm-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view blog_categories') ? '' : 'd-none' }}">
                <section class="overview-data">
                    <div class="card content-area p-4 ">
                        <div class="row align-items-center d-flex heading mb-5">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4> {{ labels('admin_labels.manage_categories', 'Manage Blog Category') }}
                                        </h4>
                                    </div>

                                    <div class="col-md-6 d-flex justify-content-end ">
                                        <div class="input-group me-2 search-input-grp ">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" data-table="admin_blog_category_table"
                                                class="form-control searchInput" placeholder="Search...">
                                            <span
                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                        </div>
                                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                            data-bs-target="#columnFilterOffcanvas" data-table="admin_blog_category_table"
                                            dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                            orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                        <a class="btn me-2" id="tableRefresh"data-table="admin_blog_category_table"><i
                                                class='bx bx-refresh'></i></a>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_blog_category_table','csv')">CSV</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_blog_category_table','json')">JSON</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_blog_category_table','sql')">SQL</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_blog_category_table','excel')">Excel</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view blog_categories'))
                            <div class="row">
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                                        data-table-id="admin_blog_category_table"
                                        data-delete-url="{{ route('blog_categories.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                                </div>
                                <div class="col-md-12">
                                    <div class="pt-0">
                                        <div class="table-responsive">
                                            <table class='table' id="admin_blog_category_table" data-toggle="table"
                                                data-loading-template="loadingTemplate"
                                                data-url="{{ route('blog_categories.list') }}"
                                                data-click-to-select="true" data-side-pagination="server"
                                                data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                data-search="false" data-show-columns="false" data-show-refresh="false"
                                                data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                                data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                                data-maintain-selected="true" data-export-types='["txt","excel"]'
                                                data-query-params="category_query_params">
                                                <thead>
                                                    <tr>
                                                        <th data-checkbox="true" data-field="delete-checkbox">
                                                            <input name="select_all" type="checkbox">
                                                        </th>
                                                        <th data-field="id" data-sortable="true" data-visible='true'>
                                                            {{ labels('admin_labels.id', 'ID') }}
                                                        <th data-field="name" data-sortable="false" data-disabled="1">
                                                            {{ labels('admin_labels.name', 'Name') }}
                                                        </th>
                                                        <th class="d-flex justify-content-center" data-field="image"
                                                            data-sortable="false">
                                                            {{ labels('admin_labels.image', 'Image') }}
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
                        @endif
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
