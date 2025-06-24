@props(['customer_reviews'])
<div class="review-inner">
    @foreach ($customer_reviews as $reviews)
        @php
            $img = getMediaImageUrl($reviews->user_profile);
            $img = dynamic_image($img, 80);
        @endphp
        <div class="spr-review d-flex w-100">
            <div class="spr-review-profile flex-shrink-0">
                <img class="blur-up lazyload" data-src="{{ $img }}" src="{{ $img }}" alt=""
                    width="200" height="200" />
            </div>
            <div class="spr-review-content flex-grow-1">
                <div class="d-flex justify-content-between flex-column mb-2">
                    <div class="title-review d-flex align-items-center justify-content-between">
                        <h5 class="spr-review-header-title text-transform-none mb-0">
                            {{ $reviews->user_name }}</h5>
                        <span class="product-review spr-starratings m-0 text-end">
                            <span class="reviewLink">
                                <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                    class="kv-ltr-theme-svg-star rating-loading review-star d-none"
                                    value="{{ $reviews->rating }}" dir="ltr" data-size="xs" data-show-clear="false"
                                    data-show-caption="false" readonly>
                            </span>
                        </span>
                    </div>
                </div>
                <p class="spr-review-body">{{ $reviews->comment }}</p>
                <div class="d-flex align-items-center overflow-auto max-w-300px">
                    @php
                        $images = json_decode($reviews->images);
                    @endphp
                    @if ($images != '' || $images != null)
                        @if (count($images) != 0)
                            @foreach ($images as $image)
                                @php
                                    $image = dynamic_image(getMediaImageUrl($image), 80);
                                @endphp
                                <div class="spr-review-profile flex-shrink-0 me-1">
                                    <a href="{{ $image }}" data-lightbox="review-images">
                                        <img class="blur-up lazyload rounded-0" data-src="{{ $image }}"
                                            src="{{ $image }}" alt="" />
                                    </a>
                                </div>
                            @endforeach
                        @endif
                    @endif
                </div>
                @php
                    $show = false;
                @endphp
                @if ($show == true)
                    @if (auth()->id() == $reviews->user_id)
                        <div class="text-end">
                            <a class="text-danger text-underline delete_rating"
                                wire:click="delete_rating">{{ labels('front_messages.delete', 'Delete') }}</a>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
