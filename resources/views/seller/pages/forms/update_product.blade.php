@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.products', 'Products') }}
@endsection
@section('content')
    <section class="main-content">
        <x-seller.breadcrumb :title="labels('admin_labels.update_product', 'Update Product')" :subtitle="labels(
            'admin_labels.add_products_with_power_and_simplicity',
            'Update products with power and simplicity',
        )" :breadcrumbs="[
            ['label' => labels('admin_labels.products', 'Products')],
            ['label' => labels('admin_labels.manage_products', 'Manage Products')],
            ['label' => labels('admin_labels.update_product', 'Update Product')],
        ]" />
    </section>
    <section>
        <div class="">
            @if (isset($data->id))
                <input type="hidden" name="edit_product_id" value="<?= isset($data->id) ? $data->id : '' ?>">
                <input type="hidden" name="category_id" value="<?= isset($data->category_id) ? $data->category_id : '' ?>">
                <input type="hidden" name="seller_id" value="<?= isset($data->seller_id) ? $data->seller_id : '' ?>">
                <input type="hidden" id="subcategory_id_js"
                    value="<?= isset($data->subcategory_id) ? $data->subcategory_id : '' ?>">
            @endif
            <form action="{{ route('seller.products.update', $data->id) }}" enctype="multipart/form-data" method="POST"
                id="save-product">
                @method('PUT')
                @csrf
                @php

                    use App\Models\Seller;
                    use Illuminate\Support\Facades\Auth;
                    $store_id = getStoreId();

                    $deliverable_type = fetchDetails(
                        'seller_store',
                        ['seller_id' => $data->seller_id, 'store_id' => $store_id],
                        ['deliverable_type', 'deliverable_zones'],
                    );
                    $deliverable_type =
                        isset($deliverable_type) && !empty($deliverable_type) ? $deliverable_type[0] : [];
                @endphp
                <div class="card p-5">
                    <h6>{{ labels('admin_labels.choose_seller', 'Select Seller And Product') }}
                    </h6>
                    <div class="col-md-12 mt-4">
                        <div class="row">
                            <div class="col-md-6">
                                {{ labels('admin_labels.choose_product_type', 'Choose Product Type') }}<span
                                    class='text-asterisks text-sm'>*</span>
                                <select class='form-control mt-4' disabled name='product_type_menu' id="product_type_menu">
                                    <option value="">Select Product Type</option>
                                    <option value="physical_product"
                                        <?= $data->type != 'digital_product' ? 'selected' : '' ?>>
                                        {{ labels('admin_labels.physical_product', 'Physical Product') }}
                                    </option>
                                    <option value="digital_product"
                                        <?= $data->type == 'digital_product' ? 'checked' : '' ?>>
                                        {{ labels('admin_labels.digital_product', 'Digital Product') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                {{ labels('admin_labels.select_category', 'Select Product Category') }}<span
                                    class='text-asterisks text-sm'>*</span>
                                <select class='form-control mt-4' name='category_id'>
                                    <option value="">Select Category</option>
                                </select>
                            </div>
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
                        @foreach ($languages as $lang)
                            @if ($lang->code !== 'en')
                                <li class="nav-item" role="presentation">
                                    <button class="language-nav-link nav-link" id="tab-{{ $lang->code }}"
                                        data-bs-toggle="tab" data-bs-target="#content-{{ $lang->code }}" type="button"
                                        role="tab" aria-controls="content-{{ $lang->code }}" aria-selected="false">
                                        {{ $lang->language }}
                                    </button>
                                </li>
                            @endif
                        @endforeach
                    </ul>

                    <div class="tab-content mt-3" id="UpdatebrandTabsContent">
                        <div class="tab-pane fade show active" id="content-en" role="tabpanel" aria-labelledby="tab-en">
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    <label for="brand_name" class="form-label">
                                        {{ labels('admin_labels.product_name', 'Product Name') }}
                                        <span class="text-asterisks text-sm">*</span>
                                    </label>
                                    <input type="text" class="form-control" placeholder="Gucci" name="pro_input_name"
                                        value="{{ isset($data->name) ? json_decode($data->name)->en : '' }}">
                                </div>
                                <div class="mb-3 col-md-6">
                                    <label for="short_description" class="control-label mb-2 mt-2">
                                        {{ labels('admin_labels.short_description', 'Short Description') }}
                                        <span class='text-asterisks text-sm'>*</span>
                                    </label>
                                    <textarea class="form-control" id="short_description" placeholder="Product Short Description" name="short_description">{{ isset($data->short_description) ? json_decode($data->short_description)->en : '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <!-- Dynamic Language Tabs -->
                        @foreach ($languages as $lang)
                            @if ($lang->code !== 'en')
                                <div class="tab-pane fade" id="content-{{ $lang->code }}" role="tabpanel"
                                    aria-labelledby="tab-{{ $lang->code }}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="translated_title_{{ $lang->code }}" class="form-label">
                                                    {{ labels('admin_labels.product_name', 'Product Name') }}
                                                    ({{ $lang->language }})
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="translated_product_name[{{ $lang->code }}]"
                                                    value="{{ isset($data->name) ? json_decode($data->name, true)[$lang->code] ?? '' : '' }}">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="translated_short_description_{{ $lang->code }}"
                                                    class="form-label">
                                                    {{ labels('admin_labels.short_description', 'Short Description') }}
                                                    ({{ $lang->language }})
                                                </label>
                                                <textarea class="form-control" id="short_description" placeholder="Product Short Description"
                                                    name="translated_product_short_description[{{ $lang->code }}]">{{ isset($data->short_description) ? json_decode($data->short_description, true)[$lang->code] ?? '' : '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="brand"
                                    class="form-label">{{ labels('admin_labels.select_brand', 'Select Brand') }}</label>
                                <select class="form-select admin_product_brand_list" id="admin_product_brand_list   "
                                    name="brand">
                                    @if (isset($data->brand) && $data->brand != '')
                                        <option value="{{ $data->brand }}"
                                            {{ isset($data->brand) && $data->brand == $brands[0]->name ? 'selected' : '' }}>
                                            {{ getDynamicTranslation('brands', 'name', $data->brand, $language_code) }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group country_list_div">
                                <label for="made_in"
                                    class="form-label">{{ labels('admin_labels.made_in', 'Made IN') }}</label>
                                <select class="col-md-12 form-control country_list" id="country_list" name="made_in">
                                    @if (isset($data->made_in) && $data->made_in != '')
                                        <option value="{{ $data->made_in }}"
                                            {{ !empty($country) && isset($country[0]->name) && $data->made_in == $country[0]->name ? 'selected' : '' }}>
                                            {{ $data->made_in }}
                                        </option>
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 indicator">
                            <label for="indicator"
                                class="form-label">{{ labels('admin_labels.indicator', 'Indicator') }}</label>
                            <select class="form-select" name="indicator">
                                <option value="0" {{ $data->indicator == '0' ? 'selected' : '' }}>None
                                </option>
                                <option value="1" {{ $data->indicator == '1' ? 'selected' : '' }}>Veg
                                </option>
                                <option value="2" {{ $data->indicator == '2' ? 'selected' : '' }}>Non-Veg
                                </option>
                            </select>

                        </div>
                        <div class="col-md-6 hsn_code">
                            <div class="form-group">
                                <label for="zipcodes"
                                    class="form-label">{{ labels('admin_labels.hsn_code', 'HSN Code') }}</label>
                                <input type="text" class="col-md-12 form-control" name="hsn_code"
                                    value="{{ isset($data->hsn_code) ? $data->hsn_code : '' }}" placeholder="HSN Code">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="tags"
                                    class="form-label">{{ labels('admin_labels.tags', 'Tags') }}</label>
                                <input type="text" class="form-control" id="tags"
                                    placeholder="dress,milk,almond" name="tags"
                                    value="{{ isset($data->tags) && !empty($data->tags) ? $data->tags : '' }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4">
                    <div class="row">
                        <h6>{{ labels('admin_labels.product_tax', 'Product Tax') }}</h6>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    @php
                                        $taxes =
                                            isset($data->tax) && $data->tax != null ? explode(',', $data->tax) : [];
                                    @endphp
                                    <label for="pro_input_tax"
                                        class="form-label">{{ labels('admin_labels.select_tax', 'Select Tax') }}</label>
                                    <select name="pro_input_tax[]" class="tax_list form-select w-100" multiple>
                                        @php
                                            $tax_name = fetchDetails(
                                                'taxes',
                                                '',
                                                ['title', 'percentage', 'id'],
                                                '',
                                                '',
                                                '',
                                                '',
                                                'id',
                                                $taxes,
                                            );
                                        @endphp
                                        @foreach ($tax_name as $row)
                                            <option value="{{ $row->id }}"
                                                @if (in_array($row->id, $taxes)) selected @endif>
                                                {{ getDynamicTranslation('taxes', 'title', $row->id, $language_code) }}
                                                ({{ $row->percentage }}%)
                                            </option>
                                        @endforeach
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
                                                name="is_prices_inclusive_tax"
                                                {{ isset($data->is_prices_inclusive_tax) && $data->is_prices_inclusive_tax == '1' ? 'checked' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4 {{ $data->type == 'digital_product' ? 'd-none' : '' }}">
                    <div class="row">
                        <div class="col col-xxl-12">
                            <h6>{{ labels('admin_labels.product_quantity_and_other', 'Product Quantity & Other') }}
                            </h6>
                            <div class="row mt-4">
                                <div class="col-md-6 total_allowed_quantity">
                                    <div class="form-group">
                                        <label for="total_allowed_quantity"
                                            class="form-label">{{ labels('admin_labels.total_allowed_quantity', 'Total Allowed Quantity') }}</label>
                                        <input type="number" class="col-md-12 form-control"
                                            name="total_allowed_quantity" min=0
                                            value="{{ isset($data->total_allowed_quantity) ? $data->total_allowed_quantity : '' }}"
                                            placeholder="Total Allowed Quantity">
                                    </div>
                                </div>
                                <div class="col-md-6 minimum_order_quantity">
                                    <div class="form-group">
                                        <label for="minimum_order_quantity"
                                            class="form-label">{{ labels('admin_labels.minimum_order_quantity', 'Minimum Order Quantity') }}</label>
                                        <input type="number" class="col-md-12 form-control"
                                            name="minimum_order_quantity" min="1"
                                            value="{{ isset($data->minimum_order_quantity) ? $data->minimum_order_quantity : '' }}"
                                            placeholder="Minimum Order Quantity">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 quantity_step_size">
                                    <div class="form-group">
                                        <label for="quantity_step_size"
                                            class="form-label">{{ labels('admin_labels.quantity_step_size', 'Quantity Step Size') }}</label>
                                        <input type="number" class="col-md-12 form-control" name="quantity_step_size"
                                            min="1"
                                            value="{{ isset($data->quantity_step_size) ? $data->quantity_step_size : '' }}"
                                            placeholder="Quantity Step Size">
                                    </div>
                                </div>
                                <div class="col-md-6 warranty_period">
                                    <div class="form-group">
                                        <label for="warranty_period"
                                            class="form-label">{{ labels('admin_labels.warrenty_period', 'Warrenty Period') }}</label>
                                        <input type="text" class="col-md-12 form-control" name="warranty_period"
                                            value="{{ isset($data->warranty_period) ? $data->warranty_period : '' }}"
                                            placeholder="Warranty Period if any">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 guarantee_period">
                                    <div class="form-group">
                                        <label for="guarantee_period"
                                            class="form-label">{{ labels('admin_labels.gurantee_period', 'Guarantee Period') }}</label>
                                        <input type="text" class="col-md-12 form-control" name="guarantee_period"
                                            value="{{ isset($data->guarantee_period) ? $data->guarantee_period : '' }}"
                                            placeholder="Guarantee Period if any">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4 {{ $data->type == 'digital_product' ? 'd-none' : '' }}">
                    <div class="row">
                        <div class="col col-xxl-12">

                            <h6>{{ labels('admin_labels.delivery_and_shipping_setting', 'Delivery And Shipping Setting') }}
                            </h6>
                            <div class="row mt-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="zipcode"
                                                class="form-label">{{ labels('admin_labels.deliverable_type', 'Deliverable Type') }}</label>
                                            <select class="form-select" name="deliverable_type" id="deliverable_type">
                                                <option value="0"
                                                    {{ $data->deliverable_type == '0' ? 'selected="selected"' : '' }}>
                                                    None
                                                </option>
                                                @if ($deliverable_type->deliverable_type == '2' || $deliverable_type->deliverable_type == '3')
                                                    <option value="1" class="all_deliverable_type d-none"
                                                        {{ $data->deliverable_type == '1' ? 'selected' : '' }}>
                                                        All
                                                    </option>
                                                @else
                                                    <option value="1" class="all_deliverable_type"
                                                        {{ $data->deliverable_type == '1' ? 'selected' : '' }}>
                                                        All
                                                    </option>
                                                @endif
                                                <option value="2"
                                                    {{ $data->deliverable_type == '2' ? 'selected' : '' }}>
                                                    Included
                                                </option>
                                                {{-- <option value="3"
                                                    {{ $data->deliverable_type == '3' ? 'selected' : '' }}>
                                                    Excluded
                                                </option> --}}
                                            </select>
                                        </div>
                                    </div>

                                    @php
                                        $zones =
                                            isset($data->deliverable_zones) && $data->deliverable_zones != null
                                                ? explode(',', $data->deliverable_zones)
                                                : [];
                                    @endphp
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="zipcodes"
                                                class="form-label">{{ labels('admin_labels.deliverable_zones', 'Deliverable Zones') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <select name="deliverable_zones[]"
                                                class="search_seller_zone form-select w-100" multiple
                                                id="deliverable_zones"
                                                {{ isset($data->deliverable_type) && ($data->deliverable_type == 2 || $data->deliverable_type == 3) ? '' : 'disabled' }}>
                                                @if (isset($data->deliverable_type) && ($data->deliverable_type == 2 || $data->deliverable_type == 3))
                                                    @php
                                                        $zone_names = fetchDetails(
                                                            'zones',
                                                            '',
                                                            [
                                                                'name',
                                                                'id',
                                                                'serviceable_city_ids',
                                                                'serviceable_zipcode_ids',
                                                            ],
                                                            '',
                                                            '',
                                                            '',
                                                            '',
                                                            'id',
                                                            $zones,
                                                        );

                                                        foreach ($zone_names as $zone) {
                                                            $zone->serviceable_city_names = getCityNamesFromIds(
                                                                $zone->serviceable_city_ids,
                                                                $language_code,
                                                            );
                                                            $zone->serviceable_zipcodes = getZipcodesFromIds(
                                                                $zone->serviceable_zipcode_ids,
                                                            );
                                                        }
                                                    @endphp

                                                    @foreach ($zone_names as $row)
                                                        <option value="{{ $row->id }}"
                                                            {{ in_array($row->id, $zones) ? 'selected' : '' }}>
                                                            ID - {{ $row->id }} | Name -
                                                            {{ getDynamicTranslation('zones', 'name', $row->id, $language_code) }}
                                                            |
                                                            Serviceable Cities:
                                                            {{ implode(', ', $row->serviceable_city_names) }}
                                                            |
                                                            Serviceable Zipcodes:
                                                            {{ implode(', ', $row->serviceable_zipcodes) }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="shipping_type"
                                            class="form-label">{{ labels('admin_labels.for_standard_shipping', 'For Standard Shipping') }}
                                        </label>
                                        <select class='form-control shiprocket_type form-select' name="pickup_location"
                                            id="pickup_location">
                                            <option value=" ">
                                                {{ labels('admin_labels.select_pickup_location', 'Select Pickup Location') }}
                                            </option>
                                            @php
                                                $pickup_location =
                                                    isset($data->pickup_location) && !empty($data->pickup_location)
                                                        ? $data->pickup_location
                                                        : '';
                                            @endphp

                                            @foreach ($shipping_data as $val)
                                                <option value="{{ $val->pickup_location }}"
                                                    @if ($val->pickup_location == $pickup_location) selected @endif>
                                                    {{ $val->pickup_location }}
                                                </option>
                                            @endforeach

                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="zipcodes"
                                            class="form-label">{{ labels('admin_labels.minimum_free_delivery_order_quantity', 'Minimum Free Delivery Order Quantity') }}</label>
                                        <input type="number" class="form-control" min=0
                                            value="{{ $data->minimum_free_delivery_order_qty }}"
                                            name="minimum_free_delivery_order_qty">
                                    </div>
                                </div>

                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="zipcodes"
                                            class="form-label">{{ labels('admin_labels.delivery_charges', 'Delivery Charges') }}</label>
                                        <input type="number" class="form-control" min=0
                                            value="{{ $data->delivery_charges }}" name="delivery_charges">
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
                                                        name="cod_allowed"
                                                        {{ isset($data->cod_allowed) && $data->cod_allowed == '1' ? 'checked' : '' }}>
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
                                                <label for=""
                                                    class="me-6 text-muted form-label">[Enable/Disable]</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id=""
                                                        name="is_returnable"
                                                        {{ isset($data->is_returnable) && $data->is_returnable == '1' ? 'checked' : '' }}>
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
                                                <label for=""
                                                    class="me-6 text-muted form-label">[Enable/Disable]</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="is_cancelable_checkbox" name="is_cancelable"
                                                        {{ isset($data->is_cancelable) && $data->is_cancelable == '1' ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mt-7 {{ isset($data->is_cancelable) && $data->is_cancelable == '1' ? '' : 'collapse' }} "
                                    id='cancelable_till'>
                                    <div class="form-group">
                                        <label for="cancelable_till"
                                            class="form-label">{{ labels('admin_labels.till_which_status', 'Cancelable Till Which Status') }}?</label>
                                        <select class='form-select' name="cancelable_till">
                                            <option value='received'
                                                {{ isset($data->cancelable_till) && $data->cancelable_till == 'received' ? 'selected' : '' }}>
                                                Received</option>
                                            <option value='processed'
                                                {{ isset($data->cancelable_till) && $data->cancelable_till == 'processed' ? 'selected' : '' }}>
                                                Processed</option>
                                            <option value='shipped'
                                                {{ isset($data->cancelable_till) && $data->cancelable_till == 'shipped' ? 'selected' : '' }}>
                                                Shipped</option>
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
                                                <label for=""
                                                    class="me-6 text-muted form-label">[Enable/Disable]</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="is_attachment_required_checkbox" name="is_attachment_required"
                                                        {{ isset($data->is_attachment_required) && $data->is_attachment_required == '1' ? 'checked' : '' }}>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4">
                    <div class="row">
                        <div class="col col-xxl-12">
                            <h6>{{ labels('admin_labels.products_additional_info', 'Product Additional Info') }}
                            </h6>
                            <div class="row">
                                <div class="col-md-12 additional-info existing-additional-settings">
                                    <div class="mt-4 col-md-12 additional-info-nav-header d-flex">
                                        <div class="col-md-6">
                                            <nav class="w-100">
                                                <div class="nav nav-tabs" id="product-tab" role="tablist">
                                                    <a class="nav-item nav-link active" id="tab-for-general-price"
                                                        data-bs-toggle="tab" href="#general-settings" role="tab"
                                                        aria-controls="general-price"
                                                        aria-selected="true">{{ labels('admin_labels.general', 'General') }}</a>
                                                    <a class="nav-item nav-link edit-product-attributes"
                                                        id="tab-for-attributes" data-bs-toggle="tab"
                                                        href="#product-attributes" role="tab"
                                                        aria-controls="product-attributes"
                                                        aria-selected="false">{{ labels('admin_labels.attributes', 'Attributes') }}</a>
                                                    <a class="nav-item nav-link  <?= $data->type == 'simple_product' || $data->type == 'digital_product' ? 'disabled d-none' : 'edit-variants' ?>"
                                                        id="tab-for-variations" data-bs-toggle="tab"
                                                        href="#product-variants" role="tab"
                                                        aria-controls="product-variants"
                                                        aria-selected="false">{{ labels('admin_labels.variantions', 'Variations') }}</a>
                                                </div>
                                            </nav>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="align-items-center d-flex form-group justify-content-end">
                                                <label for="type"
                                                    class="col-md-3">{{ labels('admin_labels.type_of_product', 'Type Of Product') }}:</label>
                                                <div class="col-md-6">
                                                    <input type="hidden" name="product_type" value="">
                                                    <input type="hidden" name="simple_product_stock_status">
                                                    <input type="hidden" name="variant_stock_level_type">
                                                    <input type="hidden" name="variant_stock_status">
                                                    <select name="type" id="product-type"
                                                        class="form-control form-select"
                                                        data-placeholder=" Type to search and select type" disabled>
                                                        <option value="">
                                                            {{ labels('admin_labels.select_type', 'Select Type') }}
                                                        </option>
                                                        <option value="simple_product"
                                                            {{ isset($data->type) && $data->type == 'simple_product' ? 'selected' : '' }}>
                                                            Simple Product
                                                        </option>
                                                        <option value="variable_product"
                                                            {{ isset($data->type) && $data->type == 'variable_product' ? 'selected' : '' }}>
                                                            Variable Product
                                                        </option>
                                                        <option value="digital_product"
                                                            {{ isset($data->type) && $data->type == 'digital_product' ? 'selected' : '' }}>
                                                            Digital Product
                                                        </option>

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="attributes_values_json_data" class="d-none">
                                        <select class="select_single"
                                            data-placeholder=" Type to search and select attributes">


                                            @foreach ($attributes as $attribute)
                                                @php
                                                    $data1 = json_encode($attribute->attribute_values, 1);
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
                                                @if ($data->type == 'simple_product' || $data->type == 'digital_product')
                                                    <div id="general_price_section">
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
                                                                            <span
                                                                                class="text-asterisks text-sm">*</span></label>
                                                                        <input type="number" name="simple_price"
                                                                            class="form-control stock-simple-mustfill-field price"
                                                                            value="{{ $product_variants[0]->price }}"
                                                                            min="0.01" step="0.01" required>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-12">
                                                                    <div class="form-group">
                                                                        <label for="type"
                                                                            class="col-md-6 form-label">{{ labels('admin_labels.special_price', 'Special Price') }}
                                                                            : <span
                                                                                class="text-asterisks text-sm">*</span></label>
                                                                        <input type="number" name="simple_special_price"
                                                                            class="form-control discounted_price"
                                                                            value="{{ $product_variants[0]->special_price }}"
                                                                            min="0" step="0.01">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div
                                                                class="col-md-6 {{ $data->type == 'digital_product' ? 'd-none' : '' }}">
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
                                                                                    <small>(kg)</small>
                                                                                </label>
                                                                                <input type="number" class="form-control"
                                                                                    name="weight" placeholder="Weight"
                                                                                    id="weight"
                                                                                    value="{{ $product_variants[0]->weight }}"
                                                                                    step="0.01" min=0>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <div class="form-group">
                                                                                <label for="height"
                                                                                    class="form-label col-md-12">{{ labels('admin_labels.height', 'Height') }}
                                                                                    <small>(cms)</small></label>
                                                                                <input type="number" min=0
                                                                                    class="form-control" name="height"
                                                                                    placeholder="Height" id="height"
                                                                                    value="{{ $product_variants[0]->height }}"
                                                                                    step="0.01">

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-6">
                                                                            <div class="form-group">
                                                                                <label for="breadth"
                                                                                    class="form-label col-md-12">{{ labels('admin_labels.bredth', 'Bredth') }}
                                                                                    <small>(cms)</small>
                                                                                </label>
                                                                                <input type="number" min=0
                                                                                    class="form-control" name="breadth"
                                                                                    placeholder="Breadth" id="breadth"
                                                                                    value="{{ $product_variants[0]->breadth }}"
                                                                                    step="0.01">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-6">
                                                                            <div class="form-group">
                                                                                <label for="length"
                                                                                    class="form-label col-md-12">{{ labels('admin_labels.length', 'Length') }}
                                                                                    <small>(cms)</small>
                                                                                </label>
                                                                                <input type="number" min=0
                                                                                    class="form-control" name="length"
                                                                                    placeholder="Length" id="length"
                                                                                    value="{{ $product_variants[0]->length }}"
                                                                                    step="0.01">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div
                                                            class="form-group  simple_stock_management {{ $data->type == 'digital_product' ? 'd-none' : '' }}">
                                                            <div class="col">
                                                                <input type="checkbox"
                                                                    name="simple_stock_management_status"
                                                                    class="align-middle simple_stock_management_status form-check-input m-0"
                                                                    {{ isset($data->id) && $data->stock_type != null ? 'checked' : '' }}>
                                                                <span
                                                                    class="align-middle">{{ labels('admin_labels.enable_stock_management', 'Enable Stock Management') }}</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div
                                                        class="simple-product-level-stock-management {{ $data->type == 'digital_product' ? 'd-none' : '' }} {{ (isset($data->id) && $data->stock_type == null) || $data->type == 'digital_product' ? 'collapse' : '' }}">
                                                        <div class="row d-flex">
                                                            <div class="col col-xs-4 col-md-4">
                                                                <div class="form-group">
                                                                    <label for=""
                                                                        class="form-label">{{ labels('admin_labels.sku', 'Sku') }}
                                                                        :</label>
                                                                    <input type="text" name="product_sku"
                                                                        class="col form-control simple-pro-sku"
                                                                        value="{{ isset($data->sku) ? $data->sku : '' }}">
                                                                </div>
                                                            </div>
                                                            <div class="col col-xs-4 col-md-4">
                                                                <div class="form-group">
                                                                    <label for=""
                                                                        class="form-label">{{ labels('admin_labels.total_stock', 'Total Stock') }}
                                                                        :</label>
                                                                    <input type="number" min="0"
                                                                        name="product_total_stock"
                                                                        class="col form-control stock-simple-mustfill-field"
                                                                        value="{{ isset($data->stock) ? $data->stock : '' }}">
                                                                </div>
                                                            </div>
                                                            <div class="col col-xs-4 col-md-4">
                                                                <div class="form-group">
                                                                    <label for=""
                                                                        class="form-label">{{ labels('admin_labels.stock_status', 'Stock Status') }}
                                                                        :</label>
                                                                    <select type="text"
                                                                        class="col form-control form-select stock-simple-mustfill-field"
                                                                        id="simple_product_stock_status">
                                                                        <option value="1"
                                                                            {{ isset($data->stock_type) && $data->stock_type != null && $data->availability == '1' ? 'selected' : '' }}>
                                                                            In Stock</option>
                                                                        <option value="0"
                                                                            {{ isset($data->stock_type) && $data->stock_type != null && $data->availability == '0' ? 'selected' : '' }}>
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
                                                @else
                                                    <div id="variant_stock_level">
                                                        <div class="form-group">
                                                            <div class="col">
                                                                <input type="checkbox"
                                                                    name="variant_stock_management_status"
                                                                    class="align-middle variant_stock_status form-check-input m-0"
                                                                    {{ isset($data->id) && $data->stock_type != null ? 'checked' : '' }}>
                                                                <span class="align-middle">
                                                                    {{ labels('admin_labels.enable_stock_management', 'Enable Stock Mamagement') }}</span>
                                                            </div>
                                                        </div>



                                                        <div class="form-group <?= intval($data->stock_type) > 0 ? '' : 'collapse' ?>"
                                                            id="stock_level">
                                                            <label for="type"
                                                                class="col-md-12 form-label">{{ labels('admin_labels.choose_stock_management_type', 'Choose Stock Management Type') }}:</label>
                                                            <div class="col-md-12">
                                                                <select id="stock_level_type"
                                                                    class="form-select variant-stock-level-type"
                                                                    data-placeholder=" Type to search and select type">
                                                                    <option value=" ">Select Stock
                                                                        Type
                                                                    </option>
                                                                    <option value="product_level"
                                                                        {{ isset($data->id) && $data->stock_type == '1' ? 'Selected' : '' }}>
                                                                        Product Level (
                                                                        Stock
                                                                        Will Be Managed Generally )</option>
                                                                    <option value="variable_level"
                                                                        {{ isset($data->id) && $data->stock_type == '2' ? 'Selected' : '' }}>
                                                                        Variable Level (
                                                                        Stock Will Be Managed Variant Wise )
                                                                    </option>
                                                                </select>
                                                                <div
                                                                    class="form-group variant-product-level-stock-management <?= intval($data->stock_type) == 1 ? '' : 'collapse' ?>">
                                                                    <div class="row d-flex mt-5">
                                                                        <div class="col col-xs-4 col-md-4">
                                                                            <div class="form-group">
                                                                                <label for=""
                                                                                    class="form-label">{{ labels('admin_labels.sku', 'Sku') }}
                                                                                    :</label>
                                                                                <input type="text"
                                                                                    name="sku_variant_type"
                                                                                    value="<?= intval($data->stock_type) == 1 && isset($product_variants[0]->id) && !empty($product_variants[0]->sku) ? $product_variants[0]->sku : '' ?>"
                                                                                    class="col form-control">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col col-xs-4 col-md-4">
                                                                            <div class="form-group">
                                                                                <label for=""
                                                                                    class="form-label">{{ labels('admin_labels.total_stock', 'Total Stock') }}:</label>
                                                                                <input type="number" min="1"
                                                                                    name="total_stock_variant_type"
                                                                                    class="col form-control variant-stock-mustfill-field"
                                                                                    value="<?= intval($data->stock_type) == 1 && isset($product_variants[0]->id) && !empty($product_variants[0]->stock) ? $product_variants[0]->stock : '' ?>">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col col-xs-4 col-md-4">
                                                                            <div class="form-group">
                                                                                <label for=""
                                                                                    class="form-label">{{ labels('admin_labels.stock_status', 'Stock Status') }}:</label>
                                                                                <select type="text"
                                                                                    id="stock_status_variant_type"
                                                                                    name="variant_status"
                                                                                    class="col form-control form-select variant-stock-mustfill-field">
                                                                                    <option value="1"
                                                                                        <?= intval($data->stock_type) == 1 && isset($product_variants[0]->id) && $product_variants[0]->availability == '1' ? 'Selected' : '' ?>>
                                                                                        In Stock
                                                                                    </option>
                                                                                    <option value="0"
                                                                                        <?= intval($data->stock_type) == 1 && isset($product_variants[0]->id) && $product_variants[0]->availability == '0' ? 'Selected' : '' ?>>
                                                                                        Out Of Stock
                                                                                    </option>
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="col float-end"> <a href="javascript:void(0);"
                                                                    class="btn btn-dark save-variant-general-settings">{{ labels('admin_labels.save_settings', 'Save Settings') }}</a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div id="digital_product_setting"
                                                class="{{ $data->type == 'digital_product' ? '' : 'collapse' }}">
                                                <div class="row form-group">
                                                    <div class="col-md-6 d-flex">
                                                        <label for="download_allowed"
                                                            class="col form-label">{{ labels('admin_labels.is_download_allowed', 'IS Download Allowed') }}?</label>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="download_allowed" id="download_allowed"
                                                                {{ $data->download_allowed != 'null' && $data->download_allowed == 1 ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6 col-xs-6 {{ isset($data->download_type) ? '' : 'collapse' }}"
                                                        id='download_type'>
                                                        <label for="download_link_type"
                                                            class="col form-label">{{ labels('admin_labels.download_link_type', 'Download Link Type') }}
                                                        </label>
                                                        <select class='form-control form-select' name="download_link_type"
                                                            id="download_link_type">
                                                            <option value=''>None</option>
                                                            <option value='self_hosted'
                                                                {{ isset($data->download_type) && $data->download_type == 'self_hosted' ? 'selected' : '' }}>
                                                                Self Hosted
                                                            </option>
                                                            <option value='add_link'
                                                                {{ isset($data->download_type) && $data->download_type == 'add_link' ? 'selected' : '' }}>
                                                                Add Link</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-6 {{ isset($data->download_type) && $data->download_type == 'add_link' ? '' : 'd-none' }}"
                                                        id="digital_link_container">
                                                        <label for="video"
                                                            class="col form-label ml-1">{{ labels('admin_labels.digital_product_link', 'Digital Product Link') }}
                                                            <span class='text-asterisks text-sm'>*</span></label>
                                                        <input type="url" class='form-control' name='download_link'
                                                            id='download_link'
                                                            value="{{ isset($data->download_type) && $data->download_type == 'add_link' ? $data->download_link : '' }}"
                                                            placeholder="Paste digital product link or URL here">
                                                    </div>

                                                    <div class="container-fluid row image-upload-section">
                                                    </div>
                                                    <div class="form-group {{ isset($data->download_type) && $data->download_type == 'self_hosted' ? '' : 'd-none' }} mt-5"
                                                        id="digital_media_container">
                                                        <a class="media_link" data-input="pro_input_zip"
                                                            data-isremovable="0" data-is-multiple-uploads-allowed="0"
                                                            data-media_type="archive,document" data-bs-toggle="modal"
                                                            data-bs-target="#media-upload-modal" value="Upload Photo">
                                                            <div
                                                                class="col-md-6 file_upload_box border file_upload_border">
                                                                <div class="mt-2">
                                                                    <div class="col-md-12  text-center">
                                                                        <div>
                                                                            <p class="caption text-dark-secondary">
                                                                                Choose file for
                                                                                product.</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </a>
                                                        <div class="row mt-3 image-upload-section">
                                                            <div
                                                                class="bg-white grow image product-image-container rounded shadow text-center m-2">
                                                                <div class='image-upload-div'><img class="img-fluid mb-2"
                                                                        src={{ asset('/assets/admin/images/doc-file.png') }}
                                                                        alt="Product File" title="Product File">
                                                                </div>
                                                                <input type="hidden" name="pro_input_zip"
                                                                    value="{{ $data->download_link }}">
                                                            </div>
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
                                                        <span class="ml-3">check if the attribute is
                                                            to be
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
                                        <div class="tab-pane fade" id="product-variants" role="tabpanel"
                                            aria-labelledby="product-variants-tab">
                                            <div class="col-md-12">
                                                <a href="javascript:void(0);" id="reset_variants"
                                                    class="btn btn-primary btn-sm mb-3 collapse text-white">{{ labels('admin_labels.reset_variants', 'Reset Variants') }}</a>
                                            </div>

                                            <div class="clearfix"></div>
                                            <div
                                                class="form-group text-center row my-auto p-2 border rounded bg-gray-light col-md-12 no-variants-added">
                                                <div class="col-md-12 text-center">
                                                    {{ labels('admin_labels.no_product_variations_added', 'No Product Variations Added') }}!
                                                </div>
                                            </div>
                                            <div id="variants_process" class="ui-sortable">
                                                <div
                                                    class="form-group move p-2 pe-0 product-variant-selectbox ps-0 pt-3 rounded row">
                                                    <div class="col-1 text-center my-auto">
                                                        <i class="fas fa-sort"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-4 p-5">
                    <div class="row">
                        <div class="col-12">
                            <div class="card p-5">
                                <h6>{{ labels('admin_labels.product_media', 'Product Media') }}(
                                    {{ labels('admin_labels.images', 'Images') }} )
                                </h6>
                                <div class="row mt-4">
                                    {{-- Main Image --}}
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            {{ labels('admin_labels.main_image', 'Main Image') }}
                                            <span class="text-asterisks text-sm">*</span>
                                        </label>
                                        <div class="form-group">
                                            <a class="media_link" data-input="pro_input_image" data-isremovable="0"
                                                data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                data-bs-target="#media-upload-modal" value="Upload Photo">
                                                <div class="file_upload_box border file_upload_border">
                                                    <div class="mt-2 text-center">
                                                        <p class="caption text-dark-secondary">Choose image for product.
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            <p class="caption text-muted mt-2">(Recommended Size : 180 x 180 pixels)</p>
                                            <p class="caption text-danger mt-2">*Only Choose When Update is necessary</p>
                                            <div class="row mt-3 image-upload-section">
                                                <div class="bg-white grow image rounded shadow text-center m-2">
                                                    <div class="image-upload-div">
                                                        <img class="img-fluid"
                                                            src="{{ route('admin.dynamic_image', [
                                                                'url' => getMediaImageUrl($data->image),
                                                                'width' => 150,
                                                                'quality' => 90,
                                                            ]) }}"
                                                            alt="Not Found" />
                                                    </div>
                                                    <input type="hidden" name="pro_input_image"
                                                        value="{{ $data->image }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Other Images --}}
                                    <div class="col-md-6">
                                        <label class="form-label">
                                            {{ labels('admin_labels.other_images', 'Other Images') }}
                                        </label>
                                        <div class="form-group">
                                            <a class="media_link" data-input="other_images[]" data-isremovable="1"
                                                data-is-multiple-uploads-allowed="1" data-bs-toggle="modal"
                                                data-bs-target="#media-upload-modal" value="Upload Photo">
                                                <div class="file_upload_box border file_upload_border">
                                                    <div class="mt-2 text-center">
                                                        <p class="caption text-dark-secondary">Choose images for product.
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            <p class="caption text-muted mt-2">(Recommended Size : 180 x 180 pixels)</p>
                                            <div class="row mt-3 image-upload-section">
                                                @php $other_images = json_decode($data->other_images); @endphp
                                                @if (!empty($other_images))
                                                    @foreach ($other_images as $row)
                                                        <div class="col-6">
                                                            <div
                                                                class="bg-white grow image rounded shadow text-center m-2">
                                                                <div class="image-upload-div">
                                                                    <img class="img-fluid mb-2"
                                                                        src="{{ route('admin.dynamic_image', [
                                                                            'url' => getMediaImageUrl($row),
                                                                            'width' => 150,
                                                                            'quality' => 90,
                                                                        ]) }}"
                                                                        alt="Not Found" />
                                                                </div>
                                                                <a href="javascript:void(0)" class="delete-img"
                                                                    data-id="{{ $data->id }}"
                                                                    data-field="other_images"
                                                                    data-img="{{ $row }}" data-table="products"
                                                                    data-path="{{ $row }}" data-isjson="true">
                                                                    <span
                                                                        class="btn btn-block bg-gradient-danger text-danger btn-xs">
                                                                        <i class="far fa-trash-alt"></i> Delete
                                                                    </span>
                                                                </a>
                                                                <input type="hidden" name="other_images[]"
                                                                    value="{{ $row }}">
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div> <!-- end .row -->
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4 p-5">
                    <div class="row">
                        <div class="col-6 col-xxl-12">
                            <div class="card p-5">
                                <h6>{{ labels('admin_labels.product_media', 'Product Media') }} (
                                    {{ labels('admin_labels.videos', 'Videos') }} )
                                </h6>
                                <div class="row mt-4">
                                    {{-- Video Type --}}
                                    <div class="form-group col-md-6">
                                        <label for="video_type" class="form-label">
                                            {{ labels('admin_labels.video_type', 'Video Type') }}
                                        </label>
                                        <select class="form-select" name="video_type" id="video_type">
                                            <option value=""
                                                {{ isset($data->video_type) && ($data->video_type == '' || $data->video_type == null) ? 'selected' : '' }}>
                                                None</option>
                                            <option value="self_hosted"
                                                {{ isset($data->video_type) && $data->video_type == 'self_hosted' ? 'selected' : '' }}>
                                                Self Hosted</option>
                                            <option value="youtube"
                                                {{ isset($data->video_type) && $data->video_type == 'youtube' ? 'selected' : '' }}>
                                                Youtube</option>
                                            <option value="vimeo"
                                                {{ isset($data->video_type) && $data->video_type == 'vimeo' ? 'selected' : '' }}>
                                                Vimeo</option>
                                        </select>
                                    </div>

                                    {{-- Video Link (YouTube/Vimeo) --}}
                                    <div class="col-md-6 {{ isset($data->video_type) && ($data->video_type == 'youtube' || $data->video_type == 'vimeo') ? '' : 'd-none' }}"
                                        id="video_link_container">
                                        <label for="video" class="form-label">
                                            {{ labels('admin_labels.video_link', 'Video Link') }}
                                            <span class="text-asterisks text-sm">*</span>
                                        </label>
                                        <input type="text" class="form-control" name="video" id="video"
                                            value="{{ isset($data->video_type) && ($data->video_type == 'youtube' || $data->video_type == 'vimeo') ? $data->video : '' }}"
                                            placeholder="Paste Youtube / Vimeo Video link or URL here">
                                    </div>

                                    {{-- Video File (Self Hosted) --}}
                                    <div class="col-md-6 {{ isset($data->video_type) && $data->video_type == 'self_hosted' ? '' : 'd-none' }}"
                                        id="video_media_container">
                                        <label for="" class="form-label">Video<span
                                                class="text-asterisks text-sm">*</span></label>
                                        <div class="form-group">
                                            <a class="media_link" data-input="pro_input_video" data-isremovable="1"
                                                data-is-multiple-uploads-allowed="0" data-media_type="video"
                                                data-bs-toggle="modal" data-bs-target="#media-upload-modal"
                                                value="Upload Photo">

                                                <div class="file_upload_box border file_upload_border">
                                                    <div class="mt-2 text-center">
                                                        <p class="caption text-dark-secondary">Choose video for product.
                                                        </p>
                                                    </div>
                                                </div>
                                            </a>
                                            <p class="caption text-danger mt-2">*Only Choose When Update is necessary</p>
                                            <div class="row mt-3 image-upload-section">
                                                <div
                                                    class="bg-white grow image product-image-container rounded shadow text-center m-2">
                                                    <div class='image-upload-div'>
                                                        <img class="img-fluid mb-2"
                                                            src="{{ asset('/assets/admin/images/video-file.png') }}"
                                                            alt="Product Video" title="Product Video">
                                                    </div>
                                                    <input type="hidden" name="pro_input_video"
                                                        value="{{ $data->video }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card p-5 mt-4">
                    <div class="col col-xxl-12">
                        <h6>{{ labels('admin_labels.product_description', 'Product Description') }}</h6>
                        <div class="row mt-4">
                            <div class="col-md-6">
                                <label class="form-label">{{ labels('admin_labels.description', 'Description') }}</label>
                                <textarea name="pro_input_description" class="form-control addr_editor" placeholder="Place some text here">{{ isset($data->description) ? $data->description : '' }}</textarea>
                            </div>

                            <div class="col-md-6">
                                <label
                                    class="form-label">{{ labels('admin_labels.extra_description', 'Extra Description') }}</label>
                                <textarea name="extra_input_description" class="form-control addr_editor" placeholder="Place some text here">{{ isset($data->extra_description) ? $data->extra_description : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="float-end ml-2 mt-xxl-3 mt-7 text-center">
                    <button type="submit" id="submit_btn"
                        class="btn btn-primary submit_button">{{ labels('admin_labels.submit', 'Submit') }}</button>
                </div>
            </form>
        </div>
    </section>
@endsection
