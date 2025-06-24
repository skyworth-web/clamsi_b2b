@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.custom_message', 'Custom Message') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.custom_message', 'Custom Message')" :subtitle="labels(
        'admin_labels.craft_personalized_messages_with_custom_message_management',
        'Craft Personalized Messages with Custom Message Management',
    )" :breadcrumbs="[['label' => labels('admin_labels.custom_message', 'Custom Message')]]" />

    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <h5>
                            {{ labels('admin_labels.add_custom_message', 'Add Custom Message') }}
                        </h5>
                    </div>
                    <form class="form-horizontal submit_form" action="{{ route('custom_message.store') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class=" card-body">
                            @php
                                $type = [
                                    'place_order',
                                    'settle_cashback_discount',
                                    'settle_seller_commission',
                                    'customer_order_received',
                                    'customer_order_processed',
                                    'customer_order_shipped',
                                    'customer_order_delivered',
                                    'customer_order_cancelled',
                                    'customer_order_returned',
                                    'customer_order_returned_request_decline',
                                    'customer_order_returned_request_approved',
                                    'delivery_boy_order_deliver',
                                    'wallet_transaction',
                                    'ticket_status',
                                    'ticket_message',
                                    'bank_transfer_receipt_status',
                                    'bank_transfer_proof',
                                ];
                            @endphp

                            <div class="form-group row">
                                <label for="type" class="form-label mb-2 mt-2">
                                    {{ labels('admin_labels.type', 'Type') }}<span class="text-danger text-sm"> *</span>
                                </label>
                                <div class="col-sm-12">
                                    <select name="type" class="form-control custom_message_type form-select">
                                        <option value=" ">{{ labels('admin_labels.select_type', 'Select Type') }}
                                        </option>
                                        @foreach ($type as $row)
                                            <option value="{{ $row }}">
                                                {{ ucwords(str_replace('_', ' ', $row)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="title"
                                    class="form-label mb-2 mt-2">{{ labels('admin_labels.title', 'Title') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-12">
                                    <input type="text" name="title" id="custom_message_title"
                                        class="form-control custom_message_title" placeholder="Title" value="" />
                                </div>
                            </div>
                            <div class="form-group row place_order d-none">
                                @php
                                    $hashtag = ['< order_id >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag_input">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row">
                                <label for="message"
                                    class="form-label mb-2 mt-2">{{ labels('admin_labels.message', 'Message') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-12">
                                    <textarea name="message" id="text-box" class="form-control" placeholder="Place some text here"></textarea>
                                </div>
                            </div>
                            <div class="form-group row place_order d-none">

                                @php
                                    $hashtag = ['< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row settle_cashback_discount d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row settle_seller_commission d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_received d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_processed d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_shipped d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_delivered d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_cancelled d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_returned d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_returned_request_approved d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row customer_order_returned_request_decline d-none">
                                @php
                                    $hashtag = ['< customer_name >', '< order_item_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row wallet_transaction d-none">
                                @php
                                    $hashtag = ['< currency >', '< returnable_amount >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row ticket_status d-none">
                                @php
                                    $hashtag = ['< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row ticket_message d-none">
                                @php
                                    $hashtag = ['< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row bank_transfer_receipt_status d-none">
                                @php
                                    $hashtag = ['< status >', '< order_id >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="form-group row bank_transfer_proof d-none">
                                @php
                                    $hashtag = ['< order_id >', '< application_name >'];
                                @endphp

                                @foreach ($hashtag as $row)
                                    <div class="col">
                                        <div class="hashtag">{{ $row }}</div>
                                    </div>
                                @endforeach

                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="reset"
                                    class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.add_custom_message', 'Add Custom Message') }}</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div
                class="col-lg-8 col-md-12 mt-md-2 mt-sm-2 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view custom_message') ? '' : 'd-none' }}">

                <section class="overview-data">
                    <div class="card content-area p-4 ">
                        <div class="row align-items-center d-flex heading mb-5">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12 col-lg-6">
                                        <h4>{{ labels('admin_labels.manage_custom_message', 'Manage Custom Message') }}
                                        </h4>
                                    </div>
                                    <div class="col-md-12 col-lg-6 d-flex justify-content-end mt-md-0 mt-sm-2">
                                        <div class="input-group me-2 search-input-grp ">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" data-table="admin_custom_message_table"
                                                class="form-control searchInput" placeholder="Search...">
                                            <span
                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                        </div>
                                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                            data-bs-target="#columnFilterOffcanvas"
                                            data-table="admin_custom_message_table" dateFilter='false'
                                            orderStatusFilter='false' paymentMethodFilter='false'
                                            orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                        <a class="btn me-2" id="tableRefresh"data-table="admin_custom_message_table"><i
                                                class='bx bx-refresh'></i></a>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_custom_message_table','csv')">CSV</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_custom_message_table','json')">JSON</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_custom_message_table','sql')">SQL</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('admin_custom_message_table','excel')">Excel</button>
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
                                    data-table-id="admin_custom_message_table"
                                    data-delete-url="{{ route('custom_messages.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                            </div>
                            <div class="col-md-12">
                                <div class="pt-0">
                                    <div class="table-responsive">
                                        <table class='table' id="admin_custom_message_table" data-toggle="table"
                                            data-loading-template="loadingTemplate"
                                            data-url="{{ route('custom_message.list') }}" data-click-to-select="true"
                                            data-side-pagination="server" data-pagination="true"
                                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                            data-show-columns="false" data-show-refresh="false"
                                            data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                            data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                            data-maintain-selected="true" data-export-types='["txt","excel"]'
                                            data-query-params="queryParams">
                                            <thead>
                                                <tr>
                                                    <th data-checkbox="true" data-field="delete-checkbox">
                                                        <input name="select_all" type="checkbox">
                                                    </th>
                                                    <th data-field="id" data-sortable="true">
                                                        {{ labels('admin_labels.id', 'ID') }}
                                                    <th data-field="title" data-disabled="1" data-sortable="false"
                                                        data-visible='true'>
                                                        {{ labels('admin_labels.title', 'Title') }}
                                                    </th>
                                                    <th data-field="message" data-sortable="false">
                                                        {{ labels('admin_labels.message', 'Message') }}</th>
                                                    <th data-field="type" data-sortable="false">
                                                        {{ labels('admin_labels.type', 'Type') }}</th>
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

    <!-- edit modal -->
    <div class="modal fade" id="edit_modal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
        aria-hidden="true">
        <form enctype="multipart/form-data" method="POST" class="submit_form">
            @method('PUT')
            @csrf
            <input type="hidden" id="edit_message_id" name="edit_message_id">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Message</h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" class="form-control custom_message_type form-select">
                                <option value=" ">Select Type</option>
                                @foreach ($type as $row)
                                    <option value="{{ $row }}">
                                        {{ ucwords(str_replace('_', ' ', $row)) }}
                                    </option>
                                @endforeach

                            </select>
                        </div>
                        <div class="form-group">
                            <label for="title">Title</label>
                            <input type="text" class="form-control title" id="" name="title">
                        </div>
                        <div class="form-group">
                            <label for="message">{{ labels('admin_labels.message', 'Message') }}</label>
                            <input type="text" class="form-control message" id="" name="message">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                            {{ labels('admin_labels.close', 'Close') }}
                        </button>
                        <button type="submit" class="btn btn-primary"
                            id="save_changes_btn">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
