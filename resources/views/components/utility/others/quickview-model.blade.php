<!-- Product Quickview Modal-->
<div wire:ignore.self class="quickview-modal modal fade" id="quickview_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div wire:loading class="my-4 ">
                <div class="d-flex justify-content-center align-items-center ">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            {{-- @dd($product[0]); --}}
            @if (count($product) >= 1)
                <div class="modal-body">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="row">
                        <div
                            class="col-12 col-sm-6 col-md-6 col-lg-6 mb-3 mb-md-0 d-flex flex-column justify-content-between">
                            <!-- Model Thumbnail -->
                            <div id="quickView" class="carousel slide">
                                <!-- Image Slide carousel items -->
                                <div class="carousel-inner">
                                    @php
                                        $main_image = dynamic_image($product[0]->image, 800);
                                    @endphp
                                    <div class="item carousel-item active" data-bs-slide-number="0">
                                        <img class="blur-up lazyload" data-src="{{ $main_image }}"
                                            src="{{ $main_image }}" alt="{{ $product[0]->name }}"
                                            title="{{ $product[0]->name }}" width="625" height="808" />
                                    </div>
                                    @if (count($product[0]->other_images) >= 1)
                                        @foreach ($product[0]->other_images as $key => $images)
                                            @php
                                                $images = dynamic_image($images, 800);
                                            @endphp
                                            <div class="item carousel-item" data-bs-slide-number="{{ $key + 1 }}">
                                                <img class="blur-up lazyload" data-src="{{ $images }}"
                                                    src="{{ $images }}" alt="{{ $product[0]->name }}"
                                                    title="{{ $product[0]->name }}" width="625" height="808" />
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div class="model-thumbnail-img">
                                    <!-- Thumbnail slide -->
                                    <div class="carousel-indicators list-inline">
                                        <div class="list-inline-item active" id="carousel-selector-0"
                                            data-bs-slide-to="0" data-bs-target="#quickView">
                                            @php
                                                $main_image = dynamic_image($product[0]->image, 200);
                                            @endphp
                                            <img class="blur-up lazyload" data-src="{{ $main_image }}"
                                                src="{{ $main_image }}" alt="{{ $product[0]->name }}"
                                                title="{{ $product[0]->name }}" width="625" height="808" />
                                        </div>
                                        @if (count($product[0]->other_images) >= 1)
                                            @foreach ($product[0]->other_images as $key => $images)
                                                @php
                                                    $images = dynamic_image($images, 200);
                                                @endphp
                                                <div class="list-inline-item" id="carousel-selector-1"
                                                    data-bs-slide-to="{{ $key + 1 }}" data-bs-target="#quickView">
                                                    <img class="blur-up lazyload" data-src="{{ $images }}"
                                                        src="{{ $images }}" alt="{{ $product[0]->name }}"
                                                        title="{{ $product[0]->name }}" width="625"
                                                        height="808" />
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    <!-- End Thumbnail slide -->
                                    <!-- Carousel arrow button -->
                                    <a class="carousel-control-prev carousel-arrow rounded-1" href="#quickView"
                                        data-bs-target="#quickView" data-bs-slide="prev"><i
                                            class="icon anm anm-angle-left-r"></i></a>
                                    <a class="carousel-control-next carousel-arrow rounded-1" href="#quickView"
                                        data-bs-target="#quickView" data-bs-slide="next"><i
                                            class="icon anm anm-angle-right-r"></i></a>
                                    <!-- End Carousel arrow button -->
                                </div>
                                <!-- End Thumbnail image -->
                            </div>
                            <!-- End Model Thumbnail -->
                            {{-- @dd($product[0]->slug) --}}
                            <div class="text-center mt-3"><a
                                    href="{{ customUrl(($this->product_type != 'combo-product' ? 'products' : 'combo-products') . '/' . $product[0]->slug) }}"
                                    class="text-link">{{ labels('front_messages.view_more_details', 'View More Details') }}</a>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-6">
                            @if (count($product[0]->tags) >= 1)
                                <div class="mb-1">
                                    @foreach ($product[0]->tags as $tag)
                                        <a wire:navigate href="{{ customUrl('products/?tag=' . $tag) }}"
                                            class="text fw-500 border border-2 px-1 tag-filter"
                                            title="{!! $tag !!}">{!! $tag !!}
                                        </a>
                                    @endforeach
                                </div>
                            @endif
                            <div class="product-arrow d-flex justify-content-between">
                                <h2 class="product-title">{{ $product[0]->name }}</h2>
                            </div>
                            <div class="product-review mt-0 mb-2">
                                <div class="rating d-flex mb-10px">
                                    <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                        class="kv-ltr-theme-svg-star rating-loading d-none"
                                        value="{{ $product[0]->rating }}" dir="ltr" data-size="xs"
                                        data-show-clear="false" data-show-caption="false" readonly>
                                    <div class="reviews ms-2"><a href="#">{{ $product[0]->no_of_ratings }}
                                            {{ labels('front_messages.reviews', 'Reviews') }}</a></div>
                                </div>
                                <div class="d-flex-center fs-5 fw-500 mb-10px">
                                    @if ($product[0]->type == 'combo-product')
                                        <span class="price product_price" id="price">
                                            {{ currentCurrencyPrice($product[0]->special_price > 0 ? $product[0]->special_price : $product[0]->price, true) }}
                                        </span>
                                    @else
                                        <span class="price product_price" id="price">
                                            {{ $product[0]->type != 'variable_product'
                                                ? ($product[0]->variants[0]->special_price == 0 || $product[0]->variants[0]->special_price == null
                                                    ? currentCurrencyPrice($product[0]->variants[0]->price, true)
                                                    : currentCurrencyPrice($product[0]->variants[0]->special_price, true))
                                                : ($product[0]->min_max_price['special_min_price'] == 0 || $product[0]->min_max_price['special_min_price'] == null
                                                    ? currentCurrencyPrice($product[0]->min_max_price['max_price'], true)
                                                    : currentCurrencyPrice($product[0]->min_max_price['special_min_price'], true) .
                                                        '-' .
                                                        currentCurrencyPrice($product[0]->min_max_price['special_max_price'], true)) }}
                                        </span>
                                    @endif
                                </div>
                                @php
                                    if ($this->product_type != 'combo-product') {
                                        $category = fetchDetails(
                                            'categories',
                                            ['id' => $product[0]->category_id],
                                            'slug',
                                        );
                                    }
                                @endphp
                                @if ($product[0]->product_type == 'digital_product')
                                    <p class="mb-10px" title="Digital Product"><ion-icon
                                            class="custom-icon fs-6 me-1"
                                            name="cube-outline"></ion-icon>{{ labels('front_messages.digital_product', 'Digital Product') }}
                                    </p>
                                @endif
                                @if ($this->product_type != 'combo-product')
                                    @if (!empty(getDynamicTranslation('brands', 'name', $product[0]->brand, $language_code)))
                                        <a wire:navigate
                                            href="{{ customUrl('products?brand=' . $product[0]->brand_slug) }}"
                                            class="text-ellipsis mb-10px" title="{!! getDynamicTranslation('brands', 'name', $product[0]->brand, $language_code) !!}"><ion-icon
                                                class="custom-icon fs-6 me-1"
                                                name="medal-outline"></ion-icon>{!! getDynamicTranslation('brands', 'name', $product[0]->brand, $language_code) !!}
                                        </a>
                                    @endif
                                    <a wire:navigate
                                        href="{{ customUrl('categories/' . $category[0]->slug . '/products') }}"
                                        class="text-ellipsis mb-10px text-secondary"
                                        title="{!! getDynamicTranslation('categories', 'name', $product[0]->category_id, $language_code) !!}"><ion-icon name="layers-outline"
                                            class="custom-icon fs-6 me-1"></ion-icon>{!! getDynamicTranslation('categories', 'name', $product[0]->category_id, $language_code) !!}
                                    </a>
                                @endif
                                <hr class="light-hr" />
                                <div class="text-muted">{{ $product[0]->short_description }}</div>
                                <hr class="light-hr" />
                                @if ($this->product_type == 'combo-product')
                                    <h4 class="fw-600 mb-0">
                                        {{ labels('front_messages.products_included_in_combo', 'Products Included In Combo') }}
                                    </h4>
                                    <div class="table-responsive w-100 mt-n2">
                                        <table class="grouped-product-list group-table">
                                            @foreach ($product[0]->product_details as $item)
                                                @php
                                                    $item_image = dynamic_image($item['image'], 200);
                                                @endphp
                                                <tr class="grouped-product-list-item border-bottom">
                                                    <td class="product-thumb">
                                                        <div class="position-relative">
                                                            <img class="blur-up lazyload"
                                                                data-src="{{ $item_image }}"
                                                                src="{{ $item_image }}" alt="{{ $item['name'] }}"
                                                                title="" width="70" />
                                                        </div>
                                                    </td>
                                                    <td class="product-label px-3">
                                                        <div class="product-name fw-500 mb-2 text-ellipsis"><a
                                                                wire:navigate
                                                                href="{{ customUrl('products/' . $item['slug']) }}">{{ $item['name'] }}</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                @endif

                                <div class="product-options d-flex-wrap">
                                    <div class="product-swatches-option">
                                        @foreach ($product[0]->attributes as $attributes)
                                            @php
                                                $attribute_ids = explode(',', $attributes['ids']);
                                                $attribute_values = explode(',', $attributes['value']);
                                            @endphp
                                            <div class="product-item swatches-size w-100 mb-4 swatch-1 option2"
                                                data-option-index="1">
                                                <label for=""
                                                    class="label d-flex align-items-center fw-500">{{ $attributes['name'] }}:</label>
                                                <ul class="variants-size size-swatches d-flex-center pt-1 clearfix">
                                                    @foreach ($attribute_values as $key => $val)
                                                        <li class="swatch x-large available p-1 toggleInput"
                                                            onclick="toggleInput(this)">
                                                            <input type="radio" class="swatchLbl attributes d-none"
                                                                data-bs-toggle="tooltip"
                                                                value="{{ $attribute_ids[$key] }}"
                                                                data-bs-placement="top" title="{{ $val }}"
                                                                id="variant-{{ $attribute_ids[$key] }}">{{ $val }}</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if ($this->product_type != 'combo-product')
                                        @foreach ($product[0]->variants as $variant)
                                            <input type="hidden" class="variants" name="variants_ids"
                                                data-image-index="" data-name=""
                                                value="{{ $variant->attribute_value_ids }}"
                                                data-id="{{ $variant->id }}" data-price="{{ $variant->price }}"
                                                data-special_price="{{ $variant->special_price }}" />
                                        @endforeach
                                    @endif
                                    <div class="product-action d-flex-wrap w-100 pt-1 mb-3 clearfix">
                                        <div class="quantity">
                                            <div class="qtyField rounded">
                                                <button class="qtyBtn minus" href="#;"><ion-icon
                                                        name="remove-outline"></ion-icon></button>
                                                <input type="number" name="quantity" value="1"
                                                    class="product-form-input qty"
                                                    max='{{ $product[0]->total_allowed_quantity == 0 ? 'Infinity' : $product[0]->total_allowed_quantity }}'
                                                    step='{{ $product[0]->quantity_step_size }}'
                                                    min='{{ $product[0]->minimum_order_quantity }}' />
                                                <button class="qtyBtn plus" href="#;"><ion-icon
                                                        name="add-outline"></ion-icon></button>
                                            </div>
                                        </div>

                                        @php
                                            if ($this->product_type != 'combo-product') {
                                                if (count($product[0]->variants) <= 1) {
                                                    $variant_id = $product[0]->variants[0]->id;
                                                } else {
                                                    $variant_id = '';
                                                }
                                            } else {
                                                $variant_id = $product[0]->id;
                                            }
                                        @endphp
                                        <div class="addtocart ms-3 fl-1">
                                            <button type="submit" name="add"
                                                class="btn product-cart-submit w-100 add_cart modal_add_cart"
                                                id="add_cart" onclick="add_cart(this)"
                                                data-product-variant-id="{{ $variant_id }}"
                                                data-name='{{ $product[0]->name }}'
                                                data-slug='{{ $product[0]->slug }}'
                                                data-brand-name="{{ getDynamicTranslation('brands', 'name', optional($product[0])->brand, $language_code) ?? '' }}"
                                                data-image='{{ $product[0]->image }}'
                                                data-product-type='{{ $product[0]->type == 'combo-product' ? 'combo' : 'regular' }}'
                                                data-max='{{ $product[0]->total_allowed_quantity }}'
                                                data-step='{{ $product[0]->quantity_step_size }}'
                                                data-store-id='{{ $product[0]->store_id }}'
                                                data-min='{{ $product[0]->minimum_order_quantity }}'><span>{{ labels('front_messages.add_to_cart', 'Add to cart') }}</span></button>
                                        </div>
                                    </div>
                                </div>
                                <!-- Social Sharing -->
                                <div class="social-sharing share-icon d-flex-center mx-0 mt-3">
                                    <span class="sharing-lbl fw-500">Share :</span>
                                    <div class="shareon"
                                        data-url="{{ customUrl(($this->product_type != 'combo-product' ? 'products' : 'combo-products') . '/' . $product[0]->slug) }}">
                                        <a class="facebook"
                                            data-text="Take a Look at this {{ $product[0]->name }} on {{ $system_settings['app_name'] }}"></a>
                                        <a class="telegram"
                                            data-text="Take a Look at this {{ $product[0]->name }} on {{ $system_settings['app_name'] }}"></a>
                                        <a class="twitter"
                                            data-text="Take a Look at this {{ $product[0]->name }} on {{ $system_settings['app_name'] }}"></a>
                                        <a class="whatsapp"
                                            data-text="Take a Look at this {{ $product[0]->name }} on {{ $system_settings['app_name'] }}"></a>
                                        <a class="email"
                                            data-text="Take a Look at this {{ $product[0]->name }} on {{ $system_settings['app_name'] }}"></a>
                                        <a class="copy-url"></a>
                                    </div>
                                </div>
                                <!-- End Social Sharing -->
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <script>
        function toggleInput(liElement) {
            var inputElement = liElement.querySelector('input[type="radio"]');
            var siblings = Array.from(liElement.parentNode.children).filter(function(child) {
                return child !== liElement;
            });
            siblings.forEach(function(sibling) {
                sibling.classList.remove("active");
            });
            if (inputElement) {
                inputElement.click();
                liElement.classList.add("active");
            }
        }
    </script>
