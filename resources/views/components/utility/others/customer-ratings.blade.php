<div>
    <div class="row">
        <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-4">
            <div class="ratings-main" wire:ignore>
                <div class="avg-rating d-flex-center mb-3">
                    <h4 class="avg-mark">{{ $product_details->rating }}</h4>
                    <div class="avg-content ms-3">
                        <p class="text-rating">{{ labels('front_messages.average_rating', 'Average Rating') }}</p>
                        <div class="ratings-full product-review">
                            <a class="reviewLink d-flex-center" href="#reviews">
                                <div>
                                    <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                        class="kv-ltr-theme-svg-star rating-loading d-none"
                                        value="{{ $product_details->rating }}" dir="ltr" data-size="l"
                                        data-show-clear="false" data-show-caption="false" readonly>
                                </div>
                                <span class="caption ms-2">{{ $product_details->no_of_ratings }}
                                    {{ labels('front_messages.ratings', 'Ratings') }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                @php
                    $product_ratings = $product_details->product_rating_data;
                    $rating1 = empty($product_ratings['star_1']) ? $product_ratings['star_1'] : 0;
                    $rating2 = empty($product_ratings['star_2']) ? $product_ratings['star_2'] : 0;
                    $rating3 = empty($product_ratings['star_3']) ? $product_ratings['star_3'] : 0;
                    $rating4 = empty($product_ratings['star_4']) ? $product_ratings['star_4'] : 0;
                    $rating5 = empty($product_ratings['star_5']) ? $product_ratings['star_5'] : 0;

                    $ratings = [
                        'star_1' => $rating1,
                        'star_2' => $rating2,
                        'star_3' => $rating3,
                        'star_4' => $rating4,
                        'star_5' => $rating5,
                    ];

                    $highest = max($rating1, $rating2, $rating3, $rating4, $rating5);
                    $highest_rating_key = array_search($highest, $ratings);
                @endphp
                <div class="ratings-list">
                    <div class="ratings-container d-flex align-items-center mt-1">
                        <div class="ratings-full product-review m-0">
                            <a class="reviewLink d-flex align-items-center" href="#reviews">
                                <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                    class="kv-ltr-theme-svg-star rating-loading" value="5" dir="ltr"
                                    data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
                            </a>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="99" aria-valuemin="0"
                                aria-valuemax="100"
                                style="width:{{ $highest != 0 ? ($rating5 / $highest) * 100 : 0 }}%;">
                            </div>
                        </div>
                        <div class="progress-value">{{ $product_ratings['star_5'] }}</div>
                    </div>
                    <div class="ratings-container d-flex align-items-center mt-1">
                        <div class="ratings-full product-review m-0">
                            <a class="reviewLink d-flex align-items-center" href="#reviews">
                                <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                    class="kv-ltr-theme-svg-star rating-loading" value="4" dir="ltr"
                                    data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
                            </a>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                aria-valuemax="100"
                                style="width:{{ $highest != 0 ? ($rating4 / $highest) * 100 : 0 }}%;">
                            </div>
                        </div>
                        <div class="progress-value">{{ $product_ratings['star_4'] }}</div>
                    </div>
                    <div class="ratings-container d-flex align-items-center mt-1">
                        <div class="ratings-full product-review m-0">
                            <a class="reviewLink d-flex align-items-center" href="#reviews">
                                <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                    class="kv-ltr-theme-svg-star rating-loading" value="3" dir="ltr"
                                    data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
                            </a>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="50" aria-valuemin="0"
                                aria-valuemax="100"
                                style="width:{{ $highest != 0 ? ($rating3 / $highest) * 100 : 0 }}%;">
                            </div>
                        </div>
                        <div class="progress-value">{{ $product_ratings['star_3'] }}</div>
                    </div>
                    <div class="ratings-container d-flex align-items-center mt-1">
                        <div class="ratings-full product-review m-0">
                            <a class="reviewLink d-flex align-items-center" href="#reviews">
                                <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                    class="kv-ltr-theme-svg-star rating-loading" value="2" dir="ltr"
                                    data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
                            </a>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="25" aria-valuemin="0"
                                aria-valuemax="100"
                                style="width:{{ $highest != 0 ? ($rating2 / $highest) * 100 : 0 }}%;">
                            </div>
                        </div>
                        <div class="progress-value">{{ $product_ratings['star_2'] }}</div>
                    </div>
                    <div class="ratings-container d-flex align-items-center mt-1">
                        <div class="ratings-full product-review m-0">
                            <a class="reviewLink d-flex align-items-center" href="#reviews">
                                <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                    class="kv-ltr-theme-svg-star rating-loading" value="1" dir="ltr"
                                    data-size="xs" data-show-clear="false" data-show-caption="false" readonly>
                            </a>
                        </div>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" aria-valuenow="5" aria-valuemin="0"
                                aria-valuemax="100"
                                style="width:{{ $highest != 0 ? ($rating1 / $highest) * 100 : 0 }}%;">
                            </div>
                        </div>
                        <div class="progress-value">{{ $product_ratings['star_1'] }}</div>
                    </div>
                </div>
            </div>
            @if ($customer_reviews != [])
                <hr class="light-hr" />
                {{-- <div class="spr-reviews"> --}}
                <div>
                    <h3 class="spr-form-title">{{ labels('front_messages.customer_reviews', 'Customer Reviews') }}
                    </h3>
                    <x-utility.others.ratingCard :$customer_reviews />
                    @if ($product_details->no_of_ratings >= 4)
                        <a href="/products/{{ $product_details->slug }}/reviews" wire:navigate
                            class="d-flex justify-content-center align-content-center pt-3 fw-500 fs-6 text-danger">{{ labels('front_messages.view_all', 'View All') }}
                            {{ $product_details->no_of_ratings }}
                            {{ labels('front_messages.reviews', 'Reviews...') }}</a>
                    @endif
                </div>
            @endif
        </div>
        @auth
            @if ($product_details->is_purchased == true)
                <div class="col-12 col-sm-12 col-md-12 col-lg-6 mb-4" wire:ignore>
                    <form wire:submit="save_review" class="product-review-form new-review-form"
                        enctype="multipart/form-data">
                        @if ($review_id != '')
                            <h3 class="spr-form-title">{{ labels('front_messages.edit_review', 'Edit Review') }}</h3>
                        @else
                            <h3 class="spr-form-title">{{ labels('front_messages.write_review', 'Write a Review') }}</h3>
                        @endif
                        <fieldset class="row spr-form-contact">
                            <div class="col-12 spr-form-review-body form-group">
                                <label class="spr-form-label"
                                    for="message">{{ labels('front_messages.title', 'Title') }}</label>
                                <div class="spr-form-input">
                                    <input wire:model="title" class="spr-form-input spr-form-input-textarea" id="title" name="title"
                                        rows="3"></input>
                                    @error('title')
                                        <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-sm-6 spr-form-review-rating form-group">
                                <input type="hidden" name="id" value="">
                                <label class="spr-form-label">{{ labels('front_messages.rating', 'Rating') }}</label>
                                <div class="product-review pt-1">
                                    <div class="review-rating">
                                        <input id="input-3-ltr-star-md" name="input-3-ltr-star-md"
                                            class="kv-ltr-theme-svg-star star-rating rating-loading review_rating"
                                            value="" wire:model="rating" dir="ltr" data-size="s"
                                            data-show-clear="false" data-show-caption="false" data-step="1">
                                    </div>
                                    @error('rating')
                                        <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 spr-form-review-body form-group">
                                <label class="spr-form-label"
                                    for="add_image">{{ labels('front_messages.add_image_or_video', 'Add Image') }}</label>
                                <input wire:model="images" id="review_image" type="file" name="image[]" multiple
                                    accept="image/gif, image/jpeg, image/png">
                            </div>
                            @error('images')
                                <p class="fw-400 text-danger mt-1"></p>
                            @enderror
                            <div class="col-12 spr-form-review-body form-group">
                                <label class="spr-form-label"
                                    for="message">{{ labels('front_messages.description', 'Description') }}</label>
                                <div class="spr-form-input">
                                    <textarea wire:model="comment" class="spr-form-input spr-form-input-textarea" id="message" name="message"
                                        rows="3"></textarea>
                                    @error('comment')
                                        <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </fieldset>
                        <div class="spr-form-actions clearfix">
                            <input type="submit" class="btn btn-primary spr-button spr-button-primary"
                                value="Submit Review" />
                        </div>
                    </form>
                </div>
            @endauth
        @endif
    </div>
</div>
