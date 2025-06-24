@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_offer_slider', 'Update Offer Slider') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.update_offer_slider', 'Update Offer Slider')" :subtitle="labels(
        'admin_labels.captivate_audiences_with_eye_catching_deal_showcases',
        'Captivate Audiences with Eye-Catching Deal Showcases',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.offers', 'Offers')],
        ['label' => labels('admin_labels.update_offer_slider', 'Update Offer Slider')],
    ]" />
    <div class="col-md-12">
        <form id="offer-slider-form" action="{{ url('admin/offer_sliders/update/' . $data->id) }}" class="submit_form"
            enctype="multipart/form-data" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="language-nav-link nav-link active" id="tab-en" data-bs-toggle="tab"
                                        data-bs-target="#content-en" type="button" role="tab"
                                        aria-controls="content-en" aria-selected="true">
                                        {{ labels('admin_labels.default', 'Default') }}
                                    </button>
                                </li>
                                {!! generateLanguageTabsNav($languages) !!}
                            </ul>
                            <div class="tab-content mt-3" id="UpdatebrandTabsContent">
                                <!-- Default 'en' tab content -->
                                <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                    aria-labelledby="tab-en">
                                    <div class="mb-3">
                                        <label for="brand_name"
                                            class="form-label">{{ labels('admin_labels.title', 'Title') }}<span
                                                class="text-asterisks text-sm">*</span></label>

                                        <input type="text" class="form-control" id="basic-default-fullname"
                                            placeholder="Gucci" name="title"
                                            value="{{ isset($data->title) ? json_decode($data->title)->en : '' }}">
                                    </div>
                                </div>
                                {!! generateUpdateableLanguageTabs(
                                    $languages,
                                    $data->title,
                                    'admin_labels.title',
                                    'Title',
                                    'translated_offer_slider_title',
                                ) !!}

                            </div>
                            <div class="mb-3">
                                <label class="form-label"
                                    for="basic-default-fullname">{{ labels('admin_labels.select_offer', 'Select Offer') }}</label>
                                <select name="offer_ids[]" required id="offer_sliders_offer"
                                    class="offer_sliders_offer form-select w-100" multiple
                                    data-placeholder="Type to search and select categories">
                                    @foreach ($offers as $offer)
                                        <option value="{{ $offer->id }}"
                                            {{ in_array($offer->id, explode(',', $data->offer_ids ?? '')) ? 'selected' : '' }}>
                                            {{ $offer->type }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">

                            <div class="form-group col-md-12">
                                <label for=""
                                    class="form-label">{{ labels('admin_labels.banner_image', 'Banner Image') }}<span
                                        class="text-asterisks text-sm">*</span></label>
                                <div class="col-md-12">
                                    <div class="row form-group">
                                        <div class="col-md-6 file_upload_box border file_upload_border mt-2">
                                            <div class="mt-2">
                                                <div class="col-md-12  text-center">
                                                    <div>
                                                        <a class="media_link" data-input="banner_image" data-isremovable="0"
                                                            data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                            data-bs-target="#media-upload-modal" value="Upload Photo">
                                                            <h4><i class='bx bx-upload'></i> Upload
                                                        </a></h4>
                                                        <p class="image_recommendation">Recommended Size: 131 x 131
                                                            pixels</p>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        @if ($data->banner_image && !empty($data->banner_image))
                                            <div class="row col-md-6">
                                                <label for="" class="text-danger">*Only Choose When Update is
                                                    necessary</label>
                                                <div class="container-fluid row image-upload-section">
                                                    <div
                                                        class="col-md-6 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                        <div class='image-upload-div'>
                                                            <img class="img-fluid mb-2" alt=""
                                                                src={{ asset('/storage/' . $data->banner_image) }} />
                                                        </div>
                                                        <input type="hidden" name="banner_image"
                                                            value='<?= $data->banner_image ?>'>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <button type="reset"
                                    class="btn mx-2 reset_button offer_slider_reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.update_offer_slider', 'Update Offer Slider') }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
