@extends('delivery_boy/layout')
@section('title')
    {{ labels('admin_labels.account', 'Account') }}
@endsection
@section('content')
    <x-delivery_boy.breadcrumb :title="labels('admin_labels.account_setting', 'Account Setting')" :subtitle="labels(
        'admin_labels.efficiently_manage_account_with_precision',
        'Efficiently Manage Account With Precision',
    )" :breadcrumbs="[['label' => labels('admin_labels.account_setting', 'Account Setting')]]" />
    <div class="col-md-6">

        <div class="card">
            <form class="form-horizontal form-submit-event submit_form"
                action="{{ route('delivery_boy.account.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card-body">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.name', 'Name') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <input type="text" class="form-control" id="name" placeholder="" name="name"
                                value="{{ $user->username }}" readonly>

                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.mobile', 'Mobile') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <input type="text" class="form-control" id="mobile" placeholder="" name="mobile"
                                value="{{ $user->mobile }}" readonly>

                        </div>

                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">

                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.email', 'Email') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <input type="text" class="form-control" id="email" placeholder="" name="email"
                                value="{{ $user->email }}" readonly>

                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.old_password', 'Old Password') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <input type="password" class="form-control" id="basic-default-fullname" placeholder=""
                                name="old_password" value="">

                        </div>


                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.password', 'New Password') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <input type="password" class="form-control" id="basic-default-fullname" placeholder=""
                                name="new_password" value="{{ old('password') }}">


                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.confirm_password', 'Confirm Password') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <input type="password" class="form-control" id="basic-default-fullname" placeholder=""
                                name="confirm_password" value="{{ old('confirm_password') }}">

                        </div>
                    </div>
                    <div class="row">
                        @php
                            $zones = !empty($user->serviceable_zones) ? explode(',', $user->serviceable_zones) : [];
                        @endphp

                        <div class="mb-3 col-md-6">
                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.serviceable_zones', 'Serviceable Zones') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <select name="serviceable_zones[]" class="form-control search_zone w-100" multiple
                                onload="multiselect()">
                                @if (!empty($zones))
                                    @php
                                        $language_code = get_language_code();
                                        $zone_names = fetchDetails(
                                            'zones',
                                            '',
                                            ['name', 'id'],
                                            '',
                                            '',
                                            '',
                                            '',
                                            'id',
                                            $zones,
                                        );
                                    @endphp
                                    @foreach ($zone_names as $row)
                                        <option value="{{ $row->id }}"
                                            @if (in_array($row->id, $zones)) selected @endif>
                                            {{ getDynamicTranslation('zones', 'name', $row->id, $language_code) }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="mb-3 col-md-6">
                            <label class="form-label"
                                for="basic-default-fullname">{{ labels('admin_labels.address', 'Address') }}</label>
                            <textarea type="text" class="form-control" placeholder="" name="address" value="">{{ $user->address }}</textarea>

                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label
                                    for="image">{{ labels('admin_labels.driving_licence_front_image', 'Driving Licence Front Image') }}
                                    <span class='text-asterisks text-sm'>*</span></label>
                                <div class="col-sm-10">

                                    <input type="file" class="filepond" name="front_licence_image" multiple
                                        data-max-file-size="30MB" data-max-files="20" accept="image/*,.webp" />

                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label
                                    for="image">{{ labels('admin_labels.driving_licence_back_image', 'Driving Licence Back Image') }}<span
                                        class='text-asterisks text-sm'>*</span></label>

                                <input type="file" class="filepond" name="back_licence_image" multiple
                                    data-max-file-size="30MB" data-max-files="20" accept="image/*,.webp" />

                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">

                            <label for="" class="text-danger mt-3">*Only Choose When Update is
                                necessary</label>
                            <div class="container-fluid row image-upload-section">
                                <div
                                    class="col-md-6 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                    <div class='image-upload-div'><img class="img-fluid edit_front_licence_image mb-2"
                                            src="{{ getMediaImageUrl($user->front_licence_image, 'DELIVERY_BOY_IMG_PATH') }}"
                                            alt="Not Found">
                                    </div>

                                    <input type="hidden" name="image" value=''>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">

                            <label for="" class="text-danger mt-3">*Only Choose When Update is
                                necessary</label>
                            <div class="container-fluid row image-upload-section">
                                <div
                                    class="col-md-6 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                    <div class='image-upload-div'><img class="img-fluid edit_back_licence_image mb-2"
                                            src="{{ getMediaImageUrl($user->back_licence_image, 'DELIVERY_BOY_IMG_PATH') }}"
                                            alt="Not Found">
                                    </div>

                                    <input type="hidden" name="image" value=''>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 d-flex justify-content-end">
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
