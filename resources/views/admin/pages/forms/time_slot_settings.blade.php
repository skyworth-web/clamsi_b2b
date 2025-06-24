@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.time_slot_settings', 'Time Slot Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.time_slots', 'Time Slots')" :subtitle="labels(
        'admin_labels.efficiently_organize_and_control_delivery_time_slots',
        'Efficiently Organize and Control Delivery Time Slots',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.time_slot_settings', 'Time Slot Settings')],
    ]" />

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 col-xl-4">
                <div class="card">
                    <form id="" action="{{ route('time_slot_config.store') }}" class="submit_form"
                        enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <h5 class="mb-4">
                                    {{ labels('admin_labels.time_slots', 'Time Slots') }}
                                </h5>
                                <div class="col-md-8 d-flex justify-content-end">
                                    <a class="toggle form-switch me-1 mb-1" title="Deactivate" href="javascript:void(0)">
                                        <input type="checkbox" class="form-check-input" role="switch"
                                            name="is_time_slots_enabled"
                                            <?= @$time_slot_config['is_time_slots_enabled'] == '1' ? 'Checked' : '' ?>>
                                    </a>
                                </div>
                            </div>
                            <div>
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.delivery_starts_from', 'Delivery Starts From') }}
                                    ?<span class="text-asterisks text-sm">*</span></label>
                                <select class="form-control form-select mb-3" name="delivery_starts_from">
                                    <option value="">
                                        {{ labels('admin_labels.select', 'Select') }}
                                    </option>

                                    @php
                                        $days = [
                                            'Today',
                                            'Tomorrow',
                                            'Third Day',
                                            'Fourth Day',
                                            'Fifth Day',
                                            'Sixth Day',
                                            'Seventh Day',
                                        ];
                                    @endphp

                                    @foreach ($days as $index => $day)
                                        @php
                                            $value = $index + 1;
                                            $selected =
                                                isset($time_slot_config['delivery_starts_from']) &&
                                                $time_slot_config['delivery_starts_from'] == $value
                                                    ? 'selected'
                                                    : '';
                                        @endphp

                                        <option value="{{ $value }}" {{ $selected }}>{{ $day }}
                                        </option>
                                    @endforeach

                                </select>

                            </div>
                            <div class="mt-3">

                                <label for=""
                                    class="form-label">{{ labels('admin_labels.how_many_days_you_want_to_allow', 'How many days you want to allow') }}
                                    ? <span class="text-asterisks text-sm">*</span></label>

                                <select class="form-control form-select" name="allowed_days">
                                    <option value="">
                                        {{ labels('admin_labels.select', 'Select') }}
                                    </option>
                                    <option value="1"
                                        <?= isset($time_slot_config['allowed_days']) && $time_slot_config['allowed_days'] == '1' ? 'selected' : '' ?>>
                                        1</option>
                                    <option value="7"
                                        <?= isset($time_slot_config['allowed_days']) && $time_slot_config['allowed_days'] == '7' ? 'selected' : '' ?>>
                                        7</option>
                                    <option value="15"
                                        <?= isset($time_slot_config['allowed_days']) && $time_slot_config['allowed_days'] == '15' ? 'selected' : '' ?>>
                                        15</option>
                                    <option value="30"
                                        <?= isset($time_slot_config['allowed_days']) && $time_slot_config['allowed_days'] == '30' ? 'selected' : '' ?>>
                                        30</option>
                                </select>

                            </div>

                            <div class="d-flex justify-content-end mt-4">
                                <button type="reset"
                                    class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit" class="btn btn-primary ms-2 submit_button"
                                    id="submit_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="d-flex justify-content-center">
                                        <div id="error_box"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12 col-xl-8 mt-md-2 mt-xl-0">
                <div class="card">
                    <form id="" action="{{ route('time_slot.store') }}" class="submit_form"
                        enctype="multipart/form-data" method="POST">
                        @csrf
                        <div class="card-body">
                            <h5 class="mb-4">
                                {{ labels('admin_labels.add_time_slot', 'Add Time Slot') }}
                            </h5>
                            <div class="mb-3">
                                <label for="" class="form-label">{{ labels('admin_labels.title', 'Title') }}<span
                                        class="text-asterisks text-sm">*</span></label>
                                <input type="text" class="form-control" name="title"
                                    value="<?= isset($fetched_data[0]['title']) ? $fetched_data[0]['title'] : '' ?>"
                                    placeholder="Morning 9AM to 12PM">
                            </div>
                            <div class="mb-3">

                                <label for=""
                                    class="form-label">{{ labels('admin_labels.from_time', 'From Time') }}<span
                                        class="text-asterisks text-sm">*</span></label>

                                <input type="time" class="form-control" name="from_time"
                                    value="<?= isset($fetched_data[0]['from_time']) ? $fetched_data[0]['from_time'] : '' ?>"
                                    placeholder="09:00:00">

                            </div>
                            <div class="mb-3">

                                <label for=""
                                    class="form-label">{{ labels('admin_labels.to_time', 'To Time') }}<span
                                        class="text-asterisks text-sm">*</span></label>


                                <input type="time" class="form-control" name="to_time"
                                    value="<?= isset($fetched_data[0]['to_time']) ? $fetched_data[0]['to_time'] : '' ?>"
                                    placeholder="12:00:00">

                            </div>
                            <div class="mb-3">

                                <label for=""
                                    class="form-label">{{ labels('admin_labels.last_order_time', 'Last Order Time') }}<span
                                        class="text-asterisks text-sm">*</span></label>

                                <input type="time" class="form-control" name="last_order_time"
                                    value="<?= isset($fetched_data[0]['last_order_time']) ? $fetched_data[0]['last_order_time'] : '' ?>"
                                    placeholder="11:00:00">

                            </div>
                            <div class="mb-3">

                                <label for="" class="form-label">{{ labels('admin_labels.status', 'Status') }}<span
                                        class="text-asterisks text-sm">*</span></label>

                                <select name="status" class="form-control form-select">
                                    <option value="">
                                        {{ labels('admin_labels.select', 'Select') }}
                                    </option>
                                    <option value="1"
                                        <?= isset($fetched_data[0]['status']) && $fetched_data[0]['status'] == 1 ? 'selected' : '' ?>>
                                        Active</option>
                                    <option value="0"
                                        <?= isset($fetched_data[0]['status']) && $fetched_data[0]['status'] == 0 ? 'selected' : '' ?>>
                                        Deactive</option>
                                </select>

                            </div>
                            <div class="row">
                                <div class="d-flex justify-content-end ">
                                    <button type="reset"
                                        class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                    <button type="submit" class="btn btn-primary submit_button" id="submit_btn">
                                        <?= isset($fetched_data[0]['id']) ? (trans()->has('admin_labels.update_setting') ? trans('admin_labels.update_setting') : 'Update Time Slots') : (trans()->has('admin_labels.add_time_slot') ? trans('admin_labels.add_time_slot') : 'Add Time Slot') ?>
                                    </button>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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
                                <h4> {{ labels('admin_labels.manage_time_slots', 'Manange Time Slots') }}
                                </h4>
                            </div>
                            <div class="col-md-12 col-lg-6 d-flex justify-content-end">
                                <div class="input-group me-2 search-input-grp ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="admin_time_slot_table"
                                        class="form-control searchInput" placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="admin_time_slot_table"
                                    dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                    orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh"data-table="admin_time_slot_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_time_slot_table','csv')">CSV</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_time_slot_table','json')">JSON</button>
                                        </li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_time_slot_table','sql')">SQL</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_time_slot_table','excel')">Excel</button>
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
                                <table class='table' id="admin_time_slot_table" data-toggle="table"
                                    data-loading-template="loadingTemplate" data-url="{{ route('time_slots.list') }}"
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
                                            <th data-field="title" data-disabled="1" data-sortable="false">
                                                {{ labels('admin_labels.title', 'Title') }}
                                            </th>
                                            <th data-field="from_time" data-sortable="true">
                                                {{ labels('admin_labels.from_time', 'From Time') }}
                                            </th>
                                            <th data-field="to_time" data-sortable="true">
                                                {{ labels('admin_labels.to_time', 'To Time') }}
                                            </th>
                                            <th data-field="last_order_time" data-sortable="true">
                                                {{ labels('admin_labels.last_order_time', 'Last Order Time') }}
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
