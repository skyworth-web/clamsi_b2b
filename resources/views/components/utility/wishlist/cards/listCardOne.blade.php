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

                <div class="table-bottom-brd table-responsive">
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
                                    <table class="table align-middle text-center order-table">
                                        <thead>
                                            <tr class="table-head text-nowrap">
                                                <th scope="col"></th>
                                                <th scope="col">
                                                    {{ labels('front_messages.image', 'Image') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.product_details', 'Product Details') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.price', 'Price') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.rating', 'Rating') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.action', 'Action') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($regular_wishlist as $details)
                                                @php
                                                    $pro_image = dynamic_image($details['image'], 50);
                                                    $pro_name = getDynamicTranslation('products','name',$details['id'],$language_code)
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <button class="btn-close remove-favorite"
                                                            data-product-id="{{ $details['id'] }}"></button>
                                                    </td>
                                                    <td>
                                                        <img class="blur-up lazyload" data-src="{{ $pro_image }}"
                                                            src="{{ $pro_image }}" alt="{{ $pro_name }}"
                                                            title="{{ $pro_name }}" />
                                                    </td>
                                                    <td><span class="name">{{ $pro_name }}</span>
                                                    </td>
                                                    <td>
                                                        @php
                                                            if ($details['type'] !== 'variable_product') {
                                                                $price = currentCurrencyPrice(
                                                                    $details['variants'][0]->price,
                                                                    true,
                                                                );
                                                                $special_price =
                                                                    isset($details['variants'][0]->special_price) &&
                                                                    $details['variants'][0]->special_price > 0
                                                                        ? currentCurrencyPrice(
                                                                            $details['variants'][0]->special_price,
                                                                            true,
                                                                        )
                                                                        : $price;
                                                            } else {
                                                                $max_price = currentCurrencyPrice(
                                                                    $details['min_max_price']['max_price'],
                                                                    true,
                                                                );
                                                                $special_min_price =
                                                                    isset(
                                                                        $details['min_max_price']['special_min_price'],
                                                                    ) &&
                                                                    $details['min_max_price']['special_min_price'] > 0
                                                                        ? currentCurrencyPrice(
                                                                            $details['min_max_price'][
                                                                                'special_min_price'
                                                                            ],
                                                                            true,
                                                                        )
                                                                        : $max_price;
                                                            }
                                                        @endphp

                                                        <span class="price fw-500">
                                                            @if ($details['type'] !== 'variable_product')
                                                                {{ $special_price }}
                                                            @else
                                                                {{ $max_price }} -
                                                                {{ $special_min_price }}
                                                            @endif
                                                        </span>
                                                    </td>

                                                    {{-- @dd($details) --}}
                                                    <td><span class="id">★{{ $details['rating'] }}</span>
                                                    </td>
                                                    <td>
                                                        @if ($details['type'] == 'variable_product')
                                                            <a href="#quickview-modal" data-bs-toggle="modal"
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
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
                                    <table class="table align-middle text-center order-table">
                                        <thead>
                                            <tr class="table-head text-nowrap">
                                                <th scope="col"></th>
                                                <th scope="col">
                                                    {{ labels('front_messages.image', 'Image') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.product_details', 'Product Details') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.price', 'Price') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.rating', 'Rating') }}
                                                </th>
                                                <th scope="col">
                                                    {{ labels('front_messages.action', 'Action') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($combo_wishlist as $details)
                                                @php
                                                    $pro_image = dynamic_image($details['image'], 50);
                                                    $combo_pro_name = getDynamicTranslation('combo_products','title',$details['id'],$language_code)
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <button class="btn-close remove-favorite"
                                                            data-product-id="{{ $details['id'] }}"
                                                            data-product-type="combo"></button>
                                                    </td>
                                                    <td>
                                                        <img class="blur-up lazyload" data-src="{{ $pro_image }}"
                                                            src="{{ $pro_image }}" alt="{{ $combo_pro_name }}"
                                                            title="{{ $combo_pro_name }}" />
                                                    </td>

                                                    <td><span class="name">{{ $combo_pro_name }}</span>
                                                    </td>
                                                    <td>
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

                                                        <span class="price fw-500">{{ $special_price }}</span>
                                                    </td>

                                                    <td><span class="id">★{{ $details['rating'] }}</span>
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-md text-nowrap add_cart"
                                                            data-product-id="{{ $details['id'] }}"
                                                            data-product-variant-id="{{ $details['id'] }}"
                                                            data-name='{{ $combo_pro_name }}'
                                                            data-slug='{{ $details['slug'] }}' data-brand-name=''
                                                            data-image='{{ $details['image'] }}'
                                                            data-product-type='combo'
                                                            data-max='{{ $details['total_allowed_quantity'] }}'
                                                            data-step='{{ $details['quantity_step_size'] }}'
                                                            data-min='{{ $details['minimum_order_quantity'] }}'
                                                            data-stock-type=''>{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
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
    </div>
    <div>{!! $links !!}</div>
</div>
