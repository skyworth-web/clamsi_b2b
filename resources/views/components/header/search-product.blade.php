<div wire:ignore.self class="search-drawer offcanvas offcanvas-top" tabindex="-1" id="search-drawer">
    <div class="container-fluid">
        <div class="search-header d-flex-center justify-content-between mb-3">
            <h3 class="title m-0">
                {{ labels('front_messages.what_are_you_looking_for?', 'What are you looking for?') }}</h3>
            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="search-body">
            <!--Search Field-->
            <div class="d-flex searchField">
                <div class="input-box d-flex fl-1">
                    <input type="text" wire:model.live.debounce.350ms="search" wire:key="search-input"
                        class="input-text border-end-0 search_text searchInput rounded-end"
                        placeholder="Search for products..." value="{{ $search }}" name="search" id="search"
                        autocomplete="off" />
                    <button
                        class="action search d-flex-justify-center btn rounded-start-0 bySearch btn-secondary">
                        <ion-icon wire:ignore class="icon fs-6" wire:loading.class="d-none" name="search-outline"></ion-icon>
                        <div class="spinner-border text-white d-none" wire:loading.class.remove="d-none" role="status">
                            <span class="sr-only"></span>
                          </div>
                        </button>
                </div>
            </div>
            <!--End Search Field-->
            <!--Search popular-->
            <div class="popular-searches d-flex-justify-center mt-3">
                <span class="title fw-600">{{ labels('front_messages.trending_now', 'Trending Now') }}:</span>
                <div class="d-flex-wrap searches-items">
                    @foreach ($topCategories as $topCategory)
                        <a wire:navigate class="text-link ms-2"
                            href="{{ customUrl('categories/' . $topCategory->slug . '/products') }}">{!! $topCategory->name !!}</a>
                    @endforeach
                </div>
            </div>
            <!--End Search popular-->
            <!--Search products-->
            <div class="search-products search-product-box">
                <ul class="items g-2 g-md-3 row row-cols-lg-4 row-cols-md-3 row-cols-sm-2">
                    @if (count($search_products) < 1 && count($combo_search_products) < 1)
                        <li class="item empty w-100 text-center text-muted">
                            {{ labels('front_messages.You_dont_have_any_items_in_your_search.', 'You don\'t have any items in your search.') }}
                        </li>
                    @else
                        <li class="item empty w-100 text-center bySearch"><span class="cursor-pointer">
                                {{ labels('front_messages.search_product_for', 'Search Product For') }} <span
                                    class="text-underline">{{ $search }}</span>
                        </li>
                        @foreach ($search_products as $product)
                            @php
                                $pro_img = dynamic_image($product->image, 120);
                            @endphp
                            <li class="item">
                                <div class="row mini-list-item clearfix">
                                    <div class="mini-image text-center col-3"><a class="item-link"
                                            href="{{ customUrl('products/' . $product->slug) }}"><img
                                                class="blur-up lazyload" data-src="{{ $pro_img }}"
                                                src="{{ $pro_img }}" alt="product"
                                                title="{!! $product->name !!}" /></a>
                                    </div>
                                    <div class="details text-left col-9">
                                        <div class="product-name fw-500"><a class="item-title text-ellipsis"
                                                href="{{ customUrl('products/' . $product->slug) }}"
                                                title="{!! $product->name !!}">{!! $product->name !!}</a>
                                        </div>
                                        <a href="{{ customUrl('categories/' . $product->category_slug . '/products') }}"
                                            class="text-ellipsis" title="{!! $product->category_name !!}"><ion-icon
                                                name="layers-outline"
                                                class="custom-icon fs-6 me-1"></ion-icon>{!! $product->category_name !!}
                                        </a>
                                        <div class="product-review d-flex align-items-center justify-content-start">
                                            <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                                class="kv-ltr-theme-svg-star rating-loading d-none"
                                                value="{{ $product->rating }}" dir="ltr" data-size="xs"
                                                data-show-clear="false" data-show-caption="false" readonly>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                        @foreach ($combo_search_products as $combo_product)
                            @php
                                $pro_img = dynamic_image($combo_product->image, 120);
                            @endphp
                            <li class="item">
                                <div class="row mini-list-item clearfix">
                                    <div class="mini-image text-center col-3"><a class="item-link"
                                            href="{{ customUrl('combo-products/' . $combo_product->slug) }}"><img
                                                class="blur-up lazyload" data-src="{{ $pro_img }}"
                                                src="{{ $pro_img }}" alt="prduct"
                                                title="{!! $combo_product->title !!}" /></a>
                                    </div>
                                    <div class="details text-left col-9">
                                        <div class="product-name fw-500"><a class="item-title text-ellipsis"
                                                href="{{ customUrl('combo-products/' . $combo_product->slug) }}"
                                                title="{!! $combo_product->title !!}">{!! $combo_product->title !!}</a>
                                        </div>
                                        <p class="m-0">{{ labels('front_messages.combo_product', 'Combo Product') }}
                                        </p>
                                        <div class="product-review d-flex align-items-center justify-content-start">
                                            <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                                class="kv-ltr-theme-svg-star rating-loading d-none"
                                                value="{{ $combo_product->rating }}" dir="ltr" data-size="xs"
                                                data-show-clear="false" data-show-caption="false" readonly>
                                        </div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>
        </div>
    </div>
</div>
