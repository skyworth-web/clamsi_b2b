@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.point_of_sale', 'Point Of Sale') }}
@endsection
@section('content')
    <section class="main-content">
        <div class="container-fluid mt-5 mb-5 px-6">
            <x-seller.breadcrumb :title="labels('admin_labels.point_of_sale', 'Point Of Sale')" :subtitle="labels(
                'admin_labels.grow_your_sales_with_the_right_tools',
                'Grow Your Sales With The Right Tools',
            )" :breadcrumbs="[
                ['label' => labels('admin_labels.manage', 'Manage')],
                ['label' => labels('admin_labels.point_of_sale', 'Point Of Sale')],
            ]" />


            <section class="pos-data row">
                <div class="col-md-12 col-xxl-8">
                    <div class="card content-area ps-5 pe-5 ">
                        <div class="mt-4 col-md-12 additional-info-nav-header d-flex">
                            <div class="col-sm-6">
                                <nav class="w-100">
                                    <div class="nav nav-tabs" id="media-tab" role="tablist">
                                        <a class="nav-item nav-link pos-nav-tab-link active" data-tab="product_tab"
                                            data-bs-toggle="tab" href="#nav-product" role="tab"
                                            aria-controls="nav-product"
                                            aria-selected="true">{{ labels('admin_labels.products', 'Products') }}</a>
                                        <a class="nav-item nav-link pos-nav-tab-link" data-tab="combo_product_tab"
                                            data-bs-toggle="tab" href="#nav-combo-poduct" role="tab"
                                            aria-controls="nav-combo-poduct"
                                            aria-selected="false">{{ labels('admin_labels.combo_products', 'Combo Products') }}</a>
                                    </div>
                                </nav>
                            </div>
                            <div class="col-sm-6">
                                <div class="align-items-center d-flex form-group justify-content-end row">

                                    <div class="col-md-5">

                                        <select class="form-select" id="product_categories" name="category_parent">
                                            <option value="">
                                                <?= isset($categories) && empty($categories) ? 'No Categories Exist' : 'Select Categories' ?>
                                            </option>
                                            {!! getCategoriesOptionHtml($categories) !!}

                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="input-group me-3 search-input-grp product-search">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" name="search_products" class="form-control"
                                                id="search_products" value="" placeholder="Search Products">
                                        </div>
                                        <div class="input-group me-3 search-input-grp combo-product-search d-none">
                                            <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                            <input type="text" name="search_products" class="form-control"
                                                id="search_combo_products" value=""
                                                placeholder="Search combo Products">
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                        <div class="tab-content" id="nav-tabContent">
                            <div class="tab-pane fade active show" id="nav-product" role="tabpanel"
                                aria-labelledby="nav-product-tab">

                                <div class="mt-4">

                                    @php

                                        use App\Models\Seller;
                                        use Illuminate\Support\Facades\Auth;

                                        $user_id = Auth::user()->id;
                                        $seller_id = Seller::where('user_id', $user_id)->value('id');
                                    @endphp
                                    <input type="hidden" name="session_user_id" id="session_user_id"
                                        value="{{ $seller_id }}" />
                                    <input type="hidden" name="limit" id="limit" value="8" />
                                    <input type="hidden" name="offset" id="offset" value="0" />
                                    <input type="hidden" name="total" id="total_products" />
                                    <input type="hidden" name="current_page" id="current_page" value="0" />
                                    <div class="row d-flex align-content-center" class="img-thumbnail" id="get_products">
                                        <!-- product display in this container -->
                                    </div>

                                </div>
                                <div class="card-footer text-muted mt-8">
                                    <div class="pagination-container"></div>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="nav-combo-poduct" role="tabpanel">

                                <div class="mt-4">
                                    <input type="hidden" name="session_user_id" id="session_user_id"
                                        value="<?= Auth::user()->id ?>" />
                                    <input type="hidden" name="limit" id="combo_products_limit" value="8" />
                                    <input type="hidden" name="offset" id="combo_products_offset" value="0" />
                                    <input type="hidden" name="total" id="total_combo_products" />
                                    <input type="hidden" name="current_page" id="combo_product_current_page"
                                        value="0" />
                                    <div class="row d-flex align-content-center" class="img-thumbnail"
                                        id="get_combo_products">
                                        <!-- product display in this container -->
                                    </div>

                                </div>
                                <div class="card-footer text-muted mt-8">
                                    <div class="combo-product-pagination-container"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Product cart display  -->
                <div class="col-md-12 col-xxl-4 pos-product-cart-detail mt-xxl-0 mt-md-4">
                    <section>
                        <form id="pos_form" method="POST" action='{{ route('place.order') }}'>
                            @csrf
                            <div class="card p-5">
                                <div class="align-items-center d-flex justify-content-between">

                                    <h6>{{ labels('admin_labels.existing_customer', 'Existing Customer') }}</h6>


                                    <div class=""><button type="button" class="btn btn-primary"
                                            data-bs-toggle="modal" data-bs-target="#register"><i
                                                class='bx bx-user-plus me-1'></i>
                                            {{ labels('admin_labels.add_new_customer', 'Add New Customer') }}</button>
                                    </div>
                                </div>
                                <!-- select user -->
                                <input type="hidden" name="user_id" id="pos_user_id" value="">
                                <input type="hidden" name="product_variant_id" value="">
                                <input type="hidden" name="quantity" value="">
                                <input type="hidden" name="total" value="">
                                <input type="hidden" name="payment_method" value="">
                                <input type="hidden" name="payment_method_name" value="">
                                <input type="hidden" name="transaction_id" value="">
                                <input type="hidden" name="product_address_id" id="product_address_id" value="">
                                <input type="hidden" name="product_address" id="product_customer_address"
                                    value="">

                                <div class="mt-5">
                                    <select class="select_user form-select" id="select_user_id"
                                        placeholder="Search for customer">
                                        <!-- user   name display here  -->
                                    </select>
                                </div>
                            </div>
                            <div class="card p-5 mt-5">
                                <div class="col-md-12 pos-cart-header d-flex justify-content-between align-items-center">
                                    <h6 class="total_product_cart_items"></h6>
                                    <button class="btn clear_cart text-danger" type="button" id=""><i
                                            class='bx bx-trash'></i>{{ labels('admin_labels.clear_cart', 'Clear Cart') }}</button>
                                    <button class="btn btn-sm btn-purchase btn-primary mb-2 d-none place_order_btn"
                                        type="submit"
                                        id="">{{ labels('admin_labels.place_order', 'Place Order') }}</button>
                                </div>

                                <div class="container">
                                    <div class="cart-items">

                                    </div>
                                </div>
                            </div>
                            <div class="card p-5 mt-5">
                                <div class="col-md-12  d-flex justify-content-between align-items-center">
                                    <h6>{{ labels('admin_labels.shipping_address', 'Shipping Address') }}</h6>
                                    <a href="#" class="btn active customer_edit_address d-none" type="button"
                                        data-bs-toggle="modal"
                                        data-bs-target="#customer_edit_address">{{ labels('admin_labels.edit_address', 'Edit Address') }}</a>
                                </div>
                                <div class="customer-address-detail mt-5">
                                </div>
                            </div>
                            <div class="card mt-5 p-5">
                                <h6>{{ labels('admin_labels.billing_details', 'Billing Details') }}</h6>
                                <div class="col-md-12 mt-5">
                                    <div class="form-group">
                                        <label class="form-label" for="delivery_charge_service">
                                            {{ labels('admin_labels.shipping_charge', 'Shipping Charge') }}</label>
                                        <input type="number" class="delivery_charge_service form-control"
                                            id="delivery_charge_service" value="" placeholder="0.00"
                                            name="delivery_charge" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label"
                                            for="discount_service">{{ labels('admin_labels.discount', 'Discount') }}</label><small>(if
                                            any)</small>
                                        <input type="number" class="discount_service form-control" id="discount_service"
                                            value="" placeholder="0.00" name="discount" min="00">
                                    </div>
                                    <div class="col-lg-12 billing-detail-table">
                                        <table class="table table-borderless w-100">
                                            <tr>
                                                <td class="cart-total ps-0">
                                                    {{ labels('admin_labels.sub_total', 'SubTotal') }}
                                                </td>
                                                <input type="hidden" class="main_cart_total" value="">
                                                <td class="cart-total-price float-end pe-0" id="cart-total-price"
                                                    data-currency="<?= isset($currency) && !empty($currency) ? $currency : '' ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="cart-total ps-0">
                                                    {{ labels('admin_labels.shipping_charges', 'Shipping Charges') }}
                                                </td>
                                                <td class="delivery_charge float-end pe-0" id="delivery_charge">
                                                    <?= $currency . 0 ?></td>
                                            </tr>
                                            <tr>
                                                <td class="cart-total ps-0">
                                                    {{ labels('admin_labels.discount_amount', 'Discount Amount') }}
                                                </td>
                                                <td class="discount_amount float-end pe-0" id="discount_amount">
                                                    <?= $currency . 0 ?></td>
                                            </tr>
                                        </table>
                                        <div class="d-flex justify-content-between">
                                            <div class="cart-total">{{ labels('admin_labels.total', 'Total') }}</div>
                                            <h6 class="final_total" id="final_total" data-currency="<?= $currency ?>">
                                            </h6>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-primary mt-5 float-end cart_product_payment"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cart_product_payment">{{ labels('admin_labels.proceed_to_order', 'Proceed To Order') }}</button>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>


                <!-- Combo Product cart display  -->

                <div class="col-md-12 col-xxl-4 mt-xxl-0 mt-md-4 pos-combo-product-cart-detail d-none">
                    <section>
                        <form id="combo_product_form" method="POST" action="{{ route('combo.place.order') }}">
                            @csrf
                            <div class="card p-5">
                                <div class="align-items-center d-flex justify-content-between">

                                    <h6>{{ labels('admin_labels.existing_customer', 'Existing Customer') }}</h6>


                                    <div class=""><button type="button" class="btn btn-primary"
                                            data-bs-toggle="modal" data-bs-target="#register"><i
                                                class='bx bx-user-plus me-1'></i>
                                            {{ labels('admin_labels.add_new_customer', 'Add New Customer') }}</button>
                                    </div>
                                </div>
                                <!-- select user -->
                                <input type="hidden" name="user_id" id="pos_user_id" value="">
                                <input type="hidden" name="combo_address_id" id="combo_address_id" value="">
                                <input type="hidden" name="combo_address" id="combo_customer_address" value="">
                                <input type="hidden" name="product_variant_id" value="">
                                <input type="hidden" name="quantity" value="">
                                <input type="hidden" name="combo_hidden_final_total" value="">
                                <input type="hidden" name="combo_hidden_sub_total" class="combo_hidden_sub_total"
                                    value="">
                                <input type="hidden" name="payment_method" id="combo_payment_method" value="">
                                <input type="hidden" name="payment_method_name" id="combo_payment_method_name"
                                    value="">
                                <input type="hidden" name="transaction_id" id="combo_transaction_id" value="">

                                <div class="mt-5">
                                    <select class="select_user form-select" id="select_combo_user_id"
                                        placeholder="Search for customer">
                                        <!-- user   name display here  -->
                                    </select>
                                </div>
                            </div>


                            <div class="card p-5 mt-5">
                                <div class="col-md-12 pos-cart-header d-flex justify-content-between align-items-center">
                                    <h6 class="total_combo_cart_items"></h6>
                                    <button class="btn clear_combo_cart text-danger" type="button" id=""><i
                                            class='bx bx-trash'></i>{{ labels('admin_labels.clear_cart', 'Clear Cart') }}</button>
                                    <button class="btn btn-sm btn-purchase btn-primary mb-2 d-none" type="submit"
                                        id="combo_place_order_btn">{{ labels('admin_labels.place_order', 'Place Order') }}</button>

                                </div>
                                <div class="container">
                                    <div class="combo-cart-items">

                                    </div>
                                </div>
                            </div>
                            <div class="card p-5 mt-5">
                                <div class="col-md-12  d-flex justify-content-between align-items-center">
                                    <h6>Shipping Address </h6>
                                    <a href="#" class="btn active combo_customer_edit_address d-none"
                                        type="button" data-bs-toggle="modal"
                                        data-bs-target="#combo_customer_edit_address">{{ labels('admin_labels.edit_address', 'Edit Address') }}</a>
                                </div>

                                <div class="combo_customer-address-detail mt-5">

                                </div>

                            </div>

                            <div class="card mt-5 p-5">
                                <h6>{{ labels('admin_labels.billing_details', 'Billing Details') }}</h6>
                                <div class="col-md-12 mt-5">
                                    <div class="form-group">
                                        <label class="form-label" for="combo_delivery_charge_service">
                                            {{ labels('admin_labels.shipping_charge', 'Shipping Charge') }}</label>
                                        <input type="number" class="combo_delivery_charge_service form-control"
                                            id="combo_delivery_charge_service" value="" placeholder="0.00"
                                            name="delivery_charge" min="0">
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label"
                                            for="discount_service">{{ labels('admin_labels.discount_amount', 'Discount Amount') }}</label><small>(if
                                            any)</small>
                                        <input type="text" class="combo_discount_service form-control"
                                            id="combo_discount_service" value="" placeholder="0.00"
                                            name="discount" min="0.00">
                                    </div>
                                    <div class="col-lg-12 billing-detail-table">
                                        <table class="table table-borderless w-100">
                                            <tr>
                                                <td class="cart-total ps-0">
                                                    {{ labels('admin_labels.sub_total', 'SubTotal') }}
                                                </td>
                                                <td class="combo_total_price float-end pe-0" id="combo_total_price"
                                                    data-currency="<?= isset($currency) && !empty($currency) ? $currency : '' ?>">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="cart-total ps-0">
                                                    {{ labels('admin_labels.shipping_charges', 'Shipping Charges') }}
                                                </td>
                                                <td class="combo_delivery_charge float-end pe-0" id="delivery_charge">
                                                    <?= $currency . 0 ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="cart-total ps-0">
                                                    {{ labels('admin_labels.discount_amount', 'Discount Amount') }}
                                                </td>
                                                <td class="combo_discount_amount float-end pe-0" id="discount_amount">
                                                    <?= $currency . 0 ?>
                                                </td>
                                            </tr>
                                        </table>
                                        <div class="d-flex justify-content-between">
                                            <div class="cart-total">{{ labels('admin_labels.total', 'Total') }}</div>
                                            <h6 class="combo_final_total" id="combo_final_total"
                                                data-currency="<?= $currency ?>"></h6>
                                        </div>
                                    </div>


                                    <button type="button"
                                        class="btn btn-primary mt-5 float-end cart_combo_product_payment"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cart_combo_product_payment">{{ labels('admin_labels.proceed_to_order', 'Proceed To Order') }}</button>
                                </div>
                            </div>
                        </form>
                    </section>
                </div>
            </section>
        </div>

        <!-- modal section -->

        <!-- ==================================== Combo product display modal ===================================== -->

        <div class="modal fade" id="combo_modal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title combo-modal-title">
                            {{ labels('admin_labels.combo_products', 'Combo Products') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="combo_id" id="combo_id" />
                        <div id="combo_data_detail">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn reset-btn"
                            data-bs-dismiss="modal">{{ labels('admin_labels.cancel', 'Cancel') }}</button>
                        <button type="button" class="btn btn-primary add_combo_button" id="" name=""
                            data-dismiss="modal" value="Save">{{ labels('admin_labels.add', 'Add') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================================== product Variants display modal ===================================== -->

        <div class="modal fade" id="product-variants-modal" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCenterTitle">
                            {{ labels('admin_labels.choose_variants', 'Choose Variants') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body pos-variant-detail">


                    </div>

                </div>
            </div>
        </div>

        <!-- ==================================== product Payment modal ===================================== -->

        <div class="modal fade" id="cart_product_payment" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCenterTitle">{{ labels('admin_labels.payment', 'Payment') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="payment_method">{{ labels('admin_labels.cash', 'Cash') }} </label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="COD">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="card_payment">{{ labels('admin_labels.card_payment', 'Card Payment') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="card_payment">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="bar_code">{{ labels('admin_labels.barcode_or_qr_code_scan', 'Bar Code/QR Code Scan') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="bar_code">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="net_banking">{{ labels('admin_labels.net_banking', 'Net Banking') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="net_banking">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="online_payment">{{ labels('admin_labels.online_payment', 'Online Payment') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="online_payment">
                            </div>
                        </div>
                        <div class="col-md-12 other">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="other">{{ labels('admin_labels.other', 'Other') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="other">
                            </div>
                        </div>
                        <div class="payment_method_name mt-3">
                            <p>{{ labels('admin_labels.payment_method_name', 'Enter Payment Method Name') }} <input
                                    type="text" class="form-control" id="payment_method_name"></p>
                        </div>
                        <div class="transaction_id mt-3">
                            <p>{{ labels('admin_labels.transaction_id', 'Enter Transaction ID') }} <input type="text"
                                    class="form-control" id="transaction_id"></p>
                        </div>


                        <div class="col-lg-12 billing-detail-table">
                            <table class="table table-borderless w-100">
                                <tr>
                                    <td class="cart-total ps-0">{{ labels('admin_labels.sub_total', 'SubTotal') }}</td>
                                    <td class="cart-total-price float-end pe-0" id="cart-total-price"
                                        data-currency="<?= isset($currency) && !empty($currency) ? $currency : '' ?>"></td>
                                </tr>
                                <tr>
                                    <td class="cart-total ps-0">
                                        {{ labels('admin_labels.shipping_charges', 'Shipping Charges') }}
                                    </td>
                                    <td class="delivery_charge float-end pe-0" id="delivery_charge"><?= $currency . 0 ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="cart-total ps-0">
                                        {{ labels('admin_labels.discount_amount', 'Discount Amount') }}
                                    </td>
                                    <td class="discount_amount float-end pe-0" id="discount_amount"><?= $currency . 0 ?>
                                    </td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-between">
                                <div class="cart-total">{{ labels('admin_labels.total', 'Total') }}</div>
                                <h6 class="final_total" id="final_total" data-currency="<?= $currency ?>"></h6>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn reset-btn"
                            data-bs-dismiss="modal">{{ labels('admin_labels.cancel', 'Cancel') }}</button>
                        <button class="btn btn-primary mb-2 product_pay_now" type="button"
                            id="">{{ labels('admin_labels.pay_now', 'Pay Now') }}</button>
                    </div>

                </div>
            </div>
        </div>


        <!-- ==================================== Combo product Payment modal ===================================== -->

        <div class="modal fade" id="cart_combo_product_payment" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">


                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCenterTitle">{{ labels('admin_labels.payment', 'Payment') }}
                        </h5>
                        <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                                data-bs-dismiss="modal" aria-label="Close"></button></div>
                    </div>
                    <div class="modal-body">
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="payment_method">{{ labels('admin_labels.cash', 'Cash') }} </label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="COD">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="card_payment">{{ labels('admin_labels.card_payment', 'Card Payment') }} </label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="card_payment">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="bar_code">{{ labels('admin_labels.barcode_or_qr_code_scan', 'Bar Code/QR Code Scan') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="bar_code">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="net_banking">{{ labels('admin_labels.net_banking', 'Net Banking') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="net_banking">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="online_payment">{{ labels('admin_labels.online_payment', 'Online Payment') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="online_payment">
                            </div>
                        </div>
                        <div class="col-md-12 other">
                            <div class="form-group product-type-box">
                                <label class="form-check-label"
                                    for="other">{{ labels('admin_labels.other', 'Other') }}</label>
                                <input class="form-check-input m-0 payment_method" type="radio" name="payment_method"
                                    value="other">
                            </div>
                        </div>
                        <div class="payment_method_name mt-3">
                            <p>{{ labels('admin_labels.payment_method_name', 'Enter Payment Method Name') }} <input
                                    type="text" class="form-control" id="combo_cart_payment_method_name"></p>
                        </div>
                        <div class="transaction_id mt-3">
                            <p>{{ labels('admin_labels.transaction_id', 'Enter Transaction ID') }}<input type="text"
                                    class="form-control" id="combo_cart_transaction_id"></p>
                        </div>


                        <div class="col-lg-12 billing-detail-table">
                            <table class="table table-borderless w-100">
                                <tr>
                                    <td class="cart-total ps-0">{{ labels('admin_labels.sub_total', 'SubTotal') }}</td>
                                    <td class="combo_total_price float-end pe-0" id="combo_total_price"
                                        data-currency="<?= isset($currency) && !empty($currency) ? $currency : '' ?>"></td>
                                </tr>
                                <tr>
                                    <td class="cart-total ps-0">
                                        {{ labels('admin_labels.shipping_charges', 'Shipping Charges') }}
                                    </td>
                                    <td class="combo_delivery_charge float-end pe-0" id="combo_delivery_charge">
                                        <?= $currency . 0 ?></td>
                                </tr>
                                <tr>
                                    <td class="cart-total ps-0">
                                        {{ labels('admin_labels.discount_amount', 'Discount Amount') }}
                                    </td>
                                    <td class="combo_discount_amount float-end pe-0" id="combo_discount_amount">
                                        <?= $currency . 0 ?></td>
                                </tr>
                            </table>
                            <div class="d-flex justify-content-between">
                                <div class="cart-total">{{ labels('admin_labels.total', 'Total') }}</div>
                                <h6 class="combo_final_total" id="combo_final_total" data-currency="<?= $currency ?>">
                                </h6>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn reset-btn"
                            data-bs-dismiss="modal">{{ labels('admin_labels.cancel', 'Cancel') }}</button>
                        <button class="btn btn-primary mb-2 " type="button"
                            id="combo_product_pay_now">{{ labels('admin_labels.pay_now', 'Pay Now') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================================== Combo product User register modal ===================================== -->

        <div class="modal fade" id="register_combo" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="post" class="register_form" action='{{ route('register.user') }}' id="">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">
                                {{ labels('admin_labels.add_new_customer', 'Add New Customer') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="mb-3 mt-3">
                                    <label class="form-label" for="name">{{ labels('admin_labels.name', 'Name') }}
                                    </label>
                                    <input type="text" class="form-control" placeholder="Customer Name"
                                        id="combo_name" name="name" value="{{ old('name') }}">
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="mobile">{{ labels('admin_labels.mobile', 'Mobile') }}</label>
                                        <input type="number" class="form-control" placeholder="Mobile"
                                            id="combo_mobile" name="mobile" min='0'
                                            value="{{ old('mobile') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="mobile">{{ labels('admin_labels.password', 'Password') }}</label>
                                        <input type="password" class="form-control" placeholder="" id="password"
                                            name="password" min='0' value="{{ old('password') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3 country_list_div">
                                        <label class="form-label"
                                            for="country">{{ labels('admin_labels.country', 'Country') }}</label>
                                        <select class="col-md-12 form-select form-control country_list" id="country_list"
                                            name="country">
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="state">{{ labels('admin_labels.state', 'State') }}</label>
                                            <input type="text" class="form-control" placeholder="state"
                                                id="combo_state" name="state" value="{{ old('state') }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="city">{{ labels('admin_labels.city', 'City') }}</label>
                                            <input type="text" class="form-control" placeholder="city"
                                                id="combo_city" name="city" value="{{ old('city') }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="address">{{ labels('admin_labels.address', 'Address') }}</label>
                                        <textarea type="text" class="form-control" placeholder="address" id="combo_address" name="address"
                                            value="{{ old('address') }}"></textarea>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn reset-btn"
                                data-bs-dismiss="modal">{{ labels('admin_labels.cancel', 'Cancel') }}</button>
                            <button type="submit" name="register" value="save"
                                class="btn btn-primary btn-sm submit_button"
                                id="save-register-result-btn">{{ labels('admin_labels.register', 'Register') }}</button>
                            <div class="mt-3">
                                <div id="save-register-result"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ========================================== Product User register modal ========================================== -->

        <div class="modal fade" id="register" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="post" class="register_form" action='{{ route('register.user') }}' id="">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">
                                {{ labels('admin_labels.add_new_customer', 'Add New Customer') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="mb-3 mt-3">
                                    <label class="form-label" for="name">{{ labels('admin_labels.name', 'Name') }}
                                    </label>
                                    <input type="text" class="form-control" placeholder="Customer Name"
                                        id="name" name="name" value="{{ old('name') }}">
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="mobile">{{ labels('admin_labels.mobile', 'Mobile') }}</label>
                                        <input type="number" class="form-control" placeholder="Mobile" id="mobile"
                                            maxlength="16" oninput="validateNumberInput(this)" name="mobile"
                                            min='0' value="{{ old('mobile') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="mobile">{{ labels('admin_labels.password', 'Password') }}</label>
                                        <input type="password" class="form-control" placeholder="" id="password"
                                            name="password" min='0' value="{{ old('password') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3 country_list_div">
                                        <label class="form-label"
                                            for="country">{{ labels('admin_labels.country', 'Country') }}</label>
                                        <select class="col-md-12 form-control form-select " id="country_list"
                                            name="country">

                                            @foreach ($countries as $country)
                                                <option name='{{ $country->name }}' value='{{ $country->name }}'>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="state">{{ labels('admin_labels.state', 'State') }}</label>
                                            <input type="text" class="form-control" placeholder="state"
                                                id="state" name="state" value="{{ old('state') }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="city">{{ labels('admin_labels.city', 'City') }}</label>
                                            <input type="text" class="form-control" placeholder="city" id="city"
                                                name="city" value="{{ old('city') }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="address">{{ labels('admin_labels.address', 'Address') }}</label>
                                        <textarea type="text" class="form-control" placeholder="address" id="address" name="address"
                                            value="{{ old('address') }}"></textarea>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn  btn-sm btn-secondary"
                                data-bs-dismiss="modal">{{ labels('admin_labels.close', 'Close') }}</button>
                            <button type="submit" name="register" value="save"
                                class="btn btn-primary btn-sm submit_button"
                                id="save-register-result-btn">{{ labels('admin_labels.register', 'Register') }}</button>
                            <div class="mt-3">
                                <div id="save-register-result"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- -------------------- product user edit address modal  --------------------------------------------------------- --}}


        <div class="modal fade" id="customer_edit_address" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="post" action='{{ route('seller.update_user_address') }}' class="submit_form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" class="address_id" name="address_id" value="">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">
                                {{ labels('admin_labels.edit_address', 'Edit Address') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="mb-3 mt-3">
                                    <label class="form-label" for="name">{{ labels('admin_labels.name', 'Name') }}
                                    </label>
                                    <input type="text" class="form-control customer_name" placeholder="Customer Name"
                                        id="name" name="name" value="">
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="mobile">{{ labels('admin_labels.mobile', 'Mobile') }}</label>
                                        <input type="number" class="form-control customer_mobile" placeholder="Mobile"
                                            maxlength="16" oninput="validateNumberInput(this)" id="mobile"
                                            name="mobile" min='0' value="{{ old('mobile') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3 country_list_div">
                                        <label class="form-label"
                                            for="country">{{ labels('admin_labels.country', 'Country') }}</label>
                                        <select class="col-md-12 form-control form-select customer_country"
                                            id="country_list" name="country">

                                            @foreach ($countries as $country)
                                                <option name='{{ $country->name }}' value='{{ $country->name }}'>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="state">{{ labels('admin_labels.state', 'State') }}</label>
                                            <input type="text" class="form-control customer_state" placeholder="state"
                                                id="state" name="state" value="{{ old('state') }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="city">{{ labels('admin_labels.city', 'City') }}</label>
                                            <input type="text" class="form-control customer_city" placeholder="city"
                                                id="city" name="city" value="">

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="address">{{ labels('admin_labels.address', 'Address') }}</label>
                                        <textarea type="text" class="form-control customer_address" placeholder="address" id="address" name="address"></textarea>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn  btn-sm btn-secondary"
                                data-bs-dismiss="modal">{{ labels('admin_labels.close', 'Close') }}</button>
                            <button type="submit" name="register" value="save"
                                class="btn btn-primary btn-sm submit_button"
                                id="save-register-result-btn">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- -------------------- combo product user edit address modal  --------------------------------------------------------- --}}


        <div class="modal fade" id="combo_customer_edit_address" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form method="post" action="{{ route('seller.update_user_address') }}" class="submit_form">
                        @csrf
                        @method('PUT')
                        <input type="hidden" class="combo_address_id" name="address_id" value="">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalCenterTitle">
                                {{ labels('admin_labels.edit_address', 'Edit Address') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <div class="mb-3 mt-3">
                                    <label class="form-label" for="name">{{ labels('admin_labels.name', 'Name') }}
                                    </label>
                                    <input type="text" class="form-control combo_customer_name"
                                        placeholder="Customer Name" id="name" name="name" value="">
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="mobile">{{ labels('admin_labels.mobile', 'Mobile') }}</label>
                                        <input type="number" class="form-control combo_customer_mobile" maxlength="16"
                                            oninput="validateNumberInput(this)" placeholder="Mobile" id="mobile"
                                            name="mobile" min='0' value="{{ old('mobile') }}">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3 country_list_div">
                                        <label class="form-label"
                                            for="country">{{ labels('admin_labels.country', 'Country') }}</label>
                                        <select class="col-md-12 form-control form-select combo_customer_country"
                                            id="country_list" name="country">

                                            @foreach ($countries as $country)
                                                <option name='{{ $country->name }}' value='{{ $country->name }}'>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="state">{{ labels('admin_labels.state', 'State') }}</label>
                                            <input type="text" class="form-control combo_customer_state"
                                                placeholder="state" id="state" name="state"
                                                value="{{ old('state') }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="city">{{ labels('admin_labels.city', 'City') }}</label>
                                            <input type="text" class="form-control combo_customer_city"
                                                placeholder="city" id="city" name="city" value="">

                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="address">{{ labels('admin_labels.address', 'Address') }}</label>
                                        <textarea type="text" class="form-control combo_customer_address" placeholder="address" id="address"
                                            name="address"></textarea>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn  btn-sm btn-secondary"
                                data-bs-dismiss="modal">{{ labels('admin_labels.close', 'Close') }}</button>
                            <button type="submit" name="register" value="save"
                                class="btn btn-primary btn-sm submit_button"
                                id="save-register-result-btn">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </section>
@endsection
