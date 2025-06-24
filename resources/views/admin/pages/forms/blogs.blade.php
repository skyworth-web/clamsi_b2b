@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.blogs', 'Blogs') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.create_blog', 'Create Blog')" :subtitle="labels(
        'admin_labels.craft_compelling_blogs_with_user_friendly_creation_tool',
        'Craft Compelling Blogs with User-Friendly Creation Tool',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.blogs', 'Blogs')],
        ['label' => labels('admin_labels.create_blogs', 'Create Blogs')],
    ]" />

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">
                        Add Blog
                    </h5>
                    <div class="row">
                        <div class="form-group">
                            <form id="" action="{{ route('blogs.store') }}" class="submit_form"
                                enctype="multipart/form-data" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        {{-- <div class="row">
                                            <div class="col-md-12">
                                                <label for="title"
                                                    class="col-sm-2 form-label">{{ labels('admin_labels.title', 'Title') }}
                                                    <span class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="title"
                                                    placeholder="Title" name="title" value="">
                                            </div>
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
                                                    <input type="text" placeholder="Title" name="title"
                                                        class="form-control" value="">
                                                </div>
                                            </div>

                                            {!! generateLanguageTabs($languages, 'admin_labels.name', 'Name', 'translated_blog_title') !!}
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-12 get_blog_category_parent">
                                                <label for="category"
                                                    class="form-label">{{ labels('admin_labels.select_category', 'Select Category') }}
                                                    <span class='text-asterisks text-sm'>*</span></label>
                                                <select name="category_id" class="form-select get_blog_category"
                                                    data-placeholder="Search Categories">
                                                    <option></option>
                                                    <!-- Repeat the options here -->
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for=""
                                                    class="form-label">{{ labels('admin_labels.image', 'Image') }}<span
                                                        class="text-asterisks text-sm">*</span></label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 col-lg-6 file_upload_box border file_upload_border mt-2">
                                                <div class="mt-2">
                                                    <div class="col-md-12  text-center">
                                                        <div>
                                                            <a class="media_link" data-input="image" data-isremovable="0"
                                                                data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                                data-bs-target="#media-upload-modal" value="Upload Photo">
                                                                <h4><i class='bx bx-upload'></i> Upload
                                                            </a></h4>
                                                            <p class="image_recommendation">Recommended Size : larger
                                                                than
                                                                400 x 260 &
                                                                smaller than 600 x 300 pixels.</p>
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

                                    <div class="col-md-12 mt-3">
                                        <label for=""
                                            class="form-label">{{ labels('admin_labels.description', 'Description') }}<span
                                                class="text-asterisks text-sm">*</span></label>
                                        <textarea name="description" class="form-control textarea addr_editor" placeholder="Place some text here"></textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="reset"
                                        class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                    <button type="submit"
                                        class="btn btn-primary submit_button">{{ labels('admin_labels.add_blog', 'Add Blog') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div
            class="col-md-12 main-content mt-4 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view blogs') ? '' : 'd-none' }}">
            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-sm-12">
                                    <h4> {{ labels('admin_labels.manage_blogs', 'Manage Blogs') }}
                                    </h4>
                                </div>
                                <div class="col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                                    <div class="input-group me-2 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="admin_blog_table" class="form-control searchInput"
                                            placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="admin_blog_table"
                                        blogCategoryFilter='true'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="admin_blog_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button"
                                            id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_blog_table','csv')">CSV</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_blog_table','json')">JSON</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_blog_table','sql')">SQL</button></li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_blog_table','excel')">Excel</button>
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
                                data-table-id="admin_blog_table"
                                data-delete-url="{{ route('blogs.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                        </div>
                        <div class="col-md-12">
                            <div class="pt-0">
                                <div class="table-responsive">
                                    <table class='table' id="admin_blog_table" data-toggle="table"
                                        data-loading-template="loadingTemplate" data-url="{{ route('blogs.list') }}"
                                        data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel"]' data-query-params="blog_query_params">
                                        <thead>
                                            <tr>
                                                <th data-checkbox="true" data-field="delete-checkbox">
                                                    <input name="select_all" type="checkbox">
                                                </th>
                                                <th data-field="id" data-sortable="true" data-visible='true'>ID
                                                </th>
                                                <th data-field="title" data-sortable="false" data-disabled="1">
                                                    {{ labels('admin_labels.title', 'Title') }}
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
                </div>
            </section>
        </div>
    </div>
@endsection
