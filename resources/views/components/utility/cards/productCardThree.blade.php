@props(['details'])
@php
    $language_code = get_language_code();
@endphp
<div class="item col-item">
    @if ($details->type != 'combo-product')
        <div class="product-box">
            <div class="product-image">
                <a wire:navigate href="{{ customUrl('products/' . $details->slug) }}"
                    class="img-box-h300px product_card_style_two_image product-img rounded-3 slider-link"
                    data-link="{{ customUrl('products/' . $details->slug) }}">
                    <img class="blur-up lazyload" src="{{ dynamic_image($details->image, 400) }}" alt="Product"
                        title="{{ $details->name }}" width="625" height="759" />
                </a>
                @if ($details->best_seller || $details->new_arrival)

                    @if ($details->best_seller)
                        <div class="product-labels"><span class="lbl pr-label2 rounded-pill">{{ labels('front_messages.best_seller', 'Best Seller') }}</span></div>
                    @endif
                    @if ($details->new_arrival)
                        <div class="product-labels"><span class="lbl pr-label2 rounded-pill">{{ labels('front_messages.new_arrivals', 'New Arrival') }}</span></div>
                    @endif
                @elseif ($details->min_max_price['discount_in_percentage'] != 0 && $details->min_max_price['discount_in_percentage'] != 100)
                    <div class="product-labels">
                        <span
                            class="lbl pr-label2 rounded-pill">{{ round($details->min_max_price['discount_in_percentage']) }}%</span>
                    </div>
                @endif

            </div>
            <div class="product-details text-left">
                <a wire:navigate href="{{ customUrl('products/?brand=' . $details->brand_slug) }}"
                    class="slider-link product-vendor text-uppercase"
                    data-link="{{ customUrl('products/?brand=' . $details->brand_slug) }}">{!! getDynamicTranslation('brands', 'name', $details->brand, $language_code) !!}</a>
                <div class="product-name-price">
                    <div class="product-name text-capitalize">
                        <a wire:navigate href="{{ customUrl('products/' . $details->slug) }}"
                            class="slider-link text-ellipsis" data-link="{{ customUrl('products/' . $details->slug) }}"
                            title="{!! $details->name !!}">{!! $details->name !!}</a>
                    </div>
                </div>
                <div class="product-price">
                    @php
                        if ($details->type == 'variable_product') {
                            $price = currentCurrencyPrice($details->min_max_price['max_price'], true);
                            $special_price =
                                $details->min_max_price['special_min_price'] &&
                                $details->min_max_price['special_min_price'] > 0
                                    ? currentCurrencyPrice($details->min_max_price['special_min_price'], true)
                                    : $price;
                        } else {
                            $price = currentCurrencyPrice($details->variants[0]->price, true);
                            $special_price =
                                $details->variants[0]->special_price && $details->variants[0]->special_price > 0
                                    ? currentCurrencyPrice($details->variants[0]->special_price, true)
                                    : $price;
                        }
                    @endphp
                    <span class="price old-price">{{ $special_price !== $price ? $price : '' }}</span>
                    <span class="price fw-500"><span wire:model="price">{{ $special_price }}</span></span>
                </div>
                <div class="product-review">
                    <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                        class="kv-ltr-theme-svg-star rating-loading d-none" value="{{ $details->rating }}"
                        dir="ltr" data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
                </div>
                <div class="button-bottom-action style1">
                    <div class="button-left">
                        @if ($details->type == 'variable_product')
                            <div class="addtocart-btn add_cart">
                                <a href="#quickview-modal"
                                    class="button-style btn btn-md quickview quick-view-modal p-2"
                                    data-bs-toggle="modal" data-bs-target="#quickview_modal"
                                    data-product-id="{{ $details->id }}" data-product-variant-id='' tabindex="0">
                                    <i class="anm anm-bag-l hdr-icon me-2"></i><span
                                        class="text">{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</span>
                                </a>
                            </div>
                        @else
                            <div class="addtocart-btn add_cart" id="add_cart"
                                data-product-variant-id="{{ $details->variants[0]->id }}"
                                data-name='{{ $details->name }}' data-slug='{{ $details->slug }}'
                                data-brand-name='{!! getDynamicTranslation('brands', 'name', $details->brand, $language_code) !!}'
                                data-image='{{ dynamic_image($details->image, 220) }}' data-product-type='regular'
                                data-max='{{ $details->total_allowed_quantity }}'
                                data-step='{{ $details->quantity_step_size }}'
                                data-min='{{ $details->minimum_order_quantity }}'
                                data-stock-type='{{ $details->stock_type }}' data-store-id='{{ $details->store_id }}'
                                data-variant-price="{{ currentCurrencyPrice($details->variants[0]->special_price) }}">
                                <a href="#quickview-modal"
                                    class="button-style btn btn-md quickview quick-view-modal p-2"
                                    data-bs-toggle="modal" data-bs-target="#quickview_modal"
                                    data-product-id="{{ $details->id }}" tabindex="0">
                                    <i class="anm anm-bag-l hdr-icon me-2"></i><span
                                        class="text">{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</span>
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="button-right">
                        <a href="#quickview-modal" class="btn-icon quickview quick-view-modal" data-bs-toggle="modal"
                            data-bs-target="#quickview_modal" data-product-id="{{ $details->id }}"
                            data-bs-placement="top" title="Quick View">
                            <i class="hdr-icon icon anm anm-search-l"></i>
                        </a>
                        <a class="btn-icon wishlist card_fav_btn {{ $details->is_favorite == 1 ? 'remove-favorite' : 'add-favorite' }}"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            title="{{ $details->is_favorite == 1 ? 'Remove From Wishlist' : 'Add To Wishlist' }}"
                            data-product-id="{{ $details->id }}" data-product-type="regular">
                            <i
                                class="hdr-icon anm {{ $details->is_favorite == 1 ? 'anm-heart text-danger' : 'anm-heart-l' }}"></i>
                        </a>
                        <a class="btn-icon compare add-compare" data-product-id="{{ $details->id }}"
                            data-bs-placement="top" title="Add to Compare"><i class="icon anm anm-random-r"></i></a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="product-box">
            <div class="product-image">
                <a class="product-img" tabindex="0">
                    <a wire:navigate href="{{ customUrl('combo-products/' . $details->slug) }}"
                        class="img-box-h300px product_card_style_two_image product-img slider-link"
                        data-link="{{ customUrl('combo-products/' . $details->slug) }}">
                        @php
                            $main_img = dynamic_image($details->image, 450);
                            $other_images = dynamic_image($details->other_images[0] ?? $details->image, 450);
                        @endphp
                        @if (!empty($other_images))
                            <img class="primary blur-up lazyloaded" data-src="{{ $main_img }}"
                                src="{{ $main_img }}" alt="Product" title="{{ $details->name }}"
                                width="625" height="759" />
                            <img class="hover product_card_style_two_image blur-up lazyload"
                                data-src="{{ $other_images ?? $main_img }}" src="{{ $other_images ?? $main_img }}"
                                alt="Product" title="{{ $details->name }}" width="625" height="759" />
                        @endif
                    </a>
                    @if ($details->best_seller || $details->new_arrival)

                        @if ($details->best_seller)
                            <div class="product-labels"><span class="lbl pr-label2 rounded-pill">{{ labels('front_messages.best_seller', 'Best Seller') }}</span>
                            </div>
                        @endif
                        @if ($details->new_arrival)
                            <div class="product-labels"><span class="lbl pr-label2 rounded-pill">{{ labels('front_messages.new_arrivals', 'New Arrival') }}</span>
                            </div>
                        @endif
                    @elseif ($details->cal_discount_percentage != 0)
                        <div class="product-labels">
                            <span
                                class="lbl pr-label2 rounded-pill">{{ round($details->cal_discount_percentage) }}%</span>
                        </div>
                    @endif
                </a>
            </div>
            <div class="product-details text-left">
                <div class="product-name-price">
                    <div class="product-name">
                        <a tabindex="0">
                            <a wire:navigate href="{{ customUrl('combo-products/' . $details->slug) }}"
                                class="slider-link text-ellipsis"
                                data-link="{{ customUrl('combo-products/' . $details->slug) }}"
                                title="{!! $details->name !!}">{!! $details->name !!}</a>
                    </div>
                </div>
                <div class="product-price m-0">
                    @php
                        $price = currentCurrencyPrice($details->price, true);
                        $special_price = currentCurrencyPrice($details->special_price, true);
                    @endphp

                    @if (!empty($special_price) && $details->special_price != 0)
                        <span
                            class="price old-price text-muted text-decoration-line-through">{{ $price }}</span>
                        <span class="price fw-500">{{ $special_price }}</span>
                    @else
                        <span class="price fw-500">{{ $price }}</span>
                    @endif

                </div>
                <div class="product-review">
                    <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                        class="kv-ltr-theme-svg-star rating-loading d-none" value="{{ $details->rating }}"
                        dir="ltr" data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
                </div>
                <div class="button-bottom-action style1 product_card_style_3">
                    <div class="button-left">
                        <div class="addtocart-btn add_cart" id="add_cart"
                            data-product-variant-id="{{ $details->id }}" data-name='{{ $details->name }}'
                            data-slug='{{ $details->slug }}' data-brand-name='' data-image='{{ $main_img }}'
                            data-product-type='combo' data-max='{{ $details->total_allowed_quantity }}'
                            data-step='{{ $details->quantity_step_size }}'
                            data-min='{{ $details->minimum_order_quantity }}'
                            data-stock-type='{{ $details->stock_type }}' data-store-id='{{ $details->store_id }}'
                            data-variant-price="{{ currentCurrencyPrice($details->special_price) }}">
                            <a class="btn btn-md p-2 button-style" data-product-id="{{ $details->id }}">
                                <i class="anm anm-bag-l hdr-icon me-2"></i><span
                                    class="text">{{ labels('front_messages.add_to_cart', 'Add to Cart') }}</span>
                            </a>
                        </div>
                    </div>
                    <div class="button-right">
                        <a href="#quickview-modal" class="btn-icon quickview quick-view-modal" data-bs-toggle="modal"
                            data-product-id="{{ $details->id }}" data-product-type="{{ $details->type }}"
                            data-bs-target="#quickview_modal" tabindex="0" data-bs-placement="top"
                            title="Quick View">
                            <i class="hdr-icon icon anm anm-search-l"></i>
                        </a>
                        <a class="btn-icon wishlist card_fav_btn {{ $details->is_favorite == 1 ? 'remove-favorite' : 'add-favorite' }}"
                            data-bs-toggle="tooltip" data-bs-placement="left"
                            title="{{ $details->is_favorite == 1 ? 'Remove From Wishlist' : 'Add To Wishlist' }}"
                            data-product-id="{{ $details->id }}" data-product-type="combo">
                            <i
                                class="hdr-icon anm {{ $details->is_favorite == 1 ? 'anm-heart text-danger' : 'anm-heart-l' }}"></i>
                        </a>
                        <a class="btn-icon compare add-compare" data-product-id="{{ $details->id }}"
                            data-product-type="combo" data-bs-placement="top" title="Add to Compare" tabindex="0"
                            data-bs-original-title="Add to Compare" aria-label="Add to Compare"><i
                                class="icon anm anm-random-r"></i></a>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
