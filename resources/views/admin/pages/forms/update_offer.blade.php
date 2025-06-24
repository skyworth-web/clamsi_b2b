@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_offer', 'Update Offer') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.update_offer', 'Update Offer')" :subtitle="labels(
        'admin_labels.boost_sales_with_captivating_and_profitable_promotions',
        'Boost Sales with Captivating and Profitable Promotions',
    )" :breadcrumbs="[['label' => labels('admin_labels.update_offer', 'Update Offer')]]" />


    <div class="col-md-12">

        <form class="form-horizontal form-submit-event submit_form" action="{{ route('offers.update', $data->id) }}"
            method="POST" id="" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.update_offer', 'Update Offer') }}
                            </h5>
                            <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="language-nav-link nav-link active" id="tab-en" data-bs-toggle="tab"
                                        data-bs-target="#content-en" type="button" role="tab"
                                        aria-controls="content-en" aria-selected="true">
                                        {{ labels('admin_labels.default', 'Default') }}
                                    </button>
                                </li>
                                {!! generateLanguageTabsNav($languages) !!}
                            </ul>
                            <div class="tab-content mt-3" id="UpdatebrandTabsContent">
                                <!-- Default 'en' tab content -->
                                <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                    aria-labelledby="tab-en">
                                    <div class="mb-3">
                                        <label for="brand_name"
                                            class="form-label">{{ labels('admin_labels.title', 'Title') }}<span
                                                class="text-asterisks text-sm">*</span></label>

                                        <input type="text" class="form-control" id="basic-default-fullname"
                                            placeholder="Gucci" name="title"
                                            value="{{ isset($data->title) ? json_decode($data->title)->en : '' }}">
                                    </div>
                                </div>
                                {!! generateUpdateableLanguageTabs(
                                    $languages,
                                    $data->title,
                                    'admin_labels.title',
                                    'Title',
                                    'translated_offer_title',
                                ) !!}

                            </div>

                            <div class="form-group">
                                <label for="offer_type" class="mb-2">{{ labels('admin_labels.type', 'Type') }}
                                    <span class='text-asterisks text-sm'>*</span>
                                </label>
                                <select name="type" id="offer_type" class="form-control type_event_trigger form-select"
                                    required="">
                                    <option value=" ">Select Type</option>
                                    <option value="default" {{ $data->type == 'default' ? 'selected' : '' }}>Default
                                    </option>
                                    <option value="categories" {{ $data->type == 'categories' ? 'selected' : '' }}>
                                        Category
                                    </option>
                                    <option value="products" {{ $data->type == 'products' ? 'selected' : '' }}>Specific
                                        Product
                                    </option>
                                    <option value="combo_products" {{ $data->type == 'combo_products' ? 'selected' : '' }}>
                                        Specific Combo Product
                                    </option>
                                    <option value="all_products" {{ $data->type == 'all_products' ? 'selected' : '' }}>
                                        All
                                        Products
                                    </option>
                                    <option value="all_combo_products"
                                        {{ $data->type == 'all_combo_products' ? 'selected' : '' }}>
                                        All Combo
                                        Products
                                    </option>
                                    <option value="brand" {{ $data->type == 'brand' ? 'selected' : '' }}>Brand
                                    </option>
                                    <option value="offer_url" {{ $data->type == 'offer_url' ? 'selected' : '' }}>Offer
                                        URL
                                    </option>
                                </select>
                            </div>
                            <div id="type_add_html">
                                <div
                                    class="form-group slider-categories {{ isset($data->type) && strtolower($data->type) == 'categories' ? '' : 'd-none' }} mt-4">
                                    <label for="category_id" class="mb-2">
                                        {{ labels('admin_labels.categories', 'Categories') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <select name="category_id" class="form-control form-select">
                                        <option value="">
                                            {{ labels('admin_labels.select_category', 'Select Category') }}</option>
                                        {!! renderCategories($categories, 0, 0, $data->type_id ?? null) !!}
                                    </select>
                                </div>
                                <div
                                    class="form-group slider-brand {{ isset($data->type) && strtolower($data->type) == 'brand' ? '' : 'd-none' }} mt-4">
                                    <label for="category_id" class="mb-2">
                                        {{ labels('admin_labels.brands', 'Brands') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <select name="brand_id" class="form-control">
                                        <option value="">Select brand</option>
                                        @foreach ($brands as $row)
                                            {{ $selected = $row['id'] == $data->type_id && strtolower($data->type) == 'brand' ? 'selected' : '' }}

                                            <option value="<?= $row['id'] ?>" {{ $selected }}>
                                                <?= getDynamicTranslation('brands', 'name', $row['id'], $language_code) ?>
                                            </option>
                                        @endforeach
                                    </select>

                                </div>
                                <div
                                    class="form-group offer-url {{ isset($data->type) && strtolower($data->type) == 'offer_url' ? '' : 'd-none' }} mt-4">
                                    <label for="slider_url" class="mb-2">
                                        {{ labels('admin_labels.link', 'Link') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="text" class="form-control" placeholder="https://example.com"
                                        name="link" value={{ isset($data->link) ? $data->link : '' }}>
                                </div>
                                <div
                                    class="form-group row slider-products {{ isset($data->type) && strtolower($data->type) == 'products' ? '' : 'd-none' }} mt-4">
                                    <label for="product_id"
                                        class="control-label mb-2">{{ labels('admin_labels.products', 'Products') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <div class="col-md-12 search_admin_product_parent">
                                        <select name="product_id" class="search_admin_product w-100"
                                            data-placeholder=" Type to search and select products" onload="multiselect()">
                                            @if (isset($data->type_id) && isset($data->type) && $data->type == 'products')
                                                @php
                                                    $product_details = fetchDetails(
                                                        'products',
                                                        ['id' => $data->type_id],
                                                        '*',
                                                    );
                                                @endphp
                                                @if (!empty($product_details))
                                                    <option value="{{ $product_details[0]->id }}" selected>
                                                        {{ getDynamicTranslation('products', 'name', $product_details[0]->id, $language_code) }}
                                                    </option>
                                                @endif
                                            @endif

                                        </select>
                                    </div>
                                </div>
                                <div
                                    class="form-group row slider-combo-products {{ isset($data->type) && strtolower($data->type) == 'combo_products' ? '' : 'd-none' }} mt-4">
                                    <label for="product_id"
                                        class="control-label mb-2">{{ labels('admin_labels.combo_products', 'Combo Products') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <div class="col-md-12">
                                        <select name="combo_product_id" class="search_admin_combo_product w-100"
                                            data-placeholder=" Type to search and select products" onload="multiselect()">
                                            @if (isset($data->type_id) && isset($data->type) && $data->type == 'combo_products')
                                                @php $product_details = fetchDetails('combo_products', ['id' => $data->type_id], '*'); @endphp
                                                @if (!empty($product_details))
                                                    <option value={{ $product_details[0]->id }} selected>
                                                        {{ getDynamicTranslation('combo_products', 'title', $product_details[0]->id, $language_code) }}
                                                    </option>
                                                @endif
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group row offer_discount d-none mt-4" id="min_max_section">
                                <div class="form-group col-md-6">
                                    <label
                                        for="">{{ labels('admin_labels.minimum_offer_discount', 'Minimum offer Discount(%)') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="number" class="form-control" name="min_discount" id="min_discount"
                                        min=1 max=100
                                        value="{{ isset($data->min_discount) && !empty($data->min_discount) ? $data->min_discount : '' }}">
                                </div>
                                <div class="form-group col-md-6">
                                    <label
                                        for="">{{ labels('admin_labels.maximum_offer_discount', 'Maximum offer Discount(%)') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="number" class="form-control" name="max_discount" id="max_discount"
                                        min=1 max=100
                                        value="{{ isset($data->max_discount) && !empty($data->max_discount) ? $data->max_discount : '' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.offer_images', 'Offer Images') }}
                            </h5>
                            <div class="form-group col-md-12 mb-4">
                                <label for="image" class="mb-2">{{ labels('admin_labels.image', 'Image') }}
                                    <span class='text-asterisks text-sm'>*</span>
                                </label>

                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <div class="col-md-12">
                                            <div class="row form-group">
                                                <div class="col-md-4 file_upload_box border file_upload_border mt-2">
                                                    <div class="mt-2">
                                                        <div class="col-md-12  text-center">
                                                            <div>
                                                                <a class="media_link" data-input="image"
                                                                    data-isremovable="0"
                                                                    data-is-multiple-uploads-allowed="0"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#media-upload-modal"
                                                                    value="Upload Photo">
                                                                    <h4><i class='bx bx-upload'></i> Upload
                                                                </a></h4>
                                                                <p class="image_recommendation">Recommended Size: 1648
                                                                    x 610 pixels</p>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                @if ($data->image && !empty($data->image))
                                                    <div class="row col-md-6">
                                                        <label for="" class="text-danger">*Only Choose When Update
                                                            is
                                                            necessary</label>
                                                        <div class="container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-6 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                <div class='image-upload-div'>
                                                                    <img class="img-fluid mb-2"
                                                                        src="{{ route('admin.dynamic_image', [
                                                                            'url' => getMediaImageUrl($data->image),
                                                                            'width' => 150,
                                                                            'quality' => 90,
                                                                        ]) }}"
                                                                        alt="Not Found" />
                                                                </div>
                                                                <input type="hidden" name="image"
                                                                    value='<?= $data->image ?>'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="form-group col-md-12">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.banner_image', 'Banner Image') }}<span
                                        class="text-asterisks text-sm">*</span></label>
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <div class="col-md-12">
                                            <div class="row form-group">
                                                <div class="col-md-4 file_upload_box border file_upload_border mt-2">
                                                    <div class="mt-2">
                                                        <div class="col-md-12  text-center">
                                                            <div>
                                                                <a class="media_link" data-input="banner_image"
                                                                    data-isremovable="0"
                                                                    data-is-multiple-uploads-allowed="0"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#media-upload-modal"
                                                                    value="Upload Photo">
                                                                    <h4><i class='bx bx-upload'></i> Upload
                                                                </a></h4>
                                                                <p class="image_recommendation">Recommended Size: 1648
                                                                    x 610 pixels</p>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                                @if ($data->banner_image && !empty($data->banner_image))
                                                    <div class="row col-md-6">
                                                        <label for="" class="text-danger">*Only Choose When Update
                                                            is
                                                            necessary</label>
                                                        <div class="container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-6 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                <div class='image-upload-div'>
                                                                    <img class="img-fluid mb-2"
                                                                        src="{{ route('admin.dynamic_image', [
                                                                            'url' => getMediaImageUrl($data->banner_image),
                                                                            'width' => 150,
                                                                            'quality' => 90,
                                                                        ]) }}"
                                                                        alt="Not Found" />
                                                                </div>
                                                                <input type="hidden" name="banner_image"
                                                                    value='<?= $data->banner_image ?>'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.update_offer', 'Update Offer') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </div>
@endsection
