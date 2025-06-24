@props([
    'regular_wishlist' => [],
    'combo_wishlist' => [],
    'favorites_count' => 0,
    'links' => null,
])
@php
    $language_code = get_language_code();
@endphp
<div class="col-12 col-sm-12 col-md-12 col-lg-9">
    <div class="dashboard-content h-100">
        <div class="h-100" id="wishlist">
            <div class="orders-card mt-0 h-100">
                <div class="top-sec d-flex-justify-center justify-content-between mb-4">
                    <h2 class="mb-0">{{ labels('front_messages.my_wishlist', 'My Wishlist') }}</h2>
                </div>
                <div class="tabs-listing section p-0">
                    <ul class="product-tabs style2 list-unstyled d-flex-wrap d-none d-md-flex" wire:ignore>
                        <li rel="regular-wishlist" class="active"><a
                                class="tablink">{{ labels('front_messages.regular_products', 'Regular Products') }}</a>
                        </li>
                        <li rel="combo-wishlist"><a
                                class="tablink">{{ labels('front_messages.combo_products', 'Combo Products') }}</a>
                        </li>
                    </ul>

                    <div class="tab-container">
                        <h3 class="tabs-ac-style rounded-5 d-md-none active" rel="regular-wishlist">
                            {{ labels('front_messages.regular_products', 'Regular Products') }}</h3>
                        <div id="regular-wishlist" class="tab-content" wire:ignore.self>
                            @if (count($regular_wishlist) >= 1)
                                <div class="grid-products grid-view-items">
                                    <div class="col-row row row-cols-2 row-cols-lg-4 row-cols-md-3 row-cols-xl-5">
                                        @foreach ($regular_wishlist as $details)
                                            @php
                                                $pro_image = dynamic_image($details['image'], 800);
                                                $pro_name = getDynamicTranslation('products','name',$details['id'],$language_code)
                                            @endphp
                                            <div class="item col-item">
                                                <div class="product-box position-relative">
                                                    <button type="button"
                                                        class="btn remove-icon close-btn remove-favorite"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                                        data-bs-original-title="Remove" aria-label="Remove"
                                                        data-product-id="{{ $details['id'] }}"><ion-icon name="close-outline"></ion-icon></button>
                                                    <div class="product-image">
                                                        <a wire:navigate
                                                            href="{{ customUrl('products/' . $details['slug']) }}"
                                                            class="all-product-img product-img rounded-3 slider-link"
                                                            data-link="{{ customUrl('products/' . $details['slug']) }}">
                                                            <img class="blur-up lazyload" src="{{ $pro_image }}"
                                                                alt="Product" title="{{ $pro_name }}"
                                                                width="625" height="808" />
                                                        </a>
                                                    </div>
                                                    <div class="product-details text-center">
                                                        <div class="product-name text-ellipsis">
                                                            <a wire:navigate
                                                                href="{{ customUrl('products/' . $details['slug']) }}">{{ $pro_name }}</a>
                                                        </div>
                                                        <div class="product-price">
                                                            @php
                                                                if ($details['type'] == 'variable_product') {
                                                                    $price = currentCurrencyPrice(
                                                                        $details['min_max_price']['max_price'],
                                                                        true,
                                                                    );
                                                                    $special_price =
                                                                        $details['min_max_price'][
                                                                            'special_min_price'
                                                                        ] &&
                                                                        $details['min_max_price']['special_min_price'] >
                                                                            0
                                                                            ? currentCurrencyPrice(
                                                                                $details['min_max_price'][
                                                                                    'special_min_price'
                                                                                ],
                                                                                true,
                                                                            )
                                                                            : $price;
                                                                } else {
                                                                    $price = currentCurrencyPrice(
                                                                        $details['variants'][0]->price,
                                                                        true,
                                                                    );
                                                                    $special_price =
                                                                        $details['variants'][0]->special_price &&
                                                                        $details['variants'][0]->special_price > 0
                                                                            ? currentCurrencyPrice(
                                                                                $details['variants'][0]->special_price,
                                                                                true,
                                                                            )
                                                                            : $price;
                                                                }
                                                            @endphp
                                                            <span
                                                                class="price old-price">{{ $special_price !== $price ? $price : '' }}</span>
                                                            <span class="price fw-500"><span
                                                                    wire:model="price">{{ $special_price }}</span></span>
                                                        </div>
                                                        <div class="product-review">
                                                            <div class="product-review">
                                                                <input id="input-3-ltr-star-md"
                                                                    name="input-3-ltr-star-md"
                                                                    class="kv-ltr-theme-svg-star rating-loading d-none"
                                                                    value="{{ $details['rating'] }}" dir="ltr"
                                                                    data-size="xs" data-show-clear="false"
                                                                    data-show-caption="false" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="button-action mt-3">
                                                            <div class="addtocart-btn">
                                                                <form class="addtocart" action="#" method="post">
                                                                    @if ($details['type'] == 'variable_product')
                                                                        <a href="#quickview-modal"
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#quickview_modal"
                                                                            class="btn btn-md text-nowrap add_cart  quickview quick-view-modal"
                                                                            data-product-id="{{ $details['id'] }}"
                                                                            data-product-variant-id=''>{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</a>
                                                                    @else
                                                                        <a class="btn btn-md text-nowrap add_cart"
                                                                            data-product-id="{{ $details['id'] }}"
                                                                            data-product-variant-id="{{ $details['variants'][0]->id }}"
                                                                            data-name='{{ $pro_name }}'
                                                                            data-slug='{{ $details['slug'] }}'
                                                                            data-brand-name='{{ $details['brand'] }}'
                                                                            data-image='{{ $details['image'] }}'
                                                                            data-product-type='regular'
                                                                            data-max='{{ $details['total_allowed_quantity'] }}'
                                                                            data-step='{{ $details['quantity_step_size'] }}'
                                                                            data-min='{{ $details['minimum_order_quantity'] }}'
                                                                            data-store-id='{{ $details['store_id'] }}'
                                                                            data-stock-type='{{ $details['stock_type'] }}'>{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</a>
                                                                    @endif
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="d-flex flex-column justify-content-center align-items-center py-5">
                                    <div class="opacity-50"><ion-icon name="bookmark-outline"
                                            class="address-location-icon text-muted"></ion-icon></div>
                                    <div class="fs-6 fw-500">
                                        {{ labels('front_messages.wishlist_is_empty', 'Your Wishlist Appears to be Empty.') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                        <h3 class="tabs-ac-style d-md-none" rel="combo-wishlist">
                            {{ labels('front_messages.combo_products', 'Combo Products') }}</h3>
                        <div id="combo-wishlist" class="tab-content" wire:ignore.self>
                            @if (count($combo_wishlist) >= 1)
                                <div class="grid-products grid-view-items">
                                    <div class="col-row row row-cols-2 row-cols-lg-4 row-cols-md-3 row-cols-xl-5">
                                        @foreach ($combo_wishlist as $details)
                                            @php
                                                $pro_image = dynamic_image($details['image'], 800);
                                                $combo_pro_name = getDynamicTranslation('combo_products','title',$details['id'],$language_code)
                                            @endphp
                                            <div class="item col-item">
                                                <div class="product-box position-relative">
                                                    <button type="button"
                                                        class="btn remove-icon close-btn remove-favorite"
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title=""
                                                        data-bs-original-title="Remove" data-product-type="combo" aria-label="Remove"
                                                        data-product-id="{{ $details['id'] }}"><ion-icon name="close-outline"></ion-icon></button>
                                                    <div class="product-image">
                                                        <a wire:navigate
                                                            href="{{ customUrl('combo-products/' . $details['slug']) }}"
                                                            class="all-product-img product-img rounded-3 slider-link"
                                                            data-link="{{ customUrl('products/' . $details['slug']) }}">
                                                            <img class="blur-up lazyload" src="{{ $pro_image }}"
                                                                alt="Product" title="{{ $combo_pro_name }}"
                                                                width="625" height="808" />
                                                        </a>
                                                    </div>
                                                    <div class="product-details text-center">
                                                        <div class="product-name text-ellipsis">
                                                            <a wire:navigate
                                                                href="{{ customUrl('combo-products/' . $details['slug']) }}">{{ $combo_pro_name }}</a>
                                                        </div>
                                                        <div class="product-price">
                                                            @php
                                                                $price = currentCurrencyPrice($details['price'], true);
                                                                $special_price =
                                                                    isset($details['special_price']) &&
                                                                    $details['special_price'] > 0
                                                                        ? currentCurrencyPrice(
                                                                            $details['special_price'],
                                                                            true,
                                                                        )
                                                                        : $price;
                                                            @endphp

                                                            <span
                                                                class="price old-price">{{ $special_price !== $price ? $price : '' }}</span>
                                                            <span class="price fw-500"><span
                                                                    wire:model="price">{{ $special_price }}</span>
                                                        </div>
                                                        <div class="product-review">
                                                            <div class="product-review">
                                                                <input id="input-3-ltr-star-md"
                                                                    name="input-3-ltr-star-md"
                                                                    class="kv-ltr-theme-svg-star rating-loading d-none"
                                                                    value="{{ $details['rating'] }}" dir="ltr"
                                                                    data-size="xs" data-show-clear="false"
                                                                    data-show-caption="false" readonly>
                                                            </div>
                                                        </div>
                                                        <div class="button-action mt-3">
                                                            <div class="addtocart-btn">

                                                                <a class="btn btn-md text-nowrap add_cart"
                                                                    data-product-id="{{ $details['id'] }}"
                                                                    data-product-variant-id="{{ $details['id'] }}"
                                                                    data-name='{{ $combo_pro_name }}'
                                                                    data-slug='{{ $details['slug'] }}'
                                                                    data-brand-name=''
                                                                    data-image='{{ $details['image'] }}'
                                                                    data-product-type='combo'
                                                                    data-max='{{ $details['total_allowed_quantity'] }}'
                                                                    data-step='{{ $details['quantity_step_size'] }}'
                                                                    data-min='{{ $details['minimum_order_quantity'] }}'
                                                                    data-stock-type=''>{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</a>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @else
                                <div class="d-flex flex-column justify-content-center align-items-center py-5">
                                    <div class="opacity-50"><ion-icon name="bookmark-outline"
                                            class="address-location-icon text-muted"></ion-icon></div>
                                    <div class="fs-6 fw-500">
                                        {{ labels('front_messages.wishlist_is_empty', 'Your Wishlist Appears to be Empty.') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div>{!! $links !!}</div>
</div>
