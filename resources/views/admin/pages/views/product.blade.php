@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.view_product', 'View Product') }}
@endsection
@section('content')

    <x-admin.breadcrumb :title="labels('admin_labels.view_product', 'View Product')" :subtitle="labels('admin_labels.view_product_details_and_analytics', 'View Product Details & Analytics')" :breadcrumbs="[
        ['label' => labels('admin_labels.products', 'Products')],
        ['label' => labels('admin_labels.view_product', 'View Product')],
    ]" />
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12">
                    <div class="row">
                        <div class="col-md-1">
                            <div class="product-image-thumbs">
                                @php
                                    $other_images = json_decode($data->other_images);
                                @endphp
                                @if (!empty($other_images))
                                    @foreach ($other_images as $row)
                                        <div class="mb-2">

                                            <a href="{{ asset('/storage/' . $row) }}"
                                                data-lightbox="image-{{ $data->id }}">
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
                                    <div class="">
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
                                <p class="mb-4 order_page_title">
                                    @php
                                        $category_name = '';
                                        foreach ($categories as $category) {
                                            if ($category->id == $data->category_id) {
                                                $category_name = getDynamicTranslation(
                                                    'categories',
                                                    'name',
                                                    $category->id,
                                                    $language_code,
                                                );
                                            }
                                        }
                                    @endphp
                                    {{ $category_name }}
                                </p>
                                @if ($sales_count > 0)
                                    <div class="d-flex align-items-center">
                                        <i class='bx bx-cart special_price fs-3 mx-2'></i>
                                        {{ $sales_count }}
                                        {{ labels('admin_labels.customers_ordered', 'Customers Ordered') }}
                                    </div>
                                @endif
                            </div>

                            <h3 class="mb-3">
                                {{ getDynamicTranslation('products', 'name', $data->id, $language_code) }}</h3>

                            <div class="d-flex justify-content-between ">
                                @if ($data->type == 'simple_product')
                                    <div class="d-flex mt-4">
                                        <p class="special_price mx-2">
                                            {{ $product_variants[0]->special_price > 0 ? $product_variants[0]->special_price : $product_variants[0]->price }}
                                        </p>
                                        @if ($product_variants[0]->special_price > 0)
                                            <p class="main_price">
                                                {{ $product_variants[0]->price }}
                                            </p>
                                        @endif
                                    </div>
                                @endif
                                <div class="d-flex mb-3">
                                    @if ($data->rating > 0)
                                        <div id="" data-rating="{{ $data->rating }}" data-rateyo-read-only="true"
                                            class="rateYo bookrating"></div>
                                        <p>{{ isset($rating['no_of_rating']) && $rating['no_of_rating'] > 0 && !empty($rating['no_of_rating']) ? '( ' . $rating['no_of_rating'] . ' reviews)' : '' }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <p> {{ getDynamicTranslation('products', 'short_description', $data->id, $language_code) }}</p>
                            <h6>{{ labels('admin_labels.product_information', 'Product Information') }}</h6>
                            @if (isset($brand_name) && !empty($brand_name))
                                <div class="d-flex mt-2">
                                    <p class="me-3 mb-1 product_information_label">
                                        {{ labels('admin_labels.brand', 'Brand') }}
                                        : </p>
                                    <p class="order_page_title">
                                        {{ isset($brand_name) ? $brand_name : '' }}</p>
                                </div>
                            @endif
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
                            <div class="d-flex">
                                <p class="me-3 mb-1 product_information_label">
                                    {{ labels('admin_labels.warrenty_period', 'Warranty Period') }} :</p>
                                <p class="order_page_title mb-0">
                                    {{ isset($data->warranty_period) && $data->warranty_period != '' ? $data->warranty_period : '' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @if ($data->type == 'variable_product')
            <div class="card mt-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>
                                {{ labels('admin_labels.variants', 'Variants') }}
                            </h6>
                            <div class="table-responsive">
                                <table class='table' id="admin_variant_table" data-toggle="table"
                                    data-loading-template="loadingTemplate" data-click-to-select="true"
                                    data-side-pagination="server" data-pagination="true"
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                    data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                    data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                    data-show-export="false" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="queryParams">
                                    <thead>
                                        <tr>
                                            <th data-field="id" data-sortable="true">
                                                {{ labels('admin_labels.id', 'ID') }}
                                            </th>

                                            <th data-field="variants" data-sortable="false">
                                                {{ labels('admin_labels.variants', 'Variants') }}
                                            </th>
                                            <th data-field="price" data-sortable="false">
                                                {{ labels('admin_labels.price', 'Price') }}
                                            </th>
                                            <th data-field="status" data-sortable="false">
                                                {{ labels('admin_labels.status', 'Status') }}
                                            </th>
                                            <th data-field="action" data-sortable="false">
                                                {{ labels('admin_labels.action', 'Action') }}
                                            </th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php $i = 1; @endphp
                                        @foreach ($product_variants as $row)
                                            @php
                                                $price =
                                                    $row->special_price != null && $row->special_price > 0
                                                        ? $row->special_price
                                                        : $row->price;
                                                $flag = $row->special_price != null && $row->special_price > 0 ? 1 : 0;
                                                $strike_off_price = $flag == 1 ? $row->price : null;
                                            @endphp

                                            <tr>
                                                <td>{{ $i++ }}</td>
                                                <td>{{ $row->attr_name . ' | ' . $row->variant_values }}</td>
                                                <td class="d-flex justify-content-center price_row">
                                                    <div class="mx-2 special_price">{{ $price }}</div>
                                                    <div class="main_price">{{ $strike_off_price }}</div>
                                                </td>

                                                <td>
                                                    <div class="d-flex justify-content-center">
                                                        <p class="mb-0 mr-2">[Enable / Disable]</p>
                                                        <div class="form-switch">
                                                            <input type="checkbox"
                                                                class="form-check-input change_variant_status"
                                                                data-id="{{ $row->id }}"
                                                                data-status="{{ $row->status }}"
                                                                data-product-id="{{ $data->id }}"
                                                                {{ $row->status == 1 ? 'checked' : '' }}>
                                                        </div>
                                                    </div>
                                                </td>
                                                @if ($row->status == 1)
                                                    <td>
                                                        <i class="bx bx-trash mx-2 special_price delete_variant fs-3"
                                                            data-id={{ $row->id }} data-status={{ $row->status }}
                                                            data-product-id={{ $data->id }}></i>
                                                    </td>
                                                @endif
                                                @if ($row->status == 7)
                                                    <td>
                                                        <i class="bx bx-revision mx-2 restore_variant delete_variant fs-3"
                                                            data-id={{ $row->id }} data-status={{ $row->status }}
                                                            data-product-id={{ $data->id }}></i>
                                                    </td>
                                                @endif
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
        @if ($data->type == 'simple_product' && $attribute_values !== [])
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
                                    data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false"
                                    data-show-columns="false" data-show-refresh="false" data-trim-on-search="false"
                                    data-sort-name="id" data-sort-order="desc" data-mobile-responsive="true"
                                    data-toolbar="" data-show-export="false" data-maintain-selected="true"
                                    data-export-types='["txt","excel"]' data-query-params="queryParams">
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
                                        @foreach ($attribute_values as $attribute_data)
                                            @foreach ($attribute_data['attribute_values'] as $key => $value)
                                                <tr>
                                                    <td>{{ $attribute_data['attribute_values_id'][$key] }}</td>
                                                    <td>{{ $value . ' | ' . $attribute_values[0]['attribute_name'] }}</td>
                                                </tr>
                                            @endforeach
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
                            data-bs-target="#product-detail" type="button" role="tab"
                            aria-controls="product-detail"
                            aria-selected="true">{{ labels('admin_labels.product_details', 'Product Details') }}</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link tab_link" id="product-review-tab" data-bs-toggle="tab"
                            data-bs-target="#product-review" type="button" role="tab"
                            aria-controls="product-review"
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
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#flush-collapse{{ $faq->id }}"
                                            aria-expanded="false" aria-controls="flush-collapse{{ $faq->id }}">
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
    </div>
@endsection
