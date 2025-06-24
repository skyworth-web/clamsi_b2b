@props(['seller'])
@php
    $img = getMediaImageUrl($seller->logo, 'SELLER_IMG_PATH');
    $img = dynamic_image($img, 300);
@endphp
<div class="P-2">
    <a wire:navigate
        href="{{ customUrl('sellers/' . $seller->slug) }}"class="testimonial-slide testimonial-content text-center">
        <div class="d-flex justify-content-center align-item-center seller-image">
            <img class="blur-up lazyload" data-src="{{ $img }}" src="{{ $img }}" alt="quotes" />
        </div>
        <div class="auhimg d-flex-justify-center text-center mt-2">
            <div class="auhtext">
                <h5 class="authour mb-1">{{ $seller->store_name ?? '' }}</h5>
            </div>
        </div>
        <div class="">
            <div class="product-review">
                <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                    class="kv-ltr-theme-svg-star rating-loading d-none" value="{{ $seller->rating }}" dir="ltr"
                    data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
            </div>
        </div>
    </a>
</div>
