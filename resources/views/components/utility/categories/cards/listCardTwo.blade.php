@props(['categories', 'language_code'])
<div class="container-fluid">
    <div class="lookbook-grid">
        {{-- <div class="row col-row row-cols-lg-4 row-cols-md-3 row-cols-sm-2 row-cols-1"> --}}
        <div class="row col-row row-cols-lg-5 row-cols-md-4 row-cols-sm-3 row-cols-2">
            @if ($categories['countRes'] >= 1)
                @foreach ($categories['categories'] as $category)
                    <div class="lookbook-item zoomscal-hov col-item">
                        <div class="lookbook-inner rounded-0 category_list_card_2">
                            @php
                                $category_banner = dynamic_image($category->banner, 650);
                            @endphp

                            <a class="zoom rounded-0 d-block zoom-scal zoom-scal-nopb">
                                <a wire:navigate href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                                    class="category-link clr-none">
                                    <img class="rounded-0 blur-up lazyloaded category_list_card_2_image" data-src="{{ $category_banner }}"
                                        src="{{ $category_banner }}" alt="{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}"
                                        title="{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}">
                                </a>
                                <div class="lookbook-caption d-flex-justify-center mainclr">
                                    <a wire:navigate
                                        href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                                        class="content clr-none d-block">
                                        <h5 class="text-1 mb-0">{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}</h5>
                                        @if ($category->product_count > 0)
                                            @if ($category->product_count > 0)
                                                <!-- Hide product count on small screens and show on medium and larger screens -->
                                                <p class="text-2 mt-1 d-none d-md-block">
                                                    {!! $category->product_count !!}
                                                    {{ labels('front_messages.products', 'Products') }}
                                                </p>
                                            @endif
                                        @endif
                                    </a>
                                </div>
                            </a>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
