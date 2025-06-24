@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.ticket', 'Ticket') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.ticket', 'Ticket')" :subtitle="labels(
        'admin_labels.streamline_support_with_efficient_ticket_management',
        'Streamline Support with Efficient Ticket Management',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.support_tickets', 'Support Tickets')],
        ['label' => labels('admin_labels.manage_tickets', 'Manage Tickets')],
    ]" />

    <div
        class="col-md-12 {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view tickets') ? '' : 'd-none' }}">
        <section class="overview-data">
            <div class="card content-area p-4 ">
                <div class="row align-items-center d-flex heading mb-5">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-sm-12 col-lg-6">
                                <h4> {{ labels('admin_labels.manage_tickets', 'Manage Tickets') }}
                                </h4>
                            </div>
                            <div class="col-sm-12 col-lg-6 d-flex justify-content-end mt-sm-2">
                                <div class="input-group me-2 search-input-grp ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" data-table="admin_ticket_table" class="form-control searchInput"
                                        placeholder="Search...">
                                    <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                </div>
                                <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                    data-bs-target="#columnFilterOffcanvas" data-table="admin_ticket_table"
                                    dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                    orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                                <a class="btn me-2" id="tableRefresh"data-table="admin_ticket_table"><i
                                        class='bx bx-refresh'></i></a>
                                <div class="dropdown">
                                    <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_ticket_table','csv')">CSV</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_ticket_table','json')">JSON</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_ticket_table','sql')">SQL</button></li>
                                        <li><button class="dropdown-item" type="button"
                                                onclick="exportTableData('admin_ticket_table','excel')">Excel</button></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-primary btn-sm delete_selected_data"
                            data-table-id="admin_ticket_table"
                            data-delete-url="{{ route('tickets.delete') }}">{{ labels('admin_labels.delete_selected', 'Delete Selected') }}</button>
                    </div>
                    <div class="col-md-12">
                        <div class="pt-0">
                            <div class="table-responsive">
                                <table class='table' id="admin_ticket_table" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('admin.tickets.getTicketList') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                    data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                    data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                    data-show-export="false" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            <th data-checkbox="true" data-field="delete-checkbox">
                                                <input name="select_all" type="checkbox">
                                            </th>
                                            <th data-field="id" data-sortable="true">
                                                {{ labels('admin_labels.id', 'ID') }}
                                            <th data-field="ticket_type_id" data-sortable="false" data-visible="false">
                                                {{ labels('admin_labels.ticket_type_id', 'Ticket Type ID') }}
                                            </th>
                                            <th data-field="ticket_type" data-disabled="1" data-sortable="false">
                                                {{ labels('admin_labels.ticket_type', 'Ticket Type') }}
                                            </th>
                                            <th data-field="user_id" data-sortable="true" data-visible="false">
                                                {{ labels('admin_labels.user_id', 'User ID') }}
                                            </th>
                                            <th data-field="username" data-disabled="1" data-sortable="true">
                                                {{ labels('admin_labels.user_name', 'User Name') }}
                                            </th>
                                            <th data-field="subject" data-disabled="1" data-sortable="false">
                                                {{ labels('admin_labels.subject', 'subject') }}
                                            </th>
                                            <th data-field="email" data-sortable="false">
                                                {{ labels('admin_labels.email', 'Email') }}
                                            </th>
                                            <th data-field="description" data-sortable="false">
                                                {{ labels('admin_labels.description', 'Description') }}
                                            </th>
                                            <th data-field="status" data-sortable="false">
                                                {{ labels('admin_labels.status', 'Status') }}
                                            </th>
                                            <th data-field="last_updated" data-sortable="false" data-visible="false">
                                                {{ labels('admin_labels.last_updated', 'Last Updated') }}
                                            </th>
                                            <th data-field="date_created" data-sortable="false">
                                                {{ labels('admin_labels.date_created', 'Date Created') }}
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
    {{-- edit modal --}}


    <div class="modal fade" id="ticket_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog  modal-xl modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="user_name"></h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal " id="ticket_send_msg_form"
                    action="{{ route('admin.tickets.sendMessage') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" name="user_id" id="user_id">
                    <input type="hidden" name="user_type" id="user_type">
                    <input type="hidden" name="ticket_id" id="ticket_id">

                    <div class="modal-body">
                        <div class="modal-body">
                            <div class="card direct-chat direct-chat-primary">
                                <div class="card-header ui-sortable-handle move">
                                    <h5 class="" id="ticket_type">
                                    </h5>
                                    <h6 class="card-title" id="subject"></h6>
                                    <span id="status"></span><br>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <p id="date_created"></p>
                                        </div>
                                        <div class="col-md-4">

                                            <select class="form-control form-select w-100 change_ticket_status"
                                                name="status">
                                                <option value="">
                                                    {{ labels('admin_labels.change_ticket_status', 'Change Ticket Status') }}
                                                </option>
                                                <option value={{ 2 }}>OPEN</option>
                                                <option value={{ 3 }}>RESOLVE</option>
                                                <option value={{ 4 }}>CLOSE</option>
                                                <option value={{ 5 }}>REOPEN</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                @php
                                    $offset = 0;
                                    $limit = 15;
                                @endphp
                                <div class="card-body">
                                    <div class="direct-chat-messages" id="element">
                                        <div class="ticket_msg" data-limit="<?= $limit ?>" data-offset="<?= $offset ?>"
                                            data-max-loaded="false">
                                        </div>
                                        <div class="scroll_div"></div>
                                    </div>
                                </div>

                                <div class="card-footer">
                                    <div class="row">
                                        <div class="input-group">
                                            <input type="hidden" name="user_id" id="user_id">
                                            <input type="hidden" name="user_type" id="user_type">
                                            <input type="hidden" name="ticket_id" id="ticket_id">
                                            <input type="text" class="form-control" name="message" id="message_input"
                                                placeholder="Type Message ...">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="input-group-append">
                                            <div class="form-group">
                                                <div class="container-fluid row image-upload-section">
                                                </div>
                                                <div class="d-flex">
                                                    <a class="uploadFile img btn btn-primary text-white mx-2"
                                                        data-input='attachments[]' data-isremovable='1'
                                                        data-is-multiple-uploads-allowed='1' data-bs-toggle="modal"
                                                        data-bs-target="#media-upload-modal" value="Upload Photo"> <i
                                                            class="fa fa-paperclip"></i></a>
                                                    <button type="submit" class="btn btn-primary ml-2"
                                                        id="submit_btn">Send</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
