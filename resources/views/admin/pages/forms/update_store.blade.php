@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_store', 'Update Store') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.add_store', 'Add Store')" :subtitle="labels(
        'admin_labels.lets_unlesh_a_wave_of_new_stores_on_your_marketplace',
        'Let’s unleash a wave of new stores on your marketplace.',
    )" :breadcrumbs="[['label' => labels('admin_labels.update_store', 'Update Store')]]" />

    @php
        $store_settings = json_decode($data->store_settings);
        // dd($store_settings);
    @endphp

    <div class="col-xxl-12 p-0">
        <div class="row cols-5 d-flex">
            <div class="col-md-12 col-xl-4 col-xxl-3">
                <div class="card p-5">
                    <div class="card1">
                        <ul id="store_progressbar" class="text-center">
                            <li class="active step0"></li>
                            <li class="step0"></li>
                            <li class="step0"></li>
                            <li class="step0"></li>
                            <li class="step0"></li>
                            <li class="step0"></li>
                            <li class="step0"></li>
                            <li class="step0"></li>
                        </ul>

                        <h6 class="mt-1">{{ labels('admin_labels.store_details', 'Store Details') }}</h6>
                        <h6>{{ labels('admin_labels.store_images', 'Store Images') }}</h6>
                        <h6>{{ labels('admin_labels.app_settings', 'App & Web Setting') }}</h6>
                        <h6>{{ labels('admin_labels.cards_display_styles', 'Cards Display Styles') }}</h6>
                        <h6>{{ labels('admin_labels.categories_display_style', 'Categories Display Style') }}</h6>
                        <h6>{{ labels('admin_labels.brands_display_style', 'Brands And Wishlist Display Style') }}</h6>
                        <h6>{{ labels('admin_labels.offer_display_style', 'Offer Display Style') }}</h6>
                        <h6>{{ labels('admin_labels.delivery_charge_setting', 'Delivery Charge Setting') }}
                        </h6>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xl-9 mt-md-2 mt-sm-2 mt-xl-2 mt-xxl-0">
                <form action="{{ url('/admin/store/update/' . $data->id) }}" enctype="multipart/form-data" method="POST"
                    class="submit_form">
                    @method('PUT')
                    @csrf
                    <div class="card2 first-screen ml-2 show">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5>{{ labels('admin_labels.store_details', 'Store Details') }}</h5>
                                    <div class="row mt-4">
                                        {{-- <div class="mb-3 col-md-12">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.name', 'Name') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="Electronics Store" name="name"
                                                value="{{ $data->name ?? '' }}">

                                        </div>
                                        <div class="mb-3 col-md-12">
                                            <label class="form-label"
                                                for="description">{{ labels('admin_labels.description', 'Description') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <textarea name="description" class="form-control" placeholder="Write here your description">{{ $data->description ?? '' }}</textarea>
                                        </div> --}}
                                        <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="language-nav-link nav-link active" id="tab-en"
                                                    data-bs-toggle="tab" data-bs-target="#content-en" type="button"
                                                    role="tab" aria-controls="content-en" aria-selected="true">
                                                    {{ labels('admin_labels.default', 'Default') }}
                                                </button>
                                            </li>
                                            @foreach ($languages as $lang)
                                                @if ($lang->code !== 'en')
                                                    <li class="nav-item" role="presentation">
                                                        <button class="language-nav-link nav-link"
                                                            id="tab-{{ $lang->code }}" data-bs-toggle="tab"
                                                            data-bs-target="#content-{{ $lang->code }}" type="button"
                                                            role="tab" aria-controls="content-{{ $lang->code }}"
                                                            aria-selected="false">
                                                            {{ $lang->language }}
                                                        </button>
                                                    </li>
                                                @endif
                                            @endforeach
                                        </ul>

                                        <div class="tab-content mt-3" id="UpdatebrandTabsContent">
                                            <!-- Default 'en' tab content -->
                                            <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                                aria-labelledby="tab-en">
                                                <div class="mb-3">
                                                    <label for="brand_name" class="form-label">
                                                        {{ labels('admin_labels.name', 'Name') }}
                                                        <span class="text-asterisks text-sm">*</span>
                                                    </label>
                                                    <input type="text" class="form-control" placeholder="Gucci"
                                                        name="name"
                                                        value="{{ isset($data->name) ? json_decode($data->name)->en : '' }}">

                                                    <label for="short_description" class="control-label mb-2 mt-2">
                                                        {{ labels('admin_labels.description', 'Description') }}
                                                        <span class='text-asterisks text-sm'>*</span>
                                                    </label>
                                                    <input type="text" class="form-control" name="description"
                                                        value="{{ isset($data->description) ? json_decode($data->description)->en : '' }}"
                                                        placeholder="Description">
                                                </div>
                                            </div>

                                            <!-- Dynamic Language Tabs -->
                                            @foreach ($languages as $lang)
                                                @if ($lang->code !== 'en')
                                                    <div class="tab-pane fade" id="content-{{ $lang->code }}"
                                                        role="tabpanel" aria-labelledby="tab-{{ $lang->code }}">
                                                        <div class="mb-3">
                                                            <label for="translated_title_{{ $lang->code }}"
                                                                class="form-label">
                                                                {{ labels('admin_labels.name', 'Name') }}
                                                                ({{ $lang->language }})
                                                            </label>
                                                            <input type="text" class="form-control"
                                                                name="translated_store_name[{{ $lang->code }}]"
                                                                value="{{ isset($data->name) ? json_decode($data->name, true)[$lang->code] ?? '' : '' }}">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label for="translated_short_description_{{ $lang->code }}"
                                                                class="form-label">
                                                                {{ labels('admin_labels.description', 'Description') }}
                                                                ({{ $lang->language }})
                                                            </label>
                                                            <input type="text" class="form-control"
                                                                name="translated_store_description[{{ $lang->code }}]"
                                                                value="{{ isset($data->description) ? json_decode($data->description, true)[$lang->code] ?? '' : '' }}">
                                                        </div>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="row mb-3">

                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <label for="is_single_seller_order_system" class="me-8">
                                                    {{ labels('admin_labels.single_seller_order_system', 'Single Seller Order System') }}?<span
                                                        class='text-danger text-sm'></span>
                                                    <small> ({{ labels('admin_labels.for_cart', 'For Cart') }})</small>
                                                </label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="is_single_seller_order_system"
                                                        <?= $data->is_single_seller_order_system == '1' ? 'Checked' : '' ?>
                                                        name="is_single_seller_order_system">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-12 col-lg-12">
                                            <label for=""
                                                class="form-label">{{ labels('admin_labels.note_for_necessary_documents', 'Note for Necessary Documents') }}</label>
                                            <textarea name="note_for_necessary_documents" class="form-control"
                                                placeholder="Please attach the food license/pharmacy license in the other documents">{{ isset($data->note_for_necessary_documents) ? $data->note_for_necessary_documents : '' }}</textarea>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label
                                                    for="">{{ labels('admin_labels.primary_theme_color', 'Primary Theme Color') }}</label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color"
                                                        value="{{ isset($data->primary_color) && !empty($data->primary_color) ? $data->primary_color : '#e0ffee' }}"
                                                        id="light_theme_color"
                                                        oninput="updateColorCode('light_theme_color')"
                                                        class="color_picker mx-2">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="light_theme_color_code" name="primary_color"
                                                class="form-control mx-2"
                                                oninput="updateColorPicker('light_theme_color', this.value)"
                                                value={{ !empty($data->primary_color) ? $data->primary_color : '' }}>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label
                                                    for="">{{ labels('admin_labels.secondary_theme_color', 'Secondary Theme Color') }}</label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color"
                                                        value="{{ isset($data->secondary_color) && !empty($data->secondary_color) ? $data->secondary_color : '#e0ffee' }}"
                                                        id="dark_theme_color"
                                                        oninput="updateColorCode('dark_theme_color')"
                                                        class="color_picker mx-2">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="dark_theme_color_code"
                                                oninput="updateColorPicker('dark_theme_color', this.value)"
                                                name="secondary_color" class="form-control mx-2"
                                                value={{ !empty($data->secondary_color) ? $data->secondary_color : '' }}>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label for=""
                                                    class="me-8">{{ labels('admin_labels.link_hover_color', 'Link Hover Color') }}</label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color"
                                                        value="{{ isset($data->hover_color) && !empty($data->hover_color) ? $data->hover_color : '#e0ffee' }}"
                                                        id="hover_color" oninput="updateColorCode('hover_color')"
                                                        class="color_picker mx-2">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="hover_color_code"
                                                oninput="updateColorPicker('hover_color', this.value)" name="hover_color"
                                                class="form-control mx-2"
                                                value={{ !empty($data->hover_color) ? $data->hover_color : '' }}>

                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label for=""
                                                    class="me-8">{{ labels('admin_labels.link_active_color', 'Link Active Color') }}</label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color"
                                                        value="{{ isset($data->active_color) && !empty($data->active_color) ? $data->active_color : '#e0ffee' }}"
                                                        id="active_color" oninput="updateColorCode('active_color')"
                                                        class="color_picker mx-2">
                                                </div>

                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="active_color_code"
                                                oninput="updateColorPicker('active_color', this.value)"
                                                name="active_color" class="form-control mx-2"
                                                value={{ !empty($data->active_color) ? $data->active_color : '' }}>

                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label for=""
                                                    class="me-8">{{ labels('admin_labels.background_color', 'Background Color') }}</label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color"
                                                        value="{{ isset($data->background_color) && !empty($data->background_color) ? $data->background_color : '#e0ffee' }}"
                                                        id="background_color"
                                                        oninput="updateColorCode('background_color')"
                                                        class="color_picker mx-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="background_color_code"
                                                oninput="updateColorPicker('background_color', this.value)"
                                                name="background_color" class="form-control mx-2"
                                                value={{ !empty($data->background_color) ? $data->background_color : '' }}>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 store-next-button text-center" data-step="step1">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2 ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5 class="mb-4">{{ labels('admin_labels.store_images', 'Store Images') }}</h5>
                                    <div class="form-group col-md-12 mb-4">
                                        <label for="image" class="mb-2">{{ labels('admin_labels.image', 'Image') }}
                                            <span class='text-asterisks text-sm'>*</span>
                                        </label>
                                        <div class="col-md-12">
                                            <div class="row form-group">
                                                <div class="col-md-8 text-center form-group">
                                                    <input type="file" class="filepond" name="update_image"
                                                        data-max-file-size="30MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>
                                                @if ($data->image && !empty($data->image))
                                                    <div class="col-md-4">
                                                        <label for="" class="text-danger">*Only Choose When Update
                                                            is
                                                            necessary</label>
                                                        <div class="container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                <div class='image-upload-div'>
                                                                    <img class="img-fluid mb-2"
                                                                        src="{{ route('admin.dynamic_image', [
                                                                            'url' => getMediaImageUrl($data->image, 'STORE_IMG_PATH'),
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
                                                @else
                                                    <div class="col-md-4 container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-9 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none store-image-container">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for=""
                                            class="form-label">{{ labels('admin_labels.banner_image', 'Banner Image') }}<span
                                                class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <div class="row form-group">
                                                <div class="col-md-8 text-center form-group">
                                                    <input type="file" class="filepond" name="update_banner_image"
                                                        data-max-file-size="30MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>

                                                @if ($data->banner_image && !empty($data->banner_image))
                                                    <div class="col-md-4">
                                                        <label for="" class="text-danger">*Only Choose When Update
                                                            is
                                                            necessary</label>
                                                        <div class="container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image ">
                                                                <div class='image-upload-div'>
                                                                    <img class="img-fluid mb-2"
                                                                        src="{{ route('admin.dynamic_image', [
                                                                            'url' => getMediaImageUrl($data->banner_image, 'STORE_IMG_PATH'),
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
                                                @else
                                                    <div class="col-md-4 container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-9 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none store-image-container">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 store-next-button text-center" data-step="step2">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2 ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5 class="mb-4">
                                        {{ labels('admin_labels.web_home_page_theme', 'Web Home Page Theme') }}</h5>
                                    {{-- @dd($store_settings); --}}
                                    <div class="form-group col-md-12 mb-4">
                                        <div class="form-group col-md-12 mb-4">
                                            <select class="web_home_page_theme form-control form-select"
                                                name="web_home_page_theme">
                                                <option value="web_home_page_theme_1"
                                                    {{ ($store_settings->web_home_page_theme ?? '') === 'web_home_page_theme_1' ? 'selected' : '' }}>
                                                    Default
                                                </option>
                                                <option value="web_home_page_theme_2"
                                                    {{ ($store_settings->web_home_page_theme ?? '') === 'web_home_page_theme_2' ? 'selected' : '' }}>
                                                    Theme 2
                                                </option>
                                                <option value="web_home_page_theme_3"
                                                    {{ ($store_settings->web_home_page_theme ?? '') === 'web_home_page_theme_3' ? 'selected' : '' }}>
                                                    Theme 3
                                                </option>
                                                <option value="web_home_page_theme_4"
                                                    {{ ($store_settings->web_home_page_theme ?? '') === 'web_home_page_theme_4' ? 'selected' : '' }}>
                                                    Theme 4
                                                </option>
                                                <option value="web_home_page_theme_5"
                                                    {{ ($store_settings->web_home_page_theme ?? '') === 'web_home_page_theme_5' ? 'selected' : '' }}>
                                                    Theme 5
                                                </option>
                                                <option value="web_home_page_theme_5"
                                                    {{ ($store_settings->web_home_page_theme ?? '') === 'web_home_page_theme_6' ? 'selected' : '' }}>
                                                    Theme 6
                                                </option>
                                            </select>
                                        </div>

                                        <!-- Theme Images -->
                                        <div class="web_home_page_theme_images store_style_box home_theme_style_box">
                                            <img src="{{ getimageurl('system_images/theme_1.png') }}"
                                                class="web_home_page_theme_1 home_theme home_theme_1" alt="Theme 1" />
                                            <img src="{{ getimageurl('system_images/theme_2.png') }}"
                                                class="web_home_page_theme_2 home_theme home_theme_2" alt="Theme 2" />
                                            <img src="{{ getimageurl('system_images/theme_3.png') }}"
                                                class="web_home_page_theme_3 home_theme home_theme_3" alt="Theme 3" />
                                            <img src="{{ getimageurl('system_images/theme_4.png') }}"
                                                class="web_home_page_theme_4 home_theme home_theme_4" alt="Theme 4" />
                                            <img src="{{ getimageurl('system_images/theme_5.png') }}"
                                                class="web_home_page_theme_5 home_theme home_theme_5" alt="Theme 5" />
                                            <img src="{{ getimageurl('system_images/theme_6.png') }}"
                                                class="web_home_page_theme_6 home_theme home_theme_6" alt="Theme 6" />
                                        </div>
                                    </div>
                                    <h5 class="mb-4">{{ labels('admin_labels.app_images', 'App Images') }}</h5>
                                    <div class="form-group col-md-12 mb-4">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="image"
                                                    class="mb-2">{{ labels('admin_labels.banner_image_for_most_selling_products', 'Banner Image (For Most Selling Products (390 x 500))') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <div class="row form-group">
                                                    <div class="col-md-8 text-center form-group">
                                                        <input type="file" class="filepond"
                                                            name="update_banner_image_for_most_selling_product"
                                                            data-max-file-size="30MB" data-max-files="20"
                                                            accept="image/*,.webp" />
                                                    </div>
                                                    @if ($data->banner_image_for_most_selling_product && !empty($data->banner_image_for_most_selling_product))
                                                        <div class="col-md-4">
                                                            <label for="" class="text-danger">*Only Choose When
                                                                Update is
                                                                necessary</label>
                                                            <div class="container-fluid row image-upload-section">
                                                                <div
                                                                    class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                    <div class='image-upload-div'>
                                                                        <img class="img-fluid mb-2"
                                                                            src="{{ route('admin.dynamic_image', [
                                                                                'url' => getMediaImageUrl($data->banner_image_for_most_selling_product, 'STORE_IMG_PATH'),
                                                                                'width' => 150,
                                                                                'quality' => 90,
                                                                            ]) }}"
                                                                            alt="Not Found" />
                                                                    </div>
                                                                    <input type="hidden"
                                                                        name="banner_image_for_most_selling_product"
                                                                        value='<?= $data->banner_image_for_most_selling_product ?>'>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-md-4 container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-9 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none store-image-container">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for=""
                                                    class="form-label">{{ labels('admin_labels.stack_image', 'Stack Image') }}(App
                                                    Home Page Background Image (390 x 501))
                                                    <span class="text-asterisks text-sm">*</span></label>
                                                <div class="row form-group">

                                                    <div class="col-md-8 text-center form-group">
                                                        <input type="file" class="filepond" name="update_stack_image"
                                                            data-max-file-size="30MB" data-max-files="20"
                                                            accept="image/*,.webp" />
                                                    </div>
                                                    @if ($data->stack_image && !empty($data->stack_image))
                                                        <div class="col-md-4">
                                                            <label for="" class="text-danger">*Only Choose When
                                                                Update is
                                                                necessary</label>
                                                            <div class="container-fluid row image-upload-section">
                                                                <div
                                                                    class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                    <div class='image-upload-div'>
                                                                        <img class="img-fluid mb-2"
                                                                            src="{{ route('admin.dynamic_image', [
                                                                                'url' => getMediaImageUrl($data->stack_image, 'STORE_IMG_PATH'),
                                                                                'width' => 150,
                                                                                'quality' => 90,
                                                                            ]) }}"
                                                                            alt="Not Found" />
                                                                    </div>
                                                                    <input type="hidden" name="stack_image"
                                                                        value='<?= $data->stack_image ?>'>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-md-4 container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-9 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none store-image-container">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="image"
                                                    class="mb-2">{{ labels('admin_labels.login_page_image', 'Login Page Image (App Login Page Background Image)') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <div class="row form-group">

                                                    <div class="col-md-8 text-center form-group">
                                                        <input type="file" class="filepond" name="update_login_image"
                                                            data-max-file-size="30MB" data-max-files="20"
                                                            accept="image/*,.webp" />
                                                    </div>
                                                    @if ($data->login_image && !empty($data->login_image))
                                                        <div class="col-md-4">
                                                            <label for="" class="text-danger">*Only Choose When
                                                                Update is
                                                                necessary</label>
                                                            <div class="container-fluid row image-upload-section">
                                                                <div
                                                                    class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                    <div class='image-upload-div'>
                                                                        <img class="img-fluid mb-2"
                                                                            src="{{ route('admin.dynamic_image', [
                                                                                'url' => getMediaImageUrl($data->login_image, 'STORE_IMG_PATH'),
                                                                                'width' => 150,
                                                                                'quality' => 90,
                                                                            ]) }}"
                                                                            alt="Not Found" />
                                                                    </div>
                                                                    <input type="hidden" name="login_image"
                                                                        value='<?= $data->login_image ?>'>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-md-4 container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-9 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none store-image-container">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="image"
                                                    class="mb-2">{{ labels('admin_labels.half_store_logo', 'Half Store Logo (try to upload square image (100 x 100))') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <div class="row form-group">

                                                    <div class="col-md-8 text-center form-group">
                                                        <input type="file" class="filepond"
                                                            name="update_half_store_logo" data-max-file-size="30MB"
                                                            data-max-files="20" accept="image/*,.webp" />
                                                    </div>
                                                    @if ($data->half_store_logo && !empty($data->half_store_logo))
                                                        <div class="col-md-4">
                                                            <label for="" class="text-danger">*Only Choose When
                                                                Update is
                                                                necessary</label>
                                                            <div class="container-fluid row image-upload-section">
                                                                <div
                                                                    class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                    <div class='image-upload-div'>
                                                                        <img class="img-fluid mb-2"
                                                                            src="{{ route('admin.dynamic_image', [
                                                                                'url' => getMediaImageUrl($data->half_store_logo, 'STORE_IMG_PATH'),
                                                                                'width' => 150,
                                                                                'quality' => 90,
                                                                            ]) }}"
                                                                            alt="Not Found" />
                                                                    </div>
                                                                    <input type="hidden" name="half_store_logo"
                                                                        value='<?= $data->half_store_logo ?>'>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="col-md-4 container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-9 col-sm-12 p-3 mb-5 bg-white rounded m-4 text-center grow image d-none store-image-container">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 store-next-button text-center" data-step="step3">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2 ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5>{{ labels('admin_labels.cards_display_style', 'Cards Display Style') }}</h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="store_display_style">
                                                        {{ labels('admin_labels.store_display_style', 'Stores display style') }}
                                                    </label>
                                                    <select class="feature_section_header_style form-select form-control"
                                                        name="store_style">
                                                        <option value="header_style_1"
                                                            {{ $store_settings->store_style === 'header_style_1' ? 'selected' : '' }}>
                                                            Style 1
                                                        </option>
                                                        <option value="header_style_2"
                                                            {{ $store_settings->store_style === 'header_style_2' ? 'selected' : '' }}>
                                                            Style 2</option>
                                                        <option value="header_style_3"
                                                            {{ $store_settings->store_style === 'header_style_3' ? 'selected' : '' }}>
                                                            Style 3</option>
                                                    </select>
                                                </div>

                                                <div class="feature_section_header_style_images store_style_box">
                                                    <img src="{{ getimageurl('system_images/store_style_1.png') }}"
                                                        class="header_style_1" alt="Store Style 1">
                                                    <img src="{{ getimageurl('system_images/store_style_2.png') }}"
                                                        class="header_style_2" alt="Store Style 2">
                                                    <img src="{{ getimageurl('system_images/store_style_3.png') }}"
                                                        class="header_style_3" alt="Store Style 3">

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="">
                                                        {{ labels('admin_labels.product_display_style', 'Products display style') }}
                                                    </label>
                                                    <select class="product_card_style form-select form-control"
                                                        name="product_style">
                                                        <option value="style_1"
                                                            {{ $store_settings->product_style === 'style_1' ? 'selected' : '' }}>
                                                            Style 1</option>
                                                        <option value="style_2"
                                                            {{ $store_settings->product_style === 'style_2' ? 'selected' : '' }}>
                                                            Style 2</option>
                                                        <option value="style_3"
                                                            {{ $store_settings->product_style === 'style_3' ? 'selected' : '' }}>
                                                            Style 3</option>
                                                    </select>
                                                </div>

                                                <div class="product_card_style_images product_style_box">
                                                    <img src="{{ getimageurl('system_images/product_card_style_1.png') }}"
                                                        class="style_1" alt="Product Card Style 1">
                                                    <img src="{{ getimageurl('system_images/product_card_style_2.png') }}"
                                                        class="style_2" alt="Product Card Style 2">
                                                    <img src="{{ getimageurl('system_images/product_card_style_3.png') }}"
                                                        class="style_3" alt="Product Card Style 3">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5>{{ labels('admin_labels.cards_display_style', 'Cards Display Style For Web') }}
                                    </h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-4">
                                                    <label class="form-label" for="products_display_style_for_web">
                                                        {{ labels('admin_labels.products_display_style_for_web', 'Products display style') }}
                                                    </label>
                                                    <select class="products_display_style_for_web form-control form-select"
                                                        name="products_display_style_for_web">
                                                        <option value="products_display_style_for_web_1"
                                                            {{ ($store_settings->products_display_style_for_web ?? '') === 'products_display_style_for_web_1' ? 'selected' : '' }}>
                                                            Style 1
                                                        </option>
                                                        <option value="products_display_style_for_web_2"
                                                            {{ ($store_settings->products_display_style_for_web ?? '') === 'products_display_style_for_web_2' ? 'selected' : '' }}>
                                                            Style 2
                                                        </option>
                                                        <option value="products_display_style_for_web_3"
                                                            {{ ($store_settings->products_display_style_for_web ?? '') === 'products_display_style_for_web_3' ? 'selected' : '' }}>
                                                            Style 3
                                                        </option>
                                                        <option value="products_display_style_for_web_4"
                                                            {{ ($store_settings->products_display_style_for_web ?? '') === 'products_display_style_for_web_4' ? 'selected' : '' }}>
                                                            Style 4
                                                        </option>
                                                        <option value="products_display_style_for_web_5"
                                                            {{ ($store_settings->products_display_style_for_web ?? '') === 'products_display_style_for_web_5' ? 'selected' : '' }}>
                                                            Style 5
                                                        </option>

                                                    </select>
                                                    <iframe id="products_display_style_for_web_iframe"
                                                        src="/admin/web_product_card_style"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 store-next-button text-center" data-step="step4">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2 ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5>{{ labels('admin_labels.categories_display_style', 'Categories Display Style') }}
                                    </h5>
                                    <div class="col-md-12 mb-4 mt-4">
                                        <label class="form-label"
                                            for="basic-default-fullname">{{ labels('admin_labels.categories_section_title', 'Categories Section Title') }}<span
                                                class='text-asterisks text-sm'>*</span></label>
                                        <input type="text" class="form-control" id="basic-default-fullname"
                                            placeholder="Shop By Categories" name="category_section_title"
                                            value="{{ isset($store_settings->category_section_title) && $store_settings->category_section_title ? $store_settings->category_section_title : '' }}">
                                    </div>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="store_display_style">
                                                        {{ labels('admin_labels.categories_display_style', 'Categories Display Style') }}
                                                    </label>
                                                    <select class="categories_style form-control form-select"
                                                        name="category_style">
                                                        <option value="category_style_1"
                                                            {{ $store_settings->category_style === 'category_style_1' ? 'selected' : '' }}>
                                                            Style 1</option>
                                                        <option value="category_style_2"
                                                            {{ $store_settings->category_style === 'category_style_2' ? 'selected' : '' }}>
                                                            Style 2</option>
                                                    </select>
                                                </div>

                                                <div class="categories_style_images category_style_box">
                                                    <img src="{{ getimageurl('system_images/categories_style_1.png') }}"
                                                        class="category_style_1" alt="Category Style 1">
                                                    <img src="{{ getimageurl('system_images/categories_style_2.png') }}"
                                                        class="category_style_2" alt="Category Style 2">

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="">
                                                        {{ labels('admin_labels.categories_card_style', 'Categories Cards Style') }}
                                                    </label>
                                                    <select class="categories_card_style form-control form-select"
                                                        name="category_card_style">
                                                        <option value="category_card_style_1"
                                                            {{ $store_settings->category_card_style === 'category_card_style_1' ? 'selected' : '' }}>
                                                            Style 1</option>
                                                        <option value="category_card_style_2"
                                                            {{ $store_settings->category_card_style === 'category_card_style_2' ? 'selected' : '' }}>
                                                            Style 2</option>
                                                        <option value="category_card_style_3"
                                                            {{ $store_settings->category_card_style === 'category_card_style_3' ? 'selected' : '' }}>
                                                            Style 3</option>
                                                    </select>
                                                </div>

                                                <div class="categories_card_style_images category_card_box">
                                                    <img src="{{ getimageurl('system_images/categories_cards_style_1.jpg') }}"
                                                        class="category_card_style_1" alt="Category Card Style 1">
                                                    <img src="{{ getimageurl('system_images/categories_cards_style_2.jpg') }}"
                                                        class="category_card_style_2" alt="Category Card Style 2">
                                                    <img src="{{ getimageurl('system_images/categories_cards_style_3.jpg') }}"
                                                        class="category_card_style_3" alt="Category Card Style 3">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5>{{ labels('admin_labels.cards_display_style', 'Cards Display Style For Web') }}
                                    </h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-4">
                                                    <label class="form-label" for="categories_display_style_for_web">
                                                        {{ labels('admin_labels.categories_display_style', 'Categories Display Style') }}
                                                    </label>
                                                    <select
                                                        class="categories_display_style_for_web form-control form-select"
                                                        name="categories_display_style_for_web">
                                                        <option value="categories_display_style_for_web_1"
                                                            {{ ($store_settings->categories_display_style_for_web ?? '') === 'categories_display_style_for_web_1' ? 'selected' : '' }}>
                                                            Style 1
                                                        </option>
                                                        <option value="categories_display_style_for_web_2"
                                                            {{ ($store_settings->categories_display_style_for_web ?? '') === 'categories_display_style_for_web_2' ? 'selected' : '' }}>
                                                            Style 2
                                                        </option>
                                                        <option value="categories_display_style_for_web_3"
                                                            {{ ($store_settings->categories_display_style_for_web ?? '') === 'categories_display_style_for_web_3' ? 'selected' : '' }}>
                                                            Style 3
                                                        </option>

                                                    </select>
                                                    <iframe class="overflow-hidden"
                                                        id="categories_display_style_for_web_iframe"
                                                        src="/admin/web_categories_style"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5>{{ labels('admin_labels.products_details_style', 'Products Details Style For Web') }}
                                    </h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-4">
                                                    <label class="form-label" for="web_product_details_style">
                                                        {{ labels('admin_labels.products_details_style_for_web', 'Products details display style') }}
                                                    </label>
                                                    <select class="web_product_details_style form-control form-select"
                                                        name="web_product_details_style">
                                                        <option value="web_product_details_style_1"
                                                            {{ ($store_settings->web_product_details_style ?? '') === 'web_product_details_style_1' ? 'selected' : '' }}>
                                                            Style 1</option>
                                                        <option value="web_product_details_style_2"
                                                            {{ ($store_settings->web_product_details_style ?? '') === 'web_product_details_style_2' ? 'selected' : '' }}>
                                                            Style 2</option>
                                                    </select>
                                                    <div
                                                        class="web_product_details_style_images store_style_box home_theme_style_box">
                                                        <img src="{{ getimageurl('system_images/details_1.png') }}"
                                                            class="web_product_details_style_1" alt="Theme 1" />
                                                        <img src="{{ getimageurl('system_images/details_2.png') }}"
                                                            class="web_product_details_style_2" alt="Theme 2" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 store-next-button text-center" data-step="step5">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2  ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5>{{ labels('admin_labels.brands_display_style', 'Brands Display Style') }}</h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="store_display_style">
                                                        {{ labels('admin_labels.brand_style', 'Brand Style') }}
                                                    </label>
                                                    <select class="brands_style form-control form-select"
                                                        name="brand_style">
                                                        <option value="brands_style_1"
                                                            {{ $store_settings->brand_style === 'brands_style_1' ? 'selected' : '' }}>
                                                            Style 1</option>
                                                        <option value="brands_style_2"
                                                            {{ $store_settings->brand_style === 'brands_style_2' ? 'selected' : '' }}>
                                                            Style 2</option>
                                                    </select>
                                                </div>

                                                <div class="brands_style_images category_style_box">
                                                    <img src="{{ getimageurl('system_images/brands_style_1.png') }}"
                                                        class="brands_style_1" alt="Brands Style 1">
                                                    <img src="{{ getimageurl('system_images/brands_style_2.png') }}"
                                                        class="brands_style_2" alt="Brands Style 2">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h5>{{ labels('admin_labels.brands_display_style', 'Brands & Wishlist Display Style For Web') }}
                                    </h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="brands_display_style">
                                                        {{ labels('admin_labels.brands_display_style', 'Brands Display Style') }}
                                                    </label>
                                                    <select class="brands_display_style_for_web form-control form-select"
                                                        name="brands_display_style_for_web">
                                                        <option value="brands_display_style_for_web_1"
                                                            {{ ($store_settings->brands_display_style_for_web ?? '') === 'brands_display_style_for_web_1' ? 'selected' : '' }}>
                                                            Style 1
                                                        </option>
                                                        <option value="brands_display_style_for_web_2"
                                                            {{ ($store_settings->brands_display_style_for_web ?? '') === 'brands_display_style_for_web_2' ? 'selected' : '' }}>
                                                            Style 2
                                                        </option>
                                                        <option value="brands_display_style_for_web_3"
                                                            {{ ($store_settings->brands_display_style_for_web ?? '') === 'brands_display_style_for_web_3' ? 'selected' : '' }}>
                                                            Style 3
                                                        </option>

                                                    </select>
                                                    <iframe class="overflow-hidden"
                                                        id="brands_display_style_for_web_iframe"
                                                        src="/admin/web_brands_style"></iframe>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="brands_display_style">
                                                        {{ labels('admin_labels.wishlist_display_style', 'Wishlist Display Style') }}
                                                    </label>
                                                    <select class="wishlist_display_style_for_web form-control form-select"
                                                        name="wishlist_display_style_for_web">
                                                        <option value="wishlist_display_style_for_web_1"
                                                            {{ ($store_settings->wishlist_display_style_for_web ?? '') === 'wishlist_display_style_for_web_1' ? 'selected' : '' }}>
                                                            Style 1
                                                        </option>
                                                        <option value="wishlist_display_style_for_web_2"
                                                            {{ ($store_settings->wishlist_display_style_for_web ?? '') === 'wishlist_display_style_for_web_2' ? 'selected' : '' }}>
                                                            Style 2
                                                        </option>

                                                    </select>
                                                    <iframe class="overflow-hidden"
                                                        id="wishlist_display_style_for_web_iframe"
                                                        src="/admin/web_wishlist_style"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 store-next-button text-center" data-step="step6">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2 ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5>{{ labels('admin_labels.offer_display_style', 'Offer Display Style') }}</h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="">
                                                        {{ labels('admin_labels.slider_style', 'Offer Style') }}
                                                    </label>
                                                    <select class="slider_style form-control form-select"
                                                        name="offer_slider_style">
                                                        <option value="slider_style_1"
                                                            {{ $store_settings->offer_slider_style === 'slider_style_1' ? 'selected' : '' }}>
                                                            Style 1</option>
                                                        <option value="slider_style_2"
                                                            {{ $store_settings->offer_slider_style === 'slider_style_2' ? 'selected' : '' }}>
                                                            Style 2</option>
                                                        <option value="slider_style_3"
                                                            {{ $store_settings->offer_slider_style === 'slider_style_3' ? 'selected' : '' }}>
                                                            Style 3</option>
                                                        <option value="slider_style_4"
                                                            {{ $store_settings->offer_slider_style === 'slider_style_4' ? 'selected' : '' }}>
                                                            Style 4</option>
                                                        <option value="slider_style_5"
                                                            {{ $store_settings->offer_slider_style === 'slider_style_5' ? 'selected' : '' }}>
                                                            Style 5</option>
                                                    </select>
                                                </div>

                                                <div class="slider_style_images store_style_box">
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_1.png') }}"
                                                        class="slider_style_1" alt="Offer Slider Style 1">
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_2.png') }}"
                                                        class="slider_style_2" alt="Offer Slider Style 2">
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_3.png') }}"
                                                        class="slider_style_3" alt="Offer Slider Style 3">
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_4.png') }}"
                                                        class="slider_style_4" alt="Offer Slider Style 4">
                                                    <img src="{{ getimageurl('system_images/offer_style_1.png') }}"
                                                        class="slider_style_5" alt="Offer Style 1">

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 store-next-button text-center" data-step="step7">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2 ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">

                                    <h5>{{ labels('admin_labels.delivery_charge_and_product_deliverability_setting', 'Delivery Charge & Product Deliverability Setting') }}
                                    </h5>


                                    <!-- Product Deliverability Settings  -->

                                    <h6 class="mt-8">
                                        {{ labels('admin_labels.product_deliverability_setting', 'Product Deliverability Setting') }}
                                    </h6>
                                    <div class="col-md-12 mt-4">
                                        <input type="hidden" name="product_deliverability_type_value"
                                            value="{{ isset($data->product_deliverability_type) ? $data->product_deliverability_type : '' }}">
                                        <div class="row">
                                            <div class="mb-3 col-md-4">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <label class="form-label" for="zipcode_wise_deliverability">
                                                            {{ labels('admin_labels.zipcode_wise_deliverability', 'Zipcode Wise Deliverability') }}
                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check form-switch float-end">
                                                            <input class="form-check-input" type="radio"
                                                                id="zipcode_wise_deliverability_switch"
                                                                name="product_deliverability"
                                                                <?= isset($data->product_deliverability_type) && $data->product_deliverability_type != 'null' && $data->product_deliverability_type == 'zipcode_wise_deliverability' ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <label class="form-label" for="city_wise_deliverability">
                                                            {{ labels('admin_labels.city_wise_deliverability', 'City Wise Deliverability') }}

                                                        </label><br>
                                                        @php
                                                            $shipping_settings = getSettings('shipping_method', true);
                                                        $shipping_settings = json_decode($shipping_settings, true); @endphp

                                                        @if (isset($shipping_settings['shiprocket_shipping_method']) && $shipping_settings['shiprocket_shipping_method'] == 1)
                                                            <span class="text-danger">(Disabled because standard shipping
                                                                is on from shipping method)</span>
                                                        @endif
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check form-switch float-end">
                                                            <input class="form-check-input" type="radio"
                                                                id="city_wise_deliverability_switch"
                                                                name="product_deliverability"
                                                                <?= $shipping_settings['shiprocket_shipping_method'] == 1 ? 'disabled' : '' ?>
                                                                <?= isset($data->product_deliverability_type) && $data->product_deliverability_type != 'null' && $data->product_deliverability_type == 'city_wise_deliverability' ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <!-- Delivery charge settings  -->

                                    <h6 class="mt-8">
                                        {{ labels('admin_labels.delivery_charge_setting', 'Delivery Charge Setting') }}
                                    </h6>
                                    <div class="col-md-12 mt-4">
                                        <input type="hidden" name="delivery_charge_type_value"
                                            value="{{ isset($data->delivery_charge_type) ? $data->delivery_charge_type : '' }}">
                                        <div class="row">

                                            <div
                                                class="mb-3 col-md-4 zipcode_wise_delivery_charge {{ $data->product_deliverability_type == 'city_wise_deliverability' ? 'd-none' : '' }}">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <label class="form-label" for="zipcode_wise_delivery_charge">
                                                            {{ labels('admin_labels.zipcode_wise', 'Zipcode Wise') }}

                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check form-switch float-end">
                                                            <input class="form-check-input" type="radio"
                                                                id="zipcode_wise_delivery_charge_switch"
                                                                name="delivery_charge_type"
                                                                <?= isset($data->delivery_charge_type) && $data->delivery_charge_type != 'null' && $data->delivery_charge_type == 'zipcode_wise_delivery_charge' ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div
                                                class="mb-3 col-md-4 city_wise_delivery_charge {{ $data->product_deliverability_type == 'zipcode_wise_deliverability' ? 'd-none' : '' }}">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <label class="form-label" for="city_wise_delivery_charge">
                                                            {{ labels('admin_labels.city_wise', 'City Wise') }}

                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check form-switch float-end">
                                                            <input class="form-check-input" type="radio"
                                                                id="city_wise_delivery_charge_switch"
                                                                name="delivery_charge_type"
                                                                <?= isset($data->delivery_charge_type) && $data->delivery_charge_type != 'null' && $data->delivery_charge_type == 'city_wise_delivery_charge' ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <div class="row ">
                                                    <div class="col-md-9">
                                                        <label class="form-label"
                                                            for="product_wise_delivery_charge_switch">
                                                            {{ labels('admin_labels.product_wise', 'Product Wise') }}

                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check form-switch float-end">
                                                            <input class="form-check-input" type="radio"
                                                                id="product_wise_delivery_charge_switch"
                                                                name="delivery_charge_type"
                                                                <?= isset($data->delivery_charge_type) && $data->delivery_charge_type != 'null' && $data->delivery_charge_type == 'product_wise_delivery_charge' ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3 col-md-4">
                                                <div class="row">
                                                    <div class="col-md-9">
                                                        <label class="form-label" for="global_delivery_charge_switch">
                                                            {{ labels('admin_labels.global_delivery_charge', 'Global Delivery Charge') }}
                                                        </label>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-check form-switch float-end">
                                                            <input class="form-check-input" type="radio"
                                                                id="global_delivery_charge_switch"
                                                                name="delivery_charge_type"
                                                                <?= isset($data->delivery_charge_type) && $data->delivery_charge_type != 'null' && $data->delivery_charge_type == 'global_delivery_charge' ? 'checked' : '' ?>>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">
                                            @php
                                                $product_wise_delivery_charge = isset(
                                                    $settings['product_wise_delivery_charge'],
                                                )
                                                    ? $settings['product_wise_delivery_charge']
                                                    : '';
                                                $d_none =
                                                    $data->delivery_charge_type == 'product_wise_delivery_charge' ||
                                                    $data->delivery_charge_type == 'zipcode_wise_delivery_charge' ||
                                                    $data->delivery_charge_type == 'city_wise_delivery_charge'
                                                        ? 'd-none'
                                                    : ''; @endphp
                                            <div class="mb-3 col-md-6 {{ $d_none }}"
                                                id="delivery_charge_amount_field">
                                                <label class="form-label" for="basic-default-fullname">
                                                    {{ labels('admin_labels.delivery_charge', 'Delivery Charge') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <input type="number" min=0 class="form-control"
                                                    id="basic-default-fullname" placeholder=""
                                                    name="delivery_charge_amount"
                                                    value="{{ isset($data->delivery_charge_amount) ? $data->delivery_charge_amount : 0 }}">

                                            </div>
                                            <div class="mb-3 col-md-6 {{ $d_none }}"
                                                id="minimum_free_delivery_amount_field">
                                                <label class="form-label" for="basic-default-fullname">
                                                    {{ labels('admin_labels.minimum_free_delivery_amount', 'Minimum Amount for Free Delivery') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <input type="number" min=0 class="form-control"
                                                    id="basic-default-fullname" placeholder=""
                                                    name="minimum_free_delivery_amount"
                                                    value="{{ isset($data->minimum_free_delivery_amount) ? $data->minimum_free_delivery_amount : 0 }}">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 text-center">
                            <button type="submit"
                                class="btn btn-primary submit_button ">{{ labels('admin_labels.update_store', 'Update Store') }}</button>
                        </div>
                    </div>
                </form>
                <div class="float-end me-0 mt-3 px-3 row">
                    <p class="prev btn reset-btn">{{ labels('admin_labels.go_back', 'Go Back') }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
