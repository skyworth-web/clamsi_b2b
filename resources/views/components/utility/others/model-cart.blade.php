<div id="minicart-drawer" wire:ignore.self class="minicart-right-drawer offcanvas offcanvas-end" tabindex="-1">
    @if (count($cart_data) < 1)
        <!--MiniCart Empty-->
        <div id="cartEmpty" class="cartEmpty d-flex-justify-center flex-column text-center p-3 text-muted">
            <div class="minicart-header d-flex-center justify-content-between w-100">
                <h4 class="fs-6">{{ labels('front_messages.your_cart', 'Your cart') }} (<span
                        class="cart_count">0</span> {{ labels('front_messages.items', 'Items') }})</h4>
                <button class="close-cart border-0" data-bs-dismiss="offcanvas" aria-label="Close"><ion-icon
                        name="close-outline" class="icon" data-bs-toggle="tooltip" data-bs-placement="left"
                        title="Close"></ion-icon></button>
            </div>
            <div id="display_cart">
                <div class="cartEmpty-content mt-4">
                    <ion-icon name="cart-outline" class="icon text-muted fs-1"></ion-icon>
                    <p class="my-3">{{ labels('front_messages.no_products_in_cart', 'No Products in the Cart') }}</p>
                    <a href="{{ customUrl('products') }}"
                        class="btn btn-primary cart-btn">{{ labels('front_messages.continue_shopping', 'Continue shopping') }}</a>
                </div>
            </div>
        </div>
        <!--End MiniCart Empty-->
    @else
        <!--MiniCart Content-->
        <div id="cart-drawer" class="block block-cart">
            <div class="minicart-header">
                <button class="close-cart border-0" data-bs-dismiss="offcanvas" aria-label="Close">
                    <ion-icon class="icon" data-bs-toggle="tooltip" data-bs-placement="left"
                        name="close-outline"></ion-icon></button>
                <h4 class="fs-6">{{ labels('front_messages.your_cart', 'Your cart') }}
                    ({{ count($cart_data['cart_items']) }} {{ labels('front_messages.items', 'Items') }})</h4>
            </div>
            <div class="minicart-content">
                <ul class="m-0 clearfix">
                    @foreach ($cart_data['cart_items'] as $items)
                        @php
                            $pro_image = dynamic_image($items['image'], 70);
                            $language_code= get_language_code();
                            $product_name = '';

                            if ($items['cart_product_type'] == 'combo') {
                                $product_name = getDynamicTranslation(
                                    'combo_products',
                                    'title',
                                    $items['product_id'],
                                    $language_code,
                                );
                            } else {
                                $product_name = getDynamicTranslation(
                                    'products',
                                    'name',
                                    $items['product_id'],
                                    $language_code,
                                );
                            }

                        @endphp
                        <li class="item d-flex justify-content-center align-items-center">
                            <a class="product-image rounded-3" wire:navigate
                                href="{{ customUrl(($items['cart_product_type'] == 'combo' ? 'combo-' : '') . 'products/' . $items['slug']) }}">
                                <img class="blur-up lazyload" data-src="{{ $pro_image }}" src="{{ $pro_image }}"
                                    alt="{{ $product_name }}" title="{{ $product_name }}" />
                            </a>
                            <div class="product-details">
                                <a class="product-title" wire:navigate
                                    href="{{ customUrl('products/' . $items['slug']) }}">{{ $product_name }}</a>
                                @if ($items['cart_product_type'] != 'combo')
                                    <div class="variant-cart my-2">
                                        {{ $items['product_variants'][0]['variant_values'] }}
                                    </div>
                                @endif
                                <div class="priceRow">
                                    <div class="product-price">
                                        @php
                                            $price = currentCurrencyPrice($items['price'], true);
                                            $special_price =
                                                isset($items['special_price']) && $items['special_price'] > 0
                                                    ? currentCurrencyPrice($items['special_price'], true)
                                                    : $price;
                                        @endphp
                                        @if ($special_price !== $price)
                                            <span class="price old-price">{{ $price }}</span>
                                        @endif
                                        <span class="price">{{ $special_price }}</span>
                                    </div>
                                </div>

                            </div>
                            <div class="qtyDetail text-end cart-qtyDetail">
                                <div class="qtyField">
                                    <button wire:ignore class="qtyBtn minus" href="#;"><ion-icon
                                            name="remove-outline"></ion-icon></button>
                                    <input type="number" name="quantity" value="{{ $items['qty'] }}" class="qty"
                                        max='{{ $items['total_allowed_quantity'] == 0 ? 'Infinity' : $items['total_allowed_quantity'] }}'
                                        step='{{ $items['quantity_step_size'] }}'
                                        min='{{ $items['minimum_order_quantity'] }}'
                                        data-variant-id='{{ $items['id'] }}'>
                                    <button wire:ignore class="qtyBtn plus" href="#;"><ion-icon
                                            name="add-outline"></ion-icon></button>
                                </div>
                                <a wire:click="remove_from_cart({{ $items['id'] }})"
                                    class="remove_from_cart remove pointer" data-variant-id="{{ $items['id'] }}">
                                    <ion-icon wire:ignore class="icon" data-bs-toggle="tooltip"
                                        data-bs-placement="top" name="close-outline"></ion-icon></a>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="minicart-bottom">
                <div class="subtotal clearfix my-3">
                    <div class="totalInfo clearfix"><span>{{ labels('front_messages.total', 'Total') }}:</span><span
                            class="item product-price">{{ currentCurrencyPrice($cart_data['sub_total'], true) }}</span>
                    </div>

                </div>
                <div class="agree-check customCheckbox">
                    <input id="prTearm" name="tearm" type="checkbox" value="tearm" required />
                    <label for="prTearm">{{ labels('front_messages.i_agree_with_the', 'I agree with the') }}
                    </label><a wire:navigate href="{{ url('term-and-conditions') }}"
                        class="ms-1 text-link">{{ labels(
                            'front_messages.terms_and_conditions',
                            'Terms & conditions',
                        ) }}</a>
                </div>
                <div class="minicart-action d-flex mt-3">
                    <a href="/cart/checkout" wire:navigate
                        class="cart-checkout proceed-to-checkout btn btn-primary w-50 me-1 disabled">{{ labels('front_messages.check_out', 'Check Out') }}</a>
                    <a href="/cart" wire:navigate
                        class="cart-checkout cart-btn btn btn-secondary w-50 ms-1 disabled">{{ labels('front_messages.view_cart', 'View Cart') }}</a>
                </div>
            </div>
        </div>
    @endif

    <!--MiniCart Content-->
</div>
