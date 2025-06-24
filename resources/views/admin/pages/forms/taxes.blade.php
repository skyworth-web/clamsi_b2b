@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.tax', 'Tax') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.taxes', 'Taxes')" :subtitle="labels(
        'admin_labels.masterful_tax_management_at_your_fingertips',
        'Masterful Tax Management at Your Fingertips',
    )" :breadcrumbs="[['label' => labels('admin_labels.tax', 'Tax')]]" />

    <div class="col-md-12">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-4">
                <div class="card">
                    <!-- form start -->
                    <form class="form-horizontal form-submit-event submit_form" action="{{ route('taxes.store') }}"
                        method="POST" id="" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.add_tax', 'Add Tax') }}
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
                                <!-- Default 'en' tab content -->
                                <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                    aria-labelledby="tab-en">
                                    <div class="mb-3">
                                        <label for="brand_name"
                                            class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                                class="text-asterisks text-sm">*</span></label>
                                        <input type="text" name="title" class="form-control" placeholder="Tax">
                                    </div>
                                </div>

                                {!! generateLanguageTabs($languages, 'admin_labels.name', 'Name', 'translated_tax_name') !!}
                            </div>
                            <div class="mb-3 col-12">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.percentage', 'Percentage') }}<span
                                        class="text-asterisks text-sm">*</span></label>
                                <input type="text" class="form-control" id="percentage" placeholder="20"
                                    name="percentage" value="">

                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="reset"
                                    class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.add_tax', 'Add Tax') }}</button>
                            </div>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="form-group" id="error_box">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- edit modal -->

            <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel">
                                {{ labels('admin_labels.edit_tax', 'Edit Tax') }}
                            </h5>
                            <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                    data-bs-dismiss="modal" aria-label="Close"></button></div>
                        </div>
                        <form enctype="multipart/form-data" method="POST" class="submit_form">
                            @method('PUT')
                            @csrf
                            <input type="hidden" class="edit_faq_id" name="edit_faq_id">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="edit_title"
                                        class="form-label">{{ labels('admin_labels.name', 'Name') }}<span
                                            class="text-asterisks text-sm">*</span></label>
                                    <input type="text" class="form-control edit_title" id="" name="edit_title">
                                </div>
                                @foreach ($languages as $lang)
                                    @if ($lang->code !== 'en')
                                        <div class="mb-3 col-md-12">
                                            <label for="translated_name_{{ $lang->code }}" class="form-label">
                                                {{ labels('admin_labels.name', 'Name') }} ({{ $lang->language }})
                                            </label>
                                            <input type="text" class="form-control translated_name_{{ $lang->code }}"
                                                id="translated_name_{{ $lang->code }}"
                                                name="translated_tax_name[{{ $lang->code }}]" value="">
                                        </div>
                                    @endif
                                @endforeach
                                <div class="form-group mt-2">
                                    <label for="edit_percentage"
                                        class="form-label">{{ labels('admin_labels.percentage', 'Percentage') }}<span
                                            class="text-asterisks text-sm">*</span></label>

                                    <input type="text" class="form-control edit_percentage" id=""
                                        name="edit_percentage">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">
                                    {{ labels('admin_labels.close', 'Close') }}
                                </button>
                                <button type="submit" class="btn btn-primary submit_button"
                                    id="">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- table --}}
            <div
                class="col-lg-8 col-md-12 col-sm-12 mt-md-2 mt-sm-2 mt-xl-0 mt-xxl-0 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view tax') ? '' : 'd-none' }}">
                <section class="overview-data">
                    <div class="card content-area p-4 ">
                        <div class="row align-items-center d-flex heading mb-5">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4>{{ labels('admin_labels.manage_taxes', 'Manage Taxes') }}
                                        </h4>
                                    </div>
                                    <div class="col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                                        <div class="input-group me-2 search-input-grp ">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" data-table="admin_tax_table"
                                                class="form-control searchInput" placeholder="Search...">
                                            <span
                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                        </div>
                                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                            data-bs-target="#columnFilterOffcanvas" data-table="admin_tax_table"
                                            dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                            orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                        <a class="btn me-2" id="tableRefresh"data-table="admin_tax_table"><i
                                                class='bx bx-refresh'></i></a>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_tax_table','csv')">CSV</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_tax_table','json')">JSON</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_tax_table','sql')">SQL</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_tax_table','excel')">Excel</button>
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
                                    data-table-id="admin_tax_table"
                                    data-delete-url="{{ route('taxes.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                            </div>
                            <div class="col-md-12">
                                <div class="pt-0">
                                    <div class="table-responsive">
                                        <table class='table' id="admin_tax_table" data-toggle="table"
                                            data-loading-template="loadingTemplate" data-url="{{ route('taxes.list') }}"
                                            data-click-to-select="true" data-side-pagination="server"
                                            data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                            data-search="false" data-show-columns="false" data-show-refresh="false"
                                            data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                            data-maintain-selected="true" data-export-types='["txt","excel"]'
                                            data-query-params="brand_query_params">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true" data-field="delete-checkbox">
                                                        <input name="select_all" type="checkbox">
                                                    </th>
                                                    <th data-field="id" data-sortable="true" data-visible="true">
                                                        {{ labels('admin_labels.id', 'ID') }}
                                                    <th data-field="title" data-disabled="1" data-sortable="false">
                                                        {{ labels('admin_labels.title', 'Title') }}
                                                    </th>
                                                    <th data-field="percentage" data-sortable="false">
                                                        {{ labels('admin_labels.percentage', 'Percentage') }}
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
    </div>
@endsection
