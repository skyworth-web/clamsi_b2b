@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.withdrawal_requests', 'Withdrawal Request') }}
@endsection
@section('content')
    <section class="main-content">
        <div class="row">
            <x-seller.breadcrumb :title="labels('admin_labels.withdrawal_requests', 'Withdrawal Request')" :subtitle="labels(
                'admin_labels.effortlessly_process_and_track_withdrawel_requests',
                'Effortlessly Process and Track Withdrawal Requests',
            )" :breadcrumbs="[
                ['label' => labels('admin_labels.wallet_management', 'Wallet Management')],
                ['label' => labels('admin_labels.withdrawal_requests', 'Withdrawal Request')],
            ]" />

            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-12 col-xxl-6">
                                    <h4>{{ labels('admin_labels.manage_withdrawal_requests', 'Manage Withdrawal Requests') }}
                                    </h4>
                                </div>
                                <div class="col-md-12 col-xxl-6 d-flex justify-content-end ">
                                    <button type="button" class="btn btn-dark me-3"
                                        data-bs-target="#send_withdrawal_request" data-bs-toggle="modal"><i
                                            class='bx bxs-paper-plane me-1'></i>{{ labels('admin_labels.send_withdrawal_request', 'Send Withdrawal Request') }}</button>

                                    <div class="input-group me-3 search-input-grp ">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="seller_withdrawal_request_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="seller_withdrawal_request_table"
                                        dateFilter='true' paymentRequestStatusFilter='true'><i
                                            class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-2" id="tableRefresh" data-table="seller_withdrawal_request_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_withdrawal_request_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_withdrawal_request_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_withdrawal_request_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('seller_withdrawal_request_table','excel')">Excel</button>
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
                                    <table class='table-striped' id='seller_withdrawal_request_table' data-toggle="table"
                                        data-loading-template="loadingTemplate"
                                        data-url="{{ route('seller.payment_request.get_payment_request_list') }}"
                                        data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
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
                                                <th data-field="user_name" data-sortable="false">
                                                    {{ labels('admin_labels.user_name', 'User Name') }}
                                                </th>
                                                <th data-field="payment_type" data-sortable="false">
                                                    {{ labels('admin_labels.type', 'Type') }}
                                                </th>
                                                <th data-field="payment_address" data-sortable="false">
                                                    {{ labels('admin_labels.payment_address', 'Paymemt Address') }}
                                                </th>
                                                <th data-field="amount_requested" data-sortable="false">
                                                    {{ labels('admin_labels.amount_requested', 'Amount Requested') }}
                                                </th>
                                                <th data-field="remarks" data-sortable="false">
                                                    {{ labels('admin_labels.remarks', 'Remarks') }}
                                                </th>
                                                <th data-field="status" data-sortable="false">
                                                    {{ labels('admin_labels.status', 'Status') }}
                                                </th>
                                                <th data-field="date_created" data-sortable="false">
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

        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="send_withdrawal_request" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form enctype="multipart/form-data" action="{{ route('seller.payment_request.add_withdrawal_request') }}"
                    method="POST" class="submit_form">
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
                                    <input type="number" class="form-control" placeholder="Amount" name="amount"
                                        min=1>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="reset" class="btn reset-btn">{{ labels('admin_labels.reset', 'Reset') }}</button>
                        <button type="submit" class="btn btn-primary submit_button"
                            id="save_changes_btn">{{ labels('admin_labels.send_withdrawal_request', 'Send Withdrawal Request') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
