<div class="modal fade" id="media-upload-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Media</h5>
                <div class="d-flex justify-content-end"><button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="Close"></button></div>
            </div>
            <div class="modal-body">
                <div class="col-md-12 main-content">
                    <div class="content-area p-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="g-1-5x"></div>
                                <input type="hidden" name="media_type" id="media_type" value="image">
                                <input type="hidden" name="current_input">
                                <input type="hidden" name="remove_state">
                                <input type="hidden" name="multiple_images_allowed_state">

                                <div class="row">
                                    <div class="col-md-12 mt-3 mb-5">
                                        <form action="{{ route('seller.media.upload') }}" class="media_submit_form"
                                            enctype="multipart/form-data" method="POST">
                                            @csrf
                                            <input type="file" class="filepond" name="documents[]" multiple
                                                data-max-file-size="30MB" data-max-files="20" />
                                            <button type="submit"
                                                class="btn btn-primary float-end mt-3">{{ labels('admin_labels.upload', 'Upload') }}</button>
                                        </form>
                                    </div>
                                </div>

                                <div class="alert alert-secondary text-dark">Select media and click choose media</div>

                                <div class="row align-items-center d-flex heading mb-5">
                                    <div class="col-md-6">
                                        <button id="upload-media" class="btn btn-danger">
                                            <i class="fa fa-plus"></i> Choose Media
                                        </button>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end ">
                                        <div class="input-group me-3 search-input-grp ">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" data-table="media-upload-table"
                                                class="form-control searchInput" placeholder="Search...">
                                            <span
                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                        </div>
                                        <a class="btn me-3" id="tableRefresh" data-table="media-upload-table"><i
                                                class='bx bx-refresh'></i></a>
                                        <div class="dropdown">
                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                aria-expanded="false">
                                                <i class='bx bx-download'></i>
                                            </a>
                                            <ul class="dropdown-menu" aria-labelledby="exportOptionsDropdown">
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('media-upload-table','csv')">CSV</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('media-upload-table','json')">JSON</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('media-upload-table','sql')">SQL</button>
                                                </li>
                                                <li><button class="dropdown-item" type="button"
                                                        onclick="exportTableData('media-upload-table','excel')">Excel</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <table class="table table-striped" data-toolbar="#toolbar" id="media-upload-table"
                                        data-page-size="5" data-toggle="table"
                                        data-url="{{ route('admin.media.list') }}" data-click-to-select="true"
                                        data-side-pagination="server" data-pagination="true"
                                        data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                        data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                        data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                        data-show-export="false" data-query-params="mediaParams">
                                        <thead>
                                            <tr>
                                                <th data-field="state" data-checkbox="true"></th>
                                                <th data-field="id" data-sortable="true" data-visible="true">ID</th>
                                                <th class="d-flex justify-content-center" data-field="image"
                                                    data-sortable="false">Image</th>
                                                <th data-field="name" data-disabled="1" data-sortable="false">Name
                                                </th>
                                                <th data-field="size" data-sortable="false">Size</th>
                                                <th data-field="extension" data-sortable="false" data-visible="true">
                                                    Extension</th>
                                                <th data-field="sub_directory" data-sortable="false"
                                                    data-visible="true">
                                                    Sub directory</th>
                                                <th data-field="disk" data-sortable="false" data-visible="true">
                                                    Disk</th>
                                                <th data-field="object_url" data-sortable="false"
                                                    data-visible="false">
                                                    S3 URL</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>

                            </div><!-- .card-body -->
                        </div><!-- .card -->
                    </div><!-- .content-area -->
                </div><!-- .col-md-12 -->
            </div><!-- .modal-body -->
        </div><!-- .modal-content -->
    </div><!-- .modal-dialog -->
</div><!-- .modal -->


