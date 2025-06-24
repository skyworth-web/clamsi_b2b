<div id="page-content">
    <!--Page Header-->
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <!--End Page Header-->
    {{-- @dd($cart_data) --}}
    <div class="container-fluid">
        @if (count($cart_data) >= 1 || count($save_for_later) >= 1)
            @if (count($cart_data) >= 1)
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-8 main-col cart-box">

                        <!--Cart Form-->
                        <table class="table align-middle">
                            <thead class="cart-row cart-header small-hide">
                                <tr>
                                    <th class="action">&nbsp;</th>
                                    <th colspan="2" class="text-start">
                                        {{ labels('front_messages.product', 'Product') }}</th>
                                    <th class="text-center"></th>
                                    <th class="text-center">{{ labels('front_messages.price', 'Price') }}</th>
                                    <th class="text-center">{{ labels('front_messages.quantity', 'Quantity') }}</th>
                                    <th class="text-center">{{ labels('front_messages.total', 'Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $is_save_later_hide = 0;
                                    $is_remove_from_cart = 0;
                                    $for_checkout = 0;
                                @endphp
                                @foreach ($cart_data['cart_items'] as $cartItem)
                                    <x-utility.cart.CardOne :$cartItem :$is_save_later_hide :$is_remove_from_cart
                                        :$for_checkout />
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!--Cart Sidebar-->
                    <div class="col-12 col-sm-12 col-md-12 col-lg-4 cart-footer">
                        <div class="cart-info sidebar-sticky">
                            <div class="cart-order-detail cart-col">
                                <div class="row g-0 pt-2 border-bottom">
                                    <span
                                        class="col-6 col-sm-6 cart-subtotal-title fs-6"><strong>{{ labels('front_messages.total', 'Total') }}</strong></span>
                                    <span
                                        class="col-6 col-sm-6 cart-subtotal-title fs-5 cart-subtotal text-end text-primary"><b
                                            class="money">{{ currentCurrencyPrice($cart_data['sub_total'], true) }}</b></span>
                                </div>

                                <p class="cart-shipping">Inclusive of all taxes & Shipping calculated at checkout</p>
                                <a href="{{ customUrl('cart/checkout') }}" wire:navigate id="cartCheckout"
                                    class="btn btn-lg my-4 checkout w-100">{{ labels('front_messages.proceed_to_checkout', 'Proceed To Checkout') }}</a>
                            </div>
                        </div>
                    </div>
                    <!--End Cart Sidebar-->
                </div>
            @endif

            @if (count($save_for_later) >= 1)
                {{-- @dd($language_code); --}}
                <section class="section product-slider pb-0">
                    <x-utility.section_header.sectionHeaderTwo :heading="$save_for_later['heading']" />
                    <!--Product Grid-->
                    <div class="grid-products grid-view-items">
                        <div class="row col-row row-cols-xl-5 row-cols-lg-4 row-cols-md-3 row-cols-2">
                            @foreach ($save_for_later['cart_items'] as $item)
                                @php
                                    // dD($save_for_later['cart_items']);
                                    $product_img = dynamic_image($item['image'], 400);
                                    $product_name = '';

                                    if ($item['cart_product_type'] == 'combo') {
                                        $product_name = getDynamicTranslation(
                                            'combo_products',
                                            'title',
                                            $item['product_id'],
                                            $language_code,
                                        );
                                    } else {
                                        $product_name = getDynamicTranslation(
                                            'products',
                                            'name',
                                            $item['product_id'],
                                            $language_code,
                                        );
                                    }

                                @endphp
                                <div class="item col-item">
                                    <div class="product-box position-relative">
                                        <button wire:ignore type="button"
                                            {{-- wire:click="remove_from_cart({{ $item['id'] }})" --}}
                                            wire:click="move_to_cart({{ $item['id'] }})"
                                            class="btn remove-icon close-btn" data-bs-toggle="tooltip"
                                            data-bs-placement="top" title="Remove"><i
                                                class="icon anm anm-times-r"></i></button>
                                        <div class="product-image">
                                            <a href="{{ customUrl('products/' . $item['slug']) }}"
                                                class="product-img rounded-0 save_later_product_img">
                                                <img class="primary rounded-0 blur-up lazyload"
                                                    data-src="{{ $product_img }}" src="{{ $product_img }}"
                                                    alt="{{ $product_name }}" title="{{ $product_name }}" />
                                                <img class="hover rounded-0 blur-up lazyload"
                                                    data-src="{{ $product_img }}" src="{{ $product_img }}"
                                                    alt="{{ $product_name }}" title="{{ $product_name }}" />
                                            </a>
                                        </div>
                                        <div class="product-details text-center">
                                            <div class="product-name">
                                                <a
                                                    href="{{ customUrl('products/' . $item['slug']) }}">{{ $product_name }}</a>
                                            </div>
                                            <div class="product-price">
                                                @if($item['special_price'] == 0 || $item['special_price'] == null)
                                                    <span class="price">{{ currentCurrencyPrice($item['price'], true) }}</span>
                                                @else
                                                    <span class="price old-price">{{ currentCurrencyPrice($item['price'], true) }}</span>
                                                    <span class="price">{{ currentCurrencyPrice($item['special_price'], true) }}</span>
                                                @endif
                                            </div>
                                            
                                            
                                            <div class="button-action mt-3">
                                                <div class="addtocart-btn">
                                                    <button wire:click="move_to_cart({{ $item['id'] }})"
                                                        class="btn btn-md">
                                                        <span
                                                            class="text">{{ labels('front_messages.move_to_cart', 'Move To Cart') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </section>
            @endif
            @php
                $store_settings = getStoreSettings();
            @endphp

            {{-- <x-dynamic-component :component="$component" :details="$details" /> --}}
            @if (!empty($related_product['product']))
                <!--Related Products-->
                <section class="section product-slider pb-0">
                    <div class="">
                        <x-utility.section_header.sectionHeaderTwo :heading="$related_product_heading" />
                        <!--Product Grid-->
                        <div wire:ignore
                            class="swiper style1-mySwiper gp15 arwOut5 hov-arrow grid-products {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                            <div class="swiper-wrapper">
                                @foreach ($related_product['product'] as $details)
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
                        <!--End Product Grid-->
                    </div>
                </section>
                <!--End Related Products-->
            @endif
        @else
            @php
                $title =
                    '<strong>' .
                    labels('front_messages.sorry', 'SORRY ') .
                    '</strong>' .
                    labels('front_messages.cart_is_currently_empty', 'Cart is currently empty');
            @endphp
            <x-utility.others.not-found :$title />
        @endif
    </div>
    <!--End Main Content-->

</div>