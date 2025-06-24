@props(['categories', 'language_code'])
<div class="container-fluid">
    <div class="row col-row masonary-filter portfolio-list">
        @if ($categories['countRes'] >= 1)
            @foreach ($categories['categories'] as $category)
                @php
                    $category_banner = dynamic_image($category->banner, 650);
                @endphp
                <div class="col-6 col-sm-6 col-md-4 col-lg-3 col-item pfashion portfolio-masonary">
                    <a wire:navigate href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                        class="portfolio-item position-relative overflow-hidden overlay d-block portfolio-popup zoomscal-hov">
                        <div class="portfolio-img zoom-scal rounded-0 category_list_card_3">
                            <img class="rounded-0 blur-up lazyloaded category_list_card_3_image" data-src="{{ $category_banner }}"
                                src="{{ $category_banner }}" alt="{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}"
                                title="{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}">

                        </div>
                        <div class="caption rounded-0">
                            <h3 class="text-white mb-2">{{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}</h3>
                        </div>
                    </a>
                </div>
            @endforeach
        @endif
    </div>
</div>
