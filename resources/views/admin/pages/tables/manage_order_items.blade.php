@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.manage_order_items', 'Manage Order Items') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.manage_order_items', 'Manage Order Items')" :subtitle="labels('admin_labels.all_information_about_order_items', 'All Information About Order Items')" :breadcrumbs="[
        ['label' => labels('admin_labels.manage_orders', 'Manage Orders')],
        ['label' => labels('admin_labels.order_items', 'Order Items')],
    ]" />


    <input type='hidden' id='order_user_id' value='<?= isset($user_id) && !empty($user_id) ? $user_id : '' ?>'>

    <!-- modal for assign tracking data for order -->

    <div class="modal fade" id="edit_order_tracking_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="user_name">
                        {{ labels('admin_labels.order_tracking', 'Order Tracking') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal submit_form" id="order_tracking_form"
                    action="{{ route('admin.orders.update_order_tracking') }}" method="POST" enctype="multipart/form-data">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="order_id" id="order_id">
                    <input type="hidden" name="order_item_id" id="order_item_id">
                    <input type="hidden" name="seller_id" id="seller_id">
                    <input type="hidden" id="edit_zipcode_id" name="edit_zipcode_id">
                    <div class="modal-body">
                        <div class="form-group ">
                            <label class="mb-2 mt-2"
                                for="courier_agency">{{ labels('admin_labels.courier_agency', 'Courier Agency') }}</label>
                            <input type="text" class="form-control" name="courier_agency" id="courier_agency"
                                placeholder="Courier Agency" />
                        </div>
                        <div class="form-group">
                            <label class="mb-2 mt-2"
                                for="tracking_id">{{ labels('admin_labels.tracking_id', 'Tracking ID') }}</label>
                            <input type="text" class="form-control" name="tracking_id" id="tracking_id"
                                placeholder="Tracking Id" />
                        </div>
                        <div class="form-group ">
                            <label class="mb-2 mt-2" for="url">{{ labels('admin_labels.url', 'URL') }}</label>
                            <input type="text" class="form-control" name="url" id="url" placeholder="URL" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary submit_button"
                                id="submit_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>




    {{-- table --}}

    <div class="card">
        <div class="card-body">

            <section class="overview-data">
                <div class="card content-area p-4 ">
                    <div class="row align-items-center d-flex heading mb-5">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-8">

                                </div>
                                <div class="col-md-4 d-flex justify-content-end">
                                    <div class="input-group me-3 search-input-grp">
                                        <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                        <input type="text" data-table="admin_order_items_table"
                                            class="form-control searchInput" placeholder="Search...">
                                        <span class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                    </div>
                                    <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                        data-bs-target="#columnFilterOffcanvas" data-table="admin_order_items_table"
                                        dateFilter='true' orderStatusFilter='true' paymentMethodFilter='true'
                                        orderTypeFilter='true' sellerFilter='true'><i class='bx bx-filter-alt'></i></a>
                                    <a class="btn me-3" id="tableRefresh" data-table="admin_order_items_table"><i
                                            class='bx bx-refresh'></i></a>
                                    <div class="dropdown">
                                        <a class="btn dropdown-toggle export-btn" type="button"
                                            id="exportOptionsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-download'></i>
                                        </a>
                                        <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_order_items_table','csv')">CSV</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_order_items_table','json')">JSON</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_order_items_table','sql')">SQL</button>
                                            </li>
                                            <li><button class="dropdown-item" type="button"
                                                    onclick="exportTableData('admin_order_items_table','excel')">Excel</button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table id="admin_order_items_table" class="table" data-toggle="table"
                                    data-loading-template="loadingTemplate"
                                    data-url="{{ route('admin.orders.item_list') }}" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                    data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                    data-sort-name="o.id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                    data-query-params="orders_query_params">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable='true'
                                                data-footer-formatter="totalFormatter">
                                                {{ labels('admin_labels.id', 'ID') }}
                                            <th data-field="order_item_id" data-disabled="1" data-sortable='true'>
                                                {{ labels('admin_labels.order_item_id', 'Order Item ID') }}
                                            </th>
                                            <th data-field="order_id" data-disabled="1" data-sortable='true'>
                                                {{ labels('admin_labels.order_id', 'Order ID') }}
                                            </th>
                                            <th data-field="user_id" data-sortable='true' data-visible="false">
                                                {{ labels('admin_labels.user_id', 'User ID') }}
                                            </th>
                                            <th data-field="seller_id" data-sortable='true' data-visible="false">
                                                {{ labels('admin_labels.seller_id', 'Seller ID') }}
                                            </th>
                                            <th data-field="is_credited" data-sortable='false' data-visible="false">
                                                {{ labels('admin_labels.comission', 'Comission') }}
                                            </th>
                                            <th data-field="quantity" data-sortable='false' data-visible="false">
                                                {{ labels('admin_labels.quantity', 'Quantity') }}
                                            </th>
                                            <th data-field="username" data-disabled="1" data-sortable='false'>
                                                {{ labels('admin_labels.user_name', 'User Name') }}
                                            </th>
                                            <th data-field="seller_name" data-disabled="1" data-sortable='false'>
                                                {{ labels('admin_labels.seller', 'Seller') }}
                                            </th>
                                            <th data-field="product_name" data-disabled="1" data-sortable='false'>
                                                {{ labels('admin_labels.product_name', 'Product Name') }}
                                            </th>
                                            <th data-field="mobile" data-sortable='false' data-visible='false'>
                                                {{ labels('admin_labels.mobile', 'Mobile') }}
                                            </th>
                                            <th data-field="sub_total" data-sortable='false' data-visible="true">
                                                {{ labels('admin_labels.total', 'Total') }}(<?= $currency ?>)
                                            </th>
                                            <th data-field="delivery_boy" data-sortable='false' data-visible='false'>
                                                {{ labels('admin_labels.deliver_by', 'Deliver By') }}
                                            </th>
                                            <th data-field="delivery_boy_id" data-sortable='false' data-visible='false'>
                                                {{ labels('admin_labels.delivery_boy_id', 'Delivery Boy ID') }}
                                            </th>
                                            <th data-field="product_variant_id" data-sortable='false'
                                                data-visible='false'>
                                                {{ labels('admin_labels.product_variant_id', 'Product Variant ID') }}
                                            </th>
                                            <th data-field="delivery_date" data-sortable='true' data-visible='false'>
                                                {{ labels('admin_labels.delivery_date', 'Delivery Date') }}
                                            </th>
                                            <th data-field="delivery_time" data-sortable='false' data-visible='false'>
                                                {{ labels('admin_labels.delivery_time', 'Delivery Time') }}
                                            </th>
                                            <th data-field="updated_by" data-sortable='false' data-visible="false">
                                                {{ labels('admin_labels.updated_by', 'Updated By') }}
                                            </th>
                                            <th data-field="active_status" data-sortable='false' data-visible='true'>
                                                {{ labels('admin_labels.active_status', 'Active Status') }}
                                            </th>
                                            <th data-field="transaction_status" data-sortable='false'
                                                data-visible='false'>
                                                {{ labels('admin_labels.transaction_status', 'Transaction Status') }}
                                            </th>
                                            <th data-field="date_added" data-sortable='true'>
                                                {{ labels('admin_labels.order_date', 'Order Date') }}
                                            </th>
                                            <th data-field="mail_status" data-sortable='false' data-visible='false'>
                                                {{ labels('admin_labels.mail_status', 'Mail Status') }}
                                            </th>
                                            <th data-field="operate" data-sortable='false'>
                                                {{ labels('admin_labels.action', 'Action') }}
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
