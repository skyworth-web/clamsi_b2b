<div id="page-contect">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    @if ($offers_sliders->original['error'] != 'true')
        <section class="section featured-content style1 index-demo2">
            <div class="container-fluid">
                @php
                    $off_sliders = $offers_sliders->original['slider_images'];
                @endphp
                @foreach ($off_sliders as $sliders)
                    <div class="section-header mb-3">
                        <h2>{{ $sliders['title'] }}</h2>
                    </div>
                    <div class="offer-slider">
                        @foreach ($sliders['offer_images'] as $offers)
                            @php
                                // dd($offers['type']);
                                $url = '';
                                if ($offers['type'] == 'categories') {
                                    $url = customUrl('categories/' . $offers['data'][0]['slug'] . '/products');
                                } elseif ($offers['type'] == 'products') {
                                    $url = customUrl('products/' . $offers['data'][0]['slug']);
                                } elseif ($offers['type'] == 'all_products') {
                                    $url = customUrl('products');
                                } elseif ($offers['type'] == 'brand') {
                                    $url = customUrl('products?brand=' . $offers['data'][0]['slug']);
                                }
                            @endphp
                            <div class="row mb-4">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                    <div
                                        class="d-flex align-items-stretch justify-content-between w-100 flex-md-row flex-column">
                                        <div
                                            class="f-item fl-1 d-flex w-100 align-items-center justify-content-center bg-light order-md-0 order-xl-0 order-sm-1 order-lg-0 order-1">
                                            <div
                                                class="f-text order-3 text-center px-4 mx-sm-5 mx-md-4 py-4 py-sm-5 py-md-4">
                                                <h3 class="fs-3 mb-0 text-black">{{ $offers['title'] }}</h3>
                                                @php
                                                    $hidden_types = [
                                                        'default',
                                                        'products',
                                                        'combo_products',
                                                        'offer_url',
                                                    ];
                                                @endphp

                                                @if (!in_array($offers['type'], $hidden_types))
                                                    <div class="rte rte-setting mb-4 pb-md-2">
                                                        <p class="text-medium"><br><b>{{ $offers['min_discount'] }} TO
                                                                {{ $offers['max_discount'] }}%
                                                                {{ labels('front_messages.discount', 'DISCOUNT') }}</b>
                                                        </p>
                                                    </div>
                                                @endif
                                                <a wire:navigate href="{{ $url }}"
                                                    class="btn bt-primary btn-lg mt20">{{ labels('front_messages.discover_now', 'Discover Now!') }}</a>
                                            </div>
                                        </div>
                                        <div class="f-item fl-1 d-flex w-100 align-items-center">
                                            <div class="f-image order-2 img-box-h400">
                                                @php
                                                    $offer_banner_img = dynamic_image($offers['banner_image'], 750);
                                                @endphp
                                                <img class="blur-up lazyloaded" data-src="{{ $offer_banner_img }}"
                                                    src="{{ $offer_banner_img }}" alt="{{ $offers['title'] }}"
                                                    title="" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    @endif
    @if (count($singleOffers) >= 1)
        {{-- @dd($singleOffers); --}}
        <section class="section collection-banners four-one-bnr">
            <div class="container-fluid">
                <div class="collection-banner-grid">
                    <div class="row">
                        <div class="collection-banner-item">
                            <div class="swiper offer-mySwiper">
                                <div class="swiper-wrapper">
                                    {{-- @foreach ($singleOffers as $data)
                                        @php
                                            $singleOffersImage = dynamic_image($data->banner_image, 635);
                                        @endphp
                                        <div class="collection-item sp-col sale-banner swiper-slide">
                                            <div class="overlay-image"></div>
                                            <a href="{{ $data->link }}"
                                                class="zoom-scal clr-none {{ $data->type != 'offer_url' ? 'slider-link' : '' }}"
                                                data-link="{{ $data->link }}"
                                                {{ $data->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                                <div class="img img-box-h300px">
                                                    <img class="blur-up lazyload w-100"
                                                        data-src="{{ $singleOffersImage }}"
                                                        src="{{ $singleOffersImage }}" alt="{{ $data->title }}"
                                                        title="" />
                                                </div>
                                                <div class="details middle-center text-center p-md-2 w-100">
                                                    <div class="inner">
                                                        <span
                                                            class="small-title mb-2 mb-lg-2 d-block text-white text-uppercase">{{ $data->title }}</span>
                                                        @php
                                                            $hidden_types = [
                                                                'default',
                                                                'products',
                                                                'combo_products',
                                                                'offer_url',
                                                            ];
                                                        @endphp

                                                        @if (!in_array($data->type, $hidden_types))
                                                            <h3 class="title text-white">
                                                                {{ $data->min_discount . '-' . $data->max_discount . '% Less' }}
                                                            </h3>
                                                        @endif
                                                        @if ($data->type !== 'default')
                                                            <span
                                                                class="btn btn-secondary btn-md  mt-3 xs-hide">{{ labels('front_messages.shop_now', 'Shop Now') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endforeach --}}
                                    @foreach ($singleOffers as $data)
                                        @php
                                            $singleOffersImage = dynamic_image($data->banner_image, 635);

                                            // Determine the correct URL
                                            $hidden_types = ['default', 'products', 'combo_products', 'offer_url'];
                                            $custom_url = $data->link;

                                            if ($data->type == 'all_products') {
                                                $custom_url = customUrl('products');
                                            } elseif (empty($custom_url)) {
                                                $custom_url = '#';
                                            }
                                            // dd($custom_url);
                                        @endphp

                                        <div class="collection-item sp-col sale-banner swiper-slide">
                                            <div class="overlay-image"></div>
                                            <a href="{{ $custom_url }}"
                                                class="zoom-scal clr-none {{ $data->type != 'offer_url' ? 'slider-link' : '' }}"
                                                data-link="{{ $custom_url }}"
                                                {{ $data->type == 'offer_url' ? 'target="_blank"' : 'wire:navigate' }}>
                                                <div class="img img-box-h300px">
                                                    <img class="blur-up lazyload w-100"
                                                        data-src="{{ $singleOffersImage }}"
                                                        src="{{ $singleOffersImage }}" alt="{{ $data->title }}" />
                                                </div>
                                                <div class="details middle-center text-center p-md-2 w-100">
                                                    <div class="inner">
                                                        <span
                                                            class="small-title mb-2 mb-lg-2 d-block text-white text-uppercase">
                                                            {{ $data->title }}
                                                        </span>

                                                        @if (!in_array($data->type, $hidden_types))
                                                            <h3 class="title text-white">
                                                                {{ $data->min_discount . '-' . $data->max_discount . '% Less' }}
                                                            </h3>
                                                        @endif

                                                        @if ($data->type !== 'default')
                                                            <span class="btn btn-secondary btn-md mt-3 xs-hide">
                                                                {{ labels('front_messages.shop_now', 'Shop Now') }}
                                                            </span>
                                                        @endif
                                                    </div>
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
                </div>
            </div>
        </section>
    @endif
    @if ($offers_sliders->original['error'] == 'true' && count($singleOffers) < 1)
        @php
            $title = labels('front_messages.no_offers_found', 'No Offers Found!');
        @endphp
        <x-utility.others.not-found :$title />
    @endif
</div>
