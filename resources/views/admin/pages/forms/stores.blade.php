@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.add_store', 'Add Store') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.add_store', 'Add Store')" :subtitle="labels(
        'admin_labels.lets_unlesh_a_wave_of_new_stores_on_your_marketplace',
        'Let’s unleash a wave of new stores on your marketplace.',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.stores', 'Stores'), 'url' => route('admin.stores.index')],
        ['label' => labels('admin_labels.add_store', 'Add Store')],
    ]" />
    <div class="col-xxl-12 p-0">
        <div class="row cols-5 d-flex">
            <div class="col-md-12 col-xl-3 col-xxl-3">
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
                <form id="" action="{{ route('admin.stores.store') }}" class="submit_form"
                    enctype="multipart/form-data" method="POST">
                    @csrf
                    <div class="card2 first-screen ml-2 show">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5>{{ labels('admin_labels.store_details', 'Store Details') }}</h5>
                                    <ul class="nav nav-tabs mt-4" id="brandTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="language-nav-link nav-link active" id="tab-en"
                                                data-bs-toggle="tab" data-bs-target="#content-en" type="button"
                                                role="tab" aria-controls="content-en" aria-selected="true">
                                                {{ labels('admin_labels.default', 'Default') }}
                                            </button>
                                        </li>
                                        {!! generateLanguageTabsNav($languages) !!}
                                    </ul>

                                    <div class="tab-content mt-3" id="brandTabsContent">
                                        <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                            aria-labelledby="tab-en">
                                            <div class="mb-3">
                                                <label for="title" class="form-label">
                                                    {{ labels('admin_labels.name', 'Name') }}<span
                                                        class="text-asterisks text-sm">*</span>
                                                </label>
                                                <input type="text" placeholder="Grocery" name="name"
                                                    class="form-control" value="{{ old('name') }}">
                                            </div>
                                            <div class="col-md-12">
                                                <label for="description" class="control-label mb-2 mt-2">
                                                    {{ labels('admin_labels.description', 'Description') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <input type="text" class="form-control" name="description"
                                                    id="description" value="" placeholder="Description">
                                            </div>
                                        </div>

                                        @foreach ($languages as $lang)
                                            @if ($lang->code !== 'en')
                                                <div class="tab-pane fade" id="content-{{ $lang->code }}" role="tabpanel"
                                                    aria-labelledby="tab-{{ $lang->code }}">
                                                    <div class="mb-3">
                                                        <label for="translated_title_{{ $lang->code }}"
                                                            class="form-label">
                                                            {{ labels('admin_labels.name', 'Name') }}
                                                            ({{ $lang->language }})
                                                        </label>
                                                        <input type="text" class="form-control"
                                                            id="translated_title_{{ $lang->code }}"
                                                            name="translated_store_name[{{ $lang->code }}]"
                                                            value="">
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="translated_short_description_{{ $lang->code }}"
                                                            class="form-label">
                                                            {{ labels('admin_labels.description', 'Description') }}
                                                            ({{ $lang->language }})
                                                        </label>
                                                        <input type="text" class="form-control"
                                                            id="translated_short_description_{{ $lang->code }}"
                                                            name="translated_store_description[{{ $lang->code }}]"
                                                            value="">
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="row mt-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <label for="is_default_store"
                                                    class="me-8">{{ labels('admin_labels.is_default_store', 'Is Default Store') }}?</label>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" id="is_default_store"
                                                        name="is_default_store">
                                                </div>
                                            </div>
                                        </div>
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
                                                placeholder="Please attach the food license/pharmacy license in the other documents"></textarea>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label
                                                    for="">{{ labels('admin_labels.primary_theme_color', 'Primary Theme Color') }}<span
                                                        class="text-asterisks text-sm">*</span></label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color" value="#e0ffee" id="light_theme_color"
                                                        oninput="updateColorCode('light_theme_color')"
                                                        class="color_picker mx-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="light_theme_color_code" name="primary_color"
                                                class="form-control mx-2"
                                                oninput="updateColorPicker('light_theme_color', this.value)">
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label
                                                    for="">{{ labels('admin_labels.secondary_theme_color', 'Secondary Theme Color') }}<span
                                                        class="text-asterisks text-sm">*</span></label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color" value="#e0ffee" id="dark_theme_color"
                                                        oninput="updateColorCode('dark_theme_color')"
                                                        class="color_picker mx-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="dark_theme_color_code"
                                                oninput="updateColorPicker('dark_theme_color', this.value)"
                                                name="secondary_color" class="form-control mx-2">
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label for=""
                                                    class="me-8">{{ labels('admin_labels.link_hover_color', 'Link Hover Color') }}<span
                                                        class="text-asterisks text-sm">*</span></label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color" value="#e0ffee" id="hover_color"
                                                        oninput="updateColorCode('hover_color')"
                                                        class="color_picker mx-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="hover_color_code"
                                                oninput="updateColorPicker('hover_color', this.value)" name="hover_color"
                                                class="form-control mx-2">

                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label for=""
                                                    class="me-8">{{ labels('admin_labels.link_active_color', 'Link Active Color') }}<span
                                                        class="text-asterisks text-sm">*</span></label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color" value="#e0ffee" id="active_color"
                                                        oninput="updateColorCode('active_color')"
                                                        class="color_picker mx-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="active_color_code"
                                                oninput="updateColorPicker('active_color', this.value)"
                                                name="active_color" class="form-control mx-2">

                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-6 col-lg-3">
                                            <div
                                                class="form-group d-flex col-md-12 align-items-center justify-content-between">
                                                <label for=""
                                                    class="me-8">{{ labels('admin_labels.background_color', 'Background Color') }}<span
                                                        class="text-asterisks text-sm">*</span></label>
                                                <div class="col-md-4 d-flex justify-content-end">
                                                    <input type="color" value="#e0ffee" id="background_color"
                                                        oninput="updateColorCode('background_color')"
                                                        class="color_picker mx-2">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6 col-lg-3">
                                            <input type="text" id="background_color_code"
                                                oninput="updateColorPicker('background_color', this.value)"
                                                name="background_color" class="form-control mx-2">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-3 store-next-button text-center" data-step="step1">
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
                                            <div class="row ">

                                                <div class="col-md-6 text-center form-group">
                                                    <input type="file" class="filepond" name="image"
                                                        data-max-file-size="30MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>


                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <label for=""
                                            class="form-label">{{ labels('admin_labels.banner_image', 'Banner Image') }}<span
                                                class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <div class="row ">
                                                <div class="col-md-6  text-center form-group">
                                                    <input type="file" class="filepond" name="banner_image"
                                                        data-max-file-size="300MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-3 store-next-button text-center" data-step="step2">
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
                                    <div class="form-group col-md-12 mb-4">
                                        <select class="web_home_page_theme form-control form-select"
                                            name="web_home_page_theme">
                                            <option value="web_home_page_theme_1">Default</option>
                                            <option value="web_home_page_theme_2">Theme 2</option>
                                            <option value="web_home_page_theme_3">Theme 3</option>
                                            <option value="web_home_page_theme_4">Theme 4</option>
                                            <option value="web_home_page_theme_5">Theme 5</option>
                                            <option value="web_home_page_theme_6">Theme 6</option>
                                        </select>
                                    </div>

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
                                            class="web_home_page_theme_6 home_theme home_theme_6" alt="Theme 5" />
                                    </div>

                                    <h5 class="mb-4">{{ labels('admin_labels.app_images', 'App Images') }}</h5>
                                    <div class="form-group col-md-12 mb-4">
                                        <div class="row form-group">
                                            <div class="col-md-6">
                                                <label for="image"
                                                    class="mb-2">{{ labels('admin_labels.banner_image_for_most_selling_products', 'Banner Image (For Most Selling Products (390 x 500))') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <div class="col-md-12  text-center form-group">
                                                    <input type="file" class="filepond"
                                                        name="banner_image_for_most_selling_product"
                                                        data-max-file-size="300MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for=""
                                                    class="form-label">{{ labels('admin_labels.stack_image', 'Stack Image') }}(App
                                                    Home Page Background Image)
                                                    <span class="text-asterisks text-sm">*</span></label>

                                                <div class="col-md-12  text-center form-group">
                                                    <input type="file" class="filepond" name="stack_image"
                                                        data-max-file-size="300MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>


                                            </div>
                                        </div>
                                        <div class="row form-group">
                                            <div class="col-md-6">
                                                <label for="image"
                                                    class="mb-2">{{ labels('admin_labels.login_page_image', 'Login Page Image (App Login Page Background Image (390 x 501))') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>

                                                <div class="col-md-12  text-center form-group">
                                                    <input type="file" class="filepond" name="login_image"
                                                        data-max-file-size="300MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="image"
                                                    class="mb-2">{{ labels('admin_labels.half_store_logo', 'Half Store Logo (try to upload square image (100 x 100))') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>

                                                <div class="col-md-12  text-center form-group">
                                                    <input type="file" class="filepond" name="half_store_logo"
                                                        data-max-file-size="300MB" data-max-files="20"
                                                        accept="image/*,.webp" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-3 store-next-button text-center" data-step="step3">
                            <button type="button"
                                class="btn btn-primary">{{ labels('admin_labels.next', 'Next') }}</button>
                        </div>
                    </div>
                    <div class="card2 ml-2">
                        <div class="row">
                            <div class="col col-xxl-12">
                                <div class="card p-5">
                                    <h5>{{ labels('admin_labels.cards_display_style', 'Cards Display Style For App') }}
                                    </h5>
                                    <div class="col-md-12 mt-4">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="store_display_style">
                                                        {{ labels('admin_labels.store_display_style', 'Stores display style') }}
                                                    </label>
                                                    <select class="feature_section_header_style form-control form-select"
                                                        name="store_style">
                                                        <option value="header_style_1">Style 1</option>
                                                        <option value="header_style_2">Style 2</option>
                                                        <option value="header_style_3">Style 3</option>
                                                    </select>
                                                </div>

                                                <div class="feature_section_header_style_images store_style_box">
                                                    <img src="{{ getimageurl('system_images/store_style_1.png') }}"
                                                        class="header_style_1" alt="Style 1" />
                                                    <img src="{{ getimageurl('system_images/store_style_2.png') }}"
                                                        class="header_style_2" alt="Style 2" />
                                                    <img src="{{ getimageurl('system_images/store_style_3.png') }}"
                                                        class="header_style_3" alt="Style 3" />

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="">
                                                        {{ labels('admin_labels.product_display_style', 'Products display style') }}
                                                    </label>
                                                    <select class="product_card_style form-control form-select"
                                                        name="product_style">
                                                        <option value="style_1">Style 1</option>
                                                        <option value="style_2">Style 2</option>
                                                        <option value="style_3">Style 3</option>
                                                    </select>
                                                </div>

                                                <div class="product_card_style_images product_style_box">
                                                    <img src="{{ getimageurl('system_images/product_card_style_1.png') }}"
                                                        class="style_1" alt="Product Card Style 1" />
                                                    <img src="{{ getimageurl('system_images/product_card_style_2.png') }}"
                                                        class="style_2" alt="Product Card Style 2" />
                                                    <img src="{{ getimageurl('system_images/product_card_style_3.png') }}"
                                                        class="style_3" alt="Product Card Style 3" />

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
                                                        <option value="products_display_style_for_web_1">Style 1</option>
                                                        <option value="products_display_style_for_web_2">Style 2</option>
                                                        <option value="products_display_style_for_web_3">Style 3</option>
                                                        <option value="products_display_style_for_web_4">Style 4</option>
                                                        <option value="products_display_style_for_web_5">Style 5</option>
                                                    </select>
                                                    <iframe id="products_display_style_for_web_iframe"
                                                        src="/admin/web_product_card_style"></iframe>
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
                                                        <option value="web_product_details_style_1">Style 1</option>
                                                        <option value="web_product_details_style_2">Style 2</option>
                                                    </select>
                                                    <iframe id="web_product_details_style_iframe"
                                                        src="/admin/web_product_details_style"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-3 store-next-button text-center" data-step="step4">
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
                                            value="{{ old('title') }}">
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
                                                        <option value="category_style_1">Style 1</option>
                                                        <option value="category_style_2">Style 2</option>
                                                    </select>
                                                </div>

                                                <div class="categories_style_images category_style_box">
                                                    <img src="{{ getimageurl('system_images/categories_style_1.png') }}"
                                                        class="category_style_1" alt="Category Style 1" />
                                                    <img src="{{ getimageurl('system_images/categories_style_2.png') }}"
                                                        class="category_style_2" alt="Category Style 2" />

                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-4">
                                                    <label class="form-label" for="">
                                                        {{ labels('admin_labels.categories_card_style', 'Categories Cards Style') }}
                                                    </label>
                                                    <select class="categories_card_style form-control form-select"
                                                        name="category_card_style">
                                                        <option value="category_card_style_1">Style 1</option>
                                                        <option value="category_card_style_2">Style 2</option>
                                                        <option value="category_card_style_3">Style 3</option>
                                                    </select>
                                                </div>

                                                <div class="categories_card_style_images category_card_box">
                                                    <img src="{{ getimageurl('system_images/categories_cards_style_1.jpg') }}"
                                                        class="category_card_style_1" alt="Category Card Style 1" />
                                                    <img src="{{ getimageurl('system_images/categories_cards_style_2.jpg') }}"
                                                        class="category_card_style_2" alt="Category Card Style 2" />
                                                    <img src="{{ getimageurl('system_images/categories_cards_style_3.jpg') }}"
                                                        class="category_card_style_3" alt="Category Card Style 3" />
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
                                                        <option value="categories_display_style_for_web_1">Style 1</option>
                                                        <option value="categories_display_style_for_web_2">Style 2</option>
                                                        <option value="categories_display_style_for_web_3">Style 3</option>
                                                    </select>
                                                    <iframe class="overflow-hidden"
                                                        id="categories_display_style_for_web_iframe"
                                                        src="/admin/web_categories_style"></iframe>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-3 store-next-button text-center" data-step="step5">
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
                                                        <option value="brands_style_1">Style 1</option>
                                                        <option value="brands_style_2">Style 2</option>
                                                    </select>
                                                </div>

                                                <div class="brands_style_images category_style_box">
                                                    <img src="{{ getimageurl('system_images/brands_style_1.png') }}"
                                                        class="brands_style_1" alt="Brand Style 1" />
                                                    <img src="{{ getimageurl('system_images/brands_style_2.png') }}"
                                                        class="brands_style_2" alt="Brand Style 2" />
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
                                                        <option value="brands_display_style_for_web_1">Style 1</option>
                                                        <option value="brands_display_style_for_web_2">Style 2</option>
                                                        <option value="brands_display_style_for_web_3">Style 3</option>
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
                                                        <option value="wishlist_display_style_for_web_1">Style 1</option>
                                                        <option value="wishlist_display_style_for_web_2">Style 2</option>
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
                        <div class="float-end ml-2 mt-3 store-next-button text-center" data-step="step6">
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
                                                        <option value="slider_style_1">Style 1</option>
                                                        <option value="slider_style_2">Style 2</option>
                                                        <option value="slider_style_3">Style 3</option>
                                                        <option value="slider_style_4">Style 4</option>
                                                        <option value="slider_style_5">Style 5</option>
                                                    </select>
                                                </div>

                                                <div class="slider_style_images store_style_box">
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_1.png') }}"
                                                        class="slider_style_1" alt="Offer Slider Style 1" />
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_2.png') }}"
                                                        class="slider_style_2" alt="Offer Slider Style 2" />
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_3.png') }}"
                                                        class="slider_style_3" alt="Offer Slider Style 3" />
                                                    <img src="{{ getimageurl('system_images/offer_slider_section_style_4.png') }}"
                                                        class="slider_style_4" alt="Offer Slider Style 4" />
                                                    <img src="{{ getimageurl('system_images/offer_style_1.png') }}"
                                                        class="slider_style_5" alt="Offer Style 1" />

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-3 store-next-button text-center" data-step="step7">
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
                                        <input type="hidden" name="product_deliverability_type_value">
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
                                                                name="product_deliverability">
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
                                                            $shipping_settings = json_decode($shipping_settings, true);
                                                        @endphp


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
                                                                <?= $shipping_settings['shiprocket_shipping_method'] == 1 ? 'disabled' : '' ?>>
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
                                        <input type="hidden" name="delivery_charge_type_value">
                                        <div class="row">
                                            <div class="mb-3 col-md-4 zipcode_wise_delivery_charge">
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
                                                                name="delivery_charge_type">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-4 city_wise_delivery_charge d-none">
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
                                                                name="delivery_charge_type">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mb-3 col-md-4">
                                                <div class="row">
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
                                                                name="delivery_charge_type">
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
                                                                name="delivery_charge_type">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row">

                                            <div class="mb-3 col-md-6 d-none" id="delivery_charge_amount_field">
                                                <label class="form-label" for="basic-default-fullname">
                                                    {{ labels('admin_labels.delivery_charge', 'Delivery Charge') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <input type="number" min=0 class="form-control"
                                                    id="basic-default-fullname" placeholder=""
                                                    name="delivery_charge_amount" value="">

                                            </div>
                                            <div class="mb-3 col-md-6 d-none" id="minimum_free_delivery_amount_field">
                                                <label class="form-label" for="basic-default-fullname">
                                                    {{ labels('admin_labels.minimum_free_delivery_amount', 'Minimum Amount for Free Delivery') }}
                                                    <span class='text-asterisks text-sm'>*</span>
                                                </label>
                                                <input type="number" min=0 class="form-control"
                                                    id="basic-default-fullname" placeholder=""
                                                    name="minimum_free_delivery_amount" value="">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="float-end ml-2 mt-xxl-3 mt-7 text-center">
                            <button type="submit"
                                class="btn btn-primary submit_button ">{{ labels('admin_labels.submit', 'Submit') }}</button>
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
