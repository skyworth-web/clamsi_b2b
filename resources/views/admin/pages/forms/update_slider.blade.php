@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_slider', 'Update Slider') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.update_slider', 'Update Slider')" :subtitle="labels(
        'admin_labels.enhance_visual_appeal_with_effortless_slider_integration',
        'Enhance Visual Appeal with Effortless Slider Integration',
    )" :breadcrumbs="[['label' => labels('admin_labels.sliders', 'Sliders')]]" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="form-horizontal form-submit-event submit_form" action="{{ route('sliders.update', $data->id) }}"
                    method="POST" id="" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <h5 class="mb-4">
                            {{ labels('admin_labels.update_slider', 'Update Slider') }}
                        </h5>
                        <div class="form-group">
                            <label for="offer_type">{{ labels('admin_labels.type', 'Type') }}
                                <span class='text-asterisks text-sm'>*</span> </label>
                            <select name="type" id="offer_type" class="form-control form-select type_event_trigger"
                                required="">
                                <option value=" ">
                                    {{ labels('admin_labels.select_type', 'Select Type') }}
                                </option>
                                <option value="default" {{ $data->type == 'default' ? 'selected' : '' }}>Default
                                </option>
                                <option value="categories" {{ $data->type == 'categories' ? 'selected' : '' }}>Category
                                </option>
                                <option value="products" {{ $data->type == 'products' ? 'selected' : '' }}>Product
                                </option>
                                <option value="combo_products" {{ $data->type == 'combo_products' ? 'selected' : '' }}>
                                    Combo
                                    Product
                                </option>
                                <option value="slider_url" {{ $data->type == 'slider_url' ? 'selected' : '' }}>Slider
                                    URL
                                </option>
                            </select>
                        </div>

                        <div id="type_add_html">
                            <div
                                class="form-group slider-categories {{ isset($data->type) && strtolower($data->type) == 'categories' ? '' : 'd-none' }}">
                                <label for="category_id">
                                    {{ labels('admin_labels.categories', 'Categories') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="category_id" class="form-control form-select">
                                    <option value="">{{ labels('admin_labels.select_category', 'Select Category') }}
                                    </option>
                                    {!! renderCategories($categories, 0, 0, $data->type_id ?? null) !!}
                                </select>

                            </div>

                            <div
                                class="form-group offer-url {{ isset($data->type) && strtolower($data->type) == 'slider_url' ? '' : 'd-none' }}">
                                <label for="slider_url"> Link <span class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control" placeholder="https://example.com" name="link"
                                    value={{ isset($data->link) ? $data->link : '' }}>
                            </div>

                            <div
                                class="form-group row slider-products {{ isset($data->type) && strtolower($data->type) == 'products' ? '' : 'd-none' }}">
                                <label for="product_id"
                                    class="control-label">{{ labels('admin_labels.products', 'Products') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <div class="col-md-12 search_admin_product_parent">
                                    <select name="product_id" class="search_admin_product w-100"
                                        data-placeholder=" Type to search and select products" onload="multiselect()">
                                        @if (isset($data->type_id) && isset($data->type) && $data->type == 'products')
                                            @php $product_details = fetchDetails('products', ['id' => $data->type_id], '*'); @endphp
                                            @if (!empty($product_details))
                                                <option value={{ $product_details[0]->id }} selected>
                                                    {{ getDynamicTranslation('products', 'name', $product_details[0]->id, $language_code) }}
                                                </option>
                                            @endif
                                        @endif
                                    </select>
                                </div>
                            </div>
                            <div
                                class="form-group row slider-combo-products {{ isset($data->type) && strtolower($data->type) == 'combo_products' ? '' : 'd-none' }}">
                                <label for="product_id"
                                    class="control-label">{{ labels('admin_labels.combo_products', 'Combo Products') }}
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
                        <div class="form-group">
                            <div>
                                <label for="image">{{ labels('admin_labels.image', 'Slider Image') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                            </div>
                            <div class="col-md-12">
                                <div class="row form-group">
                                    <div class="col-md-4 file_upload_box border file_upload_border mt-2">
                                        <div class="mt-2">
                                            <div class="col-md-12  text-center">
                                                <div>
                                                    <a class="media_link" data-input="image" data-isremovable="0"
                                                        data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                        data-bs-target="#media-upload-modal" value="Upload Photo">
                                                        <h4><i class='bx bx-upload'></i> Upload
                                                    </a></h4>
                                                    <p class="image_recommendation">Recommended Size: 1648 x 610 pixels</p>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                    @if ($data->image && !empty($data->image))
                                        <div class="row col-md-6">
                                            <label for="" class="text-danger mt-3">*Only Choose When Update is
                                                necessary</label>
                                            <div class="container-fluid row image-upload-section">
                                                <div
                                                    class="col-md-3 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                    <div class='image-upload-div'>
                                                        <img class="img-fluid mb-2"
                                                            src="{{ route('admin.dynamic_image', [
                                                                'url' => getMediaImageUrl($data->image),
                                                                'width' => 150,
                                                                'quality' => 90,
                                                            ]) }}"
                                                            alt="Not Found" />
                                                    </div>
                                                    <input type="hidden" name="image" value='<?= $data->image ?>'>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.update_slider', 'Update Slider') }}</button>
                        </div>
                        <div class="d-flex justify-content-center">
                            <div class="form-group" id="error_box">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
