<div class="modal fade" id="media-upload-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
    aria-hidden="true" data-bs-backdrop="static" data-bs-focus="false">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">Media</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                                <div class="col-md-12 mt-3 mb-3 mb-5">
                                    <!-- Change /upload-target to your upload address -->
                                    <div id="dropzone" class="dropzone"></div>
                                    <br>
                                    <a href="#" id="upload-files-btn" class="btn btn-success float-end">Upload</a>
                                </div>
                                <div class="alert alert-secondary text-white">Select media and click choose media</div>
                                <div id="toolbar">
                                    <button id="upload-media" class="btn btn-danger">
                                        <i class="fa fa-plus"></i> Choose Media
                                    </button>
                                </div>
                                <table class="table table-striped" data-toolbar="#toolbar" id="media-upload-table"
                                    data-page-size="5" data-toggle="table" data-url="/seller/media/list"
                                    data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true"
                                    data-show-columns="true" data-show-refresh="true" data-trim-on-search="false"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-show-export="true" data-query-params="mediaParams">
                                    <thead>
                                        <tr>
                                            <th data-field="state" data-checkbox="true"></th>
                                            <th data-field="id" data-sortable="true" data-visible="true">ID</th>
                                            <th class="d-flex justify-content-center" data-field="image"
                                                data-sortable="false">Image</th>
                                            <th data-field="name" data-disabled="1" data-sortable="false">Name</th>
                                            <th data-field="size" data-sortable="false">Size</th>
                                            <th data-field="extension" data-sortable="false" data-visible="false">
                                                Extension</th>
                                            <th data-field="sub_directory" data-sortable="false" data-visible="false">
                                                Sub directory</th>
                                            <th data-field="disk" data-sortable="false" data-visible="true">
                                                Disk</th>
                                            <th data-field="object_url" data-sortable="false" data-visible="false">
                                                S3 URL</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


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
                <option value="return_request_approved">Return Request Approved</option>
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

        <div class="cashCollectionTypeFilter mt-5 d-none ">
            <label for=""
                class="form-label body-default">{{ labels('admin_labels.cash_collection_type', 'Cash Collection Type') }}</label>
            <select id="cash_collection_status" name="cash_collection_status" placeholder="Select Status"
                class="form-control">
                <option value="">Select Status</option>
                <option value="delivery_boy_cash">Delivery Boy Cash In Hand</option>
                <option value="delivery_boy_cash_collection">Cash Collected by Admin</option>
            </select>
        </div>

        @php

            use Illuminate\Support\Facades\DB;

            $store_id = getStoreId();
            $sellers = DB::table('seller_store')
                ->leftJoin('seller_data', 'seller_data.id', '=', 'seller_store.seller_id')
                ->leftJoin('users', 'users.id', '=', 'seller_store.user_id')
                ->select('seller_store.*', 'seller_data.*', 'users.username') // Separate table and column names
                ->where('seller_store.store_id', $store_id)
                ->get();
        @endphp

        <div class="sellerFilter mt-5 d-none">
            <label for="" class="form-label body-default">Choose Seller</label>
            <select class='form-control' name='seller_id' id="filterSellerId">
                <option value="">Select Seller</option>
                @foreach ($sellers as $seller)
                    <option value="{{ $seller->id }}">
                        {{ $seller->username }} - {{ $seller->store_name }} (store)
                    </option>
                @endforeach
            </select>
        </div>

    </div>
    <div class="offcanvas-body" id="columnFilterOffcanvasBody">
        <h6>Table Data</h6>

    </div>
    <div class="d-flex justify-content-end mb-3 pe-3">
        <button type="reset" class="btn reset-btn reset_filter_button me-3">Reset Filter</button>
        <button type="button" class="btn form-btn" id="tableFilterBtn">Apply Filter</button>
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
                            Copyright © 2025 <a href="{{ config('app.url') }}">{{ $app_name }}</a>
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
