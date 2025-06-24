@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_promo_code', 'Update PromoCode') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.update_promo_code', 'Update PromoCode')" :subtitle="labels(
        'admin_labels.boost_sales_with_seamless_and_strategic_promocode_management',
        'Boost Sales with Seamless and Strategic Promocode Management',
    )" :breadcrumbs="[['label' => labels('admin_labels.update_promo_code', 'Update PromoCode')]]" />


    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <form class="form-horizontal form-submit-event submit_form"
                    action="{{ url('admin/promo_codes/update/' . $data->id) }}" method="POST">
                    @method('PUT')
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="language-nav-link nav-link active" id="tab-en" data-bs-toggle="tab"
                                        data-bs-target="#content-en" type="button" role="tab"
                                        aria-controls="content-en" aria-selected="true">
                                        {{ labels('admin_labels.default', 'Default') }}
                                    </button>
                                </li>
                                @foreach ($languages as $lang)
                                    @if ($lang->code !== 'en')
                                        <li class="nav-item" role="presentation">
                                            <button class="language-nav-link nav-link" id="tab-{{ $lang->code }}"
                                                data-bs-toggle="tab" data-bs-target="#content-{{ $lang->code }}"
                                                type="button" role="tab" aria-controls="content-{{ $lang->code }}"
                                                aria-selected="false">
                                                {{ $lang->language }}
                                            </button>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>

                            <div class="tab-content mt-3" id="UpdatebrandTabsContent">
                                <!-- Default 'en' tab content -->
                                <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                    aria-labelledby="tab-en">
                                    <div class="mb-3">
                                        <label for="brand_name" class="form-label">
                                            {{ labels('admin_labels.title', 'Title') }}
                                            <span class="text-asterisks text-sm">*</span>
                                        </label>
                                        <input type="text" class="form-control" placeholder="Title" name="title"
                                            value="{{ isset($data->title) ? json_decode($data->title)->en : '' }}">

                                        <label for="message" class="control-label mb-2 mt-2">
                                            {{ labels('admin_labels.message', 'Message') }}
                                            <span class='text-asterisks text-sm'>*</span>
                                        </label>
                                        <input type="text" class="form-control" name="message"
                                            value="{{ isset($data->message) ? json_decode($data->message)->en : '' }}"
                                            placeholder="Message">
                                    </div>
                                </div>

                                <!-- Dynamic Language Tabs -->
                                @foreach ($languages as $lang)
                                    @if ($lang->code !== 'en')
                                        <div class="tab-pane fade" id="content-{{ $lang->code }}" role="tabpanel"
                                            aria-labelledby="tab-{{ $lang->code }}">
                                            <div class="mb-3">
                                                <label for="translated_title_{{ $lang->code }}" class="form-label">
                                                    {{ labels('admin_labels.title', 'Title') }} ({{ $lang->language }})
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="translated_promocode_title[{{ $lang->code }}]"
                                                    value="{{ isset($data->title) ? json_decode($data->title, true)[$lang->code] ?? '' : '' }}">
                                            </div>

                                            <div class="mb-3">
                                                <label for="translated_message_{{ $lang->code }}" class="form-label">
                                                    {{ labels('admin_labels.message', 'Message') }}
                                                    ({{ $lang->language }})
                                                </label>
                                                <input type="text" class="form-control"
                                                    name="translated_promocode_message[{{ $lang->code }}]"
                                                    value="{{ isset($data->message) ? json_decode($data->message, true)[$lang->code] ?? '' : '' }}">
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.promo_codes', 'PromoCode') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="text" class="form-control" name="promo_code"
                                    value="{{ isset($data->promo_code) ? $data->promo_code : '' }}">

                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.start_date', 'Start Date') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="date" class="form-control" name="start_date" id="start_date"
                                    value="{{ isset($data->start_date) ? $data->start_date : '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2" for="">{{ labels('admin_labels.end_date', 'End Date') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="date" class="form-control" name="end_date" id="end_date"
                                    value="{{ isset($data->end_date) ? $data->end_date : '' }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.number_of_users', 'No.Of Users') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <input type="number" min=1 class="form-control" name="no_of_users" min=1
                                    value="{{ isset($data->no_of_users) ? $data->no_of_users : '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.minimum_order_amount', 'Minimum Order Amount') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="number" min=1 class="form-control" name="minimum_order_amount"
                                    value="{{ isset($data->minimum_order_amount) ? $data->minimum_order_amount : '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.discount_type', 'Discount Type') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="discount_type" class="form-control form-select discount_type">
                                    <option value="percentage"
                                        {{ isset($data->discount_type) && $data->discount_type == 'percentage' ? 'selected' : '' }}>
                                        Percentage</option>
                                    <option value="amount"
                                        {{ isset($data->discount_type) && $data->discount_type == 'amount' ? 'selected' : '' }}>
                                        {{ labels('admin_labels.amount', 'Amount') }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2" for="">{{ labels('admin_labels.discount', 'Discount') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="number" class="form-control discount" min=1 name="discount" id="discount"
                                    value="{{ isset($data->discount) ? $data->discount : '' }}">
                                <div class="error"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.max_discount_amount', 'Max Discount Amount') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <input type="number" class="form-control" name="max_discount_amount" min=1
                                    id="max_discount_amount"
                                    value="{{ isset($data->max_discount_amount) ? $data->max_discount_amount : '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2"
                                    for="">{{ labels('admin_labels.repeat_usage', 'Repeat Usage') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="repeat_usage" id="repeat_usage" class="form-control form-select">
                                    <option value="1"
                                        {{ isset($data->repeat_usage) && $data->repeat_usage == '1' ? 'selected' : '' }}>
                                        Allowed</option>
                                    <option value="0"
                                        {{ isset($data->repeat_usage) && $data->repeat_usage == '0' ? 'selected' : '' }}>
                                        Not Allowed</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3 d-none" id="repeat_usage_html">
                                <label class="mb-2 mt-2" for="">
                                    {{ labels('admin_labels.number_of_repeat_usage', 'Number Of Repeat Usage') }}
                                </label>
                                <input type="number" class="form-control" name="no_of_repeat_usage" min=1
                                    id="no_of_repeat_usage"
                                    value="{{ isset($data->no_of_repeat_usage) ? $data->no_of_repeat_usage : '' }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="mb-2 mt-2" for="">{{ labels('admin_labels.status', 'Status') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <select name="status" id="status" class="form-control form-select">
                                    <option value="">
                                        {{ labels('admin_labels.select', 'Select') }}
                                    </option>
                                    <option value="1"
                                        {{ isset($data->status) && $data->status == '1' ? 'selected' : '' }}>Active
                                    </option>
                                    <option value="0"
                                        {{ isset($data->status) && $data->status == '0' ? 'selected' : '' }}>Deactive
                                    </option>
                                </select>
                            </div>

                            <div class="row">
                                <div class="form-group col-md-6 mb-4">
                                    <label class="mb-2 mt-2" for="image"
                                        class="mb-2">{{ labels('admin_labels.image', 'Image') }}
                                        <span class='text-asterisks text-sm'>*</span>
                                    </label>
                                    <div class="col-md-12">
                                        <div class="row form-group">
                                            <div class="col-md-6 file_upload_box border file_upload_border mt-2">
                                                <div class="mt-2">
                                                    <div class="col-md-12  text-center">
                                                        <div>
                                                            <a class="media_link" data-input="image" data-isremovable="0"
                                                                data-is-multiple-uploads-allowed="0"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#media-upload-modal" value="Upload Photo">
                                                                <h4><i class='bx bx-upload'></i> Upload
                                                            </a></h4>
                                                            <p class="image_recommendation">Recommended Size: 147 x 60
                                                                pixels
                                                            </p>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                @if ($data->image && !empty($data->image))
                                                    <label for="" class="text-danger">*Only Choose When Update is
                                                        necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-6 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                            <div class='image-upload-div'>
                                                                <img class="img-fluid mb-2"
                                                                    src="{{ route('admin.dynamic_image', [
                                                                        'url' => getMediaImageUrl($data->image),
                                                                        'width' => 150,
                                                                        'quality' => 90,
                                                                    ]) }}"
                                                                    alt="Not Found">
                                                            </div>
                                                            <input type="hidden" name="image"
                                                                value='{{ $data->image }}'>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <label class="mb-2 mt-2"
                                                for="is_cashback">{{ labels('admin_labels.is_cashback', 'Is Cashback?') }}</label>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mx-8">
                                                <input class="form-check-input" type="checkbox" id=""
                                                    name="is_cashback"
                                                    <?= $data->is_cashback != 'null' && $data->is_cashback == 1 ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <label class="mb-2 mt-2"
                                                for="is_cashback">{{ labels('admin_labels.list_promocode', 'List PromoCode?') }}</label>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check form-switch mx-8">
                                                <input class="form-check-input" type="checkbox" id=""
                                                    name="list_promocode"
                                                    <?= $data->list_promocode != 'null' && $data->list_promocode == 1 ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3 d-flex justify-content-end">
                            <button type="submit" class="btn btn-sm btn-primary submit_button"
                                id="">{{ labels('admin_labels.update_promo_code', 'Update PromoCode') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
