@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.account', 'Account') }}
@endsection
@section('content')
    <section class="main-content">
        <form class="form-horizontal form-submit-event submit_form"
            action="{{ route('seller.account.update', $seller_data->id) }}" method="POST">
            @method('PUT')
            @csrf

            <input type="hidden" name="edit_store_logo" value="{{ $store_data[0]->logo }}">
            <input type="hidden" name="edit_store_thumbnail" value="{{ $store_data[0]->store_thumbnail }}">
            <input type="hidden" name="edit_address_proof" value="{{ $store_data[0]->address_proof }}">
            <input type="hidden" name="edit_authorized_signature" value="{{ $store_data[0]->authorized_signature }}">
            <input type="hidden" name="edit_national_identity_card" value="{{ $store_data[0]->national_identity_card }}">
            <input type="hidden" name="edit_profile_image"
                value="{{ isset($store_data[0]->edit_profile_image) && !empty($store_data[0]->edit_profile_image) ? $store_data[0]->edit_profile_image : '' }}">
            <div class="row position-relative">
                <div class="seller_account_banner_box">

                    <img alt="" src="{{ getMediaImageUrl($store_data[0]->store_thumbnail, 'SELLER_IMG_PATH') }}" />
                </div>
                <div class="form-group mt-2">
                    <a class="btn btn-primary btn-md change_banner_button"><i
                            class="bx bx-camera camera_icon"></i>{{ labels('admin_labels.change_banner', 'Change Banner') }}
                    </a>
                    <input id="store_thumbnail_file_upload" name="store_thumbnail" type="file" class="d-none"
                        accept="image/*">
                </div>
                <div class="container-fluid mt-5 mb-5 px-6">
                    <div class="col-md-12 seller_account_page_card">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-center">

                                            <img class="avatar rounded-circle avatar-xxl"
                                                src="
                                        {{ getMediaImageUrl($store_data[0]->logo, 'SELLER_IMG_PATH') }}"
                                                alt="User">
                                            <div class="camera_icon_div d-flex justify-content-center align-items-center">
                                                <i class="bx bx-camera camera_icon"></i>
                                            </div>

                                            <input id="store_logo_file_upload" name="store_logo" type="file"
                                                class="d-none" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="tab-content p-4" id="pills-tabContent-vertical-pills">
                                        <div class="tab-pane tab-example-design fade show active"
                                            id="pills-vertical-pills-design" role="tabpanel"
                                            aria-labelledby="pills-vertical-pills-design-tab">
                                            <div class="row">
                                                <div class="nav flex-column nav-pills p-0" id="v-pills-tab" role="tablist"
                                                    aria-orientation="vertical">
                                                    <a class="nav-link active border payment_method_title seller_account_tab"
                                                        data-bs-toggle="pill" href="#personal_details" role="tab"
                                                        aria-selected="true">{{ labels('admin_labels.personal_details', 'Personal Details') }}</a>
                                                    <a class="nav-link border payment_method_title seller_account_tab mt-2"
                                                        id="" data-bs-toggle="pill" href="#password_manage"
                                                        role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                                        tabindex="-1">{{ labels('admin_labels.password_manage', 'Password Manage') }}</a>
                                                    <a class="nav-link border payment_method_title seller_account_tab mt-2"
                                                        id="" data-bs-toggle="pill" href="#store_details"
                                                        role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                                        tabindex="-1">{{ labels('admin_labels.store_details', 'Store Details') }}</a>
                                                    <a class="nav-link border payment_method_title seller_account_tab mt-2"
                                                        id="" data-bs-toggle="pill" href="#tax_details"
                                                        role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                                        tabindex="-1">{{ labels('admin_labels.tax_details', 'Tax Details') }}</a>
                                                    <a class="nav-link border payment_method_title seller_account_tab mt-2"
                                                        id="" data-bs-toggle="pill" href="#bank_details"
                                                        role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                                        tabindex="-1">{{ labels('admin_labels.bank_details', 'Bank Details') }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="card">
                                    <div class="tab-content" id="v-pills-tabContent">
                                        <div class="tab-pane fade active show" id="personal_details" role="tabpanel">
                                            <div class="card">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center">
                                                        <h5 class="mb-0">
                                                            {{ labels('admin_labels.personal_details', 'Personal Details') }}
                                                        </h5>
                                                    </div>

                                                </div>

                                                <div class="card-body">
                                                    <div class="row">
                                                        <div class="mb-3 col-md-6">
                                                            <label for="firstName"
                                                                class="form-label">{{ labels('admin_labels.name', 'Name') }}
                                                                <span class="text-danger text-sm">*</span></label>
                                                            <input class="form-control" type="text" id="name"
                                                                name="name"
                                                                value="{{ isset($seller_data->username) ? $seller_data->username : '' }}"
                                                                autofocus />
                                                        </div>
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label"
                                                                for="phone">{{ labels('admin_labels.mobile', 'Mobile') }}
                                                                <span class="text-danger text-sm">*</span></label>
                                                            <div class="input-group input-group-merge">
                                                                <input type="number" id="phone" name="mobile"
                                                                    disabled maxlength="16"
                                                                    oninput="validateNumberInput(this)" min='1'
                                                                    class="form-control" placeholder=""
                                                                    value="{{ isset($seller_data->mobile) ? $seller_data->mobile : '' }}" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="mb-3 col-md-6">
                                                            <label class="form-label"
                                                                for="email">{{ labels('admin_labels.email', 'Email') }}
                                                                <span class="text-danger text-sm">*</span></label>
                                                            <div class="input-group input-group-merge">
                                                                <input class="form-control" type="email" name="email"
                                                                    value="{{ isset($seller_data->email) ? $seller_data->email : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3 col-md-6 form-password-toggle">
                                                            <label class="form-label"
                                                                for="address">{{ labels('admin_labels.address', 'Address') }}
                                                                <span class="text-danger text-sm">*</span></label>
                                                            <textarea name="address" class="form-control" placeholder="Write here your address">{{ isset($seller_data->address) ? $seller_data->address : '' }}</textarea>

                                                        </div>
                                                    </div>
                                                    <div class="row">

                                                        <div class="form-group col-md-4">
                                                            <div class="mb-3">

                                                                <label class="form-label"
                                                                    for="basic-default-phone">{{ labels('admin_labels.address_proof', 'Address Proof') }}
                                                                    <span class="text-danger text-sm">*</span></label>

                                                                <input type="file" class="filepond"
                                                                    name="address_proof" multiple
                                                                    data-max-file-size="30MB" accept="image/*,.webp"
                                                                    data-max-files="20" />
                                                                <img src="
                                                            {{ route('seller.dynamic_image', [
                                                                'url' => getMediaImageUrl($store_data[0]->address_proof, 'SELLER_IMG_PATH'),
                                                                'width' => 100,
                                                                'quality' => 90,
                                                            ]) }}"
                                                                    alt="user-avatar" class="d-block rounded"
                                                                    id="uploadedAvatar" />

                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-4">
                                                            <div class="mb-3">

                                                                <label class="form-label"
                                                                    for="basic-default-phone">{{ labels('admin_labels.authorized_signature', 'Authorized Signature') }}
                                                                    <span class="text-danger text-sm">*</span></label>

                                                                <input type="file" class="filepond"
                                                                    name="authorized_signature" multiple
                                                                    data-max-file-size="30MB" accept="image/*,.webp"
                                                                    data-max-files="20" />
                                                                <img src="{{ route('seller.dynamic_image', [
                                                                    'url' => getMediaImageUrl($store_data[0]->authorized_signature, 'SELLER_IMG_PATH'),
                                                                    'width' => 100,
                                                                    'quality' => 90,
                                                                ]) }}"
                                                                    alt="user-avatar" class="d-block rounded"
                                                                    id="uploadedAvatar" />

                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">

                                                                <label for="national_identity_card"
                                                                    class="form-label">{{ labels('admin_labels.national_identity_card', 'National Identity Card') }}
                                                                    </label>
                                                                <div>
                                                                    <input type="file" class="filepond"
                                                                        name="national_identity_card" multiple
                                                                        data-max-file-size="30MB" accept="image/*,.webp"
                                                                        data-max-files="20" />
                                                                    <img src="
                                                                    {{ route('seller.dynamic_image', [
                                                                        'url' => getMediaImageUrl($store_data[0]->national_identity_card, 'SELLER_IMG_PATH'),
                                                                        'width' => 100,
                                                                        'quality' => 90,
                                                                    ]) }}"
                                                                        alt="user-avatar" class="d-block rounded"
                                                                        id="uploadedAvatar" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="form-group col-md-12">
                                                                <div class="mb-3">
                                                                    <label class="form-label"
                                                                        for="basic-default-company">{{ labels('admin_labels.other_documents', 'Other Documents') }}</label>
                                                                    <small>({{ $note_for_necessary_documents }})</small>
                                                                    <input type="file" class="filepond"
                                                                        name="other_documents[]" multiple
                                                                        data-max-file-size="300MB" data-max-files="200" />
                                                                </div>
                                                                @php
                                                                    $other_documents = json_decode(
                                                                        $store_data[0]->other_documents,
                                                                    );
                                                                @endphp

                                                                @if (!empty($other_documents))
                                                                    <label for="" class="text-danger">*Only Choose
                                                                        When Update is necessary</label>
                                                                    <div class="container-fluid">
                                                                        <div class="row g-3">
                                                                            @foreach ($other_documents as $row)
                                                                                @php
                                                                                    $isPublicDisk =
                                                                                        $store_data[0]->disk == 'public'
                                                                                            ? 1
                                                                                            : 0;
                                                                                    $imagePath = $isPublicDisk
                                                                                        ? asset(
                                                                                            config(
                                                                                                'constants.SELLER_IMG_PATH',
                                                                                            ) .
                                                                                                '/' .
                                                                                                $row,
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
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="password_manage" role="tabpanel"
                                            aria-labelledby="v-pills-profile-tab">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <h5 class="mb-0">
                                                        {{ labels('admin_labels.password_manage', 'Password Manage') }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label"
                                                            for="email">{{ labels('admin_labels.old_password', 'Old Password') }}
                                                            <span class="text-danger text-sm">*</span></label>
                                                        <div class="input-group input-group-merge">
                                                            <input class="form-control" type="password"
                                                                name="old_password">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3 col-md-6 form-password-toggle">
                                                        <label class="form-label" for="password">New Password <span
                                                                class="text-danger text-sm">*</span></label>
                                                        <div class="input-group input-group-merge">
                                                            <input type="password" id="password" class="form-control"
                                                                name="password" placeholder="Enter your password"
                                                                aria-describedby="password" />
                                                            <span
                                                                class="input-group-text cursor-pointer toggle-seller-profile-password"><i
                                                                    class="bx bx-hide"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label" for="password_confirmation">Confirm
                                                            Password <span class="text-danger text-sm">*</span></label>
                                                        <div class="input-group input-group-merge">
                                                            <input type="password" id="password_confirmation"
                                                                class="form-control" name="confirm_password"
                                                                placeholder="Enter your password"
                                                                aria-describedby="password" />
                                                            <span
                                                                class="input-group-text cursor-pointer toggle-seller-profile-password"><i
                                                                    class="bx bx-hide"></i></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="store_details" role="tabpanel"
                                            aria-labelledby="v-pills-profile-tab">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <h5 class="mb-0">
                                                        {{ labels('admin_labels.store_details', 'Store Details') }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label"
                                                            for="store_name">{{ labels('admin_labels.store_name', 'Store Name') }}
                                                            <span class="text-danger text-sm">*</span></label>
                                                        <div class="input-group input-group-merge">
                                                            <input type="text" name="store_name" class="form-control"
                                                                placeholder="starbucks"
                                                                value="{{ $store_data[0]->store_name }}" />
                                                        </div>

                                                    </div>
                                                    <div class="mb-3 col-md-6">
                                                        <label class="form-label"
                                                            for="store_url">{{ labels('admin_labels.store_url', 'Store URL') }}
                                                            <span class="text-danger text-sm">*</span></label>
                                                        <div class="input-group input-group-merge">
                                                            <input type="text" name="store_url" class="form-control"
                                                                placeholder="starbucks"
                                                                value="{{ $store_data[0]->store_url }}" />
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
                                                        <div class="form-group city_list_parent">
                                                            <label for="city"
                                                                class="control-label mb-2 mt-2">{{ labels('admin_labels.city', 'City') }}
                                                                <span class='text-asterisks text-xs'>*</span></label>
                                                            <select class="form-select city_list" name="city">
                                                                {{-- @foreach ($cities as $city)
                                                                    <option value="{{ $city['id'] }}"
                                                                        {{ isset($store_data[0]->city) && $city['id'] == $store_data[0]->city ? 'selected' : '' }}>
                                                                        {{ $city['name'] }}
                                                                    </option>
                                                                @endforeach --}}
                                                                @if (isset($store_data[0]->city))
                                                                    <option value="{{ $store_data[0]->city }}" selected>
                                                                        {{ $store_data[0]->selected_city ?? 'Selected City' }}
                                                                    </option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="city"
                                                                class="control-label mb-2 mt-2">{{ labels('admin_labels.zipcode', 'Zipcode') }}
                                                                <span class='text-asterisks text-xs'>*</span></label>
                                                            <select class="form-select zipcode_list" name="zipcode">
                                                                @if (isset($store_data[0]->zipcode))
                                                                    <option value="{{ $store_data[0]->zipcode }}"
                                                                        selected>
                                                                        {{ $store_data[0]->selected_zipcode ?? 'Selected Zipcode' }}
                                                                    </option>
                                                                @endif
                                                                 {{-- @foreach ($zipcodes as $zipcode)
                                                                    <option value="{{ $zipcode['id'] }}"
                                                                        {{ isset($store_data[0]->zipcode) && $zipcode['id'] == $store_data[0]->zipcode ? 'selected' : '' }}>
                                                                        {{ $zipcode['zipcode'] }}
                                                                    </option>
                                                                @endforeach --}}
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="zipcode"
                                                                class="form-label">{{ labels('admin_labels.deliverable_type', 'Deliverable Type') }}</label>
                                                            <select class="form-select deliverable_type"
                                                                name="deliverable_type" id="deliverable_type">
                                                                <option value="1"
                                                                    {{ $store_data[0]->deliverable_type == '1' ? 'selected' : '' }}>
                                                                    All
                                                                </option>
                                                                <option value="2"
                                                                    {{ $store_data[0]->deliverable_type == '2' ? 'selected' : '' }}>
                                                                    Included
                                                                </option>
                                                                {{-- <option value="3"
                                                                    {{ $store_data[0]->deliverable_type == '3' ? 'selected' : '' }}>
                                                                    Excluded
                                                                </option> --}}
                                                            </select>
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
                                                            <label for="zipcodes"
                                                                class="form-label">{{ labels('admin_labels.deliverable_zones', 'Deliverable Zones') }}
                                                                <span class="text-asterisks text-sm">*</span></label>
                                                            <select name="deliverable_zones[]"
                                                                class="search_zone form-select w-100" multiple
                                                                id="deliverable_zones"
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
                                                                            {{ $row->name }}
                                                                            |
                                                                            Serviceable Cities:
                                                                            {{ implode(', ', $row->serviceable_city_names) }}
                                                                            |
                                                                            Serviceable Zipcodes:
                                                                            {{ implode(', ', $row->serviceable_zipcodes) }}
                                                                        </option>
                                                                    @endforeach
                                                                @endif
                                                            </select>

                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="form-group col-md-12">
                                                        <div class="mb-3">
                                                            <label class="form-label"
                                                                for="basic-default-company">{{ labels('admin_labels.description', 'Description') }}
                                                                <span class="text-danger text-sm">*</span></label>
                                                            <textarea id="basic-default-message" value="" name="description" class="form-control"
                                                                placeholder="Write some description here">{{ $store_data[0]->store_description }}</textarea>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="tax_details" role="tabpanel"
                                            aria-labelledby="v-pills-profile-tab">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <h5 class="mb-0">
                                                        {{ labels('admin_labels.tax_details', 'Tax Details') }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label for="tax_name"
                                                                class="form-label">{{ labels('admin_labels.tax_name', 'Tax Name') }}
                                                                <span class='text-danger text-sm'>*</span></label>
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
                                                                <span class='text-danger text-sm'>*</span></label>
                                                            <div>
                                                                <input type="text" class="form-control"
                                                                    id="tax_number" placeholder="Tax Number"
                                                                    name="tax_number"
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
                                                                <input type="text" class="form-control"
                                                                    id="pan_number" placeholder="Pan Number"
                                                                    name="pan_number"
                                                                    value="{{ isset($store_data[0]->pan_number) ? $store_data[0]->pan_number : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade" id="bank_details" role="tabpanel"
                                            aria-labelledby="v-pills-profile-tab">
                                            <div class="card-header d-flex justify-content-between align-items-center">
                                                <div class="d-flex align-items-center">
                                                    <h5 class="mb-0">
                                                        {{ labels('admin_labels.bank_details', 'Bank Details') }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <div class="mb-3">
                                                            <label for="tax_name"
                                                                class="col-sm-4 form-label">{{ labels('admin_labels.account_number', 'Account Number') }}
                                                                <span class='text-danger text-sm'>*</span></label>

                                                            <input type="text" class="form-control"
                                                                id="account_number" placeholder="Account Number"
                                                                name="account_number"
                                                                value="{{ isset($store_data[0]->account_number) ? $store_data[0]->account_number : '' }}">

                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <div class="mb-3">
                                                            <label for="tax_name"
                                                                class="col-sm-4 form-label">{{ labels('admin_labels.account_name', 'Account Name') }}
                                                                <span class='text-danger text-sm'>*</span></label>

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
                                                                <span class='text-danger text-sm'>*</span></label>

                                                            <input type="text" class="form-control" id="bank_name"
                                                                placeholder="Bank Name" name="bank_name"
                                                                value="{{ isset($store_data[0]->bank_name) ? $store_data[0]->bank_name : '' }}">

                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <div class="mb-3">
                                                            <label for="tax_name"
                                                                class="col-sm-4 form-label">{{ labels('admin_labels.bank_code', 'Bank Code') }}
                                                                <span class='text-danger text-sm'>*</span></label>

                                                            <input type="text" class="form-control" id="bank_code"
                                                                placeholder="Bank Code" name="bank_code"
                                                                value="{{ isset($store_data[0]->bank_code) ? $store_data[0]->bank_code : '' }}">

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-end mt-4">
                                            <button type="submit" class="btn btn-primary submit_button"
                                                id="submit_btn">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection
