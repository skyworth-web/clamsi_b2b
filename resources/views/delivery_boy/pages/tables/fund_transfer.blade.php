@extends('delivery_boy/layout')
@section('title')
    {{ labels('admin_labels.fund_transfer', 'Fund Transfer') }}
@endsection
@section('content')
    <x-delivery_boy.breadcrumb :title="labels('admin_labels.fund_transfer', 'Fund Transfer')" :subtitle="labels(
        'admin_labels.track_and_manage_fund_transfer_with_precision',
        'Track and Manage Fund Transfer with Precision',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.transactions', 'Transaction')],
        ['label' => labels('admin_labels.fund_transfer', 'Fund Transfer')],
    ]" />
    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-6">
                    <h4>{{ labels('admin_labels.fund_transfer', 'Fund Transfer') }}
                    </h4>
                </div>
                <div class="col-md-6 d-flex justify-content-end ">
                    <div class="input-group me-3 search-input-grp ">
                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                        <input type="text" data-table="delivery_boy_fund_transfer" class="form-control searchInput"
                            placeholder="Search...">
                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                    </div>
                    <a class="btn me-3" id="tableFilter" data-bs-toggle="offcanvas" data-bs-target="#columnFilterOffcanvas"
                        data-table="delivery_boy_fund_transfer" dateFilter='false' orderStatusFilter='false'
                        paymentMethodFilter='false' orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                    <a class="btn me-3" id="tableRefresh" data-table="delivery_boy_fund_transfer"><i
                            class='bx bx-refresh'></i></a>
                    <div class="dropdown">
                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class='bx bx-download'></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_fund_transfer','csv')">CSV</button></li>
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_fund_transfer','json')">JSON</button></li>
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_fund_transfer','sql')">SQL</button></li>
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_fund_transfer','excel')">Excel</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="delivery_boy_fund_transfer" data-loading-template="loadingTemplate"
                                data-toggle="table" data-url="{{ route('delivery_boy.fund.transfers.list') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true" data-export-types='["txt","excel"]'
                                data-query-params="cash_collection_query_params">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.name', 'Name') }}
                                        </th>
                                        <th data-field="mobile" data-sortable="false">
                                            {{ labels('admin_labels.mobile', 'Mobile') }}
                                        </th>
                                        <th data-field="opening_balance" data-sortable="false">
                                            {{ labels('admin_labels.opening_balance', 'Opening Balance') }}
                                        </th>
                                        <th data-field="closing_balance" data-sortable="false">
                                            {{ labels('admin_labels.closing_balance', 'Closing Balance') }}
                                        </th>
                                        <th data-field="amount" data-sortable="false">
                                            {{ labels('admin_labels.amount', 'Amount') }}
                                        </th>
                                        <th data-field="status" data-sortable="false">
                                            {{ labels('admin_labels.status', 'Status') }}
                                        </th>
                                        <th data-field="message" data-sortable="false">
                                            {{ labels('admin_labels.message', 'Message') }}
                                        </th>
                                        <th data-field="date" data-sortable="true">
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
