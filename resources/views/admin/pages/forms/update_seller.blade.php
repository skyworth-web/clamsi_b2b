@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_seller', 'Update Seller') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.update_seller', 'Update Seller')" :subtitle="labels(
        'admin_labels.empower_your_marketplace_with_seamless_seller_integration',
        'Empower Your Marketplace with Seamless Seller Integration.',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.sellers', 'Sellers'), 'url' => route('sellers.index')],
        ['label' => labels('admin_labels.update_seller', 'Update Seller')],
    ]" />

    <div>
        <form class="form-horizontal form-submit-event submit_form"
            action="{{ route('admin.sellers.update', $seller_data->id) }}" method="POST">
            @method('PUT')
            @csrf
            <input type="hidden" name="edit_store_ids"
                value="{{ isset($store_data[0]->store_id) && !empty($store_data[0]->store_id) ? $store_data[0]->store_id : '' }}">
            <input type="hidden" name="edit_store_id" value="{{ isset($store_id) && !empty($store_id) ? $store_id : '' }}">
            <input type="hidden" name="edit_store_name"
                value="{{ isset($store_data[0]->store_name) && !empty($store_data[0]->store_name) ? $store_data[0]->store_name : '' }}">
            <input type="hidden" name="edit_store_url"
                value="{{ isset($store_data[0]->store_url) && !empty($store_data[0]->store_url) ? $store_data[0]->store_url : '' }}">
            <input type="hidden" name="edit_store_description"
                value="{{ isset($store_data[0]->store_description) && !empty($store_data[0]->store_description) ? $store_data[0]->store_description : '' }}">
            <input type="hidden" name="edit_store_logo"
                value="{{ isset($store_data[0]->logo) && !empty($store_data[0]->logo) ? $store_data[0]->logo : '' }}">
            <input type="hidden" name="edit_store_thumbnail"
                value="{{ isset($store_data[0]->store_thumbnail) && !empty($store_data[0]->store_thumbnail) ? $store_data[0]->store_thumbnail : '' }}">
            <input type="hidden" name="edit_address_proof"
                value="{{ isset($store_data[0]->address_proof) && !empty($store_data[0]->address_proof) ? $store_data[0]->address_proof : '' }}">
            <input type="hidden" name="edit_profile_image"
                value="{{ isset($store_data[0]->edit_profile_image) && !empty($store_data[0]->edit_profile_image) ? $store_data[0]->edit_profile_image : '' }}">
            <input type="hidden" name="edit_authorized_signature"
                value="{{ isset($store_data[0]->authorized_signature) && !empty($store_data[0]->authorized_signature) ? $store_data[0]->authorized_signature : '' }}">
            <input type="hidden" name="edit_national_identity_card"
                value="{{ isset($store_data[0]->national_identity_card) && !empty($store_data[0]->national_identity_card) ? $store_data[0]->national_identity_card : '' }}">

            <textarea cols="20" rows="20" id="cat_data" name="commission_data" class="image-upload-btn"></textarea>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.seller_details', 'Seller Details') }}
                                </h5>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label for="firstName" class="form-label">{{ labels('admin_labels.name', 'Name') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <input class="form-control" type="text" id="name" name="name"
                                            value="{{ isset($seller_data->username) ? $seller_data->username : '' }}"
                                            autofocus />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="phone">{{ labels('admin_labels.mobile', 'Mobile') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <input type="number" id="phone" name="mobile" min='1'
                                                maxlength="16" oninput="validateNumberInput(this)" class="form-control"
                                                placeholder=""
                                                value="{{ isset($seller_data->mobile) ? $seller_data->mobile : '' }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="email">{{ labels('admin_labels.email', 'Email') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" type="email" name="email"
                                                value="{{ isset($seller_data->email) ? $seller_data->email : '' }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6 form-password-toggle">
                                        <label class="form-label"
                                            for="address">{{ labels('admin_labels.address', 'Address') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <textarea name="address" class="form-control" placeholder="Write here your address">{{ isset($seller_data->address) ? $seller_data->address : '' }}</textarea>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.profile_image', 'Profile Image') }}
                                                <span class="text-asterisks text-sm">*</span></label>

                                            <input type="file" class="filepond" name="profile_image"
                                                data-max-file-size="30MB" data-max-files="20" accept="image/*,.webp" />
                                            @if (isset($seller_data->image) && !empty($seller_data->image))
                                                @php
                                                    $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                                                    $imagePath = $isPublicDisk
                                                        ? asset(
                                                            config('constants.SELLER_IMG_PATH') . $seller_data->image,
                                                        )
                                                        : $seller_data->image;
                                                @endphp

                                                <div class="col-md-12">
                                                    <label for="" class="text-danger">*Only Choose When Update is
                                                        necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image store-image-container">
                                                            <div class='image-upload-div'>
                                                                <img src="{{ route('admin.dynamic_image', [
                                                                    'url' => getMediaImageUrl($imagePath),
                                                                    'width' => 150,
                                                                    'quality' => 90,
                                                                ]) }}"
                                                                    alt="user-avatar" class="d-block rounded"
                                                                    id="uploadedAvatar" />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.address_proof', 'Address Proof') }}
                                                <span class="text-asterisks text-sm">*</span></label>

                                            <input type="file" class="filepond" name="address_proof"
                                                data-max-file-size="30MB" data-max-files="20" accept="image/*,.webp" />
                                            @if (isset($store_data[0]->address_proof) && !empty($store_data[0]->address_proof))
                                                @php
                                                    $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                                                    $imagePath = $isPublicDisk
                                                        ? asset(
                                                            config('constants.SELLER_IMG_PATH') .
                                                                $store_data[0]->address_proof,
                                                        )
                                                        : $store_data[0]->address_proof;
                                                @endphp

                                                <div class="col-md-12">
                                                    <label for="" class="text-danger">*Only Choose When Update is
                                                        necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image store-image-container">
                                                            <div class='image-upload-div'>
                                                                <img src="{{ route('admin.dynamic_image', [
                                                                    'url' => getMediaImageUrl($imagePath),
                                                                    'width' => 150,
                                                                    'quality' => 90,
                                                                ]) }}"
                                                                    alt="user-avatar" class="d-block rounded"
                                                                    id="uploadedAvatar" />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.authorized_signature', 'Authorized Signature') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <input type="file" class="filepond" name="authorized_signature" multiple
                                                data-max-file-size="30MB" data-max-files="20" accept="image/*,.webp" />
                                            @if (isset($store_data[0]->authorized_signature) && !empty($store_data[0]->authorized_signature))
                                                @php
                                                    $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                                                    $imagePath = $isPublicDisk
                                                        ? asset(
                                                            config('constants.SELLER_IMG_PATH') .
                                                                $store_data[0]->authorized_signature,
                                                        )
                                                        : $store_data[0]->authorized_signature;
                                                @endphp

                                                <div class="col-md-12">
                                                    <label for="" class="text-danger">*Only Choose When Update is
                                                        necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image store-image-container">
                                                            <div class='image-upload-div'>
                                                                <img src="{{ route('admin.dynamic_image', [
                                                                    'url' => getMediaImageUrl($imagePath),
                                                                    'width' => 150,
                                                                    'quality' => 90,
                                                                ]) }}"
                                                                    alt="user-avatar" class="d-block rounded"
                                                                    id="uploadedAvatar" />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif


                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.comission', 'Commission') }}
                                </h5>
                                <div class="form-group col-md-12">
                                    <label for="commission"
                                        class="col-sm-12 form-label">{{ labels('admin_labels.comission', 'Commission') }}(%)
                                        <small>(Commission(%) to be given to the Super Admin on order item
                                            globally.)</small> </label>

                                    <input type="number" class="form-control" max=100 min=0 id="global_commission"
                                        placeholder="Enter Commission(%) to be given to the Super Admin on order item."
                                        name="global_commission"
                                        value="{{ isset($store_data[0]->commission) ? $store_data[0]->commission : '' }}">
                                </div>
                                @php
                                    $category_html = getCategoriesOptionHtml($categories, $existing_category_ids);
                                @endphp
                                <div class="form-group row">
                                    <label for="commission"
                                        class="col-sm-12 form-label">{{ labels('admin_labels.choose_categories_and_commission', 'Choose Categories & Commission') }}(%)
                                    </label>
                                    <div class="image-upload-btn" id="cat_html">
                                        <?= $category_html ?>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="">
                                        <a href="javascript:void(0)" id="seller_model"
                                            data-seller_id="<?= isset($store_data[0]->seller_id) && !empty($store_data[0]->seller_id) ? $store_data[0]->seller_id : '' ?>"
                                            data-cat_ids="<?= isset($store_data[0]->id) && !empty($store_data[0]->id) && isset($store_data[0]->category_ids) && !empty($store_data[0]->category_ids) ? $store_data[0]->category_ids : '' ?>"
                                            class=" btn text-white btn-primary btn-sm"
                                            title="Manage Categories & Commission" data-bs-target="#set_commission_model"
                                            data-bs-toggle="modal">{{ labels('admin_labels.add_category_comission', 'Add Category Commission') }}</a>
                                    </div>
                                </div>
                                <small>(Commission(%) to be given to the Super Admin on order item
                                    by
                                    Category you select.If you do not set the commission beside category then it
                                    will get global commission other wise particular category commission will be
                                    consider.)</small>

                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.bank_details', 'Bank Details') }}
                                </h5>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-12 form-label">{{ labels('admin_labels.account_number', 'Account Number') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="account_number"
                                                placeholder="Account Number" name="account_number"
                                                value="{{ isset($store_data[0]->account_number) ? $store_data[0]->account_number : '' }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-4 form-label">{{ labels('admin_labels.account_name', 'Account Name') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="account_name"
                                                placeholder="Account Name" name="account_name"
                                                value="{{ isset($store_data[0]->account_name) ? $store_data[0]->account_name : '' }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-4 form-label">{{ labels('admin_labels.bank_name', 'Bank Name') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="bank_name"
                                                placeholder="Bank Name" name="bank_name"
                                                value="{{ isset($store_data[0]->bank_name) ? $store_data[0]->bank_name : '' }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-4 form-label">{{ labels('admin_labels.bank_code', 'Bank Code') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="bank_code"
                                                placeholder="Bank Code" name="bank_code"
                                                value="{{ isset($store_data[0]->bank_code) ? $store_data[0]->bank_code : '' }}">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.store_details', 'Store Details') }}
                                </h5>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="store_name">{{ labels('admin_labels.store_name', 'Store Name') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <input type="text" name="store_name" class="form-control"
                                                placeholder="starbucks"
                                                value="{{ isset($store_data[0]->store_name) && !empty($store_data[0]->store_name) ? $store_data[0]->store_name : '' }}" />
                                        </div>

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="store_url">{{ labels('admin_labels.store_url', 'Store URL') }}
                                        </label>
                                        <div class="input-group input-group-merge">
                                            <input type="text" name="store_url" class="form-control"
                                                placeholder="starbucks" value="{{ $store_data[0]->store_url }}" />
                                        </div>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.logo', 'Logo') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <input type="file" class="filepond" name="store_logo" multiple
                                                data-max-file-size="30MB" data-max-files="20" accept="image/*,.webp" />

                                            @if ($store_data[0]->logo && !empty($store_data[0]->logo))
                                                @php
                                                    $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                                                    $imagePath = $isPublicDisk
                                                        ? asset(
                                                            config('constants.SELLER_IMG_PATH') . $store_data[0]->logo,
                                                        )
                                                        : $store_data[0]->logo;
                                                @endphp

                                                <div class="col-md-12">
                                                    <label for="" class="text-danger">*Only Choose When Update is
                                                        necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image store-image-container">
                                                            <div class='image-upload-div'>
                                                                <img src="{{ route('admin.dynamic_image', [
                                                                    'url' => getMediaImageUrl($imagePath),
                                                                    'width' => 150,
                                                                    'quality' => 90,
                                                                ]) }}"
                                                                    alt="user-avatar" class="d-block rounded mt-2"
                                                                    id="uploadedAvatar" />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif


                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.store_thumbnail', 'Store Thumbnail') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <input type="file" class="filepond" name="store_thumbnail" multiple
                                                data-max-file-size="30MB" data-max-files="20" accept="image/*,.webp" />
                                            @if ($store_data[0]->store_thumbnail && !empty($store_data[0]->store_thumbnail))
                                                @php
                                                    $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                                                    $imagePath = $isPublicDisk
                                                        ? asset(
                                                            config('constants.SELLER_IMG_PATH') .
                                                                $store_data[0]->store_thumbnail,
                                                        )
                                                        : $store_data[0]->store_thumbnail;
                                                @endphp

                                                <div class="col-md-12">
                                                    <label for="" class="text-danger">*Only Choose When Update is
                                                        necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image store-image-container">
                                                            <div class='image-upload-div'>
                                                                <img src="{{ route('admin.dynamic_image', [
                                                                    'url' => getMediaImageUrl($imagePath),
                                                                    'width' => 150,
                                                                    'quality' => 90,
                                                                ]) }}"
                                                                    alt="user-avatar" class="d-block rounded mt-2"
                                                                    id="uploadedAvatar" />
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-company">{{ labels('admin_labels.other_documents', 'Other Documents') }}</label>
                                            <small>({{ $note_for_necessary_documents }})</small>
                                            <input type="file" class="filepond" name="other_documents[]" multiple
                                                data-max-file-size="300MB" data-max-files="200" />
                                        </div>
                                        @php
                                            $other_documents = json_decode($store_data[0]->other_documents);
                                        @endphp

                                        @if (!empty($other_documents))
                                            <label for="" class="text-danger">*Only Choose When Update is
                                                necessary</label>
                                            <div class="container-fluid">
                                                <div class="row g-3">
                                                    @foreach ($other_documents as $row)
                                                        @php
                                                            $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                                                            $imagePath = $isPublicDisk
                                                                ? asset(
                                                                    config('constants.SELLER_IMG_PATH') . '/' . $row,
                                                                )
                                                                : $row;
                                                        @endphp
                                                        <div class="col-md-3 col-sm-6 text-center">
                                                            <div
                                                                class="bg-white grow image rounded shadow text-center p-3 m-2">
                                                                <div class='image-upload-div'>
                                                                    <img class="img-fluid mb-2"
                                                                        src="{{ route('admin.dynamic_image', [
                                                                            'url' => getMediaImageUrl($imagePath),
                                                                            'width' => 150,
                                                                            'quality' => 90,
                                                                        ]) }}"
                                                                        alt="Not Found" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-company">{{ labels('admin_labels.description', 'Description') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <textarea id="basic-default-message" value="" name="description" class="form-control"
                                                placeholder="Write some description here">{{ $store_data[0]->store_description }}</textarea>

                                        </div>
                                    </div>
                                </div>
                                {{-- @dd($store_data[0]->selected_city); --}}
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group city_list_parent">
                                            <label for="city"
                                                class="control-label mb-2 mt-2">{{ labels('admin_labels.city', 'City') }}
                                                <span class='text-asterisks text-xs'>*</span></label>
                                            <select class="form-select city_list" name="city">
                                                @if (isset($store_data[0]->city))
                                                    <option value="{{ $store_data[0]->city }}" selected>
                                                        {{ getDynamicTranslation('cities', 'name', $store_data[0]->city, $language_code) ?? 'Selected City' }}
                                                    </option>
                                                @endif
                                            </select>
                                            {{-- @foreach ($cities as $city)
                                                    <option value="{{ $city['id'] }}"
                                                        {{ isset($store_data[0]->city) && $city['id'] == $store_data[0]->city ? 'selected' : '' }}>
                                                        {{ $city['name'] }}
                                                    </option>
                                                @endforeach --}}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city"
                                                class="control-label mb-2 mt-2">{{ labels('admin_labels.zipcode', 'Zipcode') }}
                                                <span class='text-asterisks text-xs'>*</span></label>
                                            {{-- <select class="form-select zipcode_list" name="zipcode">
                                                @foreach ($zipcodes as $zipcode)
                                                    <option value="{{ $zipcode['id'] }}"
                                                        {{ isset($store_data[0]->zipcode) && $zipcode['id'] == $store_data[0]->zipcode ? 'selected' : '' }}>
                                                        {{ $zipcode['zipcode'] }}
                                                    </option>
                                                @endforeach
                                            </select> --}}
                                            <select class="form-select zipcode_list" name="zipcode">
                                                @if (isset($store_data[0]->zipcode))
                                                    <option value="{{ $store_data[0]->zipcode }}" selected>
                                                        {{ $store_data[0]->selected_zipcode ?? 'Selected Zipcode' }}
                                                    </option>
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="zipcode" class="form-label">
                                                {{ labels('admin_labels.deliverable_type', 'Deliverable Type') }}
                                            </label>
                                            <select class="form-select deliverable_type" name="deliverable_type"
                                                id="deliverable_type">
                                                <option value="1"
                                                    {{ $store_data[0]->deliverable_type == '1' ? 'selected' : '' }}>All
                                                </option>
                                                <option value="2"
                                                    {{ $store_data[0]->deliverable_type == '2' ? 'selected' : '' }}>
                                                    Included</option>
                                                {{-- <option value="3"
                                                    {{ $store_data[0]->deliverable_type == '3' ? 'selected' : '' }}>
                                                    Excluded</option> --}}
                                            </select>
                                            <small class="text-danger d-block mt-1">
                                                <strong>Note:</strong> Changing this setting will affect product
                                                deliverability settings. If you modify this, ensure that product
                                                deliverability settings are updated accordingly, or it may result in errors
                                                where products are marked as non-deliverable.
                                            </small>
                                        </div>
                                    </div>

                                    @php
                                        $zones =
                                            isset($store_data[0]->deliverable_zones) &&
                                            $store_data[0]->deliverable_zones != null
                                                ? explode(',', $store_data[0]->deliverable_zones)
                                                : [];
                                    @endphp

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="zipcodes" class="form-label">
                                                {{ labels('admin_labels.deliverable_zones', 'Deliverable Zones') }}
                                                <span class="text-asterisks text-sm">*</span>
                                            </label>
                                            <select name="deliverable_zones[]" class="search_zone form-select w-100"
                                                multiple id="deliverable_zones"
                                                {{ isset($store_data[0]->deliverable_type) && ($store_data[0]->deliverable_type == 2 || $store_data[0]->deliverable_type == 3) ? '' : 'disabled' }}>
                                                @if (isset($store_data[0]->deliverable_type) &&
                                                        ($store_data[0]->deliverable_type == 2 || $store_data[0]->deliverable_type == 3))
                                                    @php
                                                        $zone_names = fetchDetails(
                                                            'zones',
                                                            '',
                                                            [
                                                                'name',
                                                                'id',
                                                                'serviceable_city_ids',
                                                                'serviceable_zipcode_ids',
                                                            ],
                                                            '',
                                                            '',
                                                            '',
                                                            '',
                                                            'id',
                                                            $zones,
                                                        );

                                                        foreach ($zone_names as $zone) {
                                                            $zone->serviceable_city_names = getCityNamesFromIds(
                                                                $zone->serviceable_city_ids,
                                                                $language_code,
                                                            );
                                                            $zone->serviceable_zipcodes = getZipcodesFromIds(
                                                                $zone->serviceable_zipcode_ids,
                                                            );
                                                        }
                                                    @endphp

                                                    @foreach ($zone_names as $row)
                                                        <option value="{{ $row->id }}"
                                                            {{ in_array($row->id, $zones) ? 'selected' : '' }}>
                                                            ID - {{ $row->id }} | Name -
                                                            {{ getDynamicTranslation('zones', 'name', $row->id, $language_code) }}
                                                            |
                                                            Serviceable Cities:
                                                            {{ implode(', ', $row->serviceable_city_names) }} |
                                                            Serviceable Zipcodes:
                                                            {{ implode(', ', $row->serviceable_zipcodes) }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for=""
                                        class="form-label">{{ labels('admin_labels.status', 'Status') }}
                                    </label>
                                    <div class="mt-2">
                                        <div id="stsatus" class="btn-group" role="group" aria-label="Status">
                                            <label class="btn btn-outline-secondary flex-fill">
                                                <input type="radio" name="status" class="mx-1" value="0"
                                                    <?= isset($store_data[0]->seller_status) && $store_data[0]->seller_status == '0' ? 'Checked' : '' ?>>
                                                Deactive
                                            </label>
                                            <label class="btn btn-outline-primary flex-fill">
                                                <input type="radio" name="status" class="mx-1" value="1"
                                                    <?= isset($store_data[0]->seller_status) && $store_data[0]->seller_status == '1' ? 'Checked' : '' ?>>
                                                Approved
                                            </label>
                                            <label class="btn btn-outline-danger flex-fill">
                                                <input type="radio" name="status" class="mx-1" value="2"
                                                    <?= isset($store_data[0]->seller_status) && $store_data[0]->seller_status == '2' ? 'Checked' : '' ?>>
                                                Not-Approved
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        class="form-label">{{ labels('admin_labels.store_status', 'Store Status') }}
                                    </label>
                                    <div class="mt-2">
                                        <div id="stsatus" class="btn-group" role="group" aria-label="Status">
                                            <label class="btn btn-outline-primary flex-fill">
                                                <input type="radio" name="store_status" class="mx-1" value="1"
                                                    <?= isset($store_data[0]->status) && $store_data[0]->status == '1' ? 'Checked' : '' ?>>
                                                Approved
                                            </label>
                                            <label class="btn btn-outline-danger flex-fill">
                                                <input type="radio" name="store_status" class="mx-1" value="2"
                                                    <?= isset($store_data[0]->status) && $store_data[0]->status == '2' ? 'Checked' : '' ?>>
                                                Not-Approved
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.other_details', 'Other Details') }}
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_name"
                                                class="form-label">{{ labels('admin_labels.tax_name', 'Tax Name') }}
                                            </label>
                                            <div>
                                                <input type="text" class="form-control" id="tax_name"
                                                    placeholder="Tax Name" name="tax_name"
                                                    value="{{ isset($store_data[0]->tax_name) ? $store_data[0]->tax_name : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_number"
                                                class="form-label">{{ labels('admin_labels.tax_number', 'Tax Number') }}
                                            </label>
                                            <div>
                                                <input type="text" class="form-control" id="tax_number"
                                                    placeholder="Tax Number" name="tax_number"
                                                    value="{{ isset($store_data[0]->tax_number) ? $store_data[0]->tax_number : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pan_number"
                                                class="form-label">{{ labels('admin_labels.pan_number', 'Pan Number') }}</label>
                                            <div>
                                                <input type="text" class="form-control" id="pan_number"
                                                    placeholder="Pan Number" name="pan_number"
                                                    value="{{ isset($store_data[0]->pan_number) ? $store_data[0]->pan_number : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="latitude"
                                                class="form-label">{{ labels('admin_labels.latitude', 'Latitude') }}</label>
                                            <div>
                                                <input type="text" class="form-control" id="latitude"
                                                    placeholder="Latitude" name="latitude"
                                                    value="{{ isset($store_data[0]->latitude) ? $store_data[0]->latitude : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="longitude"
                                                class="form-label">{{ labels('admin_labels.longitude', 'Longitude') }}</label>
                                            <div>
                                                <input type="text" class="form-control" id="longitude"
                                                    placeholder="Longitude" name="longitude"
                                                    value="{{ isset($store_data[0]->longitude) ? $store_data[0]->longitude : '' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="national_identity_card"
                                                class="form-label">{{ labels('admin_labels.national_identity_card', 'National Identity Card') }}
                                            </label>
                                            <div>

                                                <input type="file" class="filepond" name="national_identity_card"
                                                    multiple data-max-file-size="30MB" data-max-files="20"
                                                    accept="image/*,.webp" />
                                                @if ($store_data[0]->national_identity_card && !empty($store_data[0]->national_identity_card))
                                                    @php
                                                        $isPublicDisk = $store_data[0]->disk == 'public' ? 1 : 0;
                                                        $imagePath = $isPublicDisk
                                                            ? asset(
                                                                config('constants.SELLER_IMG_PATH') .
                                                                    $store_data[0]->national_identity_card,
                                                            )
                                                            : $store_data[0]->national_identity_card;
                                                    @endphp

                                                    <div class="col-md-12">
                                                        <label for="" class="text-danger">*Only Choose When Update
                                                            is
                                                            necessary</label>
                                                        <div class="container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-9 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image store-image-container">
                                                                <div class='image-upload-div'>
                                                                    <img src="{{ route('admin.dynamic_image', [
                                                                        'url' => getMediaImageUrl($imagePath),
                                                                        'width' => 150,
                                                                        'quality' => 90,
                                                                    ]) }}"
                                                                        alt="user-avatar" class="d-block rounded"
                                                                        id="uploadedAvatar" />
                                                                </div>

                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @php
                            if (isset($store_data[0]->permissions) && !empty($store_data[0]->permissions)) {
                                $permit = json_decode($store_data[0]->permissions, true);
                            }
                        @endphp

                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">{{ labels('admin_labels.permissions', 'Permissions') }}</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="require_products_approval"
                                                class="col-sm-6 col-form-label">{{ labels('admin_labels.require_product_approvel', 'Require Product Approvel') }}?
                                            </label>
                                            <div class="col-sm-6 form-check form-switch">
                                                <input type="checkbox" class="form-check-input mx-2 float-end"
                                                    <?= isset($permit['require_products_approval']) && $permit['require_products_approval'] == '1' ? 'Checked' : '' ?>
                                                    id="require_products_approval" name="require_products_approval">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="customer_privacy"
                                                class="col-sm-5 col-form-label">{{ labels('admin_labels.view_customer_details', 'View Customer Details') }}?
                                            </label>
                                            <div class="col-sm-7 form-check form-switch">
                                                <input type="checkbox" name="customer_privacy"
                                                    <?= isset($permit['customer_privacy']) && $permit['customer_privacy'] == '1' ? 'Checked' : '' ?>
                                                    class="form-check-input mx-2 float-end">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="view_order_otp"
                                                class="col-sm-8 col-form-label">{{ labels('admin_labels.view_order_otp_and_change_delivery_status', 'View Order OTP & Can Change Delivery Status') }}?
                                            </label>
                                            <div class="col-sm-4 form-check form-switch">
                                                <input type="checkbox" name="view_order_otp"
                                                    <?= isset($permit['view_order_otp']) && $permit['view_order_otp'] == '1' ? 'Checked' : '' ?>
                                                    class="form-check-input mx-2 float-end">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit"
                    class="btn btn-primary submit_button">{{ labels('admin_labels.update', 'Update') }}</button>
            </div>
        </form>
    </div>


    {{-- commission modal --}}

    <div class="modal fade" id="set_commission_model" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Categories & Commission(%)</h5>
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal" id="add-seller-commission-form" action="" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    {{-- <input type="hidden" class="edit_faq_id" name="edit_faq_id"> --}}
                    <div class="modal-body">

                        <label for="Categories"
                            class="col-sm-2 form-label">{{ labels('admin_labels.categories', 'Categories') }}</label>

                        <div id="category_section"> </div>

                        <div class="form-group col-md-12">
                            <button type="button" id="add_category" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus"></i> Add More Category </button>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset"
                            class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                        <button type="submit" class="btn btn-primary submit_button"
                            id="save_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
