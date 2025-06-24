<div id="page-content" class="mb-0">
    @php
        $store_settings = getStoreSettings();
    @endphp
    <!--Home Slideshow-->
    <section class="slideshow slideshow-wrapper">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-12 col-lg-8">
                    <div class="home-slideshow slideshow-medium slick-arrow-dots circle-arrow">
                        @foreach ($sliders as $slider)
                            <div class="slide">
                                <div class="slideshow-wrap bg-size rounded-4">
                                    @if ($slider['type'] !== 'default')
                                        <a @if ($slider['type'] !== 'slider_url') wire:navigate @endif
                                            href="{{ $slider['link'] }}" class="slider-link"
                                            data-link="{{ $slider['link'] }}"
                                            target="{{ $slider['type'] == 'slider_url' ? '_blank' : '' }}">
                                            <div class="home_theme_two_slideshow">

                                                <img class="rounded-4 blur-up lazyload"
                                                    data-src="{{ $slider['image'] }}" src="{{ $slider['image'] }}"
                                                    alt="slideshow" title="" />
                                            </div>
                                        </a>
                                    @else
                                        <div class="home_theme_two_slideshow">

                                            <img class="rounded-4 blur-up lazyload" data-src="{{ $slider['image'] }}"
                                                src="{{ $slider['image'] }}" alt="slideshow" title="" />
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-12 col-lg-4 mt-4 mt-lg-0">
                    @if (count($offers) > 0)
                        {{-- Display first 2 offers --}}
                        @for ($i = 0; $i < min(2, count($offers)); $i++)
                            @php
                                $offersImage = dynamic_image($offers[$i]->banner_image, 635);
                            @endphp
                            <div class="collection-banner-grid">
                                <div class="row sp-row">
                                    <div class="col-12 col-sm-12 col-lg-12 collection-banner-item">
                                        <div class="collection-item sp-col">
                                            <a href="{{ $offers[$i]->link }}"
                                                class="zoom-scal clr-none {{ $offers[$i]->type != 'offer_url' ? 'slider-link' : '' }}"
                                                data-link="{{ $offers[$i]->link }}"
                                                {{ $offers[$i]->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                                <div class="img home_slideshow_offers">
                                                    <img class="blur-up lazyload w-100" data-src="{{ $offersImage }}"
                                                        src="{{ $offersImage }}" alt="{{ $offers[$i]->title }}"
                                                        title="" />
                                                </div>
                                                <div class="home_theme_two_offer_overlay"></div>
                                                <div class="details middle-center text-center p-md-2 w-100">
                                                    <div class="inner">
                                                        <span
                                                            class="small-title mb-2 mb-lg-2 d-block text-white text-uppercase">
                                                            {{ $offers[$i]->title }}
                                                        </span>
                                                        @php
                                                            $hidden_types = ['default', 'products', 'combo_products'];
                                                        @endphp

                                                        @if (!in_array($offers[$i]->type, $hidden_types))
                                                            <h3 class="title text-white mb-2">
                                                                {{ $offers[$i]->min_discount . '-' . $offers[$i]->max_discount . '% Less' }}
                                                            </h3>
                                                            <span
                                                                class="rounded-pill btn-secondary btn-md mb-3 xs-hide">
                                                                {{ labels('front_messages.shop_now', 'Shop Now') }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    @endif
                </div>
            </div>
        </div>
    </section>
    <!--End Home Slideshow-->

    {{-- Featured Section --}}

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
    {{-- @dd($offers); --}}
    {{-- Offer Section --}}
    @php
        $Thirdoffer = $offers[2] ?? '';
    @endphp
    @if (isset($Thirdoffer) && !empty($Thirdoffer) && $Thirdoffer !== '')
        <section class="section collection-banners four-one-bnr pb-0">
            @php
                $singleOffersImage = dynamic_image($Thirdoffer->banner_image, 635);
            @endphp
            <div class="container-fluid">
                <div class="collection-banner-grid onelarge-four-bnr">
                    <div class="row row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1 mb-2">
                        <div class="collection-banner-item mb-3 mb-md-0">
                            <div class="row sp-row row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1">
                                <div class="collection-item sp-col large-bnr ctImg1">
                                    <a href="{{ $Thirdoffer->link }}"
                                        class="bg-square-hv rounded-4 clr-none {{ $Thirdoffer->type != 'offer_url' ? 'slider-link' : '' }}"
                                        data-link="{{ $Thirdoffer->link }}"
                                        {{ $Thirdoffer->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                        <div class="home_theme_two_single_offer">
                                            <img class="rounded-4 w-100 blur-up lazyloaded"
                                                data-src="{{ $singleOffersImage }}" src="{{ $singleOffersImage }}"
                                                alt="{{ $Thirdoffer->title }}" title="" />
                                        </div>
                                        <div class="home_theme_two_offer_overlay"></div>
                                        <div class="details bottom-right text-left whiteText p-0">
                                            <div class="inner">
                                                <span
                                                    class="small-title mb-1 d-block xs-hide alt-font">{{ $Thirdoffer->title }}</span>
                                                <h3 class="title text-capitalize text-white mb-2">
                                                    {{ $Thirdoffer->min_discount . '-' . $Thirdoffer->max_discount . '% Less' }}
                                                </h3>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                        @if (count($offers) > 3)
                            <div class="collection-banner-item">
                                <div class="row sp-row row-cols-lg-2 row-cols-md-2 row-cols-sm-2 row-cols-2">
                                    @foreach (array_slice($offers, 3, 4) as $offer)
                                        <div class="collection-item sp-col sale-banner ctImg2">
                                            <a href="{{ $offer->link }}"
                                                class="zoom-scal rounded-4 clr-none {{ $offer->type != 'offer_url' ? 'slider-link' : '' }}"
                                                data-link="{{ $offer->link }}"
                                                {{ $offer->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                                <div class="home_theme_two_four_offers">
                                                    <img class="rounded-4 w-100 blur-up lazyloaded"
                                                        data-src="{{ $offer->image }}" src="{{ $offer->image }}"
                                                        alt="{{ $offer->title }}" title="">
                                                </div>
                                                <div class="home_theme_two_offer_overlay"></div>
                                                <div class="details middle-center text-center p-md-2 w-100">
                                                    <div class="inner">
                                                        <span
                                                            class="small-title mb-2 mb-lg-3 d-block text-white fs-6 xs-hide">
                                                            {{ $offer->title ?? "Don't Miss Our Deals" }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- end offers section  --}}

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
    <!--Popular Categories-->
    @php
        $categories = $categories['categories'];
    @endphp

    @if (is_array($categories) && count($categories) >= 1)
        <section class="section collection-slider">
            <div class="container-fluid">
                <div class="section-header style2 d-flex justify-content-between">
                    <div class="section-header-left text-start">
                        @if ($store_settings['category_section_title'] != null)
                            <h2>{{ $store_settings['category_section_title'] }}</h2>
                        @else
                            <h2>{{ labels('front_messages.popular_categories', 'Popular Categories') }}</h2>
                        @endif
                        <p>{{ labels('front_messages.explore_categories', 'Explore top picks in our Categories!') }}
                        </p>
                    </div>
                    <div class="section-header-right text-start text-sm-end mt-sm-0 d-flex-center">

                        <a wire:navigate href="{{ customUrl('categories') }}" wire:navigate
                            class="d-flex align-items-center view_more_icon arrow_icon">
                            {{-- <ion-icon wire:ignore name="arrow-forward-circle" class="me-1 fs-1"></ion-icon> --}}
                            <i class="anm anm-arrow-alt-right hdr-icon icon"></i>
                        </a>
                    </div>
                </div>
                <div class="gp15 arwOut5 hov-arrow circle-arrow">
                    <div class="swiper home-theme-2-category-mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($categories as $category)
                                <div class="swiper-slide zoomscal-hov rounded-4 home_theme_two_category_card">
                                    <a wire:navigate
                                        href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                                        class="category-link clr-none brand-box slider-link"
                                        data-link="{{ customUrl('categories/' . $category->slug . '/products') }}">
                                        <div
                                            class="zoom-scal zoom-scal-nopb rounded-circle home_theme_two_category_image">
                                            <img class="blur-up lazyload" data-src="{{ $category->image }}"
                                                src="{{ $category->image }}" alt="{!! $category->name !!}"
                                                title="" />
                                        </div>

                                        <div class="details text-center mt-2">
                                            <h4 class="category-title mb-0 fs-6 fw-600 text-capitalize">
                                                {!! $category->name !!}</h4>
                                        </div>

                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </div>
        </section>
    @endif
    <!--End Popular Categories-->

    {{-- brands  --}}
    @if (isset($brands['brands']) && is_array($brands['brands']) && count($brands['brands']) >= 1)
        <section class="section logo-section">
            <div class="container-fluid">
                <div class="section-header style2 d-flex justify-content-between">
                    <div class="section-header-left text-start">
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
                <div class="brands-list">
                    <div class="swiper home-page-2-brands-slider">
                        <div class="swiper-wrapper">
                            @foreach ($brands['brands'] as $brand)
                                <div class="swiper-slide home_page_2_brands_image">
                                    <div class="brands-logo">
                                        <a wire:navigate
                                            href="{{ customUrl('products/?brand=' . $brand['brand_slug']) }}"
                                            class="zoom-scal zoom-scal-nopb category-link clr-none bg-body brand-box slider-link home_page_two_brands_list"
                                            data-link="{{ customUrl('products/?brand=' . $brand['brand_slug']) }}">
                                            <img class="rounded-4 blur-up lazyloaded" src="{{ $brand['brand_img'] }}"
                                                data-src="{{ $brand['brand_img'] }}" alt="Brand Logo"
                                                title="">
                                        </a>
                                    </div>

                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-button-prev"></div>
                        <div class="swiper-button-next"></div>
                    </div>
                </div>


            </div>
        </section>
    @endif

    {{-- end brands  --}}
</div>