<!-- filter offcanvas -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="filtersOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">{{ labels('admin_labels.filters', 'Filters') }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>

    <div class="container-fluid table-filter-section mb-8">

        <div class="dateRangeFilter d-none mt-5">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.date_range', 'Date Range') }}</label>
            <div id="datepicker">
                <i class='bx bxs-calendar'></i>&nbsp;
                <span></span> <i class="fa fa-caret-down"></i>
            </div>
        </div>

        <div class="orderStatusFilter mt-5 d-none">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.select_status', 'Select Status') }}</label>
            <select id="order_status" name="order_status" placeholder="Select Status" class="form-select">
                <option value="">All Orders</option>
                <option value="received">Received</option>
                <option value="processed">Processed</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
                <option value="returned">Returned</option>
            </select>
        </div>

        <div class="paymentMethodFilter mt-5 d-none">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.payment_method', 'Payment Method') }}</label>
            <select id="payment_method" name="payment_method" placeholder="Select Payment Method"
                class="form-control">
                <option value="">All Payment Methods</option>
                <option value="COD">Cash On Delivery</option>
                <option value="Paypal">Paypal</option>
                <option value="RazorPay">RazorPay</option>
                <option value="Paystack">Paystack</option>
                <option value="Flutterwave">Flutterwave</option>`
                <option value="Paytm">Paytm</option>
                <option value="Stripe">Stripe</option>
                <option value="bank_transfer">Direct Bank Transfers</option>
            </select>
        </div>

        <div class="orderTypeFilter mt-5 d-none">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.order_type', 'Order Type') }}</label>
            <select id="order_type" name="order_type" placeholder="Select Order Type" class="form-control">
                <option value="">All Orders</option>
                <option value="physical_order">Physical Orders</option>
                <option value="digital_order">Digital Orders</option>
            </select>
        </div>

        <div class="categoryFilter mt-5 d-none ">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.select_category', 'Select Category') }}</label>

            <div class="col-md-12 search_seller_category">

            </div>
        </div>

        <div class="productStatusFilter mt-5 d-none ">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.product_status', 'Product Status') }}</label>
            <select class='form-select' name='status' id="status_filter">
                <option value=''>Select Status</option>
                <option value='1'>Approved</option>
                <option value='2'>Not-Approved</option>
                <option value='0'>Deactivated</option>
            </select>
        </div>
        <div class="StatusFilter mt-5 d-none ">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.select_status', 'Select Status') }}</label>
            <select class='form-select' name='status' id="statusFilter">
                <option value=''>Select Status</option>
                <option value='1'>Active</option>
                <option value='0'>Deactive</option>
            </select>
        </div>

        <div class="productTypeFilter mt-5 d-none ">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.product_type', 'Product Type') }}</label>
            <select class='form-select' name='product_type' id="product_type_filter">
                <option value=''>Select Type</option>
                <option value='simple_product'>Simple Product</option>
                <option value='variable_product'>Variable Product</option>
                <option value='digital_product'>Digital Product</option>
            </select>
        </div>
        <div class="brandFilter mt-5 d-none ">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.select_brand', 'Select Brand') }}</label>
            <select class="form-select admin_brand_list" id="admin_brand_list" name="brand">
            </select>
        </div>

        <div class="paymentRequestStatusFilter mt-5 d-none ">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.payment_request_status', 'Payment Request Status') }}</label>
            <select class='form-select' name='payment_request_status' id="payment_request_status_filter">
                <option value=''>Select Status</option>
                <option value='0'>Pending</option>
                <option value='1'>Approved</option>
                <option value='2'>Rejected</option>
            </select>
        </div>

    </div>
    <div class="offcanvas-body" id="columnFilterOffcanvasBody">
        <h6>Table Data</h6>

    </div>
    <!-- <a class="btn btn-primary" id="tableFilterBtn">Apply Filters</a> -->
    <div class="d-flex justify-content-end mb-3 pe-3">
        <button type="reset"
            class="btn reset-btn reset_filter_button me-3">{{ labels('admin_labels.reset_filter', 'Reset Filter') }}</button>
        <button type="button" class="btn form-btn"
            id="tableFilterBtn">{{ labels('admin_labels.apply_filter', 'Apply Filter') }}</button>
    </div>
</div>
@php
    $settings = getSettings('system_settings', true, true);
    $settings = json_decode($settings, true);
    $app_name = isset($settings['app_name']) && !empty($settings['app_name']) ? $settings['app_name'] : 'Eshop Plus';
@endphp
<footer class="footer mt-4 py-3 bg-body">
    <div class="px-4">
        <div class="col-12">
            <div class="text-center">
                <div class="row">
                    <div class="col-md-6">
                        <span class="copyright">
                            Copyright © 2025 <a href="{{ config('app.url') }}">{{$app_name}}</a>
                            All
                            rights reserved.
                        </span>
                    </div>
                    <div class="col-md-6 text-end">
                        <span class="text-end text-muted">
                            <span class="badge bg-primary footer-version-badge d-inline">V.
                                {{ get_current_version() }}</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>
