<!--Page Wrapper-->
<div class="page-wrapper">
    <!-- Body Container -->
    @php
        $categories = $categories['categories'];
        $store_settings = getStoreSettings();
    @endphp
    <div id="page-content" class="mb-0 index-demo11">
        {{-- <div id="page-content" class="mb-0"> --}}
        <!--Home Slideshow-->
        <section class="slideshow slideshow-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-3 d-none d-lg-block">
                        <div class="header-vertical-menu">
                            <div class="vertical-menu-content rounded-5 rounded-top-0">
                                <h4 class="menuTitle d-none"><span>Browse Categories</span></h4>
                                <ul class="menuList">
                                    @if (is_array($categories) && count($categories) >= 1)
                                        @foreach (array_slice($categories, 0, 12) as $category)
                                            <li>
                                                <a wire:navigate
                                                    href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                                                    class="nav-link">
                                                    {!! $category->name !!}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif

                                </ul>
                                <a wire:navigate href="{{ customUrl('categories') }}"
                                    class="moreCategories border-0 d-flex justify-content-between align-items-center w-100">
                                    <span>View All Categories</span>
                                    <ion-icon name="arrow-forward-outline" class="fs-5"></ion-icon>
                                </a>
                                {{-- <div class="moreCategories border-0">View All Categories</div> --}}
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                        <div class="home-slideshow slideshow-medium slick-arrow-dots rounded-pill-dots">
                            @foreach ($sliders as $slider)
                                <div class="slide home_theme_5_slider">
                                    <div class="slideshow-wrap home_theme_5_slider_wrapper rounded-5">
                                        @if ($slider['type'] !== 'default')
                                            <a @if ($slider['type'] !== 'slider_url') wire:navigate @endif
                                                href="{{ $slider['link'] }}"
                                                class="slider-link home_theme_5_slider_image"
                                                data-link="{{ $slider['link'] }}"
                                                target="{{ $slider['type'] == 'slider_url' ? '_blank' : '' }}">
                                                <picture>
                                                    <source media="(max-width:767px)" srcset="{{ $slider['image'] }}">
                                                    <img class="rounded-5 blur-up lazyload" src="{{ $slider['image'] }}"
                                                        alt="slideshow" title="" />
                                                </picture>
                                            </a>
                                        @else
                                            <picture>
                                                <source media="(max-width:767px)" srcset="{{ $slider['image'] }}">
                                                <img class="rounded-5 blur-up lazyload" src="{{ $slider['image'] }}"
                                                    alt="slideshow" title="" />
                                            </picture>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!--End Home Slideshow-->

        {{-- brands  --}}

        @if (isset($brands['brands']) && is_array($brands['brands']) && count($brands['brands']) >= 1)
            <section class="section our-service-section pb-0">
                <div class="container-fluid">
                    <div class="section-header style2 d-flex-center justify-content-between">
                        <div class="section-header-left text-start">
                            <h2>{{ labels('front_messages.popular_brands', 'Popular Brands') }}</h2>
                            <p>{{ labels('front_messages.explore_brands', 'Explore top picks in our Brands!') }}</p>
                        </div>
                        <div class="section-header-right text-start text-sm-end  mt-sm-0">
                            <a wire:navigate href="{{ customUrl('brands') }}"
                                class="d-flex align-items-center view_more_icon arrow_icon">
                                <i class="anm anm-arrow-alt-right hdr-icon icon"></i>
                            </a>
                        </div>
                    </div>
                    <div class="row sp-row row-cols-lg-4 row-cols-md-3 row-cols-sm-3 row-cols-1">
                        @foreach (array_slice($brands['brands'], 0, 8) as $brand)
                            <div class="sp-col service-info">
                                <a wire:navigate href="{{ customUrl('products/?brand=' . $brand['brand_slug']) }}"
                                    class="service-wrap d-flex align-items-center border home_theme_four_brand_card_border p-4 bg-white rounded-5"
                                    data-link="{{ customUrl('products/?brand=' . $brand['brand_slug']) }}">
                                    <div
                                        class="service-icon d-flex align-items-center justify-content-center home_theme_four_brand_card">
                                        <img class="blur-up lazyloaded" data-src="{{ $brand['brand_img'] }}"
                                            src="{{ $brand['brand_img'] }}" alt="doctor" title="">
                                    </div>
                                    <div class="service-content ms-4">
                                        <h4>{{ \Illuminate\Support\Str::words($brand['brand_name'], 20, '...') }}</h4>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- end brands  --}}
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

        {{-- rating  --}}
        @if (isset($ratings) && is_array($ratings) && count($ratings) >= 1)
            <section class="section testimonial-slider style1 pb-0">
                <div class="container-fluid">
                    <div class="section-header style2 d-flex-center justify-content-sm-between">
                        <div class="section-header-left text-start">
                            <h2 class="mb-0">What People Are Saying</h2>
                        </div>
                    </div>

                    <div class="testimonial-wraper">
                        <div class="testimonial-slider-3items gp15 rounded-pill-dots slick-arrow-dots arwOut5">
                            @foreach (array_slice($ratings, 0, 6) as $rating)
                                {{-- <div class="testimonial-slide border bg-white rounded-5">
                                    <div class="testimonial-content">
                                        <div class="auhimg d-flex align-items-center">
                                            <div class="image">
                                                <img class="rounded-circle blur-up lazyload"
                                                    src="{{ $rating->user_profile }}"
                                                    alt="{{ $rating->user_name ?: 'Anonymous' }}" width="65"
                                                    height="65">
                                            </div>
                                            <div class="auhtext ms-3">
                                                <h4 class="mb-2 pb-1">{{ $rating->title ?: 'Customer Feedback' }}</h4>
                                                <div class="product-review">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i class="icon anm {{ $i <= round($rating->rating) ? 'anm-star' : 'anm-star-o' }}"></i>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>

                                        <div class="content border-bottom">
                                            <div class="text">
                                                <p>{{ $rating->comment }}</p>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mt-3 pt-3">
                                            <div class="authour">
                                                <h5 class="mb-0">{{ $rating->user_name ?: 'Anonymous' }}</h5>
                                            </div>
                                            <div class="auhtext ms-3">
                                                <p class="text-muted">Posted on {{ \Carbon\Carbon::parse($rating->created_at)->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                    </div>

                                </div> --}}
                                <div class="testimonial-slide border bg-white rounded-5">
                                    <div class="testimonial-content">
                                        <div class="auhimg d-flex align-items-center border-bottom">
                                            <div class="image home_theme_four_user_image">
                                                <img class="rounded-circle blur-up lazyload"
                                                    src="{{ $rating->user_profile }}"
                                                    alt="{{ $rating->user_name ?: 'Anonymous' }}">
                                            </div>
                                            <div class="auhtext ms-3">
                                                <h4 class="mb-2 pb-1">{{ $rating->title ?: 'Customer Feedback' }}</h4>

                                                <div class="product-review">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i
                                                            class="icon anm {{ $i <= round($rating->rating) ? 'anm-star' : 'anm-star-o' }}"></i>
                                                    @endfor
                                                </div>
                                                <p class="mt-2">{{ $rating->comment ?: '' }}</p>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center justify-content-between mt-3 pt-3">
                                            <div class="authour">
                                                <h5 class="mb-0">{{ $rating->user_name ?: 'Anonymous' }}</h5>
                                            </div>
                                            <div class="auhtext ms-auto">
                                                <p class="text-muted">Posted on
                                                    {{ \Carbon\Carbon::parse($rating->created_at)->format('d/m/Y') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif
        {{-- end rating  --}}
        {{-- offers  --}}
        @if (count($offers) > 2)
            <section class="section">
                <div class="container-fluid">
                    <div class="collection-banner-grid home_theme_four_offer two-bnr-ct2">
                        <div class="collection-slider-3items gp15 arwOut5 hov-arrow rounded-pill-dots">
                            @foreach (array_slice($offers, 0, 3) as $offer)
                                <div class="collection-banner-item">
                                    <div class="collection-item">
                                        <a href="{{ $offer->link }}"
                                            class="rounded-5 zoom-scal zoom-scal-nopb clr-none {{ $offer->type != 'offer_url' ? 'slider-link' : '' }}"
                                            data-link="{{ $offer->link }}"
                                            {{ $offer->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                            <div class="img">
                                                <img class="rounded-5 blur-up lazyload"
                                                    data-src="{{ $offer->image }}" src="{{ $offer->image }}"
                                                    alt="{{ $row->title }}" title="" />
                                            </div>
                                            <div class="details middle-left">
                                                <div class="inner text-left">
                                                    <h3 class="title">{{ $offer->title ?? "Don't Miss Our Deals" }}
                                                    </h3>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif
        {{-- end offers  --}}
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
                                                    data-src="{{ $row->banner_image }}"
                                                    src="{{ $row->banner_image }}" alt="{{ $row->title }}"
                                                    width="309" height="483" />
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
                                                    data-src="{{ $row->banner_image }}"
                                                    src="{{ $row->banner_image }}" alt="{{ $row->title }}"
                                                    width="390" height="483" />
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
                                                    <ion-icon class="ms-1"
                                                        name="arrow-forward-outline"></ion-icon></a>
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
        <x-utility.safety_and_security.styleTwo :$settings />
        {{-- end safety and security  --}}
    </div>
</div>
