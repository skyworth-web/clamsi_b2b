@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.product_faqs', 'Combo Product FAQs') }}
@endsection
@section('content')
    <section class="main-content">
        <div class="row">
            <x-seller.breadcrumb :title="labels('admin_labels.product_faqs', 'Product FAQs')" :subtitle="labels(
                'admin_labels.empower_customers_with_clear_and_insightful_product_faqs',
                'Empower Customers with Clear and Insightful Product FAQs',
            )" :breadcrumbs="[
                ['label' => labels('admin_labels.combo_products_manage', 'Combo Products Manage')],
                ['label' => labels('admin_labels.product_faqs', 'Product FAQs')],
            ]" />

            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-xl-6">
                                    <h4>{{ labels('admin_labels.product_faqs', 'Product FAQs') }} </h4>
                                </div>
                                <div class="col-md-12 col-xl-6 d-flex justify-content-end ">
                                    <button type="button" class="btn btn-dark me-3" data-bs-target="#add_combo_product_faq"
                                        data-bs-toggle="modal"><i
                                            class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.add_faqs', 'Add FAQs') }}</button>

                                    <div class="input-group me-3 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="combo_products_faqs_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="combo_products_faqs_table"
                                        dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                        orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="combo_products_faqs_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('combo_products_faqs_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('combo_products_faqs_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('combo_products_faqs_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('combo_products_faqs_table','excel')">Excel</button>
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
                                    <table class='table' id='combo_products_faqs_table' data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('seller.combo_product_faqs.list') }}" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="DESC" data-mobile-responsive="true"
                                        data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                        data-export-types='["txt","excel","csv"]'
                                        data-export-options='{
                                            "fileName": "products-list",
                                            "ignoreColumn": ["state"]
                                            }'
                                        data-query-params="queryParams">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true">
                                                    {{ labels('admin_labels.id', 'ID') }}
                                                </th>
                                                <th data-field="user_id" data-sortable="false" data-visible='false'>
                                                    {{ labels('admin_labels.user_id', 'User ID') }}
                                                </th>
                                                <th data-field="product_name" data-sortable="false" data-visible='true'
                                                    data-disabled="1">
                                                    {{ labels('admin_labels.product_name', 'Product Name') }}
                                                </th>
                                                <th data-field="question" data-sortable="false">
                                                    {{ labels('admin_labels.question', 'Question') }}
                                                </th>
                                                <th data-field="answer" data-sortable="false">
                                                    {{ labels('admin_labels.answer', 'Answer') }}
                                                </th>
                                                <th data-field="answered_by" data-sortable="false" data-visible='false'>
                                                    {{ labels('admin_labels.answered_by', 'Answered By') }}
                                                </th>
                                                <th data-field="answered_by_name" data-sortable="false">
                                                    {{ labels('admin_labels.answered_by', 'Answered By Name') }}
                                                </th>
                                                <th data-field="date_added" data-sortable="false">
                                                    {{ labels('admin_labels.date', 'Date Added') }}
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
    </section>
    <!-- -------------------------------------------------- edit product faq's modal ----------------------------------------------- -->

    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <form enctype="multipart/form-data" method="POST" class="submit_form">
            @method('PUT')
            @csrf
            <input type="hidden" class="edit_faq_id" name="edit_faq_id">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">
                            {{ labels('admin_labels.update_product_faq', 'Update Product FAQ') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">

                        <div class="row">
                            <div class="mb-3 row">
                                <label for="question"
                                    class="col-sm-2 form-label">{{ labels('admin_labels.question', 'Question') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-12">
                                    <input type="text" class="form-control question" id="question"
                                        placeholder="question" name="question" readonly>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="answer"
                                    class="col-sm-2 form-label">{{ labels('admin_labels.answer', 'Answer') }}<span
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
                            id="save_changes_btn">{{ labels('admin_labels.update_product_faq', 'Update Product FAQ') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- -------------------------------------------------- add product faq's modal ----------------------------------------------- -->

    <div class="modal fade" id="add_combo_product_faq" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="myLargeModalLabel">
                        {{ labels('admin_labels.add_faqs', 'Add Product FAQs') }}
                    </h4>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal submit_form" action="{{ route('seller.combo_product_faqs.store') }}"
                    method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">

                        <div class="mb-3 row">
                            <label for="attributes"
                                class="col-md-12 form-label">{{ labels('admin_labels.select_product', 'Select Product') }}
                                <span class='text-asterisks text-sm'>*</span></label>
                            <div class="col-sm-12 search_seller_combo_product_parent" id="search_product">
                                <select name="product_id" class="form-select search_seller_combo_product"
                                    data-placeholder="Type to search and select products">
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="question"
                                class="col-sm-5 form-label">{{ labels('admin_labels.question', 'Question') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" id="question" placeholder="question"
                                    name="question">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="answer"
                                class="col-sm-5 form-label">{{ labels('admin_labels.answer', 'Answer') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <div class="col-sm-12">
                                <textarea class="form-control" id="answer" placeholder="answer" name="answer"></textarea>
                            </div>
                        </div>
                        <div class="mb-3 d-flex float-end">
                            <button type="reset"
                                class="btn reset-btn me-2">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary submit_button"
                                id="submit_btn">{{ labels('admin_labels.add_faqs', 'Add Product FAQs') }}</button>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center">
                        <div class="form-group" id="error_box">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
