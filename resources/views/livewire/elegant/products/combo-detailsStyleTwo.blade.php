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
                                        {!! request()->is('combo-products/*') ? strip_tags($right_breadcrumb) : $right_breadcrumb !!}
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
        <div class="product-single">
            <div class="row">
                <div class="col-lg-9 col-md-12 col-sm-12 col-12 product-layout-img-info mb-4 mb-lg-0">
                    <!--Product Content-->
                    <div class="row">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12 product-layout-img mb-4 mb-md-0">
                            <!-- Product Horizontal -->
                            <div class="product-details-img product-horizontal-style">
                                <!-- Product Main -->
                                <div class="zoompro-wrap">
                                    @php
                                        $main_image = dynamic_image($product_details->image, 600);
                                        $main_image_zoom = dynamic_image($product_details->image, 800);
                                    @endphp
                                    <div class="zoompro-span"><img id="zoompro" class="zoompro"
                                            src="{{ $main_image }}" data-zoom-image="{{ $main_image_zoom }}"
                                            alt="product" width="625" height="808"></div>
                                    <!-- End Product Image -->
                                </div>
                                <!-- End Product Main -->

                                <!-- Product Thumb -->
                                <div class="product-thumb product-horizontal-thumb mt-3">
                                    <div id="gallery" class="product-thumb-horizontal slick-slider">
                                        @foreach ($product_details->other_images as $images)
                                            @php
                                                $other_image = dynamic_image($images, 600);
                                                $other_image_zoom = dynamic_image($images, 800);
                                            @endphp
                                            <div class="slick-slide">
                                                <a data-image="{{ $other_image }}"
                                                    data-zoom-image="{{ $other_image_zoom }}">
                                                    <img class="blur-up lazyloaded" src="{{ $other_image }}"
                                                        alt="product" width="625" height="808">
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <!-- End Product Thumb -->

                            </div>
                            <!-- End Product Horizontal -->

                            <!-- Social Sharing -->
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
                            <!-- End Social Sharing -->
                        </div>


                        <div class="col-lg-6 col-md-6 col-sm-12 col-12 product-layout-info">
                            <!-- Product Details -->
                            <div class="product-single-meta">
                                <h2 class="product-main-title">{{ $product_details->name }}</h2>
                                <!-- Product Reviews -->
                                <div class="product-review d-flex-center mb-3">
                                    <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                        class="kv-ltr-theme-svg-star rating-loading d-none"
                                        value="{{ $product_details->rating }}" dir="ltr" data-size="xs"
                                        data-show-clear="false" data-show-caption="false" readonly>
                                </div>
                                <!-- End Product Reviews -->
                                <!-- Product Price -->
                                <div class="product-price d-flex-center my-3">
                                    @php
                                        $price = currentCurrencyPrice($product_details->price, true);
                                        $special_price =
                                            $product_details->special_price && $product_details->special_price > 0
                                                ? currentCurrencyPrice($product_details->special_price, true)
                                                : $price;
                                    @endphp
                                    <span class="price product_price" id="price">
                                        @if ($special_price !== $price)
                                            <span class="price old-price">{{ $price }}</span>
                                        @endif
                                        {{ $special_price }}
                                    </span>
                                </div>
                                <!-- End Product Price -->
                                <div class="mb-10px text-muted">{{ $product_details->short_description }}</div>
                                <hr class="light-hr" />
                                @if (!empty($product_details->made_in))
                                    <p class="product-sku mb-10px">
                                        {{ labels('front_messages.made_in', 'Made In') }}:<span
                                            class="text fw-500">{{ $product_details->made_in }}</span></p>
                                @endif


                                @if (count($product_details->tags) >= 1)
                                    <p class="text-uppercase text-black mb-10px"><ion-icon name="pricetags-outline"
                                            class="custom-icon fs-6 me-1"></ion-icon>
                                        @foreach ($product_details->tags as $tag)
                                            <a href="{{ customUrl('combo-products/?tag=' . $tag) }}"
                                                class="text fw-500 border border-2 px-1 p-0 tag-filter"
                                                title="{!! $tag !!}">{!! $tag !!}
                                            </a>
                                        @endforeach
                                    </p>
                                @endif
                                <!-- Product Info -->
                                @if ($product_details->stock_type != '')
                                    <div class="product-info">
                                        <p class="product-stock d-flex">Availability:
                                            <span class="pro-stockLbl ps-0">
                                                <span
                                                    class="d-flex-center stockLbl instock text-uppercase">{{ labels('front_messages.items_in_stock', 'Items are in stock!') }}</span>
                                            </span>
                                        </p>
                                    </div>
                                @endif
                                <div class="freeShipMsg featureText mb-2 d-flex align-items-center gap-2 fw-600 fs-6">
                                    <span class="seller-icon"><ion-icon name="storefront-outline"></ion-icon></span> <a
                                        wire:navigate
                                        href="{{ customUrl('sellers/' . $product_details->seller_slug) }}">{{ $product_details->seller_name }}</a>
                                </div>
                                @if (!empty($product_details->sku))
                                    <p class="product-sku">SKU:<span class="text">{{ $product_details->sku }}</span>
                                    </p>
                                @endif

                                <!-- End Product Info -->
                            </div>
                            <!-- End Product Details -->

                            <!-- Product Form -->
                            <h4>{{ labels('front_messages.products_included_in_combo', 'Products Included In Combo') }}
                            </h4>
                            <div class="table-responsive w-100 mt-n2">
                                <table class="grouped-product-list group-table">
                                    @foreach ($product_details->product_details as $item)
                                        @php
                                            $item_image = dynamic_image($item['image'], 200);
                                        @endphp
                                        <tr class="grouped-product-list-item border-bottom">
                                            <td class="product-thumb">
                                                <div class="position-relative combo-include-pro-img">
                                                    <img class="blur-up lazyload" data-src="{{ $item_image }}"
                                                        src="{{ $item_image }}" alt="{{ $item['name'] }}"
                                                        title="{{ $item['name'] }}" />
                                                </div>
                                            </td>
                                            <td class="product-label px-3">
                                                <div class="product-name fw-500 mb-2">
                                                    <a wire:navigate
                                                        href="{{ customUrl('products/' . $item['slug']) }}">{{ $item['name'] }}</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            <!-- Swatches -->
                            <div class="product-swatches-option">
                                @foreach ($product_details->attributes as $attributes)
                                    @php
                                        $attribute_ids = explode(',', $attributes['ids']);
                                        $attribute_values = explode(',', $attributes['value']);
                                    @endphp
                                    <div class="product-item swatches-size w-100 mb-2 swatch-1 option2"
                                        data-option-index="1">
                                        <label
                                            class="label d-flex align-items-center">{{ $attributes['name'] }}:</label>
                                        <ul class="variants-size size-swatches d-flex-center pt-1 clearfix">
                                            @foreach ($attribute_values as $key => $val)
                                                <li class="swatch x-large available p-1 toggleInput"
                                                    onclick="toggleInput(this)">
                                                    <input type="radio" class="swatchLbl attributes d-none"
                                                        data-bs-toggle="tooltip" value="{{ $attribute_ids[$key] }}"
                                                        data-bs-placement="top" title="{{ $val }}"
                                                        id="variant-{{ $attribute_ids[$key] }}">{{ $val }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                            <!-- End Swatches -->

                            <!-- Product Action -->
                            <div class="product-action w-100 d-flex-wrap my-3 my-md-4">
                                <!-- Product Quantity -->
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
                                <!-- End Product Quantity -->
                                @php
                                    $variant_id = $product_details->id;
                                    $variant_price = $product_details->special_price;
                                @endphp
                                <!-- Product Add -->

                                <div class="product-form-submit addcart fl-1 ms-3">
                                    <button type="submit" name="add"
                                        class="btn btn-secondary product-form-cart-submit add_cart dlt-add-cart"
                                        id="add_cart" data-product-variant-id="{{ $variant_id }}"
                                        data-name='{{ $product_details->name }}'
                                        data-slug='{{ $product_details->slug }}' data-brand-name=''
                                        data-image='{{ $product_details->image }}' data-product-type='combo'
                                        {{ $product_details->total_allowed_quantity == 0 ? '' : "data-max=' $product_details->total_allowed_quantity '" }}
                                        data-step='{{ $product_details->quantity_step_size }}'
                                        data-min='{{ $product_details->minimum_order_quantity }}'
                                        data-stock-type='{{ $product_details->stock_type }}'
                                        data-store-id='{{ $product_details->store_id }}'
                                        data-variant-price="{{ $variant_price }}">
                                        <span>{{ labels('front_messages.add_to_cart', 'Add to cart') }}</span>
                                    </button>
                                </div>
                                <!-- Product Add -->
                                <!-- Product Buy -->
                                <div class="product-form-submit buyit d-flex w-100 mt-3">
                                    <button type="submit" class="btn btn-primary buy_now add_cart dlt-add-cart"
                                        data-product-variant-id="{{ $variant_id }}"
                                        data-name='{{ $product_details->name }}'
                                        data-slug='{{ $product_details->slug }}' data-brand_name=''
                                        data-image='{{ $product_details->image }}' data-product-type='combo'
                                        {{ $product_details->total_allowed_quantity == 0 ? '' : "data-max=' $product_details->total_allowed_quantity '" }}
                                        data-step='{{ $product_details->quantity_step_size }}'
                                        data-min='{{ $product_details->minimum_order_quantity }}'
                                        data-store-id='{{ $product_details->store_id }}'
                                        data-variant-price="{{ $variant_price }}">
                                        <span>{{ labels('front_messages.buy_it_now', 'Buy it now') }}</span>
                                    </button>
                                    <!-- Wishlist Remove Button -->
                                    <a href="#"
                                        class="btn btn-secondary wishlist-submit w-auto p-3 ms-2 remove-favorite rem-fav-btn text-danger {{ $product_details->is_favorite == 0 ? 'd-none' : '' }}"
                                        data-product-id="{{ $product_details->id }}" data-product-type="combo"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ labels('front_messages.remove_from_wishlist', 'Remove from Wishlist') }}">
                                        <i class="icon anm anm-heart-l fs-6"></i>
                                    </a>

                                    <!-- Wishlist Add Button -->
                                    <a href="#"
                                        class="btn btn-secondary wishlist-submit w-auto p-3 ms-2 add-favorite {{ $product_details->is_favorite == 0 ? '' : 'd-none' }}"
                                        data-product-id="{{ $product_details->id }}" data-product-type="combo"
                                        data-bs-toggle="tooltip" data-bs-placement="top"
                                        title="{{ labels('front_messages.add_to_wishlist', 'Add to Wishlist') }}">
                                        <i class="icon anm anm-heart-l fs-6"></i>
                                    </a>

                                    <!-- Compare Button (Added Next to Wishlist) -->
                                    <a href="#" class="btn btn-secondary w-auto p-3 ms-2 add-compare compare"
                                        data-product-id="{{ $product_details->id }}" data-product-variant-id=""
                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Add to Compare">
                                        <i class="icon anm anm-random-r fs-6"></i>
                                    </a>
                                </div>
                                <!-- End Product Buy -->
                            </div>
                            <!-- End Product Action -->
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
                                        <input type="hidden" name="product_type" id="product_type" value="combo">
                                    </div>
                                @endif
                            @endif
                            <!-- End Product Form -->
                        </div>
                    </div>
                    <!--Product Content-->
                    <!--Product Nav-->
                    @if ($siblingsProduct['previous_product'] != null)
                        <a wire:navigate
                            href="{{ customUrl('combo-products/' . $siblingsProduct['previous_product']->slug) }}"
                            class="product-nav prev-pro clr-none d-flex-center justify-content-between border-radius"
                            title="Previous Product">
                            @php
                                $previous_product_img = dynamic_image($siblingsProduct['previous_product']->image, 200);
                            @endphp
                            <span class="details">
                                <span class="name fw-600">{{ $siblingsProduct['previous_product']->name }}</span>
                                <span
                                    class="price">{{ currentCurrencyPrice((float) $siblingsProduct['previous_product']->special_price, true) }}</span>
                            </span>
                            <span class="img"><img class="rounded-0 rounded-start-0"
                                    src="{{ $previous_product_img }}"
                                    alt="{{ $siblingsProduct['previous_product']->name }}" width="120"
                                    height="170" /></span>
                        </a>
                    @endif
                    @if ($siblingsProduct['next_product'] != null)
                        <a wire:navigate
                            href="{{ customUrl('combo-products/' . $siblingsProduct['next_product']->slug) }}"
                            class="product-nav next-pro clr-none d-flex-center justify-content-between border-radius"
                            title="Next Product">
                            @php
                                $next_product_img = dynamic_image($siblingsProduct['next_product']->image, 200);
                            @endphp
                            <span class="img"><img class="rounded-0 rounded-end-0" src="{{ $next_product_img }}"
                                    alt="{{ $siblingsProduct['next_product']->name }}" width="120"
                                    height="170" /></span>
                            <span class="details">
                                <span class="name fw-600">{{ $siblingsProduct['next_product']->name }}</span>
                                <span
                                    class="price">{{ currentCurrencyPrice(
                                        (float) ($siblingsProduct['next_product']->special_price > 0
                                            ? $siblingsProduct['next_product']->special_price
                                            : $siblingsProduct['next_product']->price),
                                        true,
                                    ) }}
                                </span>
                            </span>
                        </a>
                    @endif
                    <!--End Product Nav-->
                    <!--Product Accordian-->
                    <div class="accordion tab-accordian-style section pb-0" id="productAccordian">
                        @if ($product_details->description != '')
                            <div class="accordion-item border-0 bg-transparent mb-2">
                                <h2 class="accordion-header" id="headingOne"><button class="accordion-button"
                                        type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                                        aria-expanded="true"
                                        aria-controls="collapseOne">{{ labels('front_messages.description', 'Description') }}</button>
                                </h2>
                                <div id="collapseOne" class="accordion-collapse collapse show"
                                    aria-labelledby="headingOne" data-bs-parent="#productAccordian">
                                    <div class="accordion-body px-0 product-description">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                                                {!! $product_details->description !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        @if ($product_details->attributes != [])
                            <div class="accordion-item border-0 bg-transparent mb-2">
                                <h2 class="accordion-header" id="headingFive">
                                    <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#collapseFive"
                                        aria-expanded="false"
                                        aria-controls="collapseFive">{{ labels('front_messages.additional_information', 'Additional Information') }}</button>
                                </h2>
                                <div id="collapseFive" class="accordion-collapse collapse"
                                    aria-labelledby="headingFive" data-bs-parent="#productAccordian">
                                    <div class="accordion-body px-0 product-description" id="additionalInformation">
                                        <div class="row">
                                            <div class="col-12 col-sm-12 col-md-12 col-lg-12 mb-4 mb-md-0">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered align-middle table-part mb-0">
                                                        @foreach ($product_details->attributes as $attributes)
                                                            <tr>
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
                            </div>
                        @endif
                        <div class="accordion-item border-0 bg-transparent mb-2">
                            <h2 class="accordion-header" id="headingFour"><button class="accordion-button collapsed"
                                    type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour"
                                    aria-expanded="false" aria-controls="collapseFour">Reviews</button></h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                                data-bs-parent="#productAccordian">
                                <div class="accordion-body px-0" id="reviews">
                                    <div class="row">
                                        <livewire:pages.customer-ratings :product_id="$product_id" :product_details="$product_details" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Product Accordian-->
                </div>

                <div class="col-lg-3 col-md-12 col-sm-12 col-12 product-sidebar sidebar sidebar-bg">
                    <!--Shipping Info-->
                    @if ($product_details->product_type == 'digital_product')
                        <div class="freeShipMsg featureText mb-2 d-flex"><ion-icon name="cube-outline"
                                class="fs-5 me-2"></ion-icon>{{ labels('front_messages.digital_product', 'Digital Product') }}
                        </div>
                    @else
                        <div class="sidebar-widget clearfix">
                            <div class="widget-content pt-0 mt-0 border-0">
                                <div class="store-info-item d-flex align-items-center mb-3">
                                    <div class="icon me-3"><ion-icon name="ribbon-outline"
                                            class="fs-5 me-2"></ion-icon></div>
                                    <div class="content">
                                        <h5 class="title text-transform-none mb-1">Satisfaction Guarantee</h5>
                                        @if (!empty($product_details->guarantee_period))
                                            <p class="text text-muted text-small">
                                                {{ $product_details->guarantee_period }}</p>
                                        @else
                                            {{ labels('front_messages.no_guarantee', 'No Guarantee') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="store-info-item d-flex align-items-center mb-3">
                                    <div class="icon me-3"><ion-icon name="shield-checkmark-outline"
                                            class="fs-5 me-2"></ion-icon></div>
                                    <div class="content">
                                        <h5 class="title text-transform-none mb-1">Satisfaction Warranty</h5>
                                        @if (!empty($product_details->warranty_period))
                                            <p class="text text-muted text-small">
                                                {{ $product_details->warranty_period }}</p>
                                        @else
                                            {{ labels('front_messages.no_warranty', 'No Warranty') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="store-info-item d-flex align-items-center mb-3">
                                    <div class="icon me-3"><ion-icon name="pin-outline" class="fs-5 me-2"></ion-icon>
                                    </div>
                                    <div class="content">
                                        <h5 class="title text-transform-none mb-1">Cash On Delivery</h5>
                                        <p class="text text-muted text-small">
                                            {{ $product_details->cod_allowed == 1 ? 'Cash on Delivery available' : 'Cash on Delivery Not available' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="store-info-item d-flex align-items-center mb-3">
                                    <div class="icon me-3"><ion-icon name="refresh-outline"
                                            class="fs-5 me-2"></ion-icon></div>
                                    <div class="content">
                                        <h5 class="title text-transform-none mb-1">Returnable</h5>
                                        <p class="text text-muted text-small">
                                            {{ $product_details->is_returnable == 1 ? 'Returnable' : 'Non Returnable' }}
                                        </p>
                                    </div>
                                </div>
                                <div class="store-info-item d-flex align-items-center mb-3">
                                    <div class="icon me-3"><ion-icon name="shield-checkmark-outline"
                                            class="fs-5 me-2"></ion-icon></div>
                                    <div class="content">
                                        <h5 class="title text-transform-none mb-1">
                                            {{ labels('front_messages.cancel_till', 'Cancel Till') }}</h5>
                                        @if ($product_details->is_cancelable == 1)
                                            <p class="text text-muted text-small">
                                                {{ $product_details->cancelable_till }}</p>
                                        @else
                                            <p class="text text-muted text-small">
                                                {{ labels('front_messages.non_cancelable', 'Non Cancelable') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                    <!--End Shipping Info-->

                    <!--Related Products-->
                    @if (count($relative_products) >= 1)
                        <!--Related Products-->
                        <div class="sidebar-widget sidePro">
                            <div class="widget-title">
                                <h2>{{ labels('front_messages.products_related_to', 'Products Related to ') . $product_details->name }}
                                </h2>
                            </div>
                            <div class="widget-content">
                                <div class="sideProSlider grid-products col-sm-4 col-lg-12">
                                    @foreach ($relative_products as $details)
                                        <div class="item">
                                            <div class="product-image">
                                                @php
                                                    $store_settings = getStoreSettings();
                                                    $component = getProductDisplayComponent($store_settings);
                                                @endphp

                                                <x-dynamic-component :component="$component" :details="$details" />
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>
                        <!--End Related Products-->
                    @endif
                    <!--End Related Products-->
                </div>
            </div>
        </div>
    </div>
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
                    <div class="selectedOpt product_price">
                        @php
                            // Get numeric values for comparison
                            $priceValue = $product_details->price;
                            $specialPriceValue =
                                isset($product_details->special_price) && $product_details->special_price > 0
                                    ? $product_details->special_price
                                    : null;

                            // Determine the final price to display
                            $finalPrice =
                                $specialPriceValue === null || $specialPriceValue >= $priceValue
                                    ? $priceValue
                                    : $specialPriceValue;

                            // Format price for display
                            $displayPrice = currentCurrencyPrice($finalPrice, true);
                        @endphp

                        <span class="price">{{ $displayPrice }}</span>
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
                    {{ $product_details->total_allowed_quantity == 0 ? '' : "data-max=' $product_details->total_allowed_quantity '" }}
                    data-step='{{ $product_details->quantity_step_size }}'
                    data-min='{{ $product_details->minimum_order_quantity }}'
                    data-store-id='{{ $product_details->store_id }}' data-variant-price="{{ $variant_price }}"
                    data-product-type='combo'>
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
