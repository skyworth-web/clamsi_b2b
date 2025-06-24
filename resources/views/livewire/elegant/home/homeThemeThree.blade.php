<div id="page-content" class="mb-0">
    @php
        $store_settings = getStoreSettings();
    @endphp
    {{-- category section  --}}
    @php
        $categories = $categories['categories'];
    @endphp

    @if (is_array($categories) && count($categories) >= 6)
        <section class="section collection-banners six-two-bnr py-0">
            <div class="container-fluid px-0">
                <div class="section-header d-none">
                    <h2>Explore All Department</h2>
                </div>

                <div class="collection-banner-grid">
                    <div class="row g-0">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-4 collection-banner-item frt-column">
                            <div class="collection-item">
                                <a wire:navigate
                                    href="{{ customUrl('categories/' . $categories[0]->slug . '/products') }}"
                                    data-link="{{ customUrl('categories/' . $categories[0]->slug . '/products') }}"
                                    class="zoom-scal home_theme_three_first_category">
                                    <div class="img">
                                        <img class="blur-up w-100 h-100 lazyloaded"
                                            data-src="{{ $categories[0]->banner }}" src="{{ $categories[0]->banner }}"
                                            alt="{{ $categories[0]->name }}" title="">
                                    </div>
                                    <div class="details bottom-left p-lg-0">
                                        <div class="inner">
                                            <span class="btn btn-light btn-lg">{{ $categories[0]->name }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3 collection-banner-item two-column">
                            <div class="collection-item">
                                <a wire:navigate
                                    href="{{ customUrl('categories/' . $categories[1]->slug . '/products') }}"
                                    data-link="{{ customUrl('categories/' . $categories[1]->slug . '/products') }}"
                                    class="zoom-scal">
                                    <div class="img home_theme_three_second_category">
                                        <img class="blur-up w-100 lazyloaded" data-src="{{ $categories[1]->banner }}"
                                            src="{{ $categories[1]->banner }}" alt="{{ $categories[1]->name }}"
                                            title="">
                                    </div>
                                    <div class="details bottom-left p-lg-0">
                                        <div class="inner">
                                            <span class="btn btn-light btn-lg">{{ $categories[1]->name }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <div class="collection-item">
                                <a wire:navigate
                                    href="{{ customUrl('categories/' . $categories[2]->slug . '/products') }}"
                                    data-link="{{ customUrl('categories/' . $categories[2]->slug . '/products') }}"
                                    class="zoom-scal">
                                    <div class="img home_theme_three_third_category">
                                        <img class="blur-up w-100 lazyloaded" data-src="{{ $categories[2]->banner }}"
                                            src="{{ $categories[2]->banner }}" alt="{{ $categories[2]->name }}"
                                            title="">
                                    </div>
                                    <div class="details bottom-left p-lg-0">
                                        <div class="inner">
                                            <span class="btn btn-light btn-lg">{{ $categories[2]->name }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-12 col-md-12 col-lg-5 collection-banner-item thr-column">
                            <div class="row g-0">
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6 collection-banner-item">
                                    <div class="collection-item">
                                        <a wire:navigate
                                            href="{{ customUrl('categories/' . $categories[3]->slug . '/products') }}"
                                            data-link="{{ customUrl('categories/' . $categories[3]->slug . '/products') }}"
                                            class="zoom-scal">
                                            <div class="img home_theme_three_fourth_category">
                                                <img class="blur-up w-100 lazyloaded"
                                                    data-src="{{ $categories[3]->banner }}"
                                                    src="{{ $categories[3]->banner }}"
                                                    alt="{{ $categories[3]->name }}" title="">
                                            </div>
                                            <div class="details bottom-left p-lg-0">
                                                <div class="inner">
                                                    <span
                                                        class="btn btn-light btn-lg">{{ $categories[3]->name }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-6 col-md-6 col-lg-6 collection-banner-item">
                                    <div class="collection-item">
                                        <a wire:navigate
                                            href="{{ customUrl('categories/' . $categories[4]->slug . '/products') }}"
                                            data-link="{{ customUrl('categories/' . $categories[4]->slug . '/products') }}"
                                            class="zoom-scal">
                                            <div class="img home_theme_three_fourth_category">
                                                <img class="blur-up w-100 lazyloaded"
                                                    data-src="{{ $categories[4]->banner }}"
                                                    src="{{ $categories[4]->banner }}"
                                                    alt="{{ $categories[4]->name }}" title="" width="340"
                                                    height="346">
                                            </div>
                                            <div class="details bottom-left p-lg-0">
                                                <div class="inner">
                                                    <span
                                                        class="btn btn-light btn-lg">{{ $categories[4]->name }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div class="collection-item">
                                <a wire:navigate
                                    href="{{ customUrl('categories/' . $categories[5]->slug . '/products') }}"
                                    data-link="{{ customUrl('categories/' . $categories[5]->slug . '/products') }}"
                                    class="zoom-scal">
                                    <div class="img home_theme_three_sixth_category">
                                        <img class="blur-up w-100 lazyloaded" data-src="{{ $categories[5]->banner }}"
                                            src="{{ $categories[5]->banner }}" alt="{{ $categories[5]->name }}"
                                            title="" width="689" height="347">
                                    </div>
                                    <div class="details bottom-left p-lg-0">
                                        <div class="inner">
                                            <span
                                                class="btn btn-light btn-lg text-capitalize">{{ $categories[5]->name }}</span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- end category section  --}}

    {{-- First Featured Section --}}

    @foreach ($sections as $count_key => $row)
        @if ($count_key == 0) {{-- Display only the first section --}}
            @if (!empty($row->product_details) && count((array) $row->product_details) > 0)
                @if ($row->style == 'style_1')
                    <section class="section product-slider tab-slider-product">
                        <div class="container-fluid">
                            <x-utility.section_header.sectionHeaderOne :title="$row" />
                            <div
                                class="swiper style1-mySwiper gp15 arwOut5 hov-arrow grid-products {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                                <div class="swiper-wrapper">
                                    @foreach ($row->product_details as $details)
                                        <div class="swiper-slide">
                                            @php
                                                $component = getProductDisplayComponent($store_settings);
                                            @endphp

                                            <x-dynamic-component :component="$component" :details="$details" />
                                        </div>
                                    @endforeach
                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                        </div>
                    </section>
                @endif

                @if ($row->style == 'style_2')
                    <section class="section product-banner-slider pt-0">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-9">
                                    <div
                                        class="grid-products swiper style2-mySwiper gp15 arwOut5 hov-arrow circle-arrow arrowlr-0 {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                                        <div class="swiper-wrapper">
                                            @foreach ($row->product_details as $details)
                                                <div class="swiper-slide">
                                                    @php
                                                        $component = getProductDisplayComponent($store_settings);
                                                    @endphp

                                                    <x-dynamic-component :component="$component" :details="$details" />
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                </div>
                                <div class="col-lg-3 mt-4 mt-lg-0">
                                    <div class="ctg-bnr-wrap two position-relative h-100">
                                        <div class="ctg-image ratio ratio-1x1 h-100">
                                            <img class="blur-up lazyload object-fit-cover"
                                                data-src="{{ $row->banner_image }}" src="{{ $row->banner_image }}"
                                                alt="{{ $row->title }}" />
                                        </div>
                                        <div
                                            class="ctg-content text-white d-flex justify-content-center flex-column h-100">
                                            <h2 class="ctg-title">{{ $row->title }}</h2>
                                            <p class="ctg-des mt-1 mb-4">{{ $row->short_description }}</p>
                                            <a wire:navigate
                                                href="{{ customUrl('section/' . $row->slug . '/' . $row->id . '/' . ($row->product_type == 'custom_combo_products' ? 'combo-' : '') . 'products') }}"
                                                class="btn btn-secondary">
                                                <span
                                                    class="text">{{ labels('front_messages.shop_now', 'Shop Now') }}</span>
                                                <span class="button-icon"><ion-icon
                                                        name="arrow-forward-outline"></ion-icon></span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

                @if ($row->style == 'style_3')
                    <section class="section product-banner-slider">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-lg-3 mb-4 mb-lg-0">
                                    <div class="ctg-bnr-wrap one position-relative h-100">
                                        <div class="ctg-image ratio ratio-1x1 h-100">
                                            <img class="blur-up lazyload object-fit-cover"
                                                data-src="{{ $row->banner_image }}" src="{{ $row->banner_image }}"
                                                alt="{{ $row->title }}" />
                                        </div>
                                        <div
                                            class="ctg-content text-white d-flex justify-content-center flex-column h-100">
                                            <h2 class="ctg-title">{{ $row->title }}</h2>
                                            <p class="ctg-des mt-3 mb-4">{{ $row->short_description }}</p>
                                            <a wire:navigate
                                                href="{{ customUrl('section/' . $row->slug . '/' . $row->id . '/' . ($row->product_type == 'custom_combo_products' ? 'combo-' : '') . 'products') }}"
                                                class="btn btn-secondary">
                                                {{ labels('front_messages.explore_now', 'Explore Now') }}
                                                <ion-icon class="ms-1" name="arrow-forward-outline"></ion-icon>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-9">
                                    <div
                                        class="grid-products swiper style2-mySwiper gp15 arwOut5 hov-arrow circle-arrow arrowlr-0 {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                                        <div class="swiper-wrapper">
                                            @foreach ($row->product_details as $details)
                                                <div class="swiper-slide">
                                                    @php
                                                        $component = getProductDisplayComponent($store_settings);
                                                    @endphp

                                                    <x-dynamic-component :component="$component" :details="$details" />
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            @endif
        @endif
    @endforeach

    {{-- End Featured Section --}}

    {{-- offer section  --}}
    @if (count($offers) > 4)
        <section class="section collection-banners four-one-bnr p-0">
            <div class="container-fluid">
                <div class="collection-banner-grid">
                    <div class="row row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1">
                        <div class="collection-banner-item home_theme_three_offer_collection">
                            <div class="row sp-row row-cols-lg-2 row-cols-md-2 row-cols-sm-2 row-cols-2">
                                {{-- @foreach (array_slice($offers, 3, 4) as $offer) --}}
                                @foreach (array_slice($offers, 0, 4) as $offer)
                                    <div class="collection-item sp-col">
                                        <a href="{{ $offer->link }}" data-link="{{ $offer->link }}"
                                            class="zoom-scal clr-none home_theme_three_offers {{ $offer->type != 'offer_url' ? 'slider-link' : '' }}"
                                            {{ $offer->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                            <div class="img">
                                                <img class="blur-up w-100 lazyloaded" data-src="{{ $offer->image }}"
                                                    src="{{ $offer->image }}" alt="{{ $offer->title }}"
                                                    title="" width="306" height="307">
                                            </div>
                                            <div class="details middle-center p-lg-0">
                                                <div class="inner">
                                                    <span
                                                        class="btn btn-light btn-lg text-nowrap">{{ $offer->title ?? "Don't Miss Our Deals" }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        {{-- Use the fifth offer in the last div --}}
                        <div class="collection-banner-item mt-3 mt-md-0 home_theme_three_single_offer_collection">
                            <div class="row sp-row row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1">
                                <div class="collection-item sp-col large-bnr">
                                    <a href="{{ $offers[4]->link }}" data-link="{{ $offers[4]->link }}"
                                        class="zoom-scal clr-none home_theme_three_single_offer {{ $offers[4]->type != 'offer_url' ? 'slider-link' : '' }}"
                                        {{ $offers[4]->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                        <div class="img">
                                            <img class="blur-up w-100 lazyloaded" data-src="{{ $offers[4]->image }}"
                                                src="{{ $offers[4]->image }}" alt="{{ $offers[4]->title }}"
                                                title="" width="646" height="648">
                                        </div>
                                        <div class="details middle-left p-lg-0">
                                            <div class="inner">
                                                <span
                                                    class="small-title mb-2 mb-lg-2 d-block text-uppercase">{{ $offers[4]->title ?? 'Limited Offer' }}</span>
                                                <h2 class="title">{{ $offers[4]->title ?? 'Exclusive Deal' }}</h2>
                                                <span
                                                    class="text button-text">{{ labels('front_messages.shop_now', 'Shop Now') }}</span>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- end offer section  --}}

    {{-- Remaining Featured Sections --}}
    @foreach ($sections as $count_key => $row)
        @if ($count_key > 0) {{-- Display remaining sections --}}
            @if (!empty($row->product_details) && count((array) $row->product_details) > 0)
                @if ($row->style == 'style_1')
                    <section class="section product-slider tab-slider-product">
                        <div class="container-fluid">
                            <x-utility.section_header.sectionHeaderOne :title="$row" />
                            <div
                                class="swiper style1-mySwiper gp15 arwOut5 hov-arrow grid-products {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                                <div class="swiper-wrapper">
                                    @foreach ($row->product_details as $details)
                                        <div class="swiper-slide">
                                            @php
                                                $component = getProductDisplayComponent($store_settings);
                                            @endphp

                                            <x-dynamic-component :component="$component" :details="$details" />
                                        </div>
                                    @endforeach
                                </div>
                                <div class="swiper-button-next"></div>
                                <div class="swiper-button-prev"></div>
                            </div>
                        </div>
                    </section>
                @endif

                @if ($row->style == 'style_2')
                    <section class="section product-banner-slider pt-0">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                                    <div
                                        class="grid-products swiper style2-mySwiper gp15 arwOut5 hov-arrow circle-arrow arrowlr-0 {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                                        <div class="swiper-wrapper">
                                            @foreach ($row->product_details as $details)
                                                <div class="swiper-slide ">
                                                    @php
                                                        $component = getProductDisplayComponent($store_settings);
                                                    @endphp

                                                    <x-dynamic-component :component="$component" :details="$details" />
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-12 col-lg-3 mt-4 mt-lg-0">
                                    <div class="ctg-bnr-wrap two position-relative h-100">
                                        <div class="ctg-image ratio ratio-1x1 h-100">
                                            <img class="blur-up lazyload object-fit-cover"
                                                data-src="{{ $row->banner_image }}" src="{{ $row->banner_image }}"
                                                alt="{{ $row->title }}" width="309" height="483" />
                                        </div>
                                        <div
                                            class="ctg-content text-white d-flex-justify-center flex-nowrap flex-column h-100">
                                            <h2 class="ctg-title text-white m-0">{{ $row->title }}</h2>
                                            <p class="ctg-des mt-1 mb-4">{{ $row->short_description }}</p>
                                            <a wire:navigate
                                                href="{{ customUrl('section/' . $row->slug . '/' . $row->id . '/' . ($row->product_type == 'custom_combo_products' ? 'combo-' : '') . 'products') }}"
                                                class="btn btn-secondary explore-btn button-style" href="">
                                                <span
                                                    class="text button-text">{{ labels('front_messages.shop_now', 'Shop Now') }}</span>
                                                <span class="button-icon button-icon-right"><ion-icon
                                                        name="arrow-forward-outline"></ion-icon></span></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
                @if ($row->style == 'style_3')
                    <section class="section product-banner-slider">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-3 mb-4 mb-lg-0">
                                    <div class="ctg-bnr-wrap one position-relative h-100">
                                        <div class="ctg-image ratio ratio-1x1 h-100">
                                            <img class="blur-up lazyload object-fit-cover"
                                                data-src="{{ $row->banner_image }}" src="{{ $row->banner_image }}"
                                                alt="{{ $row->title }}" width="390" height="483" />
                                        </div>
                                        <div
                                            class="ctg-content text-white d-flex-justify-center flex-nowrap flex-column h-100">
                                            <h2 class="ctg-title text-white m-0">{{ $row->title }}
                                            </h2>
                                            <p class="ctg-des mt-3 mb-4">{{ $row->short_description }}</p>
                                            <a wire:navigate
                                                href="{{ customUrl('section/' . $row->slug . '/' . $row->id . '/' . ($row->product_type == 'custom_combo_products' ? 'combo-' : '') . 'products') }}"
                                                class="btn btn-secondary explore-btn"
                                                href="">{{ labels('front_messages.explore_now', 'Explore Now') }}
                                                <ion-icon class="ms-1" name="arrow-forward-outline"></ion-icon></a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                                    <div
                                        class="grid-products swiper style2-mySwiper gp15 arwOut5 hov-arrow circle-arrow arrowlr-0 {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                                        <div class="swiper-wrapper">
                                            @foreach ($row->product_details as $details)
                                                <div class="swiper-slide ">
                                                    @php
                                                        $component = getProductDisplayComponent($store_settings);
                                                    @endphp

                                                    <x-dynamic-component :component="$component" :details="$details" />
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="swiper-button-next"></div>
                                        <div class="swiper-button-prev"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                @endif
            @endif
        @endif
    @endforeach

    {{-- End Section --}}

    {{-- safety and security  --}}
    <x-utility.safety_and_security.styleOne :$settings />
    {{-- end safety and security  --}}

    {{-- brands  --}}
    @if (isset($brands['brands']) && is_array($brands['brands']) && count($brands['brands']) >= 1)
        <div class="section home-instagram pb-0 mb-3">
            <div class="container-fluid">
                <div class="section-header style2 d-flex justify-content-between">
                    <div class="section-header text-start">
                        <h2>{{ labels('front_messages.popular_brands', 'Popular Brands') }}</h2>
                        <p>{{ labels('front_messages.explore_brands', 'Explore top picks in our Brands!') }}</p>
                    </div>
                    <div class="section-header-right text-start text-sm-end mt-sm-0 d-flex-center">
                        <a wire:navigate href="{{ customUrl('brands') }}"
                            class="d-flex align-items-center view_more_icon arrow_icon">
                            <i class="anm anm-arrow-alt-right hdr-icon icon"></i>
                        </a>
                    </div>
                </div>

                <!-- Swiper Slider -->
                <div class="swiper home_theme_three_brands_swiper">
                    <div class="swiper-wrapper">
                        @foreach ($brands['brands'] as $brand)
                            <div class="swiper-slide">
                                <div class="brand-item">
                                    <a wire:navigate href="{{ customUrl('products/?brand=' . $brand['brand_slug']) }}"
                                        class="zoom-scal home_theme_three_brand_card"
                                        data-link="{{ customUrl('products/?brand=' . $brand['brand_slug']) }}">
                                        <img class="blur-up lazyload" src="{{ $brand['brand_img'] }}"
                                            alt="{{ $brand['brand_name'] }}" width="310" height="310">
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <!-- Add Pagination & Navigation -->
                    <div class="swiper-button-next"></div>
                    <div class="swiper-button-prev"></div>
                </div>
            </div>
        </div>
    @endif

    {{-- end brands  --}}
</div>
