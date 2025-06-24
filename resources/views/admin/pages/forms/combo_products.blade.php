@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.combo_products', 'Combo Products') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.add_product', 'Add Product')" :subtitle="labels('admin_labels.add_products_with_power_and_simplicity', 'Add products with power and simplicity')" :breadcrumbs="[
        ['label' => labels('admin_labels.products', 'Products')],
        ['label' => labels('admin_labels.manage_products', 'Manage Products')],
        ['label' => labels('admin_labels.add_product', 'Add Product')],
    ]" />

    <section class="overview-statistic">
        <div class="">
            <form action="{{ route('admin.combo_products.store') }}" class="submit_form" enctype="multipart/form-data"
                method="POST" id="">
                @csrf
                <input type="hidden" class='main_combo_seller_id' value=''>
                <div class="card p-5">
                    <h6>{{ labels('admin_labels.choose_seller', 'Select Seller And Product') }}
                    </h6>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            {{ labels('admin_labels.choose_seller', 'Choose Seller') }}<span
                                class='text-asterisks text-sm'>*</span>
                            <select class='form-control mt-4 combo_seller_id form-select' name='seller_id' id="seller_id">
                                <option value="">Select Seller</option>
                                @foreach ($sellers as $seller)
                                    <option value="{{ $seller->id }}">
                                        {{ $seller->username }} - {{ $seller->store_name }} (store)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            {{ labels('admin_labels.choose_product_type', 'Choose Product Type') }}<span
                                class='text-asterisks text-sm'>*</span>
                            <select class='form-control mt-4' name='product_type_in_combo' id="product_type_menu">
                                <option value="">Select Product Type</option>
                                <option value="physical_product">
                                    {{ labels('admin_labels.physical_product', 'Physical Product') }}
                                </option>
                                <option value="digital_product">
                                    {{ labels('admin_labels.digital_product', 'Digital Product') }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4">
                    <h6>{{ labels('admin_labels.product_information', 'Product Information') }}</h6>
                    <ul class="nav nav-tabs mt-4" id="brandTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="language-nav-link nav-link active" id="tab-en" data-bs-toggle="tab"
                                data-bs-target="#content-en" type="button" role="tab" aria-controls="content-en"
                                aria-selected="true">
                                {{ labels('admin_labels.default', 'Default') }}
                            </button>
                        </li>
                        {!! generateLanguageTabsNav($languages) !!}
                    </ul>

                    <div class="tab-content mt-3" id="brandTabsContent">
                        <div class="tab-pane fade show active" id="content-en" role="tabpanel" aria-labelledby="tab-en">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="pro_input_text"
                                        class="form-label">{{ labels('admin_labels.product_name', 'Product Name') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" id="pro_input_text"
                                        placeholder="Product Name" name="title">
                                </div>
                                <div class="col-md-6">
                                    <label for="pro_short_description"
                                        class="form-label">{{ labels('admin_labels.short_description', 'Short Description') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <textarea class="form-control" id="short_description" placeholder="Product Short Description" name="short_description"></textarea>
                                </div>
                            </div>
                        </div>

                        @foreach ($languages as $lang)
                            @if ($lang->code !== 'en')
                                <div class="tab-pane fade" id="content-{{ $lang->code }}" role="tabpanel"
                                    aria-labelledby="tab-{{ $lang->code }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="translated_name_{{ $lang->code }}" class="form-label">
                                                {{ labels('admin_labels.product_name', 'Product Name') }}
                                                ({{ $lang->language }})
                                            </label>
                                            <input type="text" class="form-control"
                                                id="translated_name_{{ $lang->code }}"
                                                name="translated_product_title[{{ $lang->code }}]" value="">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="translated_short_description_{{ $lang->code }}"
                                                class="form-label">
                                                {{ labels('admin_labels.short_description', 'Short Description') }}
                                                ({{ $lang->language }})
                                            </label>
                                            <textarea class="form-control" id="translated_short_description_{{ $lang->code }}"
                                                placeholder="Product Short Description"name="translated_product_short_description[{{ $lang->code }}]"></textarea>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <!-- Tags and Physical Products -->
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tags" class="form-label">{{ labels('admin_labels.tags', 'Tags') }}</label>
                                <input type="text" class="form-control" id="tags" placeholder="dress,milk,almond"
                                    name="tags">
                            </div>
                        </div>

                        <div class="col-md-6 physical_product_in_combo">
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    {{ labels('admin_labels.products', 'Physical Products') }}
                                    <span class="text-asterisks text-sm">*</span>
                                </label>
                                <select name="physical_product_variant_id[]"
                                    class="select2 form-select search_admin_product_for_combo w-100" multiple></select>
                            </div>
                        </div>
                        <!-- Digital Products -->

                        <div class="col-md-6 digital_product_in_combo d-none">
                            <div class="form-group">
                                <label class="form-label" for="name">
                                    {{ labels('admin_labels.products', 'Digital Products') }}
                                    <span class="text-asterisks text-sm">*</span>
                                </label>
                                <select name="digital_product_id[]"
                                    class="select2 form-select search_admin_digital_product w-100" multiple></select>
                            </div>

                        </div>
                    </div>

                    <!-- Has Similar Products Switch -->
                    <div class="row mt-4">
                        <div class="form-group col-md-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label class="form-label">
                                    {{ labels('admin_labels.has_similar_products', 'Has similar products') }}?
                                </label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input has_similar_product" type="checkbox"
                                        name="has_similar_product">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Similar Products Selection -->
                    <div class="row mt-3 d-none" id="similar_product">
                        <div class="col-md-12">
                            <label class="form-label" for="similar_product_id">
                                {{ labels('admin_labels.select_products', 'Select Products') }}
                                <span class="text-asterisks text-sm">*</span>
                            </label>
                            <select name="similar_product_id[]"
                                class="select2 form-select search_admin_combo_product w-100" multiple></select>
                        </div>
                    </div>
                </div>

                <div class="card p-5 mt-4">
                    <h6>{{ labels('admin_labels.product_tax', 'Product Tax') }}</h6>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="pro_input_tax"
                                    class="form-label">{{ labels('admin_labels.select_tax', 'Select Tax') }}</label>

                                <select name="pro_input_tax[]" class="tax_list form-select w-100" multiple>
                                    <option value="">
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mt-7">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <label for="is_prices_inclusive_tax"
                                        class="form-label">{{ labels('admin_labels.tax_includes_in_price', 'Tax Includes In Price') }}</label>
                                </div>
                                <div class="d-flex">
                                    <label for="" class="me-6 text-muted">[Enable/Disable]</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id=""
                                            name="is_prices_inclusive_tax">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4 combo_product_quantity_and_others">
                    <h6>{{ labels('admin_labels.product_quantity_and_other', 'Product Quantity & Other') }}
                    </h6>
                    <div class="row mt-4">
                        <div class="col-md-6 total_allowed_quantity">
                            <div class="form-group">
                                <label for="total_allowed_quantity"
                                    class="form-label">{{ labels('admin_labels.total_allowed_quantity', 'Total Allowed Quantity') }}</label>
                                <input type="number" class="col-md-12 form-control" name="total_allowed_quantity"
                                    value="" min=1 placeholder="Total Allowed Quantity">
                            </div>
                        </div>
                        <div class="col-md-6 minimum_order_quantity">
                            <div class="form-group">
                                <label for="minimum_order_quantity"
                                    class="form-label">{{ labels('admin_labels.minimum_order_quantity', 'Minimum Order Quantity') }}</label>
                                <input type="number" class="col-md-12 form-control" name="minimum_order_quantity"
                                    min="1" value="1" placeholder="Minimum Order Quantity">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 quantity_step_size">
                            <div class="form-group">
                                <label for="quantity_step_size"
                                    class="form-label">{{ labels('admin_labels.quantity_step_size', 'Quantity Step Size') }}</label>
                                <input type="number" class="col-md-12 form-control" name="quantity_step_size"
                                    min="1" value="1" placeholder="Quantity Step Size">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4 combo_delivery_and_shipping_setting">
                    <h6>{{ labels('admin_labels.delivery_and_shipping_setting', 'Delivery And Shipping Setting') }}
                    </h6>

                    <div class="row mt-4">
                        <div class="col-md-6 ">
                            <div class="form-group">
                                <label for="zipcode"
                                    class="form-label">{{ labels('admin_labels.deliverable_type', 'Deliverable Type') }}</label>
                                <select class="form-select form-select" name="deliverable_type" id="deliverable_type">
                                    <option value="0">None</option>
                                    <option value="1" class="combo_all_deliverable_type">All</option>
                                    <option value="2">specific</option>
                                    {{-- <option value="3">Excluded</option> --}}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zipcodes"
                                    class="form-label">{{ labels('admin_labels.deliverable_zones', 'Deliverable Zones') }}</label>
                                <select name="deliverable_zones[]" class="search_seller_zone form-select w-100" multiple
                                    id="deliverable_zones" disabled>
                                    <option value="">
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="shipping_type"
                                    class="form-label">{{ labels('admin_labels.for_standard_shipping', 'For Standard Shipping') }}
                                </label>
                                <select class='form-control shiprocket_type' name="pickup_location" id="pickup_location">
                                    <option value=" ">
                                        {{ labels('admin_labels.select_pickup_location', 'Select Pickup Location') }}
                                    </option>

                                </select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zipcodes"
                                    class="form-label">{{ labels('admin_labels.minimum_free_delivery_order_quantity', 'Minimum Free Delivery Order Quantity') }}</label>
                                <input type="number" class="form-control" value="" min=1
                                    name="minimum_free_delivery_order_qty">
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="zipcodes"
                                    class="form-label">{{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}</label>
                                <input type="number" class="form-control" value="" min=1 name="delivery_charges">
                            </div>
                        </div>
                        <div class="col-md-6 mt-7">
                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <label for="is_cod_allowed"
                                            class="form-label">{{ labels('admin_labels.is_cod_allowed', 'IS COD Allowed') }}?</label>
                                    </div>
                                    <div class="d-flex">
                                        <label for="" class="me-6 text-muted">[Enable/Disable]</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id=""
                                                name="cod_allowed">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mt-7 ">
                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <label for="is_returnable"
                                            class="form-label">{{ labels('admin_labels.is_returnable', 'IS Returnable') }}?</label>
                                    </div>
                                    <div class="d-flex">
                                        <label class="me-6 text-muted form-label">[Enable/Disable]</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id=""
                                                name="is_returnable">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-7 ">
                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <label for="is_cancelable"
                                            class="form-label">{{ labels('admin_labels.is_cancelable', 'IS Cancelable') }}?</label>
                                    </div>
                                    <div class="d-flex">
                                        <label class="me-6 text-muted form-label">[Enable/Disable]</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="is_cancelable_checkbox"
                                                name="is_cancelable">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">

                        <div class="col-md-6 mt-7 collapse" id='cancelable_till'>
                            <div class="form-group">
                                <label for="cancelable_till"
                                    class="form-label">{{ labels('admin_labels.till_which_status', 'Cancelable Till Which Status') }}?</label>
                                <select class='form-select form-select' name="cancelable_till">
                                    <option value='received'>Received</option>
                                    <option value='processed'>Processed</option>
                                    <option value='shipped'>Shipped</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mt-7 ">
                            <div class="form-group">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <label for="is_attachment_required"
                                            class="form-label">{{ labels('admin_labels.is_attachment_required', 'IS Attachment Required') }}?</label>
                                    </div>
                                    <div class="d-flex">
                                        <label class="me-6 text-muted form-label">[Enable/Disable]</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                id="is_attachment_required_checkbox" name="is_attachment_required">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4">
                    <h6>{{ labels('admin_labels.products_additional_info', 'Product Additional Info') }}
                    </h6>

                    <div class="row mt-4">
                        <div class="col-md-12 additional-info existing-additional-settings">
                            <div class="mt-4 col-md-12 additional-info-nav-header d-flex">
                                <div class="col-md-6">
                                    <nav class="w-100">
                                        <div class="nav nav-tabs" id="product-tab" role="tablist">
                                            <a class="nav-item nav-link active" id="tab-for-general-price"
                                                data-bs-toggle="tab" href="#general-settings" role="tab"
                                                aria-controls="general-price"
                                                aria-selected="true">{{ labels('admin_labels.general', 'General') }}</a>
                                            <a class="nav-item nav-link edit-product-attributes" id="tab-for-attributes"
                                                data-bs-toggle="tab" href="#product-attributes" role="tab"
                                                aria-controls="product-attributes"
                                                aria-selected="false">{{ labels('admin_labels.attributes', 'Attributes') }}</a>

                                        </div>
                                    </nav>
                                </div>
                                <input type="hidden" name="product_type" value="">

                                <input type="hidden" name="variant_stock_level_type">
                                <input type="hidden" name="variant_stock_status">
                                <input type="hidden" id="combo_type" value="combo_product">

                            </div>
                            <div id="attributes_values_json_data" class="d-none">
                                <select class="select_single" data-placeholder=" Type to search and select attributes">
                                    <option value=""></option>

                                    @foreach ($attributes as $attribute)
                                        @php
                                            $data = json_encode($attribute->attribute_values, 1);
                                        @endphp
                                        <option name='{{ $attribute->name }}' value='{{ $attribute->name }}'
                                            data-values='{{ json_encode($attribute->attribute_values, 1) }}'>
                                            {{ $attribute->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="tab-content p-3 col-md-12" id="nav-tabContent">
                                <div class="tab-pane fade active show" id="general-settings" role="tabpanel"
                                    aria-labelledby="general-settings-tab">

                                    <div id="product-general-settings">
                                        <div id="general_price_section" class="">
                                            <div class="row">
                                                <div class="col-md-6">

                                                    <ul>
                                                        <li>
                                                            <h6>{{ labels('admin_labels.price_info', 'Price Info') }}
                                                            </h6>
                                                        </li>
                                                    </ul>

                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="simple_price"
                                                                class="col-md-6 form-label">{{ labels('admin_labels.price', 'Price') }}:
                                                                <span class="text-asterisks text-sm">*</span></label>
                                                            <input type="number" name="simple_price"
                                                                class="form-control stock-simple-mustfill-field price"
                                                                min="0.01" step="0.01">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label for="type"
                                                                class="col-md-6 form-label">{{ labels('admin_labels.special_price', 'Special Price') }}
                                                                : <span class="text-asterisks text-sm">*</span></label>
                                                            <input type="number" name="simple_special_price"
                                                                class="form-control discounted_price" min="0"
                                                                step="0.01">
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="dimensions " id="product-dimensions">

                                                        <ul>
                                                            <li>
                                                                <h6>{{ labels('admin_labels.standard_shipping_weightage', 'Standard shipping weightage') }}
                                                                </h6>
                                                            </li>
                                                        </ul>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="weight"
                                                                        class="form-label col-md-12">{{ labels('admin_labels.weight', 'Weight') }}
                                                                        <small>(kg)</small></label>
                                                                    <input type="number" class="form-control"
                                                                        name="weight" placeholder="Weight"
                                                                        value="0" step="0.01" min=0>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="height"
                                                                        class="form-label col-md-12">{{ labels('admin_labels.height', 'Height') }}
                                                                        <small>(cms)</small></label>
                                                                    <input type="number" class="form-control"
                                                                        name="height" placeholder="Height"
                                                                        id="height" value="0" step="0.01"
                                                                        min=0>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="breadth"
                                                                        class="form-label col-md-12">{{ labels('admin_labels.bredth', 'Bredth') }}
                                                                        <small>(cms)</small> </label>
                                                                    <input type="number" class="form-control"
                                                                        name="breadth" placeholder="Breadth"
                                                                        id="breadth" value="0" step="0.01"
                                                                        min=0>
                                                                </div>
                                                            </div>
                                                            <div class="col-6">
                                                                <div class="form-group">
                                                                    <label for="length"
                                                                        class="form-label col-md-12">{{ labels('admin_labels.length', 'Length') }}
                                                                        <small>(cms)</small> </label>
                                                                    <input type="number" class="form-control"
                                                                        name="length" placeholder="Length"
                                                                        id="length" value="0" step="0.01"
                                                                        min=0>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="form-group  simple_stock_management">
                                                <div class="col">
                                                    <input type="checkbox" name="simple_stock_management_status"
                                                        class="align-middle simple_stock_management_status form-check-input m-0">
                                                    <span
                                                        class="align-middle">{{ labels('admin_labels.enable_stock_management', 'Enable Stock Management') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="simple-product-level-stock-management collapse">
                                            <div class="row d-flex">
                                                <div class="col col-xs-4 col-md-4">
                                                    <div class="form-group">
                                                        <label class="form-label">{{ labels('admin_labels.sku', 'Sku') }}
                                                            <span class='text-asterisks text-xs'>*</span>
                                                            :</label>
                                                        <input type="text" name="product_sku"
                                                            class="col form-control simple-pro-sku" value="">
                                                    </div>
                                                </div>
                                                <div class="col col-xs-4 col-md-4">
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label">{{ labels('admin_labels.total_stock', 'Total Stock') }}
                                                            <span class='text-asterisks text-xs'>*</span>
                                                            :</label>
                                                        <input type="number" min="0" name="product_total_stock"
                                                            class="col form-control stock-simple-mustfill-field">
                                                    </div>
                                                </div>
                                                <div class="col col-xs-4 col-md-4">
                                                    <div class="form-group">
                                                        <label
                                                            class="form-label">{{ labels('admin_labels.stock_status', 'Stock Status') }}
                                                            :</label>
                                                        <select type="text" name="simple_product_stock_status"
                                                            class="col form-control stock-simple-mustfill-field form-select"
                                                            id="simple_product_stock_status">
                                                            <option value="1">
                                                                In Stock</option>
                                                            <option value="0">
                                                                Out Of Stock</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group collapse simple-product-save">
                                            <div class="col-md-12">
                                                <a href="javascript:void(0);"
                                                    class="btn btn-dark save-settings float-end">{{ labels('admin_labels.save_settings', 'Save Settings') }}</a>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="digital_product_setting" class="collapse">
                                        <div class="row form-group">
                                            <div class="col-md-6 d-flex">
                                                <label for="download_allowed"
                                                    class="col form-label">{{ labels('admin_labels.is_download_allowed', 'IS Download Allowed') }}?</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="download_allowed" id="download_allowed">
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-xs-6 collapse" id='download_type'>
                                                <label for="download_link_type"
                                                    class="col form-label">{{ labels('admin_labels.download_link_type', 'Download Link Type') }}
                                                    <span class='text-asterisks text-sm'>*</span></label>
                                                <select class='form-control form-select' name="download_link_type"
                                                    id="download_link_type">
                                                    <option value=''>None</option>
                                                    <option value='self_hosted'>Self Hosted</option>
                                                    <option value='add_link'>Add Link</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 d-none" id="digital_link_container">
                                                <label for="video"
                                                    class="col form-label ml-1">{{ labels('admin_labels.digital_product_link', 'Digital Product Link') }}
                                                    <span class='text-asterisks text-sm'>*</span></label>
                                                <input type="url" class='form-control' name='download_link'
                                                    id='download_link' value=""
                                                    placeholder="Paste digital product link or URL here">
                                            </div>
                                            <div class="form-group d-none mt-5" id="digital_media_container">
                                                <label for="image"
                                                    class="ml-2 form-label">{{ labels('admin_labels.file', 'File') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <a class="media_link" data-input="pro_input_zip" data-isremovable="0"
                                                    data-is-multiple-uploads-allowed="0"
                                                    data-media_type="archive,document" data-bs-toggle="modal"
                                                    data-bs-target="#media-upload-modal" value="Upload Photo">
                                                    <div class="col-md-6 file_upload_box border file_upload_border">
                                                        <div class="mt-2">
                                                            <div class="col-md-12  text-center">
                                                                <div>
                                                                    <p class="caption text-dark-secondary">
                                                                        Choose video for product.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>

                                                <div class="row mt-3 image-upload-section">
                                                </div>
                                            </div>

                                            <div class="form-group mt-4">
                                                <div class="col float-end">
                                                    <a href="javascript:void(0);"
                                                        class="btn btn-dark save-digital-product-settings">{{ labels('admin_labels.save_settings', 'Save Settings') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-pane" id="product-attributes" role="tabpanel"
                                    aria-labelledby="product-attributes-tab">
                                    <div class="d-flex">
                                        <div class="info col-md-6 p-3" id="note">
                                            <div class="col-12 d-flex align-center">
                                                <strong>{{ labels('admin_labels.note', 'Note') }}
                                                    :</strong>
                                                <input type="checkbox" checked=""
                                                    class="ml-3 my-auto custom-checkbox form-check-input ms-1 me-1"
                                                    readonly>
                                                <span class="ml-3">check if the attribute is to be
                                                    used for variation</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 d-flex justify-content-end">
                                            <button type="button" id="add_attributes"
                                                class="btn  btn-primary m-2 btn-xs">{{ labels('admin_labels.add_attributes', 'Add Attributes') }}</button>
                                            <a href="javascript:void(0);" id=""
                                                class="save_attributes btn btn-dark m-2 btn-xs d-none">{{ labels('admin_labels.save_attributes', 'Save Attributes') }}</a>
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                    <div id="attributes_process">
                                        <div
                                            class="form-group text-center row my-auto p-2 border rounded bg-gray-light col-md-12 no-attributes-added">
                                            <div class="col-md-12 text-center">
                                                {{ labels('admin_labels.no_product_attributes_are_added', 'No Product Attributes Are Added') }}!
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4">
                    <h6>{{ labels('admin_labels.product_media', 'Product Media') }}
                        ({{ labels('admin_labels.images', 'Images') }})</h6>
                    <div class="row mt-4">
                        <div class="col-md-6">
                            <label class="form-label">
                                {{ labels('admin_labels.main_image', 'Main Image') }}
                                <span class="text-asterisks text-sm">*</span>
                            </label>
                            <div class="form-group">
                                <a class="media_link" data-input="image" data-isremovable="0"
                                    data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                    data-bs-target="#media-upload-modal" value="Upload Photo">
                                    <div class="file_upload_box border file_upload_border">
                                        <div class="mt-2 text-center">
                                            <p class="caption text-dark-secondary">Choose image for product.</p>
                                        </div>
                                    </div>
                                </a>
                                <p class="image_recommendation mt-2">(Recommended Size : 180 x 180 pixels)</p>
                                <div class="container-fluid row mt-3 image-upload-section">
                                    <div class="col-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ labels('admin_labels.other_images', 'Other Images') }}</label>
                            <div class="form-group">
                                <a class="media_link" data-input="other_images[]" data-isremovable="1"
                                    data-is-multiple-uploads-allowed="1" data-bs-toggle="modal"
                                    data-bs-target="#media-upload-modal" value="Upload Photo">
                                    <div class="file_upload_box border file_upload_border">
                                        <div class="mt-2 text-center">
                                            <p class="caption text-dark-secondary">Choose images for product.</p>
                                        </div>
                                    </div>
                                </a>
                                <p class="image_recommendation mt-2">(Recommended Size : 180 x 180 pixels)</p>
                                <div class="row mt-3 image-upload-section"></div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="card p-5 mt-4">
                    <h6>{{ labels('admin_labels.product_description', 'Product Description') }}</h6>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <label class="form-label">{{ labels('admin_labels.description', 'Description') }}</label>
                            <textarea name="description" class="form-control addr_editor" placeholder="Place some text here"></textarea>
                        </div>
                    </div>
                </div>
                <div class="float-end ml-2 mt-xxl-3 mt-7 text-center">
                    <button type="submit"
                        class="btn btn-primary submit_button">{{ labels('admin_labels.submit', 'Submit') }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
