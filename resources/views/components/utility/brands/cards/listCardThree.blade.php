@props(['brands', 'language_code'])
<div class="container-fluid">
    <div class="collection-style1 row col-row row-cols-xl-8 row-cols-lg-auto row-cols-md-4 row-cols-3">
        @if (count($brands) >= 1)
            @foreach ($brands as $brand)
                <div class="category-item col-item">
                    @php
                        $brand_img = dynamic_image($brand->image, 400);
                    @endphp
                    <a wire:navigate href="{{ customUrl('products/?brand=' . $brand->slug) }}"
                        class="category-link clr-none brand-item">
                        <div class="brands-image zoom-scal zoom-scal-nopb rounded-circle">
                            <img class="blur-up rounded-circle lazyload" data-src="{{ $brand_img }}"
                                src="{{ $brand_img }}"
                                alt="{{ getDynamicTranslation('brands', 'name', $brand->id, $language_code) }}"
                                title="{{ getDynamicTranslation('brands', 'name', $brand->id, $language_code) }}" />
                        </div>
                        <div class="details mt-3 d-flex justify-content-center align-items-center">
                            <h4 class="category-title mb-0">
                                {{ getDynamicTranslation('brands', 'name', $brand->id, $language_code) }}</h4>
                        </div>
                    </a>
                </div>
            @endforeach
        @endif
    </div>
</div>
