@props(['categories', 'language_code'])
<div class="container-fluid">
    <div class="collection-masonary grid-mr-20">
        <div class="grid-masonary">
            <div class="grid-sizer col-12 col-sm-6 col-md-6 col-lg-4"></div>

            @if ($categories['countRes'] >= 1)
                @foreach ($categories['categories'] as $category)
                    <div class="collection-style4 row m-0">
                        <div class="category-item col-12 col-sm-6 col-md-6 col-lg-4 col-item zoomscal-hov masonary-item">
                            <a wire:navigate href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                                class="category-link clr-none">
                                <div class="overlay-image"></div>

                                @php
                                    $category_banner = dynamic_image($category->banner, 650);
                                @endphp

                                <div class="zoom-scal zoom-scal-nopb rounded-0 category-image">
                                    <img class="rounded-0 blur-up lazyload w-100"
                                        data-src="{{ $category_banner }}"
                                        src="{{ $category_banner }}"
                                        alt="{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}"
                                        title="{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}" />
                                </div>

                                <div class="details">
                                    <h3 class="category-title mb-0 text-white fs-4">{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}</h3>
                                    <span class="btn btn-secondary btn-sm">
                                        {{ labels('front_messages.shop_now', 'Shop Now') }}
                                    </span>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
