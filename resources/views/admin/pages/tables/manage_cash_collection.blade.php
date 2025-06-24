@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.cash_collection', 'Cash Collection') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.cash_collection', 'Cash Collection')" :subtitle="labels(
        'admin_labels.track_and_manage_delivery_boy_cash_collection_with_precision',
        'Track and Manage Delivery Boy Cash Collection with Precision',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.delivery_boys', 'Delivery Boys')],
        ['label' => labels('admin_labels.cash_collection', 'Cash Collection')],
    ]" />


    {{-- collection model --}}


    <div class="modal fade" id="cash_collection_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myLargeModalLabel">
                        {{ labels('admin_labels.update_transaction', 'Update Transaction') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form class="submit_form" action="{{ route('admin.manage_cash_collection') }}" method="POST"
                    enctype="multipart/form-data">
                    <input type="hidden" id="edit_zipcode_id" name="edit_zipcode_id">
                    <div class="modal-body">
                        <input type='hidden' name="delivery_boy_id" id="delivery_boy_id" value='' />
                        <input type='hidden' class="delivery_boy_cash_recived" value='' />
                        <div class="mb-3 col-md-12 delivery_boy_wallet_transaction_parent">
                            <label for="details" class="form-label">{{ labels('admin_labels.customer', 'Customer') }}
                                <span class='text-asterisks text-sm'>*</span></label>

                            <select id="delivery_boy_select" class="form-control w-100" id="details">
                                <!-- Options will be populated dynamically -->
                            </select>

                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="amount"
                                class="form-label">{{ labels('admin_labels.amount_to_be_collect', 'Amount To be Collect') }}
                                <span class='text-asterisks text-sm'>*</span></label>

                            <input type="text" name="amount" id="amount" class="form-control"
                                onkeyup="validate_amount(this.value);">

                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="date" class="form-label">{{ labels('admin_labels.date', 'Date') }}
                                <small>(DD-MM-YYYY)</small><span class='text-asterisks text-sm'>*</span></label>

                            <input type="datetime-local" name="date" id="date" class="form-control"
                                value="{{ date('Y-m-d') }}">

                        </div>
                        <div class="mb-3 col-md-12">
                            <label for="message" class="form-label">{{ labels('admin_labels.message', 'Message') }}
                            </label>

                            <textarea class="form-control" rows="3" name="message" id="message"></textarea>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary submit_button" id="submit_btn">
                                {{ labels('admin_labels.collect', 'Collect') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <section
        class="overview-data {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view delivery_boy_cash_collection') ? '' : 'd-none' }}">

        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-lg-6 col-xl-4">
                            <h4>{{ labels('admin_labels.cash_collection', 'Cash Collection') }}
                            </h4>
                        </div>
                        <div class="col-lg-12 col-md-12 col-xl-8 d-flex justify-content-end">
                            <button type="button" class="btn btn-dark me-3" data-bs-target="#cash_collection_modal"
                                data-bs-toggle="modal"><i
                                    class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.cash_collection', 'Cash Collection') }}</button>
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_delivery_boy_cash_collection"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_delivery_boy_cash_collection"
                                dateFilter='true' cashCollectionTypeFilter='true' deliveryBoyFilter='true'><i
                                    class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_delivery_boy_cash_collection"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boy_cash_collection','csv')">CSV</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boy_cash_collection','json')">JSON</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boy_cash_collection','sql')">SQL</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_delivery_boy_cash_collection','excel')">Excel</button>
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
                            <table class='table' id="admin_delivery_boy_cash_collection" data-toggle="table"
                                data-loading-template="loadingTemplate"
                                data-url="{{ route('admin.get_cash_collection') }}" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true"
                                data-export-types='["txt","excel"]' data-query-params="cash_collection_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.user_name', 'User Name') }}
                                        </th>
                                        <th data-field="mobile" data-sortable="false">
                                            {{ labels('admin_labels.mobile', 'Mobile') }}
                                        </th>
                                        <th data-field="amount" data-sortable="false"
                                            data-footer-formatter="priceFormatter">
                                            {{ labels('admin_labels.amount', 'Amount') }}
                                        </th>
                                        <th data-field="type" data-sortable="false">
                                            {{ labels('admin_labels.status', 'Status') }}
                                        </th>
                                        <th data-field="message" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.message', 'Message') }}
                                        </th>
                                        <th data-field="transaction_date" data-sortable="true">
                                            {{ labels('admin_labels.date', 'Date') }}
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
