@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.attributes', 'Attributes') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.attributes', 'Attributes')" :subtitle="labels(
        'admin_labels.efficiently_manage_product_attributes_with_precision',
        'Efficiently Manage Product Attributes with Precision',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.products', 'Products'), 'url' => route('admin.products.index')],
        ['label' => labels('admin_labels.attributes', 'Attributes')],
    ]" />

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 col-lg-4">
                <div class="card">
                    <form class="submit_form" action="{{ route('admin.attributes.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.add_attribute', 'Add Attributes') }}
                            </h5>
                            <div class="form-group col-sm-12 mb-4">
                                <label for="category_id"
                                    class="">{{ labels('admin_labels.categories', 'Categories') }}
                                    <span class='text-asterisks text-sm'>*</span></label>

                                <select name="category_id" id="category_id" class="search_admin_category w-100"
                                    data-placeholder=" Type to search and select categories">
                                    <option value="" selected></option>
                                </select>

                            </div>
                            <div class="form-group col-sm-12 mb-4">
                                <label for="name"
                                    class="">{{ labels('admin_labels.attribute_name', 'Attribute Name') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="attribute_id" id="attribute" class="w-100"
                                    data-placeholder=" Type to search and select Attribute">
                                    <option value=""></option>
                                </select>

                                <div class="col-sm-12 attribute_name d-none">
                                    <input type="text" class="form-control " id="name" placeholder="Name"
                                        name="name" value="">
                                </div>
                            </div>
                            <div class="form-group d-flex justify-content-between  col-sm-12 mb-4">
                                <label for="name"
                                    class="">{{ labels('admin_labels.attribute_values', 'Attribute Values') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <button type="button btn-sm" id="add_attribute_value" class="btn btn-primary btn-sm"><i
                                        class="bx bx-plus"></i>
                                    {{ labels('admin_labels.add_value', 'Add Value') }}
                                </button>

                            </div>
                            <div id="attribute_section"> </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="reset"
                                    class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit" class="btn btn-primary submit_button"
                                    id="">{{ labels('admin_labels.add_attribute', 'Add Attribute') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- table  --}}
            <div
                class="col-lg-8 col-md-12 mt-md-2 mt-sm-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view attributes') ? '' : 'd-none' }}">
                <section class="overview-data">
                    <div class="card content-area p-4 ">
                        <div class="row align-items-center d-flex heading mb-5">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4>{{ labels('admin_labels.manage_attributes', 'Manage Attributes') }}
                                        </h4>
                                    </div>

                                    <div class="col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                                        <div class="input-group me-2 search-input-grp ">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" data-table="admin_attribute_table"
                                                class="form-control searchInput" placeholder="Search...">
                                            <span
                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                        </div>
                                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                            data-bs-target="#columnFilterOffcanvas" data-table="admin_attribute_table"
                                            dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                            orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                        <a class="btn me-2" id="tableRefresh"data-table="admin_attribute_table"><i
                                                class='bx bx-refresh'></i></a>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_attribute_table','csv')">CSV</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_attribute_table','json')">JSON</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_attribute_table','sql')">SQL</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_attribute_table','excel')">Excel</button>
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
                                    <div class="table-responsive">
                                        <table class='table' id="admin_attribute_table" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="{{ route('admin.attributes.list') }}" data-click-to-select="true"
                                            data-side-pagination="server" data-pagination="true"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                            data-show-columns="false" data-show-refresh="false"
                                            data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                            data-maintain-selected="true" data-export-types='["txt","excel"]'
                                            data-query-params="queryParams">
                                            <thead>
                                                <tr>
                                                    <th data-field="id" data-sortable="true">
                                                        {{ labels('admin_labels.id', 'ID') }}
                                                    </th>
                                                    <th data-field="value" data-disabled="1" data-sortable="false">
                                                        {{ labels('admin_labels.attributes', 'Attributes') }}
                                                    </th>
                                                    <th data-field="name" data-sortable="false" data-disabled="1">
                                                        {{ labels('admin_labels.name', 'Name') }}
                                                    </th>
                                                    <th data-field="category" data-sortable="false">
                                                        {{ labels('admin_labels.category', 'Category') }}
                                                    </th>
                                                    <th data-field="status" data-sortable="false">
                                                        {{ labels('admin_labels.status', 'Status') }}
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
