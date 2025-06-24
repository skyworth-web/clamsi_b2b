@extends('delivery_boy/layout')
@section('title')
    {{ labels('admin_labels.cash_collection', 'Cash Collection') }}
@endsection
@section('content')
    <x-delivery_boy.breadcrumb :title="labels('admin_labels.cash_collection', 'Cash Collection')" :subtitle="labels(
        'admin_labels.track_and_manage_delivery_boy_cash_collection_with_precision',
        'Track and Manage Delivery Boy Cash Collection with Precision',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.transactions', 'Transaction')],
        ['label' => labels('admin_labels.cash_collection', 'Cash Collection')],
    ]" />
    <div class="row col-12 d-flex">
        <div class="col-md-3">

            <div class="card mt-4 flip_icon_card">
                <div class="p-4 pb-0">
                    <div class="setting_icons_div test d-flex justify-content-center align-items-center">
                        <i class="bx bx-coin-stack setting_icons"></i>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center">
                    <h5 class="card-title m-0 mx-2 setting_card_title">
                        {{ labels('admin_labels.cash_in_hand', 'Cash In Hand') }}
                        :
                        <?= isset($cash_in_hand) && !empty($cash_in_hand) ? $cash_in_hand : '0' ?>
                    </h5>

                </div>
            </div>

        </div>

        <div class="col-md-3">
            <div class="card mt-4 flip_icon_card">
                <div class="p-4 pb-0">
                    <div class="setting_icons_div d-flex justify-content-center align-items-center">
                        <i class="bx bx-credit-card setting_icons"></i>
                    </div>
                </div>
                <div class="card-body d-flex align-items-center">
                    <h5 class="card-title m-0 mx-2 setting_card_title">
                        {{ labels('admin_labels.cash_collected', 'Cash Collected') }}
                        :
                        <?= isset($cash_collected) && !empty($cash_collected) ? $cash_collected : '0' ?>
                    </h5>
                </div>
            </div>

        </div>
    </div>


    {{-- table  --}}
    <section class="overview-data mt-4">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-6">
                    <h4>{{ labels('admin_labels.cash_collection', 'Cash Collection') }}
                    </h4>
                </div>
                <div class="col-md-6 d-flex justify-content-end ">
                    <div class="input-group me-3 search-input-grp ">
                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                        <input type="text" data-table="delivery_boy_cash_collection" class="form-control searchInput"
                            placeholder="Search...">
                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                    </div>
                    <a class="btn me-3" id="tableFilter" data-bs-toggle="offcanvas" data-bs-target="#columnFilterOffcanvas"
                        data-table="delivery_boy_cash_collection" dateFilter='true' cashCollectionTypeFilter='true'><i
                            class='bx bx-filter-alt'></i></a>
                    <a class="btn me-3" id="tableRefresh" data-table="delivery_boy_cash_collection"><i
                            class='bx bx-refresh'></i></a>
                    <div class="dropdown">
                        <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class='bx bx-download'></i>
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_cash_collection','csv')">CSV</button></li>
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_cash_collection','json')">JSON</button></li>
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_cash_collection','sql')">SQL</button></li>
                            <li><button class="dropdown-item" type="button"
                                    onclick="exportTableData('delivery_boy_cash_collection','excel')">Excel</button></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="pt-0">
                        <div class="table-responsive">
                            <table class='table' id="delivery_boy_cash_collection" data-loading-template="loadingTemplate"
                                data-toggle="table" data-url="{{ route('delivery_boy.cash.collection.list') }}"
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
