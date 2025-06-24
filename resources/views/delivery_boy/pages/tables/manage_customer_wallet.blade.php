@extends('delivery_boy/layout')
@section('title')
    {{ labels('admin_labels.wallet_transaction', 'Wallet Transaction') }}
@endsection
@section('content')
    <x-delivery_boy.breadcrumb :title="labels('admin_labels.wallet_transaction', 'Wallet Transaction')" :subtitle="labels(
        'admin_labels.track_and_manage_wallet_transactions_with_precision',
        'Track and Manage Wallet Transactions with Precision',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.customers', 'Customers')],
        ['label' => labels('admin_labels.wallet_transaction', 'Wallet Transaction')],
    ]" />
    {{-- add model  --}}


    <div class="modal fade" id="send_withdrawal_request" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <form class="submit_form" method="POST"
                    action="{{ route('delivery_boy.payment_request.add_withdrawal_request') }}"
                    enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">
                            {{ labels('admin_labels.send_withdrawal_request', 'Send Withdrawal Request') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="user_id" value="<?= $user_id ?>">
                        <div class="row">
                            <div class="mb-3 row">
                                <label for="payment_address"
                                    class="col-sm-12 form-label">{{ labels('admin_labels.payment_details', 'Payment Details') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-12">
                                    <textarea type="text" class="form-control" placeholder="Payment Details" name="payment_address"></textarea>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="amount"
                                    class="col-sm-12 form-label">{{ labels('admin_labels.amount', 'Amount') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-12">
                                    <input type="number" class="form-control" placeholder="Amount" name="amount" min=1>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary submit_button"
                                id="submit_btn">{{ labels('admin_labels.withdraw_money', 'Withdraw Money') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div class="col-md-3">

        <div class="card mt-4 flip_icon_card">
            <div class="p-4 pb-0">
                <div class="setting_icons_div test d-flex justify-content-center align-items-center">
                    <i class="bx bx-coin-stack setting_icons"></i>
                </div>
            </div>
            <div class="card-body d-flex align-items-center">
                <h5 class="card-title m-0 mx-2 setting_card_title">
                    {{ labels('admin_labels.wallet_balance', 'Wallet Balance') }}
                    :
                    <?= isset($wallet_balance) && !empty($wallet_balance) ? $wallet_balance : '0' ?>
                </h5>

            </div>
        </div>

    </div>


    {{-- table  --}}
    <div class="card content-area p-4 mt-4">
        <div class="row align-items-center d-flex heading mb-5">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 col-xxl-6">
                        <h4>{{ labels('admin_labels.manage_delivery_boy_wallet_transaction', 'Manage Delivery Boy Wallet Transactions') }}
                        </h4>
                    </div>
                    <div class="col-md-12 col-xxl-6 d-flex justify-content-end ">
                        <button type="button" class="btn btn-dark me-3" data-bs-target="#send_withdrawal_request"
                            data-bs-toggle="modal"><i
                                class='bx bx-plus-circle me-1'></i>{{ labels('admin_labels.withdraw_money', 'Withdraw Money') }}</button>
                        <div class="input-group me-2 search-input-grp ">
                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                            <input type="text" data-table="delivery_boy_customer_wallet_table"
                                class="form-control searchInput" placeholder="Search...">
                            <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                        </div>
                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                            data-bs-target="#columnFilterOffcanvas" data-table="delivery_boy_customer_wallet_table"
                            dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                            orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                        <a class="btn me-2" id="tableRefresh"data-table="delivery_boy_customer_wallet_table"><i
                                class='bx bx-refresh'></i></a>
                        <div class="dropdown">
                            <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class='bx bx-download'></i>
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_customer_wallet_table','csv')">CSV</button>
                                </li>
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_customer_wallet_table','json')">JSON</button>
                                </li>
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_customer_wallet_table','sql')">SQL</button>
                                </li>
                                <li><button class="dropdown-item" type="button"
                                        onclick="exportTableData('delivery_boy_customer_wallet_table','excel')">Excel</button>
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
                        <table class='table' id="delivery_boy_customer_wallet_table" data-toggle="table"
                            data-loading-template="loadingTemplate"
                            data-url="{{ route('delivery_boy.getTransactionList') }}" data-click-to-select="true"
                            data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                            data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                            data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                            data-show-export="false" data-maintain-selected="true" data-export-types='["txt","excel"]'
                            data-query-params="customer_wallet_query_params">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">
                                        {{ labels('admin_labels.id', 'ID') }}
                                    <th data-field="type" data-sortable="false">
                                        {{ labels('admin_labels.transaction_type', 'Transaction Type') }}
                                    </th>
                                    <th data-field="payu_txn_id" data-sortable="false" data-visible="false">
                                        {{ labels('admin_labels.pay_transaction_id', 'Pay Transaction ID') }}
                                    </th>
                                    <th data-field="amount" data-sortable="false">
                                        {{ labels('admin_labels.amount', 'Amount') }}
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
