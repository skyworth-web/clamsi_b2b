@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.product_faqs', 'Product FAQs') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.product_faqs', 'Product FAQs')" :subtitle="labels(
        'admin_labels.empower_customers_with_clear_and_insightful_product_faqs',
        'Empower Customers with Clear and Insightful Product FAQs',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.products', 'Products')],
        ['label' => labels('admin_labels.product_faqs', 'Product FAQs')],
    ]" />


    <!-- prduct faqs add modal -->

    <div class="modal fade" id="add_combo_product_faq" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myLargeModalLabel">
                        {{ labels('admin_labels.add_faqs', 'Add FAQs') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal submit_form" action="{{ route('combo_product_faqs.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="mb-3  col-md-12">
                            <label for="attributes"
                                class="form-label">{{ labels('admin_labels.select_product', 'Select Product') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <div class="col-sm-12 search_admin_combo_product_parent">
                                <select name="product_id" class="form-select search_admin_combo_product"
                                    data-placeholder="Type to search and select products">
                                </select>
                            </div>
                        </div>
                        <div class="mb-3  col-md-12">
                            <label for="question" class="form-label">{{ labels('admin_labels.question', 'Question') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="question" placeholder="question"
                                    name="question">
                            </div>
                        </div>
                        <div class="mb-3  col-md-12">
                            <label for="answer" class="form-label">{{ labels('admin_labels.answer', 'Answer') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <div class="col-sm-12">
                                <textarea class="form-control" id="answer" placeholder="answer" name="answer"></textarea>
                            </div>
                        </div>
                        <div class="mb-3 d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.add_faqs', 'Add FAQs') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- edit model  --}}

    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        {{ labels('admin_labels.update_product_faq', 'Update Product FAQ') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form enctype="multipart/form-data" method="POST" class="submit_form">
                    @method('PUT')
                    @csrf
                    <input type="hidden" class="edit_faq_id" name="edit_faq_id">
                    <div class="modal-body">

                        <div class="row">
                            <div class="mb-3 row col-md-12">
                                <label for="question"
                                    class="form-label">{{ labels('admin_labels.question', 'Question') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-12">
                                    <input type="text" class="form-control question" id="question"
                                        placeholder="question" name="question" readonly>
                                </div>
                            </div>
                            <div class="mb-3 row col-md-12">
                                <label for="answer" class="form-label">{{ labels('admin_labels.answer', 'Answer') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-12">
                                    <textarea class="form-control answer" id="answer" placeholder="answer" name="answer"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{ labels('admin_labels.close', 'Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary submit_button"
                            id="">{{ labels('admin_labels.update_product_faq', 'Update Product FAQ') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- table  --}}

    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-sm-12">
                            <h4>{{ labels('admin_labels.faqs', 'FAQs') }} </h4>
                        </div>
                        <div class="col-md-12 col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                            <button type="button" class="btn btn-dark me-3" data-bs-target="#add_combo_product_faq"
                                data-bs-toggle="modal"><i
                                    class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.add_faqs', 'Add FAQs') }}</button>
                            <div
                                class="input-group me-2 search-input-grp {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view combo_product_faq') ? '' : 'd-none' }}">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_combo_product_faq_table"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view combo_product_faq') ? '' : 'd-none' }}"
                                id="tableFilter" data-bs-toggle="offcanvas" data-bs-target="#columnFilterOffcanvas"
                                data-table="admin_combo_product_faq_table" dateFilter='false' orderStatusFilter='false'
                                paymentMethodFilter='false' orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view combo_product_faq') ? '' : 'd-none' }}"
                                id="tableRefresh"data-table="admin_combo_product_faq_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div
                                class="dropdown {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view combo_product_faq') ? '' : 'd-none' }}">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_combo_product_faq_table','csv')">CSV</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_combo_product_faq_table','json')">JSON</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_combo_product_faq_table','sql')">SQL</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_combo_product_faq_table','excel')">Excel</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                class="row {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view combo_product_faq') ? '' : 'd-none' }}">
                <div class="col-md-6">
                    <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                        data-table-id="admin_combo_product_faq_table"
                        data-delete-url="{{ route('combo_faqs.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                </div>
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table id='admin_combo_product_faq_table' data-toggle="table"
                                data-loading-template="loadingTemplate"
                                data-url="{{ route('admin.combo_product_faqs.list') }}" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true"
                                data-export-types='["txt","excel","csv"]'
                                data-export-options='{
                        "fileName": "products-list",
                        "ignoreColumn": ["state"]
                        }'
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-checkbox="true" data-field="delete-checkbox">
                                            <input name="select_all" type="checkbox">
                                        </th>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        </th>
                                        <th data-field="username" data-sortable="false" data-visible='false'>
                                            {{ labels('admin_labels.user_name', 'User Name') }}
                                        </th>
                                        <th data-field="product_name" data-sortable="false" data-visible='true'
                                            data-disabled="1">
                                            {{ labels('admin_labels.product_name', 'Product Name') }}
                                        </th>
                                        <th data-field="question" data-sortable="false" data-disabled="1">
                                            {{ labels('admin_labels.question', 'Question') }}
                                        </th>
                                        <th data-field="answer" data-sortable="false" data-disabled="1">
                                            {{ labels('admin_labels.answer', 'Answer') }}
                                        </th>
                                        <th data-field="answered_by_name" data-sortable="false">
                                            {{ labels('admin_labels.answered_by', 'Answered By') }}
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
@endsection
