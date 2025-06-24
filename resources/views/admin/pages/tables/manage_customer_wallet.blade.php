@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.wallet_transaction', 'Wallet Transaction') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.wallet_transaction', 'Wallet Transaction')" :subtitle="labels(
        'admin_labels.track_and_manage_wallet_transactions_with_precision',
        'Track and Manage Wallet Transactions with Precision',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.customers', 'Customers')],
        ['label' => labels('admin_labels.wallet_transaction', 'Wallet Transaction')],
    ]" />


    {{-- add model  --}}


    <div class="modal fade" id="add_wallet_transaction" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title h4" id="myLargeModalLabel">
                        {{ labels('admin_labels.add_wallet_transaction', 'Add Wallet Transaction') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form class="submit_form" method="POST" action="{{ route('admin.customers.updateCustomerWallet') }}"
                    enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="col-md-12 customer_wallet_transaction_parent">
                            <label for="customer"
                                class="col-form-label">{{ labels('admin_labels.customer', 'Customer') }}</label>
                            <select id="customerSelect" class="form-control w-100">
                                <!-- Options will be populated dynamically -->
                            </select>
                            <input type="hidden" id="user_id" name="user_id">
                        </div>
                        <div class="col-md-12">
                            <label for="type"
                                class=" col-form-label">{{ labels('admin_labels.select_type', 'Select Type') }}</label>
                            <select name="type" class='form-control form-select'>
                                <option value="credit">Credit </option>
                                <option value="debit">Debit</option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="amount"
                                class=" col-form-label">{{ labels('admin_labels.amount', 'Amount') }}</label>
                            <input type="number" min=1 class="form-control" id="amount" placeholder="Enter Amount"
                                name="amount">
                        </div>
                        <div class="col-md-12">
                            <label for="message"
                                class=" col-form-label">{{ labels('admin_labels.message', 'Message') }}</label>
                            <textarea class="form-control" id="message" placeholder="Enter Message Here" name="message"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary submit_button"
                                id="submit_btn">{{ labels('admin_labels.add_transaction', 'Add Transaction') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{-- transaction model --}}


    <div class="modal fade" id="transaction_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
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
                <form class="form-horizontal " id="edit_transaction_form"
                    action="{{ route('admin.customers.edit_transactions') }}" method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="id">
                        <div class="col-md-12">
                            <label for="transaction" class="mb-2 mt-2">
                                {{ labels('admin_labels.update_transaction', 'Update Transaction') }}
                            </label>
                            <select class="form-control form-select" name="status" id="t_status">
                                <option value="awaiting"> Awaiting </option>
                                <option value="success"> Success </option>
                                <option value="failed"> Failed </option>
                            </select>
                        </div>
                        <div class="col-md-12">
                            <label for="txn_id"
                                class="mb-2 mt-2">{{ labels('admin_labels.transaction_id', 'Transaction ID') }}</label>
                            <input type="text" class="form-control" name="txn_id" id="txn_id"
                                placeholder="txn_id" />
                        </div>
                        <div class="col-md-12">
                            <label for="message"
                                class="mb-2 mt-2">{{ labels('admin_labels.message', 'Message') }}</label>
                            <input type="text" class="form-control" name="message" id="transaction_message"
                                placeholder="Message" />
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary submit_button"
                                id="submit_btn">{{ labels('admin_labels.update_transaction', 'Update Transaction') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- table  --}}
    <section
        class="overview-data {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view customer_wallet_transaction') ? '' : 'd-none' }}">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-xxl-6">
                            <h4>{{ labels('admin_labels.manage_customer_wallet_transaction', 'Manage Customer Wallet Transactions') }}
                            </h4>
                        </div>
                        <div class="col-md-12 col-xxl-6 d-flex justify-content-end ">
                            <button type="button" class="btn btn-dark me-3" data-bs-target="#add_wallet_transaction"
                                data-bs-toggle="modal"><i
                                    class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.add_transaction', 'Add Transaction') }}</button>
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_customer_wallet_table"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_customer_wallet_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh"data-table="admin_customer_wallet_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_wallet_table','csv')">CSV</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_wallet_table','json')">JSON</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_wallet_table','sql')">SQL</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_customer_wallet_table','excel')">Excel</button>
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
                            <table class='table' id="admin_customer_wallet_table" data-toggle="table"
                                data-loading-template="loadingTemplate"
                                data-url="{{ route('admin.customers.getTransactionList') }}" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true"
                                data-export-types='["txt","excel"]' data-query-params="customer_wallet_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.user_name', 'User Name') }}
                                        </th>
                                        <th data-field="order_id" data-sortable="false">
                                            {{ labels('admin_labels.order_id', 'Order ID') }}
                                        </th>
                                        <th data-field="txn_id" data-sortable="false">
                                            {{ labels('admin_labels.transaction_id', 'Transaction ID') }}
                                        </th>
                                        <th data-field="type" data-sortable="false">
                                            {{ labels('admin_labels.transaction_type', 'Transaction Type') }}
                                        </th>
                                        <th data-field="payu_txn_id" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.pay_transaction_id', 'Pay Transaction ID') }}
                                        </th>
                                        <th data-field="amount" data-sortable="false">
                                            {{ labels('admin_labels.amount', 'Amount') }}
                                        </th>
                                        <th data-field="status" data-sortable="false">
                                            {{ labels('admin_labels.status', 'Status') }}
                                        </th>
                                        <th data-field="message" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.message', 'Message') }}
                                        </th>
                                        <th data-field="created_at" data-sortable="true">
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
