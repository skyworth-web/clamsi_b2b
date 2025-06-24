@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.order_manage', 'Order Manage') }}
@endsection
@section('content')
    <section class="main-content">
        <x-seller.breadcrumb :title="labels('admin_labels.order_details', 'Order Details')" :subtitle="labels('admin_labels.every_detail_at_your_fingertips', 'Every detail at your fingertips')" :breadcrumbs="[
            ['label' => labels('admin_labels.order_manage', 'Order Manage')],
            ['label' => labels('admin_labels.order', 'Order')],
            ['label' => labels('admin_labels.order_details', 'Order Details')],
        ]" />
        {{-- @dd($order_detls[0]); --}}
        <section>
            <div class="card content-area p-3">
                <div class="align-items-center d-flex justify-content-between">
                    <div>
                        <span
                            class="body-default text-muted">{{ labels('admin_labels.order_number', 'Order Number') }}</span>
                        <p class="lead">#{{ $order_detls[0]->id }}</p>
                    </div>
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="body-default text-muted">
                                {{ labels('admin_labels.order_date', 'Order Date') }} :
                            </span>
                            <span class="body-default ms-2">
                                {{ date('d M, Y', strtotime($order_detls[0]->created_at)) }}
                            </span>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="body-default text-muted">
                                {{ labels('admin_labels.order_note', 'Order Note') }} :
                            </span>
                            <span class="body-default ms-2">
                                {{ $order_detls[0]->notes ?? '' }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>

            <div class="row mt-5 order-info">
                @if ($is_customer_privacy_permission == 1)
                    <div class="col-md-4">
                        <div class="card">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h6>{{ labels('admin_labels.customer_info', 'Customer Info') }}</h6>
                                    <div class="d-flex mt-3 align-items-center">
                                        <span class="body-default me-1">{{ labels('admin_labels.name', 'Name') }}:</span>
                                        <span class="caption text-muted">{{ $order_detls[0]->user_name }}</span>
                                    </div>

                                    <div class="d-flex mt-2 align-items-center">
                                        <span
                                            class="body-default me-1">{{ labels('admin_labels.mobile', 'Contact') }}:</span>
                                        @if (!empty($order_detls[0]->mobile))
                                            <span class="caption text-muted">{{ $order_detls[0]->mobile }}</span>
                                        @else
                                            <span
                                                class="caption text-muted">{{ !empty($mobile_data[0]->mobile) ? $mobile_data[0]->mobile : 'N/A' }}</span>
                                        @endif
                                    </div>

                                    <div class="d-flex mt-2 align-items-center">
                                        <span class="body-default me-1">{{ labels('admin_labels.email', 'Email') }}:</span>
                                        <span class="caption text-muted">{{ $order_detls[0]->email }}</span>
                                    </div>
                                </div>
                                <div>
                                    <img src="{{ $items[0]['user_profile'] }}" class="customer-img-box">
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-4">
                    <div class="card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>{{ labels('admin_labels.shipping_info', 'Shipping Info') }}</h6>
                                <div class="d-flex mt-3 align-items-center">
                                    <span class="body-default me-1">{{ labels('admin_labels.name', 'Name') }}:</span>
                                    <span class="caption text-muted">{{ $order_detls[0]->user_name }}</span>
                                </div>

                                <div class="d-flex mt-2 align-items-center">
                                    <span class="body-default me-1">{{ labels('admin_labels.mobile', 'Contact') }}:</span>
                                    @if ($order_detls[0]->mobile != '' && isset($order_detls[0]->mobile))
                                        <span class="caption text-muted">{{ $order_detls[0]->mobile }}</span>
                                    @else
                                        <span class="caption text-muted">{{ $mobile_data[0]->mobile }}</span>
                                    @endif
                                </div>
                                <div class="d-flex mt-2 align-items-center">
                                    <span class="body-default me-1">{{ labels('admin_labels.address', 'Address') }}:</span>
                                    <span class="caption text-muted">{{ $order_detls[0]->address }}</span>
                                </div>
                            </div>
                            <div>
                                <img src="{{ $items[0]['user_profile'] }}" class="customer-img-box">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6>{{ labels('admin_labels.seller_info', 'Seller Info') }}</h6>
                                <div class="d-flex mt-3 align-items-center">
                                    <span
                                        class="body-default me-1">{{ labels('admin_labels.seller_name', 'Seller Name') }}:</span>
                                    <span class="caption text-muted">{{ $sellers[0]['seller_name'] }}</span>
                                </div>

                                <div class="d-flex mt-2 align-items-center">
                                    <span class="body-default me-1">{{ labels('admin_labels.mobile', 'Contact') }}:</span>
                                    <span class="caption text-muted">{{ $sellers[0]['seller_mobile'] }}</span>
                                </div>
                                <div class="d-flex mt-2 align-items-center">
                                    <span class="body-default me-1">{{ labels('admin_labels.email', 'Email') }}:</span>
                                    <span class="caption text-muted">{{ $sellers[0]['seller_email'] }}</span>
                                </div>
                            </div>
                            <div>
                                <img src="{{ !empty($sellers[0]['shop_logo']) ? getmediaimageurl($sellers[0]['shop_logo']) : '' }}"
                                    class="customer-img-box">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="row mt-5 order-detail">
                <div class="col-lg-12 col-xl-12">
                    <div class="card ">
                        <div class="nav nav-tabs" id="product-tab" role="tablist">
                            <a class="nav-item nav-link active" id="order-items-tab" data-bs-toggle="tab"
                                href="#order-items" role="tab" aria-controls="order-items"
                                aria-selected="true">{{ labels('admin_labels.order_items', 'Order Items') }}</a>
                            @if ($items[0]['product_type'] != 'digital_product' && empty($order_tracking_data[0]['shipment_id']))
                                <a class="nav-item nav-link" id="shipments-tab" data-bs-toggle="tab" href="#shipments"
                                    role="tab" aria-controls="shipments"
                                    aria-selected="false">{{ labels('admin_labels.shipments', 'Shipments') }}</a>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="order-items" role="tabpanel"
                                    aria-labelledby="order-items-tab">
                                    <table
                                        class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table w-100 edit-order-table">
                                        <thead>
                                            {{-- @dd($items); --}}
                                            <tr>
                                                @if ($items[0]['product_type'] == 'digital_product')
                                                    <th></th>
                                                @endif
                                                <th>
                                                    {{ labels('admin_labels.id', 'Id') }}
                                                </th>
                                                <th>{{ labels('admin_labels.name', 'Name') }}</th>
                                                <th>{{ labels('admin_labels.image', 'Image') }}</th>
                                                <th>{{ labels('admin_labels.attachment', 'Attachment') }}</th>
                                                <th>{{ labels('admin_labels.quantity', 'Quantity') }}</th>
                                                <th>{{ labels('admin_labels.product_type', 'Product Type') }}</th>
                                                <th>{{ labels('admin_labels.variations', 'Variant') }}</th>
                                                <th>{{ labels('admin_labels.discount', 'Discounted Price') }}</th>
                                                <th>{{ labels('admin_labels.sub_total', 'Sub Total') }}</th>
                                                <th>{{ labels('admin_labels.active_status', 'Active Status') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php
                                                $total = 0;
                                                $tax_amount = 0;
                                                $item_subtotal = 0;
                                            @endphp
                                            @foreach ($items as $index => $item)
                                                @php
                                                    $is_allow_to_ship_order = true;
                                                @endphp
                                                @if ($item['active_status'] == 'draft' || $item['active_status'] == 'awaiting')
                                                    @php
                                                        $is_allow_to_ship_order = false;
                                                    @endphp
                                                @endif
                                                @php
                                                    $selected = '';
                                                    $item['discounted_price'] =
                                                        $item['discounted_price'] == '' ? 0 : $item['discounted_price'];
                                                    $total += $subtotal =
                                                        $item['quantity'] != 0 &&
                                                        ($item['discounted_price'] != '' &&
                                                            $item['discounted_price'] > 0) &&
                                                        $item['price'] > $item['discounted_price']
                                                            ? $item['price'] - $item['discounted_price']
                                                            : $item['price'] * $item['quantity'];
                                                    $tax_amount += $item['tax_amount'];
                                                    $total += $subtotal = $tax_amount;
                                                    $item_subtotal += (float) $item['item_subtotal'];
                                                @endphp
                                                <tr>
                                                    @if ($items[0]['product_type'] == 'digital_product')
                                                        <td><input type="checkbox" name="order_item_ids[]"
                                                                value="{{ $item['id'] }}"
                                                                class="checked_order_items form-check-input selected_order_item_ids">
                                                        </td>
                                                    @endif
                                                    <td class="align-items-center d-flex">{{ $index + 1 }}</td>
                                                    @php
                                                        $product_name = json_decode($item['pname'], true);
                                                        $product_name = $product_name['en'] ?? '';
                                                    @endphp
                                                    <td>
                                                        <h6 class="title-color">
                                                            <a href="{{ $items[0]['order_type'] === 'combo_order' ? route('seller.combo_products.show', $item['product_id']) : route('seller.product.show', $item['product_id']) }}"
                                                                title="Click To View Product" target="_blank">
                                                                {{ $product_name }}
                                                            </a>
                                                        </h6>

                                                    </td>
                                                    <td>
                                                        <div class="order-image-box">
                                                            <a href={{ getMediaImageUrl($item['product_image']) }}
                                                                target=""
                                                                data-lightbox="image-'{{ $item['product_id'] }}'">
                                                                <img class="rounded"
                                                                    src="{{ getMediaImageUrl($item['product_image']) }}"
                                                                    alt="{{ $product_name }}">
                                                            </a>
                                                        </div>
                                                    </td>
                                                    <td class="d-flex justify-content-center">
                                                        @if (!empty($item['attachment']))
                                                            <a href="{{ getMediaImageUrl($item['attachment']) }}"
                                                                target="_blank" class="image-link">
                                                                <i class='attachment_icon bx bx-link fs-3'></i>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item['quantity'] }}</td>
                                                    <td>{{ str_replace('_', ' ', ucfirst($item['product_type'])) }}</td>
                                                    <td>{{ isset($item['product_variants']) && !empty($item['product_variants'][0]['variant_values'])
                                                        ? str_replace(',', ' | ', $item['product_variants'][0]['variant_values'])
                                                        : '-' }}
                                                    </td>
                                                    <td>{{ $item['discounted_price'] > 0 ? $item['discounted_price'] : $item['price'] }}
                                                    </td>
                                                    <td>{{ $item['item_subtotal'] }}</td>
                                                    @php
                                                        $badges = [
                                                            'awaiting' => 'secondary',
                                                            'received' => 'primary',
                                                            'processed' => 'info',
                                                            'shipped' => 'warning',
                                                            'delivered' => 'success',
                                                            'returned' => 'danger',
                                                            'cancelled' => 'danger',
                                                            'return_request_approved' => 'success',
                                                            'return_request_decline' => 'danger',
                                                            'return_request_pending' => 'warning',
                                                            'return_pickedup' => 'success',
                                                        ];

                                                        if ($item['active_status'] == 'return_request_pending') {
                                                            $status = 'Return Requested';
                                                        } elseif ($item['active_status'] == 'return_request_approved') {
                                                            $status = 'Return Approved';
                                                        } elseif ($item['active_status'] == 'return_request_decline') {
                                                            $status = 'Return Declined';
                                                        } else {
                                                            $status = $item['active_status'];
                                                        }
                                                    @endphp
                                                    <td>
                                                        <small>
                                                            <span
                                                                class="mt-1 badge badge-sm bg-{{ $badges[$item['active_status']] }}">
                                                                {{ $status }}
                                                            </span>
                                                        </small>
                                                    </td>
                                                </tr>
                                                <span class="d-none"
                                                    id="product_variant_id_{{ $item['product_variant_id'] }}">
                                                    {!! json_encode([
                                                        'id' => $item['id'],
                                                        'unit_price' => $item['price'],
                                                        'quantity' => $item['quantity'],
                                                        'delivered_quantity' => $item['delivered_quantity'],
                                                        'active_status' => $item['active_status'],
                                                    ]) !!}
                                                </span>

                                                <input type="hidden" class="product_variant_id"
                                                    name="product_variant_id" value="{{ $item['product_variant_id'] }}">
                                                <input type="hidden" class="product_name" name="product_name"
                                                    value="{{ $product_name }}">
                                                <input type="hidden" class="order_item_id" name="order_item_id"
                                                    value="{{ $item['id'] }}">
                                            @endforeach
                                        </tbody>
                                    </table>
                                    @if ($items[0]['product_type'] == 'digital_product')
                                        <select name="status" class="form-control digital_order_status mb-3">
                                            <option value=''>Select Status</option>
                                            <option value="received"
                                                <?= $item['active_status'] == 'received' ? 'selected' : '' ?>>Received
                                            </option>
                                            <option value="delivered"
                                                <?= $item['active_status'] == 'delivered' ? 'selected' : '' ?>>Delivered
                                            </option>
                                        </select>
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-primary digital_order_status_update">Submit</button>
                                        </div>
                                    @endif
                                </div>
                                @if ($items[0]['product_type'] != 'digital_product' && empty($order_tracking_data[0]['shipment_id']))
                                    <div class="tab-content">
                                        <div class="tab-pane fade" id="shipments" role="tabpanel"
                                            aria-labelledby="shipments-tab">
                                            <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                                data-bs-target="#create_parcel_modal" onclick="parcelModal()">Create A
                                                Parcel</button>
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-sm-12 d-flex justify-content-end mt-md-0 mt-sm-2">
                                                        <div class="input-group me-2 search-input-grp ">
                                                            <span class="search-icon"><i
                                                                    class='bx bx-search-alt'></i></span>
                                                            <input type="text" data-table="seller_parcel_table"
                                                                class="form-control searchInput" placeholder="Search...">
                                                            <span
                                                                class="input-group-text">{{ labels('admin_labels.search', 'Search') }}</span>
                                                        </div>
                                                        <a class="btn me-2" id="tableFilter" data-bs-toggle="offcanvas"
                                                            data-bs-target="#columnFilterOffcanvas"
                                                            data-table="seller_parcel_table" StatusFilter='true'><i
                                                                class='bx bx-filter-alt'></i></a>
                                                        <a class="btn me-2" id="tableRefresh"
                                                            data-table="seller_parcel_table"><i
                                                                class='bx bx-refresh'></i></a>
                                                        <div class="dropdown">
                                                            <a class="btn dropdown-toggle export-btn" type="button"
                                                                id="exportOptionsDropdown" data-bs-toggle="dropdown"
                                                                aria-expanded="false">
                                                                <i class='bx bx-download'></i>
                                                            </a>
                                                            <ul class="dropdown-menu"
                                                                aria-labelledby="exportOptionsDropdown">
                                                                <li><button class="dropdown-item" type="button"
                                                                        onclick="exportTableData('seller_parcel_table','csv')">CSV</button>
                                                                </li>
                                                                <li><button class="dropdown-item" type="button"
                                                                        onclick="exportTableData('seller_parcel_table','json')">JSON</button>
                                                                </li>
                                                                <li><button class="dropdown-item" type="button"
                                                                        onclick="exportTableData('seller_parcel_table','sql')">SQL</button>
                                                                </li>
                                                                <li><button class="dropdown-item" type="button"
                                                                        onclick="exportTableData('seller_parcel_table','excel')">Excel</button>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <table class='table edit-order-table' id="seller_parcel_table"
                                                data-toggle="table" data-loading-template="loadingTemplate"
                                                data-url="{{ route('seller.parcels.list') }}" data-click-to-select="true"
                                                data-side-pagination="server" data-pagination="true"
                                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                                data-show-columns="false" data-show-refresh="false"
                                                data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                                data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                                data-maintain-selected="true" data-export-types='["txt","excel"]'
                                                data-query-params="parcel_query_params" id="parcel_table">

                                                <thead>
                                                    <tr>
                                                        <th data-field="id" data-sortable='true'>
                                                            {{ labels('admin_labels.id', 'Id') }}
                                                        </th>
                                                        <th data-field="order_id" data-sortable='true'>
                                                            {{ labels('admin_labels.order_id', 'Order Id') }}</th>
                                                        <th data-field="name" data-sortable='false'>
                                                            {{ labels('admin_labels.name', 'Name') }}</th>
                                                        <th data-field="status" data-sortable='false'>
                                                            {{ labels('admin_labels.status', 'Status') }}</th>
                                                        <th data-field="otp" data-sortable='false'>
                                                            {{ labels('admin_labels.otp', 'OTP') }}</th>
                                                        <th data-field="created_at" data-sortable='false'>
                                                            {{ labels('admin_labels.date_created', 'Date Created') }}</th>
                                                        <th data-field="operate" data-sortable="false">
                                                            {{ labels('admin_labels.action', 'Action') }}</th>
                                                    </tr>
                                                </thead>
                                            </table>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        {{-- modal for create parcel --}}
        @if ($is_allow_to_ship_order == true)
            <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="create_parcel_modal"
                aria-labelledby="editModalLabel" aria-hidden="true">
                <input type="hidden" id="order_id" name="order_id" value="{{ $order_detls[0]->id }}" />
                <!-- In the modal -->
                <input type="hidden" id="modal_order_id" name="order_id" value="">
                <input type="hidden" class="seller_id" value="{{ $sellers[0]['id'] }}" />
                <input type="hidden" id="parcel_order_type" name="parcel_order_type"
                    value="{{ $order_detls[0]->order_type }}" />
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myModalLabel">Create a Parcel</h5>
                            <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                    data-bs-dismiss="modal" aria-label="Close"></button></div>
                        </div>
                        <div class="modal-body" id="empty_box_body"></div>
                        <div class="modal-body" id="modal-body">
                            <div class="input-group flex-nowrap">
                                <span class="input-group-text bg-gradient-light">Parcel Title</span>
                                <input type="text" class="form-control" placeholder="Parcel Title"
                                    aria-label="Username" aria-describedby="addon-wrapping" id="parcel_title" required>
                            </div>
                            <table class="table mt-2">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Product Name</th>
                                        <th scope="col">Product Varient ID</th>
                                        <th scope="col">Order Quantity</th>
                                        <th scope="col">Unit Price</th>
                                        <th scope="col">Select Items</th>
                                    </tr>
                                </thead>
                                <tbody id="product_details">
                                </tbody>
                            </table>
                            <div class="d-flex justify-content-end px-2">
                                <button type="button" class="btn btn-primary" id="ship_parcel_btn">Ship</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <!-- modal for order tracking -->
        <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="order_tracking_modal"
            aria-labelledby="editModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="user_name">
                            {{ labels('admin_labels.order_tracking', 'Order Tracking') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <form class="form-horizontal " id="order_tracking_form"
                                    action="{{ route('seller.orders.update_order_tracking') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @method('POST')
                                    @csrf
                                    <input type="hidden" name="parcel_id">
                                    <div class="card-body pad">
                                        <div class="form-group ">
                                            <label
                                                for="courier_agency">{{ labels('admin_labels.courier_agency', 'Courier Agency') }}</label>
                                            <input type="text" class="form-control" name="courier_agency"
                                                id="courier_agency" placeholder="Courier Agency" />
                                        </div>
                                        <div class="form-group ">
                                            <label
                                                for="tracking_id">{{ labels('admin_labels.tracking_id', 'Tracking Id') }}</label>
                                            <input type="text" class="form-control" name="tracking_id"
                                                id="tracking_id" placeholder="Tracking Id" />
                                        </div>
                                        <div class="form-group ">
                                            <label for="url">{{ labels('admin_labels.url', 'Url') }}</label>
                                            <input type="text" class="form-control" name="url" id="url"
                                                placeholder="URL" />
                                        </div>
                                        <div class="d-flex justify-content-end">
                                            <button type="reset"
                                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                            <button type="submit" class="btn btn-primary"
                                                id="submit_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- modal for send digital product -->
        <div id="sendMailModal" class="modal fade editSendMail" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-focus="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ labels('admin_labels.manage_digital_product', 'Manage Digital Product') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>

                    <div class="modal-body ">
                        <form class="form-horizontal form-submit-event submit_form"
                            action="{{ route('seller.orders.send_digital_product') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <input type="hidden" name="order_id" value="{{ $order_detls[0]->order_id }}">
                                <input type="hidden" name="order_item_id" value="">
                                <input type="hidden" name="username" value="{{ $order_detls[0]->uname }}">
                                <div class="row form-group">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="product_name">{{ labels('admin_labels.email', 'Customer Email') }}</label>
                                            <input type="text" class="form-control" id="email" name="email"
                                                value="{{ $order_detls[0]->user_email }}" readonly>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="product_name">{{ labels('admin_labels.subject', 'Subject') }}
                                            </label>
                                            <input type="text" class="form-control" id="subject"
                                                placeholder="Enter Subject for email" name="subject" value="">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-label"
                                                for="product_name">{{ labels('admin_labels.message', 'Message') }}</label>
                                            <textarea class="textarea" id="mail_msg" placeholder="Message for Email" name="message"></textarea>
                                        </div>
                                    </div>

                                    <div class="col-12 mt-2" id="digital_media_container">
                                        <label class="form-label" for="image"
                                            class="ml-2">{{ labels('admin_labels.file', 'File') }}
                                            <span class='text-asterisks text-sm'>*</span></label>
                                        <div class='col-md-12'><a class="uploadFile img btn btn-primary text-white btn-sm"
                                                data-input='pro_input_file' data-isremovable='0'
                                                data-media_type="archive,document" data-is-multiple-uploads-allowed='0'
                                                data-bs-toggle="modal" data-bs-target="#media-upload-modal"
                                                value="Upload Photo"><i class='fa fa-upload'></i> Upload</a></div>
                                        <div class="container-fluid row image-upload-section">
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-success mt-3" id="submit_btn"
                                    value="Save">{{ labels('admin_labels.send_mail', 'Send Mail') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- shiprocket order parcel modal --}}

        <div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" id="order_parcel_modal"
            data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            {{ labels('admin_labels.create_shiprocket_order_parcel', 'Create Shiprocket Order Parcel') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card card-info">
                                    <!-- form start -->
                                    <form class="form-horizontal " id="shiprocket_order_parcel_form" action=""
                                        method="POST">
                                        @method('POST')
                                        @csrf
                                        @php
                                            $total_items = count($items);
                                        @endphp
                                        <div class="card-body pad">
                                            <div class="form-group">
                                                <input type="hidden" id="order_id" name="order_id"
                                                    value="{{ $order_detls[0]->id }}" />
                                                <input type="hidden" name="user_id" id="user_id"
                                                    value="{{ $order_detls[0]->user_id }}" />
                                                <input type="hidden" name="total_order_items" id="total_order_items"
                                                    value="{{ $total_items }}" />
                                                <input type="hidden" name="shiprocket_seller_id" value="" />
                                                <input type="hidden" name="fromseller" value="1"
                                                    id="fromseller" />
                                                <textarea id="order_items" name="order_items[]" hidden>{{ json_encode($items, JSON_FORCE_OBJECT) }}</textarea>
                                            </div>
                                            <div class="mt-1 p-2 create-parcel-note">
                                                <p>
                                                    <b>Note:</b> Make your pickup location associated with the order is
                                                    verified from <a class="text-decoration-underline"
                                                        href="https://app.shiprocket.in/company-pickup-location?redirect_url="
                                                        target="_blank"> Shiprocket
                                                        Dashboard </a> and then in <a href="" target="_blank"
                                                        class="text-decoration-underline"> admin panel
                                                    </a>. If it is not verified you will not be able to generate AWB
                                                    later on.
                                                </p>
                                            </div>
                                            <div class="col-md-12 mt-4">
                                                <div class="form-group">
                                                    <label class="form-label"
                                                        for="pickup_location">{{ labels('admin_labels.pickup_location', 'Pickup Location') }}</label>
                                                    <input type="text" class="form-control" name="pickup_location"
                                                        id="pickup_location" placeholder="Pickup Location" value=""
                                                        readonly />

                                                </div>
                                            </div>

                                            <ul>
                                                <li>
                                                    <h6>{{ labels(
                                                        'admin_labels.total_weight_of_parcel',
                                                        'Total Weight Of                                                                                                                                                                                                                                                                                                                                                                                                                            Parcel',
                                                    ) }}
                                                    </h6>
                                                </li>
                                            </ul>
                                            <div class="form-group row mt-4">
                                                <div class="col-3">
                                                    <label for="parcel_weight"
                                                        class="form-label col-md-12">{{ labels('admin_labels.weight', 'Weight') }}
                                                        <small>(kg)</small> <span
                                                            class='text-asterisks text-xs'>*</span></label>
                                                    <input type="number" class="form-control" name="parcel_weight"
                                                        placeholder="Parcel Weight" id="parcel_weight" value=""
                                                        step=".01" min=0>
                                                </div>
                                                <div class="col-3">
                                                    <label for="parcel_height"
                                                        class="form-label col-md-12">{{ labels('admin_labels.height', 'Height') }}
                                                        <small>(cms)</small> <span
                                                            class='text-asterisks text-xs'>*</span></label>
                                                    <input type="number" class="form-control" name="parcel_height"
                                                        placeholder="Parcel Height" id="parcel_height" value=""
                                                        min="1">
                                                </div>
                                                <div class="col-3">
                                                    <label for="parcel_breadth"
                                                        class="form-label col-md-12">{{ labels('admin_labels.breadth', 'Breadth') }}
                                                        <small>(cms)</small>
                                                        <span class='text-asterisks text-xs'>*</span></label>
                                                    <input type="number" class="form-control" name="parcel_breadth"
                                                        placeholder="Parcel Breadth" id="parcel_breadth" value=""
                                                        min="1">
                                                </div>
                                                <div class="col-3">
                                                    <label for="parcel_length"
                                                        class="form-label col-md-12">{{ labels('admin_labels.length', 'Length') }}
                                                        <small>(cms)</small> <span
                                                            class='text-asterisks text-xs'>*</span></label>
                                                    <input type="number" class="form-control" name="parcel_length"
                                                        placeholder="Parcel Length" id="parcel_length" value=""
                                                        min="1">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn reset-btn"
                                                data-dismiss="modal">{{ labels('admin_labels.close', 'Close') }}</button>
                                            <button type="submit"
                                                class="btn btn-primary create_shiprocket_parcel">{{ labels('admin_labels.create_order', 'Create Order') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- modal for show parcel product details --}}

        <div class="modal fade" id="view_parcel_items_modal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header mb-1">
                        <h5 class="modal-title" id="myModalLabel">
                            {{ labels('admin_labels.parcel_items', 'Parcel Items') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Image</th>
                                    <th scope="col">Quantity</th>
                                    <th scope="col">Status</th>

                                </tr>
                            </thead>
                            <tbody id="parcel_product_details">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- modal for update parcel items status --}}

    <div class="modal fade" id="parcel_status_modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="myModalLabel">Update Parcel Status</h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="parcel_id" id="parcel_id">
                    @if (isset($items[0]['product_type']) && $items[0]['product_type'] != 'digital_product')
                        <div class="col-md-12 mb-2">
                            <label class="badge badge-success">Select status which you want to update
                            </label>
                        </div>
                        <div id="parcel-items-container"></div>
                    @endif

                    <ul class="nav nav-pills mb-3 d-block" id="pills-tab" role="tablist">
                        @if ($order_detls[0]->is_shiprocket_order == 0)
                            <div class="d-flex justify-content-center align-items-center">
                                <h5 class="text-middle-line mt-2" type="button"><span>Local Shipping</span></h5>
                            </div>
                        @else
                            <div class="d-flex justify-content-center align-items-center">
                                <h5 class="text-middle-line" type="button"><span>Standard Shipping (Shiprocket)</span>
                                </h5>
                            </div>
                            <div>
                                <div>
                                    <button class="btn my-2 btn-danger" type="button" data-toggle="collapse"
                                        data-target="#collapseTracking" aria-expanded="false"
                                        aria-controls="collapseTracking">
                                        Cancelled Shiprocket Order Details
                                    </button>
                                    <div class="collapse" id="collapseTracking">
                                        <div class="card card-body">
                                            <div id="tracking_box_old"></div>
                                        </div>
                                    </div>
                                </div>
                                <div id="tracking_box"></div>
                            </div>
                            <div class="py-2 manage_shiprocket_box">
                                <p class="mb-2">If the Order Status Does Not Change Automatically, Please <a
                                        class="refresh_shiprocket_status">Refresh</a></p>
                                <div class="d-flex">
                                    <button data-fromseller="1"
                                        class="btn btn-outline-danger me-2 cancel_shiprocket_order">Cancel Shiprocket
                                        Order</button>
                                </div>
                            </div>

                            @php
                                $seller_order = getOrderDetails(
                                    ['o.id' => $order_detls[0]->order_id, 'oi.seller_id' => $sellers[0]['id']],
                                    '',
                                    '',
                                    $store_id,
                                );
                                $pickup_location = collect($seller_order)
                                    ->pluck('pickup_location')
                                    ->unique()
                                    ->values()
                                    ->all();
                            @endphp
                            @if (!empty($pickup_location[0]))
                                @foreach ($pickup_location as $location)
                                    @php
                                        $ids = '';
                                    @endphp

                                    @foreach ($seller_order as $row)
                                        @if ($row->pickup_location == $location)
                                            @php
                                                $ids .= $row->order_item_id . ',';
                                            @endphp
                                        @endif
                                    @endforeach

                                    @php
                                        $order_item_ids = explode(',', trim($ids, ','));
                                        $order_tracking_data = getShipmentId(
                                            $order_item_ids[0],
                                            $order_detls[0]->order_id,
                                        );
                                        $shiprocket_order =
                                            $order_tracking_data && is_array($order_tracking_data)
                                                ? get_shiprocket_order($order_tracking_data[0]['shiprocket_order_id'])
                                                : '';
                                    @endphp

                                    @foreach ($order_item_ids as $id)
                                        @php
                                            $active_status = fetchDetails(
                                                'order_items',
                                                ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                'active_status',
                                            )[0]->active_status;
                                        @endphp
                                        @if ($shiprocket_order != '')
                                            @if ($shiprocket_order['data']['status'] == 'PICKUP SCHEDULED' && $active_status != 'shipped')
                                                @php
                                                    updateOrder(
                                                        ['active_status' => 'shipped'],
                                                        ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                        false,
                                                        'order_items',
                                                    );
                                                    updateOrder(
                                                        ['status' => 'shipped'],
                                                        ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                        true,
                                                        'order_items',
                                                    );
                                                    $type = ['type' => 'customer_order_shipped'];
                                                    $order_status = 'shipped';
                                                @endphp
                                            @endif
                                        @endif

                                        @if (isset($shiprocket_order['data']) &&
                                                ($shiprocket_order['data']['status'] == 'CANCELED' ||
                                                    $shiprocket_order['data']['status'] == 'CANCELLATION REQUESTED') &&
                                                $active_status != 'cancelled')
                                            @php
                                                updateOrder(
                                                    ['active_status' => 'cancelled'],
                                                    ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                    false,
                                                    'order_items',
                                                );
                                                updateOrder(
                                                    ['status' => 'cancelled'],
                                                    ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                    true,
                                                    'order_items',
                                                );
                                                $type = ['type' => 'customer_order_cancelled'];
                                                $order_status = 'cancelled';
                                            @endphp
                                        @endif

                                        @if (isset($shiprocket_order['data']) &&
                                                strtolower($shiprocket_order['data']['status']) == 'delivered' &&
                                                $active_status != 'delivered')
                                            @php
                                                updateOrder(
                                                    ['active_status' => 'delivered'],
                                                    ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                    false,
                                                    'order_items',
                                                );
                                                updateOrder(
                                                    ['status' => 'delivered'],
                                                    ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                    true,
                                                    'order_items',
                                                );
                                                $type = ['type' => 'customer_order_delivered'];
                                                $order_status = 'delivered';
                                            @endphp
                                        @endif

                                        @if (isset($shiprocket_order['data']) &&
                                                $shiprocket_order['data']['status'] == 'READY TO SHIP' &&
                                                $active_status != 'processed')
                                            @php
                                                updateOrder(
                                                    ['active_status' => 'processed'],
                                                    ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                    false,
                                                    'order_items',
                                                );
                                                updateOrder(
                                                    ['status' => 'processed'],
                                                    ['id' => $id, 'seller_id' => $sellers[0]['id']],
                                                    true,
                                                    'order_items',
                                                );
                                                $type = ['type' => 'customer_order_processed'];
                                                $order_status = 'processed';
                                            @endphp
                                        @endif
                                    @endforeach
                                @endforeach
                            @endif


                            @if (isset($location) && !empty($location) && $location != 'NULL')
                                <div class="row m-2 ml-6">
                                    @if (
                                        !empty($shiprocket_order) &&
                                            isset($shiprocket_order['data']) &&
                                            !empty($shiprocket_order['data']) &&
                                            isset($order_tracking_data[0]['shipment_id']) &&
                                            !empty($order_tracking_data[0]['shipment_id']) &&
                                            $order_tracking_data[0]['is_canceled'] != 1 &&
                                            $shiprocket_order['data']['status'] != 'CANCELED')
                                        <div class="col-md-2">
                                            <span class="badge bg-success ml-1">Order created</span>
                                        </div>
                                    @endif

                                    @if (isset($items[0]['product_type']) && $items[0]['product_type'] != 'digital_product')
                                        @if (empty($order_tracking_data[0]['shipment_id']))
                                            <div class="col-md-2">
                                                <span class="badge bg-primary ml-1">Order not created</span>
                                            </div>
                                        @endif
                                    @endif

                                    @if (
                                        (!empty($shiprocket_order) &&
                                            isset($shiprocket_order['data']) &&
                                            !empty($shiprocket_order['data']) &&
                                            (isset($order_tracking_data[0]['is_canceled']) && $order_tracking_data[0]['is_canceled'] != 0)) ||
                                            (isset($shiprocket_order['data']) && $shiprocket_order['data']['status'] == 'CANCELED'))
                                        <div class="col-md-6 me-2 ms-3">
                                            <span class="badge bg-danger ml-1">Order cancelled</span>
                                        </div>
                                    @endif
                                    <div class="col-md-10">
                                        <div class="col-md-6 d-flex flex-wrap justify-content-around">
                                            @if (isset($order_tracking_data[0]) &&
                                                    isset($order_tracking_data[0]['shipment_id']) &&
                                                    $order_tracking_data[0]['shipment_id'] != 0)
                                                @if (empty($order_tracking_data[0]['awb_code']) || $order_tracking_data[0]['awb_code'] == 'NULL')
                                                    @if (isset($shiprocket_order['data']) && $shiprocket_order['data']['status'] != 'CANCELED')
                                                        <button type="button" title="Generate AWB"
                                                            class="btn btn-primary btn-sm me-1 generate_awb"
                                                            data-fromseller="1"
                                                            id="{{ $order_tracking_data[0]['shipment_id'] }}">AWB</button>
                                                    @endif
                                                @else
                                                    @if (
                                                        !empty($shiprocket_order) &&
                                                            empty($order_tracking_data[0]['pickup_scheduled_date']) &&
                                                            ($shiprocket_order['data']['status_code'] != 4 ||
                                                                $shiprocket_order['data']['status'] !=
                                                                    'PICKUP
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                SCHEDULED') &&
                                                            $shiprocket_order['data']['status'] != 'CANCELED' &&
                                                            $shiprocket_order['data']['status'] != 'CANCELLATION REQUESTED')
                                                        <button type="button" title="Send Pickup Request"
                                                            class="btn btn-primary btn-sm me-1 send_pickup_request"
                                                            data-fromseller="1"
                                                            name="{{ $order_tracking_data[0]['shipment_id'] }}">
                                                            <i class="fas fa-shipping-fast"></i>
                                                        </button>
                                                    @endif

                                                    @if (isset($order_tracking_data[0]['is_canceled']) && $order_tracking_data[0]['is_canceled'] == 0)
                                                        {{-- @if (isset($order_tracking_data[0]['is_canceled']) && $order_tracking_data[0]['is_canceled'] == 1) --}}
                                                        <button type="button" title="Cancel Order"
                                                            class="btn btn-primary btn-sm me-1 cancel_shiprocket_order"
                                                            data-fromseller="1"
                                                            name="{{ $order_tracking_data[0]['shiprocket_order_id'] }}">
                                                            <i class="fas fa-redo-alt"></i>
                                                        </button>
                                                    @endif

                                                    @if (isset($order_tracking_data[0]['label_url']) && !empty($order_tracking_data[0]['label_url']))
                                                        <a href="{{ $order_tracking_data[0]['label_url'] }}"
                                                            title="Download Label" data-fromseller="1"
                                                            class="btn btn-primary btn-sm me-1 download_label text-white gap-2">
                                                            <i class="fas fa-download"></i> Label
                                                        </a>
                                                    @else
                                                        <button type="button" title="Generate Label"
                                                            class="btn btn-primary btn-sm me-1 generate_label"
                                                            data-fromseller="1"
                                                            name="{{ $order_tracking_data[0]['shipment_id'] }}">
                                                            <i class="fas fa-tags"></i>
                                                        </button>
                                                    @endif

                                                    @if (isset($order_tracking_data[0]['invoice_url']) && !empty($order_tracking_data[0]['invoice_url']))
                                                        <a href="{{ $order_tracking_data[0]['invoice_url'] }}"
                                                            title="Download Invoice" data-fromseller="1"
                                                            class="btn btn-primary btn-sm me-1 download_invoice text-white gap-2">
                                                            <i class="fas fa-download"></i> Invoice
                                                        </a>
                                                    @else
                                                        <button type="button" title="Generate Invoice"
                                                            class="btn btn-primary btn-sm me-1 generate_invoice"
                                                            data-fromseller="1"
                                                            name="{{ $order_tracking_data[0]['shiprocket_order_id'] }}">
                                                            <i class="far fa-money-bill-alt"></i>
                                                        </button>
                                                    @endif

                                                    @if (isset($order_tracking_data[0]['awb_code']) && !empty($order_tracking_data[0]['awb_code']))
                                                        <a href="https://shiprocket.co/tracking/{{ $order_tracking_data[0]['awb_code'] }}"
                                                            target="_blank" title="Track Order"
                                                            class="btn btn-primary action-btn btn-sm me-1 track_order text-white"
                                                            name="{{ $order_tracking_data[0]['shiprocket_order_id'] }}">
                                                            <i class="fas fa-map-marker-alt"></i>
                                                        </a>
                                                    @endif
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </ul>
                    @if ($order_detls[0]->is_shiprocket_order == 0)
                        <select name="status" class="form-control parcel_status mb-3">
                            <option value=''>Select Status</option>
                            <option value="received">Received</option>
                            <option value="processed">Processed</option>
                            <option value="shipped">Shipped</option>
                            <option value="delivered">Delivered</option>
                        </select>
                    @endif
                    <div class="tab-content" id="pills-tabContent">
                        @if ($order_detls[0]->is_shiprocket_order == '0')
                            <div class="tab-pane fade show active" id="pills-local" role="tabpanel"
                                aria-labelledby="pills-local-tab">
                                <select id="deliver_by" name="deliver_by" class="form-control mb-2">
                                    <option value="">Select Delivery Boy</option>
                                    @foreach ($delivery_res as $row)
                                        <option value="{{ $row->id }}"
                                            {{ $order_detls[0]->delivery_boy_id == $row->id ? 'selected' : '' }}>
                                            {{ $row->username }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @else
                            <div class="tab-pane fade show active" id="pills-standard" role="tabpanel"
                                aria-labelledby="pills-standard-tab">
                                <div class="card card-info shiprocket_order_box">
                                    <!-- form start -->
                                    <form class="form-horizontal" id="shiprocket_order_parcel_form" action=""
                                        method="POST">
                                        @csrf
                                        @php
                                            $total_items = count($items);
                                        @endphp
                                        <div>
                                            <div class="form-group">
                                                <input type="hidden" id="order_id" name="order_id"
                                                    value="{{ $order_detls[0]->id }}" />
                                                <input type="hidden" name="user_id" id="user_id"
                                                    value="{{ $order_detls[0]->user_id }}" />
                                                <input type="hidden" name="total_order_items" id="total_order_items"
                                                    value="{{ $total_items }}" />
                                                <input type="hidden" name="shiprocket_seller_id"
                                                    class="shiprocket_seller_id" value="{{ $sellers[0]['id'] }}" />
                                                <input type="hidden" name="fromseller" value="1"
                                                    id="fromseller" />
                                                <textarea id="order_items" name="order_items[]" hidden>{{ json_encode($items, JSON_FORCE_OBJECT) }}</textarea>
                                                <input type="hidden" name="order_tracking[]" id="order_tracking"
                                                    value="{{ json_encode($order_tracking) }}" />
                                                <input type="hidden" name="parcel_data[]" id="parcel_data" />
                                            </div>

                                            <div class="mt-1 p-2 bg-dark text-white rounded">
                                                <p>
                                                    <b>Note:</b> Make your pickup location associated with the order is
                                                    verified from <a class="text-white text-decoration-underline"
                                                        href="https://app.shiprocket.in/company-pickup-location?redirect_url="
                                                        target="_blank"> Shiprocket
                                                        Dashboard </a> and then in <a href="" target="_blank"
                                                        class="text-white text-decoration-underline"> admin panel
                                                    </a>. If it is not verified you will not be able to generate AWB
                                                    later on.
                                                </p>is not verified, you will not be able to generate AWB later on.
                                                </p>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-4">
                                                    <label for="txn_amount">Pickup location</label>
                                                </div>
                                                <div class="col-8">
                                                    <input type="text" class="form-control" name="pickup_location"
                                                        id="pickup_location" placeholder="Pickup Location"
                                                        value="{{ $order_detls[0]->pickup_location }}" readonly />
                                                </div>
                                            </div>

                                            <div class="form-group row mt-4">
                                                <div class="col-3">
                                                    <label for="parcel_weight" class="control-label col-md-12">Weight
                                                        <small>(kg)</small> <span class="text-danger text-xs">*</span>
                                                    </label>
                                                    <input type="number" class="form-control" name="parcel_weight"
                                                        placeholder="Parcel Weight" id="parcel_weight" value=""
                                                        step=".01">
                                                </div>
                                                <div class="col-3">
                                                    <label for="parcel_height" class="control-label col-md-12">Height
                                                        <small>(cms)</small> <span class="text-danger text-xs">*</span>
                                                    </label>
                                                    <input type="number" class="form-control" name="parcel_height"
                                                        placeholder="Parcel Height" id="parcel_height" value=""
                                                        min="1">
                                                </div>
                                                <div class="col-3">
                                                    <label for="parcel_breadth" class="control-label col-md-12">Breadth
                                                        <small>(cms)</small> <span class="text-danger text-xs">*</span>
                                                    </label>
                                                    <input type="number" class="form-control" name="parcel_breadth"
                                                        placeholder="Parcel Breadth" id="parcel_breadth" value=""
                                                        min="1">
                                                </div>
                                                <div class="col-3">
                                                    <label for="parcel_length" class="control-label col-md-12">Length
                                                        <small>(cms)</small> <span class="text-danger text-xs">*</span>
                                                    </label>
                                                    <input type="number" class="form-control" name="parcel_length"
                                                        placeholder="Parcel Length" id="parcel_length" value=""
                                                        min="1">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-dismiss="modal">Close</button>
                                            <button type="submit" class="btn btn-primary create_shiprocket_parcel">Create
                                                Order</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        @endif

                    </div>

                    @if ($order_detls[0]->is_shiprocket_order == 0)
                        <div class="d-flex justify-content-end p-2">
                            <button type="button"
                                class="btn btn-primary btn-sm me-1 parcel_order_status_update">Update</button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
