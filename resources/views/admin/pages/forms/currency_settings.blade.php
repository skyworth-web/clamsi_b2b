@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.currency_setting', 'Currency Setting') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp

    <x-admin.breadcrumb :title="labels('admin_labels.currency_setting', 'Currency Setting')" :subtitle="labels(
        'admin_labels.efficiently_organize_and_control_currency',
        'Efficiently Organize and Control Currency',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.currency_setting', 'Currency Setting')],
    ]" />


    <div class="row gy-2">
        <div class="col-xl-6 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-4">
                        {{ labels('admin_labels.add_currency', 'Add Currency') }}
                    </h5>
                    <form id="" action="{{ route('currency_setting.store') }}" class="submit_form"
                        enctype="multipart/form-data" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="">{{ labels('admin_labels.currency_name', 'Currency Name') }}</label>
                                <input type="text" name="name" class="form-control" id="name"
                                    placeholder="India Rupee">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label
                                    for="">{{ labels('admin_labels.currency_symbol', 'Currency Symbol') }}</label>
                                <input type="text" name="symbol" class="form-control" id="symbol" placeholder="₹">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="">{{ labels('admin_labels.currency_code', 'Currency Code') }}</label>
                                <input type="text" name="code" class="form-control" id="code" placeholder="inr">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label
                                    for="">{{ labels('admin_labels.currency_exchange_rate', 'Currency Exchange Rate') }}</label>
                                <input type="number" min="0" max="1000000" name="exchange_rate" step="0.00000001"
                                    class="form-control" id="exchange_rate" placeholder="80">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="tio-add"></i>
                                {{ labels('admin_labels.add_currency', 'Add Currency') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-12">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="mb-4">
                        {{ labels('admin_labels.system_default_currency', 'System Default Currency') }}
                    </h5>
                    <form class="submit_form" action="{{ route('default_currency.set') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-2">
                                    <select class="form-control default_currency select2" name="currency_id">
                                        @foreach ($currencies as $currency)
                                            @php
                                                $selected =
                                                    isset($currency) && !empty($currency) && $currency->is_default == 1
                                                        ? 'selected'
                                                        : '';
                                            @endphp
                                            <option value="{{ $currency->id }}" {{ $selected }}>{{ $currency->name }}
                                            </option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="d-flex justify-content-end flex-wrap gap-10">
                                    <button type="submit"
                                        class="btn btn-primary">{{ labels('admin_labels.save', 'Save') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <form class="submit_form" action="{{ route('exchange_rate_aap_id.store') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-12">
                                    <div class="form-group mb-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <label
                                                for="">{{ labels('admin_labels.exchange_rate_api_id', 'Exchange Rate API ID') }}</label>
                                            <button type="button" class="btn btn-sm btn-primary mb-4"
                                                data-bs-toggle="modal" data-bs-target="#currency_detail_modal">
                                                Where to find exchange rate api id?
                                            </button>
                                        </div>

                                        <div class="modal fade" id="currency_detail_modal" tabindex="-1"
                                            aria-labelledby="exampleModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-body">
                                                        <p>1. You have to sign up at this <a
                                                                href="https://openexchangerates.org/account/app-ids">URL</a>.
                                                        </p>
                                                        <p>2. You will see the plans you can choose from. Opt for the <a
                                                                href="https://openexchangerates.org/signup/free">Free
                                                                Plan</a>, which provides hourly updates (with the base
                                                            currency USD) and up to 1,000 requests/month.</p>
                                                        <p>3. After signing in, you will be able to see the "App IDs" menu
                                                            in the sidebar.</p>
                                                        <p>4. Click on that, and you will be able to see the app ID. Copy
                                                            that ID and paste it into the input box provided below:</p>
                                                        <div class="currency_api_details">
                                                            <img alt=""
                                                                src="{{ getimageurl('system_images/currency_api_details.png') }}" />
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="text" name="exchange_rate_app_id" class="form-control"
                                            id="exchange_rate_app_id" placeholder="" onfocus="focused(this)"
                                            onfocusout="defocused(this)"
                                            value={{ isset($app_id['exchange_rate_app_id']) ? $app_id['exchange_rate_app_id'] : '' }}>
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-12 mt-3">
                                <div class="d-flex justify-content-end flex-wrap gap-10">
                                    <button type="submit"
                                        class="btn btn-primary">{{ labels('admin_labels.save', 'Save') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- edit modal -->

    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">
                        {{ labels('admin_labels.edit_currency', 'Edit Currency') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form enctype="multipart/form-data" method="POST" class="submit_form">
                    @method('PUT')
                    @csrf
                    <input type="hidden" id="edit_currency_id" name="edit_currency_id">
                    <div class="modal-body" role="document">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="">{{ labels('admin_labels.currency_name', 'Currency Name') }}</label>
                                <input type="text" name="name" class="form-control name" id=""
                                    placeholder="India Rupee">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label
                                    for="">{{ labels('admin_labels.currency_symbol', 'Currency Symbol') }}</label>
                                <input type="text" name="symbol" class="form-control symbol" id=""
                                    placeholder="₹">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="">{{ labels('admin_labels.currency_code', 'Currency Code') }}</label>
                                <input type="text" name="code" class="form-control code" id=""
                                    placeholder="inr">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label
                                    for="">{{ labels('admin_labels.currency_exchange_rate', 'Currency Exchange Rate') }}</label>
                                <input type="number" min="0" max="1000000" name="exchange_rate"
                                    step="0.00000001" class="form-control exchange_rate" id=""
                                    placeholder="80">
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{ labels('admin_labels.close', 'Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary"
                            id="save_changes_btn">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- table --}}

    <div class="col-md-12 mt-4">
        <section class="overview-data">
            <div class="card content-area p-4 ">
                <div class="row align-items-center d-flex heading mb-5">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-12 col-lg-6">
                                <h4> {{ labels('admin_labels.currency', 'Currency') }}
                                </h4>
                            </div>
                            <div class="col-md-12 col-lg-6 d-flex justify-content-end">
                                <div class="input-group me-2 search-input-grp ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="admin_currency_table"
                                        class="form-control searchInput" placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="admin_currency_table"
                                    dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                    orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh" data-table="admin_currency_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_currency_table','csv')">CSV</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_currency_table','json')">JSON</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_currency_table','sql')">SQL</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_currency_table','excel')">Excel</button>
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
                                <table class='table' id="admin_currency_table" data-toggle="table"
                                    data-loading-template="loadingTemplate" data-url="{{ route('currency.list') }}"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                    data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">
                                                {{ labels('admin_labels.id', 'ID') }}
                                            </th>
                                            </th>
                                            <th data-field="name" data-disabled="1" data-sortable="false">
                                                {{ labels('admin_labels.name', 'Name') }}
                                            </th>
                                            <th data-field="symbol" data-sortable="false">
                                                {{ labels('admin_labels.symbol', 'Symbol') }}
                                            </th>
                                            <th data-field="exchange_rate" data-sortable="false">
                                                {{ labels('admin_labels.exchange_rate', 'Exchange Rate') }}
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
@endsection
