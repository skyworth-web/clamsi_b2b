@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.system_settings', 'System Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;

    @endphp

    <x-admin.breadcrumb :title="labels('admin_labels.system_settings', 'System Settings')" :subtitle="labels(
        'admin_labels.fine_tune_platform_dynamics_with_system_settings_mastery',
        'Fine-Tune Platform Dynamics with System Settings Mastery',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.home', 'Home'), 'url' => route('admin.home')],
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.system_settings', 'System Settings')],
    ]" />


    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="tab-content p-4" id="pills-tabContent-vertical-pills">
                    <div class="tab-pane tab-example-design fade show active" id="pills-vertical-pills-design" role="tabpanel"
                        aria-labelledby="pills-vertical-pills-design-tab">
                        <div class="row">

                            <div class="nav flex-column nav-pills p-2" id="v-pills-tab" role="tablist"
                                aria-orientation="vertical">
                                <a class="nav-link active border payment_method_title" data-bs-toggle="pill"
                                    href="#app_setting" role="tab"
                                    aria-selected="true">{{ labels('admin_labels.app_settings', 'App Settings') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#logo_and_storage_setting" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.logo_and_storage', 'Logo & Storage') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#system_setting" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.system_settings', 'System Settings') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#delivery_charge_setting" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.delivery_boy_settings', 'Delivery Boy Settings') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#app_and_system_setting" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.app_and_system_settings', 'App & System Settings') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#refer_and_earn_setting" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.refer_and_earn_settings', 'Refer & Earn Settings') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#welcome_wallet_balance" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.welcome_wallet_balance', 'Welcome Wallet Balance') }}
                                </a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#maintenance_mode" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.maintenence_mode', 'Maintenence Mode') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#authentication_setting" role="tab" aria-controls="v-pills-profile"
                                    aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.authentication_setting', 'Authentication Setting') }}</a>
                                <a class="nav-link border payment_method_title mt-2" id="" data-bs-toggle="pill"
                                    href="#cron_job" role="tab" aria-controls="v-pills-profile" aria-selected="false"
                                    tabindex="-1">{{ labels('admin_labels.cron_job_url', 'Cron Job URL') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <form id="" action="{{ route('system_settings.store') }}" class="submit_form"
                enctype="multipart/form-data" method="POST">
                @csrf
                <div class="card">
                    <div class="tab-content" id="v-pills-tabContent">
                        <div class="tab-pane fade active show" id="app_setting" role="tabpanel">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ labels('admin_labels.app_and_version_settings', 'App & Version Settings') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mt-4">

                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.app_name', 'App Name') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="app_name"
                                                value="<?= isKeySetAndNotEmpty($settings, 'app_name') ? $settings['app_name'] : '' ?>">

                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.support_number', 'Support Number') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" maxlength="16" oninput="validateNumberInput(this)"
                                                class="form-control" id="basic-default-fullname" placeholder=""
                                                name="support_number"
                                                value="<?= isKeySetAndNotEmpty($settings, 'support_number') ? $settings['support_number'] : '' ?>">

                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.support_email', 'Support Email') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="email" class="form-control" id="basic-default-fullname"
                                                placeholder="eshop@gmail.com" name="support_email"
                                                value="<?= isKeySetAndNotEmpty($settings, 'support_email') ? $settings['support_email'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.current_version_of_android_app', 'Current Version Of Android APP') }}(Customer
                                                app)<span class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="current_version_of_android_app"
                                                value="<?= isKeySetAndNotEmpty($settings, 'current_version_of_android_app') ? $settings['current_version_of_android_app'] : '' ?>">

                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.current_version_of_ios_app', 'Current Version Of IOS APP') }}(Customer
                                                app)<span class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="current_version_of_ios_app"
                                                value="<?= isKeySetAndNotEmpty($settings, 'current_version_of_ios_app') ? $settings['current_version_of_ios_app'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.current_version_of_android_app', 'Current Version Of Android APP') }}(Seller
                                                app)<span class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="current_version_of_android_app_for_seller"
                                                value="<?= isKeySetAndNotEmpty($settings, 'current_version_of_android_app_for_seller') ? $settings['current_version_of_android_app_for_seller'] : '' ?>">

                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.current_version_of_ios_app', 'Current Version Of IOS APP') }}(Seller
                                                app)<span class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="current_version_of_ios_app_for_seller"
                                                value="<?= isKeySetAndNotEmpty($settings, 'current_version_of_ios_app_for_seller') ? $settings['current_version_of_ios_app_for_seller'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.current_version_of_android_app', 'Current Version Of Android APP') }}(Delivery
                                                Boy app)<span class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="current_version_of_android_app_for_delivery_boy"
                                                value="<?= isKeySetAndNotEmpty($settings, 'current_version_of_android_app_for_delivery_boy') ? $settings['current_version_of_android_app_for_delivery_boy'] : '' ?>">

                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.current_version_of_ios_app', 'Current Version Of IOS APP') }}(Delivery
                                                Boy app)<span class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="current_version_of_ios_app_for_delivery_boy"
                                                value="<?= isKeySetAndNotEmpty($settings, 'current_version_of_ios_app_for_delivery_boy') ? $settings['current_version_of_ios_app_for_delivery_boy'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.version_system_status', 'Version System Status') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <div class="form-check form-switch mx-5">
                                                <input class="form-check-input" type="checkbox" id=""
                                                    name="version_system_status"
                                                    <?= $settings['version_system_status'] != 'null' && $settings['version_system_status'] == 1 ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="logo_and_storage_setting" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ labels('admin_labels.logo_and_favicon_setting', 'Logo & Favicon Setting') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="col-md-12 form-group">
                                        <label for="image">{{ labels('admin_labels.logo', 'Logo') }}
                                            <span class='text-asterisks text-sm'>*</span></label>
                                        <div>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="file_upload_box border file_upload_border mt-4">
                                                        <div class="mt-2 text-center">
                                                            <a class="media_link" data-input="logo" data-isremovable="0"
                                                                data-is-multiple-uploads-allowed="0"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#media-upload-modal" value="Upload Photo">
                                                                <h4><i class='bx bx-upload'></i> Upload</h4>
                                                            </a>
                                                            <p class="image_recommendation">Recommended
                                                                Size
                                                                : larger than 120 x 120 & smaller than 150 x 150
                                                                pixels.</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    @if (!empty($settings['logo']))
                                                        <label for="" class="text-danger mt-3">*Only Choose When
                                                            Update
                                                            is necessary</label>
                                                        <div class="container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-4 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                <div class=''>
                                                                    <div class='upload-media-div'><img
                                                                            class="img-fluid mb-2"
                                                                            src="{{ asset('storage' . $settings['logo']) }}"
                                                                            alt="Not Found"></div>
                                                                    <input type="hidden" name="logo" id='logo'
                                                                        value='<?= $settings['logo'] ?>'>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @else
                                                        <div class="container-fluid row image-upload-section">
                                                            <div
                                                                class="col-md-4 shadow p-2 mb-5 bg-white rounded m-2 text-center grow image d-none">
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group">
                                        <label for="image">{{ labels('admin_labels.favicon', 'Favicon') }}
                                            <span class='text-asterisks text-sm'>*</span></label>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="file_upload_box border file_upload_border mt-4">
                                                    <div class="mt-2 text-center">
                                                        <a class="media_link" data-input="favicon" data-isremovable="0"
                                                            data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                            data-bs-target="#media-upload-modal" value="Upload Photo">
                                                            <h4><i class='bx bx-upload'></i> Upload
                                                        </a></h4>
                                                        <p class="image_recommendation">Recommended
                                                            Size
                                                            : larger than 120 x 120 & smaller than 150 x 150
                                                            pixels.</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                @if (!empty($settings['favicon']))
                                                    <label for="" class="text-danger mt-3">*Only Choose When
                                                        Update
                                                        is necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-4 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                            <div class=''>
                                                                <div class='upload-media-div'><img class="img-fluid mb-2"
                                                                        src="{{ asset('storage' . $settings['favicon']) }}"
                                                                        alt="Not Found"></div>
                                                                <input type="hidden" name="favicon" id='favicon'
                                                                    value='<?= $settings['favicon'] ?>'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-4 shadow p-2 mb-5 bg-white rounded m-2 text-center grow image d-none">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                    </div>
                                    <label class="form-label"
                                        for="basic-default-fullname">{{ labels('admin_labels.on_boarding_media_type', 'On Boarding Media Type') }}</label>
                                    <div class="col-md-6">
                                        <select class="form-select form-select-md mb-3"
                                            aria-label=".form-select-md example" name="on_boarding_media_type">
                                            <option value="image"
                                                {{ isset($settings['on_boarding_media_type']) && $settings['on_boarding_media_type'] == 'image' ? 'selected' : '' }}>
                                                Image</option>
                                            <option value="video"
                                                {{ isset($settings['on_boarding_media_type']) && $settings['on_boarding_media_type'] == 'video' ? 'selected' : '' }}>
                                                Video
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <label class="form-label"
                                            for="image">{{ labels('admin_labels.on_boarding_image', 'OnBoarding Image') }}
                                            <span class='text-asterisks text-sm'>*</span><span
                                                class="ms-2">({{ labels('admin_labels.upload_maximum_four_images_for_onboarding', 'Upload maximum 4 images for onboarding') }})</span></label>
                                        <div class="row">
                                            <div class="form-group">
                                                <a class="media_link" data-input="on_boarding_image[]"
                                                    data-media_type='image' data-isremovable="1"
                                                    data-is-multiple-uploads-allowed="1" data-max_files_allow="4"
                                                    data-bs-toggle="modal" data-bs-target="#media-upload-modal"
                                                    value="Upload Photo">

                                                    <div class="col-md-12 file_upload_box border file_upload_border">
                                                        <div class="mt-2">
                                                            <div class="col-md-12  text-center">
                                                                <div>
                                                                    <p class="caption text-dark-secondary">Choose
                                                                        images for onboarding.</p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </a>
                                                @if (!empty($settings['on_boarding_image']))
                                                    <label for="" class="text-danger mt-3">*Only Choose When
                                                        Update is
                                                        necessary</label>

                                                    <div class="container-fluid row image-upload-section">
                                                        @foreach ($settings['on_boarding_image'] as $row)
                                                            <div
                                                                class="bg-white grow image product-image-container rounded shadow text-center m-2">
                                                                <div class='image-upload-div'>
                                                                    <img class="img-fluid mb-2" alt="Not Found"
                                                                        src="{{ asset('storage' . $row) }}" />
                                                                </div>
                                                                <a href="javascript:void(0)" class="delete-onboard-media"
                                                                    data-field="on_boarding_image" data-img="<?= $row ?>"
                                                                    data-table="settings" data-path="<?= $row ?>"
                                                                    data-isjson="true">
                                                                    <span
                                                                        class="btn btn-block bg-gradient-danger text-danger btn-xs"><i
                                                                            class="far fa-trash-alt "></i>
                                                                        Delete</span></a>
                                                                <input type="hidden" name="on_boarding_image[]"
                                                                    value='<?= $row ?>'>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div class="row mt-3 image-upload-section">
                                                    </div>
                                                @endif


                                            </div>
                                        </div>
                                    </div>


                                    <div class="col-md-12 form-group">
                                        <label class="form-label"
                                            for="image">{{ labels('admin_labels.on_boarding_video', 'OnBoarding Video') }}
                                            <span class='text-asterisks text-sm'>*</span><span
                                                class="ms-2">({{ labels('admin_labels.upload_maximum_four_videos_for_onboarding', 'Upload maximum 4 videos for onboarding') }})</span></label>
                                        <div class="form-group">
                                            <a class="media_link" data-input="on_boarding_video[]"
                                                data-media_type='video' data-isremovable="1"
                                                data-is-multiple-uploads-allowed="1" data-bs-toggle="modal"
                                                data-bs-target="#media-upload-modal" value="Upload Photo">

                                                <div class="col-md-12 file_upload_box border file_upload_border">
                                                    <div class="mt-2">
                                                        <div class="col-md-12  text-center">
                                                            <div>
                                                                <p class="caption text-dark-secondary">Choose
                                                                    videos for onboarding.</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </a>

                                            @if (!empty($settings['on_boarding_video']))
                                                <label for="" class="text-danger mt-3">*Only Choose When Update is
                                                    necessary</label>

                                                <div class="container-fluid row image-upload-section">
                                                    @foreach ($settings['on_boarding_video'] as $row)
                                                        <div
                                                            class="bg-white grow image product-image-container rounded shadow text-center m-2">
                                                            <div class='image-upload-div'>
                                                                <img class="img-fluid mb-2"
                                                                    src='{{ config('app.url') . 'assets/admin/images/video-file.png' }}'
                                                                    alt="Not Found" />
                                                            </div>
                                                            <a href="javascript:void(0)" class="delete-onboard-media"
                                                                data-field="on_boarding_video" data-img="<?= $row ?>"
                                                                data-table="settings" data-path="<?= $row ?>"
                                                                data-isjson="true">
                                                                <span
                                                                    class="btn btn-block bg-gradient-danger text-danger btn-xs"><i
                                                                        class="far fa-trash-alt "></i> Delete</span></a>
                                                            <input type="hidden" name="on_boarding_video[]"
                                                                value='<?= $row ?>'>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="row mt-3 image-upload-section">
                                                </div>
                                            @endif

                                        </div>
                                    </div>

                                    <label class="form-label"
                                        for="basic-default-fullname">{{ labels('admin_labels.storage_setting', 'Storage Setting') }}</label>
                                    <div class="col-md-6">
                                        <select class="form-select form-select-md mb-3"
                                            aria-label=".form-select-md example" name="storage_type">
                                            <option value="local"
                                                {{ isKeySetAndNotEmpty($settings, 'storage_type') && $settings['storage_type'] == 'local' ? 'selected' : '' }}>
                                                Local
                                            </option>
                                            <option value="aws_s3"
                                                {{ isset($settings['storage_type']) && !empty($settings['storage_type']) && $settings['storage_type'] == 'aws_s3' ? 'selected' : '' }}>
                                                AWS S3
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="system_setting" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ labels('admin_labels.system_settings', 'System Settings') }}
                                    </h5>

                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.system_timezone', 'System Timezone') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <select id="system_timezone" name="system_timezone" required
                                                class="form-control form-select col-md-12 select2">
                                                <option value=" ">
                                                    {{ labels('admin_labels.select_timezone', 'Select Timezone') }}
                                                </option>
                                                @foreach ($timezone as $t)
                                                    <option value="{{ $t['zone'] }}"
                                                        data-gmt="{{ $t['diff_from_GMT'] }}"
                                                        {{ isset($settings['system_timezone']) && $settings['system_timezone'] == $t['zone'] ? 'selected' : '' }}>
                                                        {{ $t['zone'] . ' - ' . $t['diff_from_GMT'] . ' - ' . $t['time'] }}
                                                    </option>
                                                @endforeach

                                            </select>
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.minimum_cart_amount', 'Minimum Cart Amount') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="minimum_cart_amount" min=1
                                                value="<?= isKeySetAndNotEmpty($settings, 'minimum_cart_amount') ? $settings['minimum_cart_amount'] : '' ?>">

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.maximum_item_allowed_in_cart', 'Maximum Items Allowed In Cart') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="maximum_item_allowed_in_cart"
                                                value="<?= isKeySetAndNotEmpty($settings, 'maximum_item_allowed_in_cart') ? $settings['maximum_item_allowed_in_cart'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.low_stock_limit', 'Low stock limit') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="low_stock_limit"
                                                value="<?= isKeySetAndNotEmpty($settings, 'low_stock_limit') ? $settings['low_stock_limit'] : '' ?>">

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.max_days_to_return_item', 'Max days to return item') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" class="form-control" id="basic-default-fullname" min=1
                                                placeholder="" name="max_days_to_return_item"
                                                value="<?= isKeySetAndNotEmpty($settings, 'max_days_to_return_item') ? $settings['max_days_to_return_item'] : '' ?>">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="delivery_charge_setting" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">

                                    <h5> {{ labels('admin_labels.delivery_boy_settings', 'Delivery Boy Setting') }}
                                    </h5>
                                </div>
                                <div class="card-body">

                                    <div class="row mt-4">
                                        <div class="mb-3 col-md-6 d-flex align-items-center">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.order_delivery_otp_system', 'Order Delivery OTP System') }}
                                                <span class='text-asterisks text-sm'>*</span>
                                            </label>
                                            <div class="form-check form-switch mx-8">
                                                <input class="form-check-input" type="checkbox" id=""
                                                    name="order_delivery_otp_system"
                                                    <?= $settings['order_delivery_otp_system'] != 'null' && $settings['order_delivery_otp_system'] == 1 ? 'checked' : '' ?>>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.delivery_boy_bonus', 'Delivery Boy Bonus') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="delivery_boy_bonus"
                                                value="<?= isKeySetAndNotEmpty($settings, 'delivery_boy_bonus') ? $settings['delivery_boy_bonus'] : '' ?>">

                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="app_and_system_setting" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">

                                    <h5>{{ labels('admin_labels.app_and_system_settings', 'App & System Setting') }}
                                    </h5>
                                </div>
                                <div class="card-body">

                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.tax_name', 'Tax Name') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="tax_name"
                                                value="<?= isKeySetAndNotEmpty($settings, 'tax_name') ? $settings['tax_name'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.tax_number', 'Tax Number') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="text" class="form-control" id="basic-default-fullname"
                                                placeholder="" name="tax_number"
                                                value="<?= isKeySetAndNotEmpty($settings, 'tax_number') ? $settings['tax_number'] : '' ?>">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="mb-3 col-md-6 d-none">
                                            <div class="row align-items-center">
                                                <div class="col-md-9">
                                                    <label class="form-label"
                                                        for="enable_cart_button_on_product_list_view">
                                                        {{ labels('admin_labels.enable_cart_button_on_product_list_view', 'Enable Cart Button on Products List view?') }}
                                                        <span class='text-asterisks text-sm'>*</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="enable_cart_button_on_product_list_view"
                                                            name="enable_cart_button_on_product_list_view"
                                                            <?= $settings['enable_cart_button_on_product_list_view'] != 'null' && $settings['enable_cart_button_on_product_list_view'] == 1 ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <div class="row align-items-center">
                                                <div class="col-md-9">
                                                    <label class="form-label" for="expand_product_image">
                                                        {{ labels('admin_labels.expand_product_image', 'Expand Product Images?') }}
                                                        <span class='text-asterisks text-sm'>*</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="expand_product_image" name="expand_product_image"
                                                            <?= $settings['expand_product_image'] != 'null' && $settings['expand_product_image'] == 1 ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <h6 class="mb-4">
                                        {{ labels('admin_labels.social_login', 'Social Login') }}
                                    </h6>

                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <label class="form-label" for="google">
                                                        {{ labels('admin_labels.google', 'google') }}
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input mx-2" type="checkbox"
                                                            id="google" name="google"
                                                            <?= $settings['google'] != 'null' && $settings['google'] == 1 ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-12">
                                                    <label class="form-label d-flex align-items-center" for="facebook">
                                                        {{ labels('admin_labels.facebook', 'Facebook') }}
                                                        <span class="">(Only For Web)</span>
                                                    </label>
                                                </div>

                                                <div class="col-md-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input mx-2" type="checkbox"
                                                            id="facebook" name="facebook"
                                                            <?= $settings['facebook'] != 'null' && $settings['facebook'] == 1 ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- Apple -->
                                        <div class="mb-3 col-md-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-4">
                                                    <label class="form-label" for="apple">
                                                        {{ labels('admin_labels.apple', 'Apple') }}
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input mx-2" type="checkbox"
                                                            id="apple" name="apple"
                                                            <?= $settings['apple'] != 'null' && $settings['apple'] == 1 ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="refer_and_earn_setting" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">

                                    <h5>{{ labels('admin_labels.refer_and_earn_settings', 'Refer & Earn Settings') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="refer_and_earn_status">
                                                        {{ labels('admin_labels.refer_and_earn_status', 'Refer & Earn Status?') }}
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="refer_and_earn_status" name="refer_and_earn_status"
                                                            <?= $settings['refer_and_earn_status'] != 'null' && $settings['refer_and_earn_status'] == 1 ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.minimum_refer_and_earn_amount', 'Minimum Refer & Earn Order Amount') }}</label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="minimum_refer_and_earn_amount"
                                                value="<?= isKeySetAndNotEmpty($settings, 'minimum_refer_and_earn_amount') ? $settings['minimum_refer_and_earn_amount'] : '' ?>">

                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.minimum_refer_and_earn_bonus', 'Minimum Refer & Earn Bonus') }}</label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="minimum_refer_and_earn_bonus"
                                                value="<?= isKeySetAndNotEmpty($settings, 'minimum_refer_and_earn_bonus') ? $settings['minimum_refer_and_earn_bonus'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.refer_and_earn_method', 'Refer & Earn Method') }}</label>
                                            <select class="form-select form-select-md mb-3"
                                                aria-label=".form-select-md example" name="refer_and_earn_method">
                                                <option
                                                    <?= $settings['refer_and_earn_method'] != null && $settings['refer_and_earn_method'] == 'percentage' ? 'selected' : '' ?>
                                                    value="percentage">
                                                    Percentage
                                                </option>
                                                <option
                                                    <?= $settings['refer_and_earn_method'] != null && $settings['refer_and_earn_method'] == 'amount' ? 'selected' : '' ?>
                                                    value="amount">
                                                    {{ labels('admin_labels.amount', 'Amount') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.max_refer_and_earn_amount', 'Maximum Refer & Earn Amount') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="max_refer_and_earn_amount"
                                                value="<?= isKeySetAndNotEmpty($settings, 'max_refer_and_earn_amount') ? $settings['max_refer_and_earn_amount'] : '' ?>">

                                        </div>
                                        <div class="mb-3 col-md-6">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.number_of_times_bonus_given_to_customer', 'Number of times Bonus to be given to the customer') }}<span
                                                    class='text-asterisks text-sm'>*</span></label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="number_of_times_bonus_given_to_customer"
                                                value="<?= isKeySetAndNotEmpty($settings, 'number_of_times_bonus_given_to_customer') ? $settings['number_of_times_bonus_given_to_customer'] : '' ?>">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="welcome_wallet_balance" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">

                                    <h5 class>
                                        {{ labels('admin_labels.welcome_wallet_balance', 'Welcome Wallet Balance') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <div class="row align-items-center">
                                                <div class="col-md-6">
                                                    <label class="form-label" for="wallet_balance_status">
                                                        {{ labels('admin_labels.wallet_balance_status', 'Wallet Balance Status?') }}
                                                        <span class='text-asterisks text-sm'>*</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="wallet_balance_status" name="wallet_balance_status"
                                                            <?= $settings['wallet_balance_status'] != 'null' && $settings['wallet_balance_status'] == 1 ? 'checked' : '' ?>>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="mb-3 col-md-6" id="delivery_charge_amount_field">
                                            <label class="form-label" for="basic-default-fullname">
                                                {{ labels('admin_labels.wallet_balance_amount', 'Wallet Balance Amount') }}
                                                <span class='text-asterisks text-sm'>*</span>
                                            </label>
                                            <input type="number" min=1 class="form-control" id="basic-default-fullname"
                                                placeholder="" name="wallet_balance_amount"
                                                value="<?= isKeySetAndNotEmpty($settings, 'wallet_balance_amount') ? $settings['wallet_balance_amount'] : '' ?>">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="maintenance_mode" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">

                                    <h5>{{ labels('admin_labels.maintenence_mode', 'Maintenence Mode') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <div class="row">
                                                <label class="form-label col-md-4" for="customer_app_maintenance_status">
                                                    {{ labels('admin_labels.customer_app', 'Customer App') }}
                                                </label>
                                                <div class="form-check form-switch col-md-6">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="customer_app_maintenance_status"
                                                        name="customer_app_maintenance_status"
                                                        <?= $settings['customer_app_maintenance_status'] != 'null' && $settings['customer_app_maintenance_status'] == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <div class="row">
                                                <label class="form-label col-md-4" for="seller_app_maintenance_status">
                                                    {{ labels('admin_labels.seller_app', 'Seller App') }}
                                                </label>
                                                <div class="form-check form-switch col-md-6">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="seller_app_maintenance_status"
                                                        name="seller_app_maintenance_status"
                                                        <?= $settings['seller_app_maintenance_status'] != 'null' && $settings['seller_app_maintenance_status'] == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-3 col-md-4">
                                            <div class="row">
                                                <label class="form-label col-md-6"
                                                    for="delivery_boy_app_maintenance_status">
                                                    {{ labels('admin_labels.delivery_boy_app', 'Delivery boy App') }}
                                                </label>
                                                <div class="form-check form-switch col-md-6">
                                                    <input class="form-check-input" type="checkbox"
                                                        id="delivery_boy_app_maintenance_status"
                                                        name="delivery_boy_app_maintenance_status"
                                                        <?= $settings['delivery_boy_app_maintenance_status'] != 'null' && $settings['delivery_boy_app_maintenance_status'] == 1 ? 'checked' : '' ?>>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="row">
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.message_for_customer_app', 'Message for Customer App') }}</label>
                                            <textarea type="text" class="form-control" id="basic-default-fullname" placeholder=""
                                                name="message_for_customer_app" value=""><?= isKeySetAndNotEmpty($settings, 'message_for_customer_app') ? $settings['message_for_customer_app'] : '' ?></textarea>

                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.message_for_seller_app', 'Message for Seller App') }}</label>
                                            <textarea type="text" class="form-control" id="basic-default-fullname" placeholder=""
                                                name="message_for_seller_app" value=""><?= isKeySetAndNotEmpty($settings, 'message_for_seller_app') ? $settings['message_for_seller_app'] : '' ?></textarea>

                                        </div>
                                        <div class="mb-3 col-md-4">
                                            <label class="form-label"
                                                for="basic-default-fullname">{{ labels('admin_labels.message_for_delivery_boy_app', 'Message for Delivery Boy App') }}</label>
                                            <textarea type="text" class="form-control" id="basic-default-fullname" placeholder=""
                                                name="message_for_delivery_boy_app" value=""><?= isKeySetAndNotEmpty($settings, 'message_for_delivery_boy_app') ? $settings['message_for_delivery_boy_app'] : '' ?></textarea>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="authentication_setting" role="tabpanel"
                            aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ labels('admin_labels.authentication_setting', 'Authentication Setting') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="authentication_method" value="firebase"
                                                    id="firebase_radio_button"
                                                    <?= $settings['authentication_method'] == 'firebase' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="firebase_radio_button">
                                                    Firebase Authentication
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="authentication_method" value="sms" id="sms_radio_button"
                                                    <?= $settings['authentication_method'] == 'sms' ? 'checked' : '' ?>>
                                                <label class="form-check-label" for="sms_radio_button">
                                                    Custom SMS Gateway OTP based
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mt-2">
                                            <div class="firebase_config d-none">
                                                <a href="{{ route('firebase') }}">
                                                    <p class="text-danger">Please config firebase config here *</p>
                                                </a>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mt-2">
                                            <div class="sms_gateway d-none">
                                                <a href="{{ route('sms_gateway') }}">
                                                    <p class="text-danger"> Please config SMS gateway config here * </p>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="cron_job" role="tabpanel" aria-labelledby="v-pills-profile-tab">
                            <div class="card">
                                <div class="card-header">
                                    <h5>{{ labels('admin_labels.cron_job_url', 'Cron Job URL') }}
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="mb-3 col-md-6">
                                            <div class="row align-items-center">
                                                <label class="form-label col-md-6" for="basic-default-fullname">
                                                    {{ labels('admin_labels.seller_comission', 'Seller Commission') }}
                                                </label>
                                                <div class="col-md-6">
                                                    <a class="btn btn-xs mx-2 btn-primary text-white mb-2"
                                                        data-bs-toggle="modal" data-bs-target="#howItWorksModal"
                                                        title="How it works">{{ labels('admin_labels.how_it_works', 'How It Works') }}?</a>
                                                </div>
                                            </div>
                                            <input type="text" disabled class="form-control"
                                                id="basic-default-fullname" placeholder="" name="seller_commission"
                                                value="{{ config('app.url') . 'admin/cronjob/settleSellerCommission' }}">
                                        </div>

                                        <div class="mb-3 col-md-6">
                                            <div class="row align-items-center">
                                                <label class="form-label col-md-6" for="basic-default-fullname">
                                                    {{ labels('admin_labels.promo_code_discount', 'Promo Code Discount') }}
                                                </label>
                                                <div class="col-md-6">
                                                    <a class="btn btn-xs mx-2 btn-primary text-white mb-2"
                                                        data-bs-toggle="modal" data-bs-target="#howItWorksModal1"
                                                        title="How it works">{{ labels('admin_labels.how_it_works', 'How It Works') }}?</a>
                                                </div>
                                            </div>
                                            <input type="text" disabled class="form-control"
                                                id="basic-default-fullname" placeholder="" name="promocode_discount"
                                                value="{{ config('app.url') . 'admin/cronjob/settleCashbackDiscount' }}">
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- How Promo Code Discount Modal -->
                        <div class="modal fade" id="howItWorksModal1" tabindex="-1" role="dialog"
                            aria-labelledby="howItWorksModalLabel1" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="howItWorksModalLabel1">How Promo Code
                                            Discount will get credited?</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <ol>
                                            <li>Cron job must be set on your server for Promo Code Discount
                                                to
                                                work.</li>
                                            <li>Cron job will run every midnight at 12:00 AM.</li>
                                            <li>Formula for Add Promo Code Discount is <b>Sub total
                                                    (Excluding
                                                    delivery charge) - promo code discount percentage /
                                                    Amount</b></li>
                                            <li>For example, if the sub total is 1300 and promo code
                                                discount is
                                                100, then 1300 - 100 = 1200, so 100 will get credited into
                                                the
                                                User's wallet</li>
                                            <li>If Order status is delivered and Return Policy is expired,
                                                then
                                                only users will get Promo Code Discount.</li>
                                            <li>Ex - 1: Order placed on 10-Sep-22 and return policy days are
                                                set
                                                to 1, so 10-Sep + 1 day = 11-Sep. Promo code discount will
                                                get
                                                credited on 11-Sep-22 at 12:00 AM (Midnight)</li>
                                            <li>If Promo Code Discount doesn't work, make sure the cron job
                                                is
                                                set properly and it is working. If you don't know how to set
                                                a
                                                cron job for once in a day, please take help from server
                                                support
                                                or do a search for it.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- How Seller Commission Modal -->
                        <div class="modal fade" id="howItWorksModal" tabindex="-1" role="dialog"
                            aria-labelledby="howItWorksModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title" id="howItWorksModalLabel">How seller
                                            commission will get credited?</h4>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <ol>
                                            <li>Cron job must be set (For once in a day) on your server for
                                                seller commission to work.</li>
                                            <li>Cron job will run every midnight at 12:00 AM.</li>
                                            <li>Formula for seller commission is <b>Sub total (Excluding
                                                    delivery charge) / 100 * seller commission
                                                    percentage</b>
                                            </li>
                                            <li>For example, if the sub total is 1378 and seller commission
                                                is
                                                20%, then 1378 / 100 * 20 = 275.6, so 1378 - 275.6 = 1102.4
                                                will
                                                get credited into the seller's wallet</li>
                                            <li>If Order item's status is delivered, then only the seller
                                                will
                                                get commission.</li>
                                            <li>Ex - 1: Order placed on 11-Aug-21 and product return days
                                                are
                                                set to 0, so 11-Aug + 0 days = 11-Aug. Seller commission
                                                will
                                                get credited on 12-Aug-21 at 12:00 AM (Midnight)</li>
                                            <li>Ex - 2: Order placed on 11-Aug-21 and product return days
                                                are
                                                set to 7, so 11-Aug + 7 days = 18-Aug. Seller commission
                                                will
                                                get credited on 19-Aug-21 at 12:00 AM (Midnight)</li>
                                            <li>If seller commission doesn't work, make sure the cron job is
                                                set
                                                properly and it is working. If you don't know how to set a
                                                cron
                                                job for once in a day, please take help from server support
                                                or
                                                do a search for it.</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end  mt-4">
                        <button type="reset"
                            class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                        <button type="submit"
                            class="btn btn-primary submit_button">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
