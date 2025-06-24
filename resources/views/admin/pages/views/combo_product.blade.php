@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.view_product', 'View Combo Product') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.view_product', 'View Product')" :subtitle="labels('admin_labels.view_product_details_and_analytics', 'View Product Details & Analytics')" :breadcrumbs="[
        ['label' => labels('admin_labels.products', 'Products')],
        ['label' => labels('admin_labels.view_product', 'View Product')],
    ]" />
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-1">
                        <div class="product-image-thumbs">
                            @php
                                $other_images = json_decode($data->other_images);
                            @endphp
                            @if (!empty($other_images))
                                @foreach ($other_images as $row)
                                    <div class="mb-2">

                                        <a href="{{ asset('/storage/' . $row) }}" data-lightbox="image-{{ $data->id }}">
                                            <img src="{{ route('admin.dynamic_image', [
                                                'url' => getMediaImageUrl($row),
                                                'width' => 100,
                                                'quality' => 100,
                                            ]) }}"
                                                alt="Avatar" class="" />
                                        </a>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="text-center">
                            <div class="tab-pane active" id="">
                                <div>
                                    <img src="{{ route('admin.dynamic_image', [
                                        'url' => getMediaImageUrl($data->image),
                                        'width' => 500,
                                        'quality' => 100,
                                    ]) }}"
                                        alt="Avatar" class="" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-between">
                            <h3 class="mb-3">
                                {{ getDynamicTranslation('combo_products', 'title', $data->id, $language_code) }}</h3>
                        </div>
                        <div class="d-flex justify-content-between ">

                            <div class="d-flex mt-4">
                                @if ($data->special_price && $data->special_price != 0)
                                    <p class="special_price mx-2">
                                        {{ $data->special_price }}
                                    </p>
                                    <p class="main_price text-decoration-line-through">
                                        {{ $data->price }}
                                    </p>
                                @else
                                    <p class="">
                                        {{ $data->price }}
                                    </p>
                                @endif
                            </div>



                            <div class="d-flex mb-3">
                                @if ($data->rating > 0)
                                    <div id="" data-rating="{{ $data->rating }}" data-rateyo-read-only="true"
                                        class="rateYo bookrating"></div>
                                    <p>{{ isset($rating['no_of_rating']) && $rating['no_of_rating'] > 0 && !empty($rating['no_of_rating']) ? '( ' . $rating['no_of_rating'] . ' reviews)' : '' }}
                                    </p>
                                @endif
                            </div>
                        </div>
                        <p> {{ getDynamicTranslation('combo_products', 'short_description', $data->id, $language_code) }}
                        </p>
                        <h6>{{ labels('admin_labels.product_information', 'Product Information') }}</h6>
                        <div class="d-flex ">
                            <p class="me-3 mb-1 product_information_label">{{ labels('admin_labels.sku', 'Sku') }} :
                            </p>
                            <p class="order_page_title mb-0">
                                {{ isset($data->sku) ? $data->sku : '' }}</p>
                        </div>
                        <div class="d-flex ">
                            <p class="me-3 mb-1 product_information_label">{{ labels('admin_labels.tags', 'Tags') }} :
                            </p>
                            <p class="order_page_title">
                                {{ isset($data->tags) ? $data->tags : '' }}</p>
                        </div>
                        <div class="d-flex">
                            <p class="me-3 mb-1 product_information_label">
                                {{ labels('admin_labels.cancelable', 'Cancelable') }} :</p>
                            <p class="order_page_title mb-0">
                                {{ isset($data->is_cancelable) && $data->is_cancelable == 1 ? 'Yes' : 'No' }}
                            </p>
                        </div>
                        <div class="d-flex">
                            <p class="me-3 mb-1 product_information_label">
                                {{ labels('admin_labels.returnable', 'Returnable') }} :</p>
                            <p class="order_page_title mb-0">
                                {{ isset($data->is_returnable) && $data->is_returnable == 1 ? 'Yes' : 'No' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if ($attributes !== [])
        <div class="card mt-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <h6 class="mb-4">
                            {{ labels('admin_labels.attributes', 'Attributes') }}
                        </h6>
                        <div class="table-responsive">
                            <table class='table' id="admin_variant_table" data-toggle="table"
                                data-loading-template="loadingTemplate" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true" data-export-types='["txt","excel"]'
                                data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-field="id" data-sortable="true">
                                            {{ labels('admin_labels.id', 'ID') }}
                                        </th>
                                        <th data-field="price" data-sortable="false">
                                            {{ labels('admin_labels.values', 'Values') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $i = 1; @endphp
                                    @foreach ($attributes as $attribute_data)
                                        <tr>
                                            <td>{{ $attribute_data->attribute_value_id }}</td>
                                            <td>{{ $attribute_data->value . ' | ' . $attribute_data->name }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <div class="card mt-4">
        <div class="card-body">
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active tab_link" id="product-detail-tab" data-bs-toggle="tab"
                        data-bs-target="#product-detail" type="button" role="tab" aria-controls="product-detail"
                        aria-selected="true">{{ labels('admin_labels.product_details', 'Product Details') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab_link" id="product-review-tab" data-bs-toggle="tab"
                        data-bs-target="#product-review" type="button" role="tab" aria-controls="product-review"
                        aria-selected="false">{{ labels('admin_labels.product_reviews', 'Product Reviews') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link tab_link" id="product-faqs-tab" data-bs-toggle="tab"
                        data-bs-target="#product-faqs" type="button" role="tab" aria-controls="product-faqs"
                        aria-selected="false">{{ labels('admin_labels.product_faqs', 'Product FAQs') }}</button>
                </li>
            </ul>
            <div class="tab-content" id="myTabContent">
                <div class="tab-pane fade show active mt-4" id="product-detail" role="tabpanel"
                    aria-labelledby="product-detail-tab">
                    {!! $data->description != '' ? $data->description : $data->extra_description !!}
                </div>
                <div class="tab-pane fade" id="product-review" role="tabpanel" aria-labelledby="product-review-tab">
                    @foreach ($rating['product_rating'] as $product_rating)
                        @php
                            $user_profile =
                                !empty($product_rating->user_profile) &&
                                file_exists(
                                    public_path(config('constants.USER_IMG_PATH') . $product_rating->user_profile),
                                )
                                    ? getMediaImageUrl($product_rating->user_profile, 'USER_IMG_PATH')
                                    : getImageUrl('no-user-img.jpeg', '', '', 'image', 'NO_USER_IMAGE');
                        @endphp
                        <div class="p-4">
                            <img class="avatar rounded-circle avatar-sm mx-2" src="{{ $user_profile }}"
                                alt="User Profile">
                            <p class="mt-2 mx-2">{{ $product_rating->user_name ?? '' }}</p>
                            <div id="" data-rating="{{ $product_rating->rating ?? '0' }}"
                                data-rateyo-read-only="true" class="rateYo bookrating"></div>
                            <p class="mt-2">{{ $product_rating->comment ?? '' }}</p>
                            @if (!empty($product_rating->images))
                                <div class="mt-2">
                                    @foreach ($product_rating->images as $image)
                                        <img src="{{ $image }}" alt="Review Image" class="img-thumbnail"
                                            style="max-width: 100px;">
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="tab-pane fade" id="product-faqs" role="tabpanel" aria-labelledby="product-faqs-tab">
                    <div class="accordion accordion-flush mt-4" id="accordionFlushExample">
                        @foreach ($product_faqs['data'] as $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="flush-heading{{ $faq->id }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#flush-collapse{{ $faq->id }}" aria-expanded="false"
                                        aria-controls="flush-collapse{{ $faq->id }}">
                                        {{ $faq->question }}
                                    </button>
                                </h2>
                                <div id="flush-collapse{{ $faq->id }}" class="accordion-collapse collapse"
                                    aria-labelledby="flush-heading{{ $faq->id }}"
                                    data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">{{ $faq->answer }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
