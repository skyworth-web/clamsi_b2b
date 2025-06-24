@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.category_order', 'Category Order') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.category_order', 'Category Order')" :subtitle="labels('admin_labels.optimize_and_manage_category_order', 'Optimize and Manage Category Order')" :breadcrumbs="[
        ['label' => labels('admin_labels.categories', 'Categories')],
        ['label' => labels('admin_labels.category_order', 'Category Order')],
    ]" />


    <section class="overview-data">
        <div class="card content-area p-4 ">
            <div class="row">
                <div class="col-md-12 main-content">

                    <div class="col-md-6 heading mb-5">
                        <h4>{{ labels('admin_labels.manage_category_order', 'Manage category Order') }}
                        </h4>
                    </div>
                    <div class="row d-flex justify-content-center">
                        <div class="col-md-8">
                            <div class="row font-weight-bold">
                                <div class="col-3">Row Order</div>
                                <div class="col-3">Image</div>
                                <div class="col-2">Name</div>
                            </div>

                            <div class="category-order-container">
                                @php
                                    $i = 0;
                                @endphp
                                @if (!empty($categories))
                                    @foreach ($categories as $row)
                                        <div class="list-item" id="{{ $row['id'] }}">
                                            <div class="item-content d-flex justify-content-between align-items-center">
                                                <span class="order col-md-4">{{ $i }}</span>
                                                <div class="col-md-4">
                                                    <img alt=""
                                                        src="{{ route('admin.dynamic_image', [
                                                            'url' => getMediaImageUrl($row['image']),
                                                            'width' => 60,
                                                            'quality' => 90,
                                                        ]) }}">
                                                </div>
                                                <span
                                                    class="col-md-4">{{ getDynamicTranslation('categories', 'name', $row['id'], $language_code) }}</span>
                                            </div>
                                        </div>
                                        @php
                                            $i++;
                                        @endphp
                                    @endforeach
                                @endif
                            </div>
                            <div class="d-flex justify-content-end"> <button type="button" class="btn btn-dark mt-4"
                                    id="save_category_order">{{ labels('admin_labels.save', 'Save') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
