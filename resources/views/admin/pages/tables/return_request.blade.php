@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.return_requests', 'Return Request') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.return_requests', 'Return Request')" :subtitle="labels(
        'admin_labels.streamline_and_manage_return_requests_with_ease',
        'Streamline and Manage Return Requests with Ease',
    )" :breadcrumbs="[['label' => labels('admin_labels.return_requests', 'Return Request')]]" />
    {{-- table --}}

    <section
        class="overview-data {{ $user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view return_request') ? '' : 'd-none' }}">
        <div class="card content-area p-4 ">
            <div class="row align-items-center d-flex heading mb-5">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-12 col-lg-6">
                            <h4>{{ labels('admin_labels.return_requests', 'Return Request') }}
                            </h4>
                        </div>
                        <div class="col-md-12 col-lg-6 d-flex justify-content-end ">
                            <div class="input-group me-2 search-input-grp ">
                                <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                <input type="text" data-table="admin_return_request_table"
                                    class="form-control searchInput" placeholder="Search...">
                                <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                            </div>
                            <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                data-bs-target="#columnFilterOffcanvas" data-table="admin_return_request_table"
                                dateFilter='false' orderStatusFilter='false' paymentMethodFilter='false'
                                orderTypeFilter='false'><i class='bx bx-filter-alt'></i></a>
                            <a class="btn me-2" id="tableRefresh" data-table="admin_return_request_table"><i
                                    class='bx bx-refresh'></i></a>
                            <div class="dropdown">
                                <a class="btn dropdown-toggle export-btn" type="button" id="exportOptionsDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class='bx bx-download'></i>
                                </a>
                                <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_return_request_table','csv')">CSV</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_return_request_table','json')">JSON</button>
                                    </li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_return_request_table','sql')">SQL</button></li>
                                    <li><button class="dropdown-item" type="button"
                                            onclick="exportTableData('admin_return_request_table','excel')">Excel</button>
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
                            <table class='table' id="admin_return_request_table" data-toggle="table"
                                data-loading-template="loadingTemplate" data-url="{{ route('admin.return_request.list') }}"
                                data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true" data-export-types='["txt","excel"]'
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        <th data-field="order_id" data-disabled="1" data-sortable="true">
                                            {{ labels('admin_labels.order_id', 'Order ID') }}
                                        </th>
                                        <th data-field="order_item_id" data-disabled="1" data-sortable="true">
                                            {{ labels('admin_labels.order_item_id', 'Order Item ID') }}
                                        </th>
                                        <th data-field="delivery_boy_id" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.delivery_boy_id', 'Delivery Boy ID') }}
                                        </th>
                                        <th data-field="user_name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.user_name', 'User Name') }}
                                        </th>
                                        <th data-field="store_name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.store_name', 'Store Name') }}
                                        </th>
                                        <th data-field="product_name" data-disabled="1" data-sortable="false">
                                            {{ labels('admin_labels.product_name', 'Product Name') }}
                                        </th>
                                        <th data-field="price" data-sortable="false">
                                            {{ labels('admin_labels.price', 'Price') }}
                                        </th>
                                        <th data-field="discounted_price" data-sortable="false" data-visible="false">
                                            {{ labels('admin_labels.discounted_price', 'Discounted Price') }}
                                        </th>
                                        <th data-field="quantity" data-sortable="false">
                                            {{ labels('admin_labels.quantity', 'Quantity') }}
                                        </th>
                                        <th data-field="sub_total" data-sortable="false">
                                            {{ labels('admin_labels.sub_total', 'Sub Total') }}
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

    {{-- edit modal --}}

    <div class="modal fade" id="request_request_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        {{ labels('admin_labels.update_return_request', 'Update Return Request') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal submit_form" action="{{ route('admin.return_request.update') }}"
                    method="POST" enctype="multipart/form-data">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="return_request_id" id="return_request_id">
                    <input type="hidden" name="user_id" id="user_id">
                    <input type="hidden" name="order_item_id" id="order_item_id">
                    <input type="hidden" name="delivery_by" id="delivery_by">
                    <div class="modal-body">
                        <div class="form-group">
                            <label for=""
                                class="control-label col-md-3 col-sm-3 col-xs-12">{{ labels('admin_labels.status', 'Status') }}
                                <span class='text-asterisks text-sm'>*</span></label>
                            <div class="col-md-7 col-sm-6 col-xs-12">
                                <div id="status" class="btn-group">
                                    <label class="btn btn-warning mx-2" data-toggle-class="btn-primary"
                                        data-toggle-passive-class="btn-default" id="pending_label">
                                        <input type="radio" name="status" value="0" class='pending mx-1'>
                                        Pending
                                    </label>
                                    <label class="btn btn-danger mx-2" data-toggle-class="btn-primary"
                                        data-toggle-passive-class="btn-default" id="rejected_label">
                                        <input type="radio" name="status" value="2" id="rejected"
                                            class='rejected mx-1'>
                                        Rejected
                                    </label>
                                    <label class="btn btn-primary mx-2" data-toggle-class="btn-primary"
                                        data-toggle-passive-class="btn-default" id="approved_label">
                                        <input type="radio" name="status" value="1" id="approved"
                                            class='approved mx-1'>
                                        Approved
                                    </label>
                                    <label class="btn btn-secondary mx-2" data-toggle-class="btn-primary"
                                        data-toggle-passive-class="btn-default" id="return_pickedup_label">
                                        <input type="radio" name="status" id="return_pickedup" value="8"
                                            class='return_pickedup mx-1'>
                                        Return Pickedup
                                    </label>
                                    <label class="btn btn-success mx-2" data-toggle-class="btn-primary"
                                        data-toggle-passive-class="btn-default" id="returned_label">
                                        <input type="radio" id="returned" name="status" value="3"
                                            class='returned mx-1'>
                                        Returned
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 d-none" id="return_request_delivery_by">
                            <label for=""
                                class="control-label">{{ labels('admin_labels.select_delivery_boy', 'Select Delivery Boy') }}</label>
                            <select id="deliver_by" name="deliver_by" class="form-control form-select">
                                @foreach ($deliveryRes as $row)
                                    <option value="{{ htmlspecialchars($row->id) }}">
                                        {{ htmlspecialchars($row->username) }}</option>
                                @endforeach

                            </select>
                        </div>
                        <div class="form-group mt-2">
                            <label class="" for="">{{ labels('admin_labels.remarks', 'Remark') }}</label>
                            <textarea id="update_remarks" name="update_remarks" class="form-control col-12 "></textarea>
                        </div>
                        <input type="hidden" id="id" name="id">
                        <div class="ln_solid"></div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.update_return_request', 'Update Return Request') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
