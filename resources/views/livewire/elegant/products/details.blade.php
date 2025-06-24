<div id="page-content" wire:ignore>
    {{-- <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb /> --}}
    <div class="template-product">
        <div class="page-header text-center">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <!--Breadcrumbs-->
                        <div class="breadcrumbs"><a wire:navigate href="{{ customUrl('/') }}"
                                title="Back to the home page">{{ labels('front_messages.home', 'Home') }}</a><span
                                class="main-title fw-bold"><ion-icon class="align-text-top icon"
                                    name="chevron-forward-outline"></ion-icon>{!! $bread_crumb['page_main_bread_crumb'] ?? '' !!}</span>
                            @if (isset($bread_crumb['right_breadcrumb']) && !empty($bread_crumb['right_breadcrumb']))
                                @foreach ($bread_crumb['right_breadcrumb'] as $right_breadcrumb)
                                    <span class="main-title fw-bold">
                                        <ion-icon class="align-text-top icon" name="chevron-forward-outline"></ion-icon>
                                        {!! request()->is('products/*') ? strip_tags($right_breadcrumb) : $right_breadcrumb !!}
                                    </span>
                                @endforeach

                            @endif
                        </div>
                        <!--End Breadcrumbs-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        {{-- @dd($product_details) --}}

        <div class="product-single">
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-12 col-12 product-layout-img mb-4 mb-md-0">
                    <div class="product-sticky-style">
                        <div class="product-details-img product-thumb-left-style d-flex justify-content-center">
                            <div class="product-thumb thumb-left">
                                <div id="gallery" class="product-thumb-vertical h-100">
                                    @php
                                        $main_image = dynamic_image($product_details->image, 600);
                                        $main_image_zoom = dynamic_image($product_details->image, 800);
                                    @endphp
                                    <a data-image="{{ $main_image }}" data-zoom-image="{{ $main_image_zoom }}"
                                        class="slick-slide slick-cloned active">
                                        <img class="blur-up lazyload rounded-0" data-src="{{ $main_image }}"
                                            src="{{ $main_image }}" alt="product" width="625" height="808" />
                                    </a>
                                    @foreach ($product_details->other_images as $images)
                                        @php
                                            $other_image = dynamic_image($images, 600);
                                            $other_image_zoom = dynamic_image($images, 800);
                                        @endphp
                                        <a data-image="{{ $other_image }}" data-zoom-image="{{ $other_image_zoom }}"
                                            class="slick-slide slick-cloned active">
                                            <img class="blur-up lazyload rounded-0" data-src="{{ $other_image }}"
                                                src="{{ $other_image }}" alt="product" width="625"
                                                height="808" />
                                        </a>
                                    @endforeach
                                    @foreach ($product_details->variants as $variants)
                                        @foreach ($variants->images as $images)
                                            @php
                                                $other_image = dynamic_image($images, 600);
                                                $other_image_zoom = dynamic_image($images, 800);
                                            @endphp
                                            <a data-image="{{ $other_image }}"
                                                data-zoom-image="{{ $other_image_zoom }}"
                                                class="slick-slide slick-cloned active">
                                                <img class="blur-up lazyload rounded-0" data-src="{{ $other_image }}"
                                                    src="{{ $other_image }}" alt="product" width="625"
                                                    height="808" />
                                            </a>
                                        @endforeach
                                    @endforeach
                                </div>
                            </div>
                            <div class="zoompro-wrap product-zoom-right rounded-0">
                                <div class="zoompro-span"><img id="zoompro" class="zoompro rounded-0"
                                        src="{{ $main_image }}" data-zoom-image="{{ $main_image_zoom }}"
                                        alt="product" /></div>
                            </div>
                        </div>
                        {{-- @dd($system_settings) --}}
                        <div class="social-sharing d-flex-center justify-content-center mt-3 mt-md-4 lh-lg">
                            <span class="sharing-lbl fw-600">{{ labels('front_messages.share', 'Share') }} :</span>
                            <div class="shareon">
                                <a class="facebook"
                                    data-text="Take a Look at this {{ $product_details->name }} on {{ $system_settings['app_name'] }}"></a>
                                <a class="telegram"
                                    data-text="Take a Look at this {{ $product_details->name }} on {{ $system_settings['app_name'] }}"></a>
                                <a class="twitter"
                                    data-text="Take a Look at this {{ $product_details->name }} on {{ $system_settings['app_name'] }}"></a>
                                <a class="whatsapp"
                                    data-text="Take a Look at this {{ $product_details->name }} on {{ $system_settings['app_name'] }}"></a>
                                <a class="email"
                                    data-text="Take a Look at this {{ $product_details->name }} on {{ $system_settings['app_name'] }}"></a>
                                <a class="copy-url"></a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-6 col-sm-12 col-12 product-layout-info">
                    <div class="product-single-meta">
                        <h2 class="product-main-title mb-2 text-capitalize">{{ $product_details->name }}</h2>
                        <div class="product-review d-flex-center mb-2">
                            <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                class="kv-ltr-theme-svg-star rating-loading d-none"
                                value="{{ $product_details->rating }}" dir="ltr" data-size="xs"
                                data-show-clear="false" data-show-caption="false" readonly>
                        </div>
                        <div class="product-price d-flex-center">
                            @if ($product_details->type != 'variable_product')
                                @php
                                    $price = currentCurrencyPrice($product_details->variants[0]->price, true);
                                    $special_price =
                                        $product_details->variants[0]->special_price &&
                                        $product_details->variants[0]->special_price > 0
                                            ? currentCurrencyPrice($product_details->variants[0]->special_price, true)
                                            : $price;
                                @endphp
                                <span class="price old-price">{{ $special_price !== $price ? $price : '' }}</span>
                                <span class="price product_price" id="price">{{ $special_price }}</span>
                            @else
                                @php
                                    $max_price = currentCurrencyPrice(
                                        $product_details->min_max_price['max_price'],
                                        true,
                                    );
                                    $special_min_price =
                                        $product_details->min_max_price['special_min_price'] &&
                                        $product_details->min_max_price['special_min_price'] > 0
                                            ? currentCurrencyPrice(
                                                $product_details->min_max_price['special_min_price'],
                                                true,
                                            )
                                            : $max_price;
                                @endphp
                                <span class="price product_price" id="price">{{ $max_price }} -
                                    {{ $special_min_price }}</span>
                            @endif
                        </div>

                        {{-- @dd($product_details); --}}
                        <div class="mb-10px text-muted">{{ $product_details->short_description }}</div>
                        <hr class="light-hr" />
                        @if (!empty($product_details->made_in))
                            <p class="product-sku pb-1 mb-10px">{{ labels('front_messages.made_in', 'Made In') }}:<span
                                    class="text fw-500">{{ $product_details->made_in }}</span></p>
                        @endif
                        @php
                            $category = fetchDetails('categories', ['id' => $product_details->category_id], 'slug');
                        @endphp
                        <a href="{{ customUrl('categories/' . $category[0]->slug . '/products') }}"
                            class="product-type fs-6 mb-10px" title="{!! getDynamicTranslation('categories', 'name', $product_details->category_id, $language_code) !!}"><ion-icon
                                name="layers-outline" class="custom-icon fs-6 me-1"></ion-icon>{!! getDynamicTranslation('categories', 'name', $product_details->category_id, $language_code) !!}
                        </a>
                        @if (!empty($product_details->sku))
                            <p class="product-sku mb-10px">{{ labels('front_messages.sku', 'SKU') }}:<span
                                    class="text fw-500">{{ $product_details->sku }}</span></p>
                        @endif

                        @if (count($product_details->tags) >= 1)
                            <p class="text-uppercase text-black mb-10px"><ion-icon name="pricetags-outline"
                                    class="custom-icon fs-6 me-1"></ion-icon>
                                @foreach ($product_details->tags as $tag)
                                    <a href="{{ customUrl('products/?tag=' . $tag) }}"
                                        class="text fw-500 border border-2 px-1 tag-filter"
                                        title="{!! $tag !!}">{!! $tag !!}
                                    </a>
                                @endforeach
                            </p>
                        @endif
                        @if ($product_details->stock_type != '')
                            <div class="product-availability mb-10px position-static col-lg-9">
                                <div class="lh-1 d-flex justify-content-between">
                                    <div class="text-sold fw-600 text-success">
                                        {{ labels('front_messages.currently', 'Currently') }}
                                        , <strong class="text-link"></strong>
                                        {{ labels('front_messages.items_in_stock', 'Items are in stock!') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    <hr class="light-hr" />
                    <div class="product-swatches-option">
                        @foreach ($product_details->attributes as $attributes)
                            @php
                                $attribute_ids = explode(',', $attributes['ids']);
                                $attribute_values = explode(',', $attributes['value']);
                            @endphp
                            <div class="product-item swatches-size w-100 mb-2 swatch-1 option2" data-option-index="1">
                                <label class="label d-flex align-items-center">{{ $attributes['name'] }}</label>
                                <ul class="variants-size size-swatches d-flex-center pt-1 clearfix">

                                    {{-- only change price  --}}

                                    {{-- @foreach ($attribute_values as $key => $val)
                                        <li class="swatch x-large available p-1 toggleInput"
                                            onclick="toggleInput(this)">
                                            <input type="radio" class="swatchLbl attributes d-none"
                                                data-bs-toggle="tooltip" value="{{ $attribute_ids[$key] }}"
                                                data-bs-placement="top" title="{{ $val }}"
                                                id="variant-{{ $attribute_ids[$key] }}">{{ $val }}</span>
                                        </li>
                                    @endforeach --}}
                                    @php
                                        $variantsCollection = collect($product_details->variants);
                                        $uniqueAttributes = array_unique($attribute_values);
                                        // dd($uniqueAttributes);
                                    @endphp

                                    @foreach ($uniqueAttributes as $key => $val)
                                        @php
                                            // Find the correct variant based on attribute value
                                            $variant = $variantsCollection->firstWhere(
                                                'attribute_value_ids',
                                                (string) $attribute_ids[$key],
                                            );

                                            // Check if the variant image exists, otherwise use the main product image
                                            $variantImage =
                                                isset($variant->images_md[0]) && !empty($variant->images_md[0])
                                                    ? $variant->images_md[0]
                                                    : $product_details->image;

                                            $variantZoomImage =
                                                isset($variant->images_md[0]) && !empty($variant->images_md[0])
                                                    ? $variant->images_md[0]
                                                    : $product_details->image;
                                        @endphp

                                        <li class="swatch x-large available p-1 toggleInput"
                                            onclick="toggleInput(this)">
                                            <input type="radio" class="swatchLbl attributes d-none"
                                                data-bs-toggle="tooltip" value="{{ $attribute_ids[$key] }}"
                                                data-bs-placement="top" title="{{ $val }}"
                                                data-image="{{ $variantImage }}"
                                                data-zoom-image="{{ $variantZoomImage }}"
                                                id="variant-{{ $attribute_ids[$key] }}">
                                            {{ $val }}
                                        </li>
                                    @endforeach


                                </ul>
                            </div>
                        @endforeach
                        {{-- @dd($product_details->attributes); --}}
                    </div>
                    @foreach ($product_details->variants as $variant)
                        <input type="hidden" class="variants" name="variants_ids" data-image-index=""
                            data-name="" value="{{ $variant->attribute_value_ids }}"
                            data-id="{{ $variant->id }}" data-price="{{ currentCurrencyPrice($variant->price) }}"
                            data-special_price="{{ currentCurrencyPrice($variant->special_price) }}" />
                    @endforeach

                    <div class="product-action w-100 d-flex-wrap my-3 my-md-4">
                        <div class="product-form-quantity d-flex-center">
                            <div class="qtyField">
                                <button class="qtyBtn minus" href="#;"><ion-icon
                                        name="remove-outline"></ion-icon></button>
                                <input type="number" name="quantity" value="1"
                                    class="product-form-input qty dlt-qty"
                                    max='{{ $product_details->total_allowed_quantity == 0 ? 'Infinity' : $product_details->total_allowed_quantity }}'
                                    step='{{ $product_details->quantity_step_size }}'
                                    min='{{ $product_details->minimum_order_quantity }}' />
                                <button class="qtyBtn plus" href="#;"><ion-icon
                                        name="add-outline"></ion-icon></button>
                            </div>
                        </div>
                        @php
                            if (count($product_details->variants) <= 1) {
                                $variant_id = $product_details->variants[0]->id;
                                $variant_price = $product_details->variants[0]->special_price;
                            } else {
                                $variant_id = '';
                                $variant_price = '';
                            }
                        @endphp
                        {{-- {{ dd($product_details)}} --}}
                        <div class="product-form-submit addcart fl-1 ms-3">
                            <button type="submit" name="add"
                                class="btn btn-secondary product-form-cart-submit add_cart dlt-add-cart"
                                id="add_cart" data-product-variant-id="{{ $variant_id }}"
                                data-name='{{ $product_details->name }}' data-slug='{{ $product_details->slug }}'
                                data-brand-name='{{ getDynamicTranslation('brands', 'name', $product_details->brand, $language_code) }}'
                                data-image='{{ $product_details->image }}' data-product-type='regular'
                                data-max='{{ $product_details->total_allowed_quantity }}'
                                data-step='{{ $product_details->quantity_step_size }}'
                                data-min='{{ $product_details->minimum_order_quantity }}'
                                data-stock-type='{{ $product_details->stock_type }}'
                                data-store-id='{{ $product_details->store_id }}'
                                data-variant-price="{{ $variant_price }}">
                                <span>{{ labels('front_messages.add_to_cart', 'Add to cart') }}</span>
                            </button>
                        </div>
                        <div class="product-form-submit buyit fl-1 ms-3">
                            <button type="submit" class="btn btn-primary buy_now add_cart dlt-add-cart"
                                data-product-variant-id="{{ $variant_id }}"
                                data-name='{{ $product_details->name }}' data-slug='{{ $product_details->slug }}'
                                data-brand_name='{{ getDynamicTranslation('brands', 'name', $product_details->brand, $language_code) }}'
                                data-image='{{ $product_details->image }}' data-product-type='regular'
                                data-max='{{ $product_details->total_allowed_quantity }}'
                                data-step='{{ $product_details->quantity_step_size }}'
                                data-min='{{ $product_details->minimum_order_quantity }}'
                                data-store-id='{{ $product_details->store_id }}'
                                data-variant-price="{{ $variant_price }}">
                                <span>{{ labels('front_messages.buy_it_now', 'Buy it now') }}</span>
                            </button>
                        </div>
                    </div>
                    <p class="infolinks d-flex-center justify-content-start mb-2">
                        <a class="cursor-pointer text-link remove-favorite rem-fav-btn text-danger {{ $product_details->is_favorite == 0 ? 'd-none' : 'd-flex' }}"
                            data-product-id="{{ $product_details->id }}">
                            <i class="hdr-icon icon anm anm-heart fs-6 me-2"></i>
                            <span>{{ labels('front_messages.remove_from_wishlist', 'Remove from Wishlist') }}</span>
                        </a>

                        <a class="cursor-pointer text-link add-favorite {{ $product_details->is_favorite == 0 ? 'd-flex' : 'd-none' }}"
                            data-product-id="{{ $product_details->id }}">
                            <i class="hdr-icon icon anm anm-heart-l fs-6 me-2"></i>
                            <span>{{ labels('front_messages.add_to_wishlist', 'Add to Wishlist') }}</span>
                        </a>

                        <a class="text-link add-compare" data-product-id="{{ $product_details->id }}"
                            data-product-variant-id=""><i class="icon anm anm-random-r fs-6 me-2"></i></ion-icon>
                            <span>{{ labels('front_messages.add_to_compare', 'Add to Compare') }}</span></a>
                    </p>
                    <hr class="light-hr" />
                    <div class="product-info">
                        @if ($product_details->product_type == 'digital_product')
                            <div class="freeShipMsg featureText mb-2 d-flex"><ion-icon name="cube-outline"
                                    class="fs-5 me-2"></ion-icon>{{ labels('front_messages.digital_product', 'Digital Product') }}
                            </div>
                        @else
                            <div class="freeShipMsg featureText mb-2 d-flex"><ion-icon name="ribbon-outline"
                                    class="fs-5 me-2"></ion-icon>
                                @if (!empty($product_details->guarantee_period))
                                    <b class="freeShip me-1">{{ $product_details->guarantee_period }}</b>
                                @else
                                    {{ labels('front_messages.no_guarantee', 'No Guarantee') }}
                                @endif
                            </div>
                            <div class="freeShipMsg featureText mb-2 d-flex"><ion-icon name="shield-checkmark-outline"
                                    class="fs-5 me-2"></ion-icon>
                                @if (!empty($product_details->warranty_period))
                                    <b class="freeShip me-1">{{ $product_details->warranty_period }}</b>
                                @else
                                    {{ labels('front_messages.no_warranty', 'No Warranty') }}
                                @endif
                            </div>
                            <div class="freeShipMsg featureText mb-2 d-flex"><ion-icon name="refresh-outline"
                                    class="fs-5 me-2"></ion-icon>{{ $product_details->is_returnable == 1 ? 'Returnable' : 'Non Returnable' }}
                            </div>
                            <div class="freeShipMsg featureText mb-2 d-flex"><ion-icon name="pin-outline"
                                    class="fs-5 me-2"></ion-icon>
                                {{ $product_details->cod_allowed == 1 ? 'Cash on Delivery available' : 'Cash on Delivery Not available' }}
                            </div>
                            <div class="freeShipMsg featureText mb-2 d-flex"><ion-icon name="shield-checkmark-outline"
                                    class="fs-5 me-2"></ion-icon>
                                @if ($product_details->is_cancelable == 1)
                                    <b class="freeShip me-1">{{ labels('front_messages.cancel_till', 'Cancel Till') }}
                                        {{ $product_details->cancelable_till }}</b>
                                @else
                                    {{ labels('front_messages.non_cancelable', 'Non Cancelable') }}
                                @endif
                            </div>
                        @endif
                        @if ($product_details->product_type == 'simple_product')
                            @if ($product_details->stock_type != '')
                                <p class="product-stock d-flex">
                                    {{ labels('front_messages.availability', 'Availability') }}:
                                    <span class="pro-stockLbl ps-0">
                                        @if ($product_details->availability >= 1)
                                            <span
                                                class="d-flex-center stockLbl instock text-uppercase">{{ labels('front_messages.in_stock', 'In stock') }}</span>
                                        @else
                                            <span
                                                class="d-flex-center stockLbl outstock text-uppercase text-danger">{{ labels('front_messages.out_of_stock', 'Out of Stock') }}</span>
                                        @endif
                                    </span>
                                </p>
                            @endif
                        @else
                            @if ($product_details->variants[0]->stock_type != '')
                                <p class="product-stock d-flex">
                                    {{ labels('front_messages.availability', 'Availability') }}:
                                    <span class="pro-stockLbl ps-0">
                                        @if ($product_details->variants[0]->availability >= 1)
                                            <span
                                                class="d-flex-center stockLbl instock text-uppercase">{{ labels('front_messages.in_stock', 'In stock') }}</span>
                                        @else
                                            <span
                                                class="d-flex-center stockLbl outstock text-uppercase text-danger">{{ labels('front_messages.out_of_stock', 'Out of Stock') }}</span>
                                        @endif
                                    </span>
                                </p>
                            @endif
                        @endif
                        <hr class="light-hr" />
                        <div class="freeShipMsg featureText mb-2 d-flex align-items-center gap-2 fw-600 fs-6"><span
                                class="seller-icon"><ion-icon name="storefront-outline"></ion-icon></span> <a
                                wire:navigate
                                href="{{ customUrl('sellers/' . $product_details->seller_slug) }}">{{ $product_details->seller_name }}</a>
                        </div>
                        {{-- @dd($product_details) --}}
                        <div class="product-review d-flex-center mb-2 gap-2">
                            <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                class="kv-ltr-theme-svg-star rating-loading d-none"
                                value="{{ $product_details->seller_rating }}" dir="ltr" data-size="xs"
                                data-show-clear="false" data-show-caption="false" readonly></span>|<span
                                class="fw-500">{{ $product_details->seller_rating }}</span>
                        </div>
                        @if ($product_details->product_type != 'digital_product')
                            <hr class="light-hr" />
                            @if ($deliverabilitySettings != false)
                                <p class="featureText">
                                    {{ labels('front_messages.check_product_deliverability', 'Check Product Deliverability') }}
                                </p>
                                <div class="row align-items-center">
                                    @if ($deliverabilitySettings[0]->product_deliverability_type == 'city_wise_deliverability')
                                        <div class="col-md-10 city_list_div">
                                            <div>
                                                <label for="city"
                                                    class="d-none">{{ labels('front_messages.city', 'City') }}
                                                    <span class="required">*</span></label>
                                                <select class="col-md-12 form-control city_list" id="city_list"
                                                    name="city">
                                                </select>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-md-10">
                                            <label for="pincode"
                                                class="d-none">{{ labels('front_messages.pincode', 'Pincode') }}
                                                <span class="required-f">*</span></label>
                                            <input name="pincode" placeholder="Enter Pincode" value=""
                                                id="pincode" type="text">
                                        </div>
                                    @endif
                                    <div class="col-md-2 mt-2 mt-md-0">
                                        <button type="submit"
                                            class="btn rounded w-100 check-product-deliverability"><span>{{ labels('front_messages.check', 'Check') }}</span></button>
                                    </div>
                                    @if ($deliverabilitySettings[0]->product_deliverability_type == 'city_wise_deliverability')
                                        <p class="fw-400 text-danger text-small">
                                            {{ labels('front_messages.city_not_on_list', 'If your city is not on the list') }}
                                            {{ labels('front_messages.cannot_deliver', 'we cannot deliver the product there') }}.
                                        </p>
                                    @endif
                                    <p class="featureText deliverability-res"></p>
                                    <input type="hidden" name="product_deliverability_type"
                                        id="product_deliverability_type"
                                        value="{{ $deliverabilitySettings[0]->product_deliverability_type }}">
                                    <input type="hidden" name="product_id" id="product_id"
                                        value="{{ $product_id }}">
                                    <input type="hidden" name="product_type" id="product_type" value="regular">
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        {{-- @dd($product_details); --}}
        @if ($siblingsProduct['previous_product'] != null)
            <a wire:navigate href="{{ customUrl('products/' . $siblingsProduct['previous_product']->slug) }}"
                class="product-nav prev-pro clr-none d-flex-center justify-content-between border-radius"
                title="Previous Product">
                @php
                    $previous_product_img = dynamic_image($siblingsProduct['previous_product']->image, 200);
                @endphp
                <span class="details">
                    <span class="name fw-600">{{ $siblingsProduct['previous_product']->name }}</span>
                    <span
                        class="price">{{ currentCurrencyPrice(
                            (float) ($siblingsProduct['previous_product']->min_max_price['special_min_price'] > 0
                                ? $siblingsProduct['previous_product']->min_max_price['special_min_price']
                                : $siblingsProduct['previous_product']->min_max_price['min_price']),
                            true,
                        ) }}
                    </span>
                </span>
                <span class="img"><img class="rounded-0 rounded-start-0" src="{{ $previous_product_img }}"
                        alt="{{ $siblingsProduct['previous_product']->name }}" /></span>
            </a>
        @endif
        @if ($siblingsProduct['next_product'] != null)
            <a wire:navigate href="{{ customUrl('products/' . $siblingsProduct['next_product']->slug) }}"
                class="product-nav next-pro clr-none d-flex-center justify-content-between border-radius"
                title="Next Product">
                @php
                    $next_product_img = dynamic_image($siblingsProduct['next_product']->image, 200);
                @endphp
                <span class="img"><img class="rounded-0 rounded-end-0" src="{{ $next_product_img }}"
                        alt="{{ $siblingsProduct['next_product']->name }}" /></span>
                <span class="details">
                    <span class="name fw-600">{{ $siblingsProduct['next_product']->name }}</span>
                    <span
                        class="price">{{ currentCurrencyPrice(
                            (float) ($siblingsProduct['next_product']->min_max_price['special_min_price'] > 0
                                ? $siblingsProduct['next_product']->min_max_price['special_min_price']
                                : $siblingsProduct['next_product']->min_max_price['min_price']),
                            true,
                        ) }}
                    </span>
                </span>
            </a>
        @endif
        <div class="tabs-listing section pb-0">
            <ul class="product-tabs list-unstyled d-flex-wrap border-bottom d-none d-md-flex">
                @if ($product_details->description != '')
                    <li rel="description" class="active"><a
                            class="tablink"rel="description">{{ labels('front_messages.description', 'Description') }}</a>
                    </li>
                @endif
                @if ($product_details->attributes != [])
                    <li rel="additionalInformation"><a class="tablink"
                            rel="additionalInformation">{{ labels('front_messages.additional_information', 'Additional Information') }}</a>
                    </li>
                @endif
                <li rel="reviews"><a class="tablink"
                        rel="reviews">{{ labels('front_messages.reviews', 'Reviews') }}</a></li>
            </ul>

            <div class="tab-container">
                <!--Description-->
                @if ($product_details->description != '')
                    <h3 class="tabs-ac-style d-md-none active" rel="description">
                        {{ labels('front_messages.description', 'Description') }}</h3>
                    <div id="description" class="tab-content">
                        <div class="product-description">
                            <div class="row">
                                <div class="col-12 product-description">
                                    {!! $product_details->description !!}
                                    <div class="mt-3">
                                        {!! $product_details->extra_description !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Description-->
                @endif
                @if ($product_details->attributes != [])
                    <!--Additional Information-->
                    <h3 class="tabs-ac-style d-md-none" rel="additionalInformation">
                        {{ labels('front_messages.additional_information', 'Additional Information') }}
                    </h3>
                    <div id="additionalInformation" class="tab-content">
                        <div class="product-description">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-4 mb-md-0">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle table-part mb-0">
                                            @foreach ($product_details->attributes as $attributes)
                                                <tr>
                                                    {{-- @dd($attributes) --}}
                                                    <th>{{ $attributes['name'] }}</th>
                                                    <td>{{ $attributes['value'] }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Additional Information-->
                @endif
                <!--Review-->
                <h3 class="tabs-ac-style d-md-none" rel="reviews">{{ labels('front_messages.reviews', 'Reviews') }}
                </h3>
                <div id="reviews" class="tab-content">
                    <livewire:pages.customer-ratings :product_id="$product_id" :product_details="$product_details" />

                </div>
                <!--End Review-->
            </div>
        </div>
        <!--End Product Tabs-->
    </div>
    {{-- @dd($relative_products); --}}
    <!--Related Products-->
    @if (count($relative_products) >= 1)
        <section class="section product-slider pb-0">
            <div class="container-fluid">
                @php
                    $heading['title'] = labels('front_messages.related_products', 'Related Products');
                    $heading['short_description'] =
                        labels('front_messages.products_related_to', 'Products Related to ') . $product_details->name;
                @endphp
                <x-utility.section_header.sectionHeaderTwo :$heading />
                @php
                    $store_settings = getStoreSettings();
                @endphp
                <!--Product Grid-->
                <div
                    class="swiper style1-mySwiper gp15 arwOut5 hov-arrow grid-products {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }}">
                    <div class="swiper-wrapper">
                        @foreach ($relative_products as $details)
                            <div class="swiper-slide ">
                                @php
                                    $store_settings = getStoreSettings();
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
    @endif
    <!--End Related Products-->
    <div class="pswp" tabindex="-1" role="dialog">
        <div class="pswp__bg"></div>
        <div class="pswp__scroll-wrap">
            <div class="pswp__container">
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
                <div class="pswp__item"></div>
            </div>
            <div class="pswp__ui pswp__ui--hidden">
                <div class="pswp__top-bar">
                    <div class="pswp__counter"></div>
                    <button class="pswp__button pswp__button--close" title="Close (Esc)"></button>
                    <button class="pswp__button pswp__button--share" title="Share"></button>
                    <button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>
                    <button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>
                    <div class="pswp__preloader">
                        <div class="pswp__preloader__icn">
                            <div class="pswp__preloader__cut">
                                <div class="pswp__preloader__donut"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">
                    <div class="pswp__share-tooltip"></div>
                </div>
                <button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>
                <button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>
                <div class="pswp__caption">
                    <div class="pswp__caption__center"></div>
                </div>
            </div>
        </div>
    </div>
    <!--Product Video Modal-->
    <div class="productVideo-modal modal fade" id="productVideo_modal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="ratio ratio-16x9 productVideo-wrap">
                        <iframe class="rounded-0" src="https://www.youtube.com/embed/NpEaa2P7qZI"
                            title="YouTube video" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- @dd($product_details); --}}
    <!-- Sticky Cart -->
    <div class="stickyCart">
        <div class="container-fluid">
            <div id="stickycart-form" class="d-flex-center justify-content-center">
                @php
                    $image = dynamic_image($product_details->image, 200);
                @endphp

                <div class="product-featured-img"><img class="blur-up lazyload" data-src="{{ $image }}"
                        src="{{ $image }}" alt="product" width="120" height="170" /></div>
                <div class="sticky-title ms-2 ps-1 pe-5">{{ $product_details->name }}</div>
                <div class="stickyOptions position-relative">
                    <div class="selectedOpt sticky-cart-variant product_price">
                        @php
                            if ($product_details->type != 'variable_product') {
                                $price = currentCurrencyPrice($product_details->variants[0]->price, true);
                                $special_price =
                                    isset($product_details->variants[0]->special_price) &&
                                    $product_details->variants[0]->special_price > 0
                                        ? currentCurrencyPrice($product_details->variants[0]->special_price, true)
                                        : $price;
                            } else {
                                $max_price = currentCurrencyPrice($product_details->min_max_price['max_price'], true);
                                $special_min_price =
                                    isset($product_details->min_max_price['special_min_price']) &&
                                    $product_details->min_max_price['special_min_price'] > 0
                                        ? currentCurrencyPrice(
                                            $product_details->min_max_price['special_min_price'],
                                            true,
                                        )
                                        : $max_price;
                            }
                        @endphp

                        @if ($product_details->type != 'variable_product')
                            {{ $special_price }}
                        @else
                            {{ $max_price }} - {{ $special_min_price }}
                        @endif
                    </div>

                </div>
                <div class="qtyField mx-2">
                    <button class="qtyBtn minus" href="#;"><ion-icon name="remove-outline"></ion-icon></button>
                    <input type="text" name="quantity" value="1" class="product-form-input qty dlt-qty"
                        max='{{ $product_details->total_allowed_quantity == 0 ? 'Infinity' : $product_details->total_allowed_quantity }}'
                        step='{{ $product_details->quantity_step_size }}'
                        min='{{ $product_details->minimum_order_quantity }}' />
                    <button class="qtyBtn plus" href="#;"><ion-icon name="add-outline"></ion-icon></button>
                </div>
                <button type="submit" name="add"
                    class="btn btn-secondary product-form-cart-submit add_cart dlt-add-cart"
                    data-product-variant-id="{{ $variant_id }}"
                    data-max='{{ $product_details->total_allowed_quantity }}'
                    data-step='{{ $product_details->quantity_step_size }}'
                    data-min='{{ $product_details->minimum_order_quantity }}'
                    data-store-id='{{ $product_details->store_id }}' data-variant-price="{{ $variant_price }}"
                    data-product-type='regular'>
                    <span>{{ labels('front_messages.add_to_cart', 'Add to cart') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('frontend/elegant/js/lightbox.js') }}" defer></script>
<script>
    function toggleInput(liElement) {
        var inputElement = liElement.querySelector('input[type="radio"]');
        if (inputElement) {
            inputElement.click();
        }
    }
</script>
