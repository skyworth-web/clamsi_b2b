@props(['category'])
@php
   $store_settings = getStoreSettings();
@endphp
<div class="swiper-slide slider-brand zoomscal-hov rounded-4">
    <a wire:navigate href="{{ customUrl('categories/' . $category->slug . '/products') }}"
        class="category-link clr-none brand-box slider-link"
        data-link="{{ customUrl('categories/' . $category->slug . '/products') }}">
        <div class="zoom-scal zoom-scal-nopb {{ ($store_settings['category_card_style'] == 'category_card_style_1') ? "img-box-rectangle-h150" : "img-box-h140"}} {{($store_settings['category_card_style'] == 'category_card_style_3') ? "rounded-circle" : "" }}"><img class="blur-up lazyload" data-src="{{ $category->image }}"
                src="{{ $category->image }}" alt="{!! $category->name !!}" title="" /></div>
        @if ($store_settings['category_style'] != 'category_style_2')
            <div class="details text-center">
                <h4 class="category-title mb-0 fs-6 fw-600 text-capitalize">{!! $category->name !!}</h4>
            </div>
        @endif
    </a>
</div>
