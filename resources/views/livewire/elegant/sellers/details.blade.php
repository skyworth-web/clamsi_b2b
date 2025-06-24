<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    {{-- @dd($seller) --}}
    @php
        $img = getMediaImageUrl($seller[0]->logo, 'SELLER_IMG_PATH');
        $img = dynamic_image($img, 230);
    @endphp
    <div class="container-fluid h-100">
        <div class="orders-card mt-0 h-100 mb-2">
            <div class="row mt-3">
                <div class="col-lg-2 col-md-3 col-sm-4">
                    <div class="product-img mb-3 mb-sm-0">
                        <img class="rounded-0 blur-up lazyload" data-src="{{ $img }}" src="{{ $img }}"
                            alt="product" title="" width="545" height="700" />
                    </div>
                </div>
                <div class="col-lg-10 col-md-9 col-sm-8">
                    <div class="tracking-detail d-flex-center">
                        <ul>
                            <li>
                                <div class="left"><span>{{ labels('front_messages.seller', 'Seller') }}</span></div>
                                <div class="right"><span>{{ $seller_details['username'] ?? '' }}</span></div>
                            </li>
                            <li>
                                <div class="left"><span>{{ labels('front_messages.products', 'Products') }}</span>
                                </div>
                                <div class="right"><span>{{ count($products) }}
                                        {{ labels('front_messages.products', 'Products') }}</span></div>
                            </li>
                            <li>
                                <div class="left"><span>{{ labels('front_messages.ratings', 'Ratings') }}</span></div>
                                <div class="right"><span>{{ $seller[0]->rating }}</span> <ion-icon
                                        name="star"></ion-icon></div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="top-sec d-flex-justify-center justify-content-between my-4">
                <h2 class="mb-0">{{ labels('front_messages.products', 'Products') }}</h2>
            </div>
            @php
                $store_settings = getStoreSettings();
            @endphp
            @if (count($products['product']) >= 1)
                <div
                    class="grid-products grid-view-items {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                    <div class="row col-row product-options row-cols-lg-4 row-cols-md-3 row-cols-sm-3 row-cols-2">
                        @foreach ($products['product'] as $details)
                            @php
                                $store_settings = getStoreSettings();
                                $component = getProductDisplayComponent($store_settings);
                            @endphp

                            <x-dynamic-component :component="$component" :details="$details" />
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $title = labels('front_messages.seller_dont_have_any_products', 'Seller Don\'t Have Any Products');
                @endphp
                <x-utility.others.not-found :$title />
            @endif
        </div>
        <div class="pt-2">{!! $products['links'] !!}</div>
    </div>
</div>
