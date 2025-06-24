@props(['sub_categories','language_code'])
{{-- <div class="collection-slider-6items gp10 slick-arrow-dots sub-collection section pt-0"> --}}
<div class="swiper sub_category-mySwiper mb-2">
    <div class="swiper-wrapper">
        @foreach ($sub_categories as $sub_category)
            <a href="{{ customUrl('categories/' . $sub_category->slug . '/products') }}"
                class="swiper-slide category-link clr-none">
                <div class="zoom-scal zoom-scal-nopb rounded-0 sub_categories_image"><img
                        class="rounded-0 blur-up lazyload" data-src="{{ asset('storage/' . $sub_category->image) }}"
                        src="{{ asset('storage/' . $sub_category->image) }}" alt="{{ getDynamicTranslation('categories', 'name', $sub_category->id, $language_code) }}"
                        title="{{ getDynamicTranslation('categories', 'name', $sub_category->id, $language_code) }}" width="365" height="365" /></div>
                <div class="details text-center">
                    <h4 class="category-title mb-0">
                        {{ getDynamicTranslation('categories', 'name', $sub_category->id, $language_code) }}
                    </h4>
                </div>
            </a>
        @endforeach
    </div>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
</div>
{{-- </div> --}}
