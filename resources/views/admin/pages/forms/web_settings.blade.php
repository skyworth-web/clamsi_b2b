@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.web_settings', 'Web Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="card mb-1">
            <div class="card-body">
                <h4 class="card-title">{{ labels('admin_labels.web_settings', 'Web Settings') }}</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <div class="card sticky-top">
                    <ul class="nav flex-column nav-pills menu payment-sidebar">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" role="tab"
                                href="#general_setting">{{ labels('admin_labels.general_settings', 'General Settings') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab"
                                href="#themes">{{ labels('admin_labels.themes', 'Themes') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab"
                                href="#languages">{{ labels('admin_labels.language', 'Language') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab"
                                href="#firebase_setting">{{ labels('admin_labels.firebase_settings', 'Firebase Settings') }}</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="tab-content col-md-10">

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="general_setting" class="card tab-pane active">
                                <div class="card-body">
                                    <form id="general_setting_form" action="{{ route('web_settings.store') }}"
                                        class="submit_form" enctype="multipart/form-data" method="POST">
                                        @csrf
                                        <h5 class="card-title">General Settings</h5>
                                        <div class="row">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="form-group col-md-4">
                                                        <label
                                                            for="site_title">{{ labels('admin_labels.site_title', 'Site Title') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <input type="text" class="form-control" name="site_title"
                                                            value="<?= isset($web_settings['site_title']) ? outputEscaping($web_settings['site_title']) : '' ?>"
                                                            placeholder="Prefix title for the website. " />
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label
                                                            for="support_number">{{ labels('admin_labels.support_number', 'Support Number') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <input type="text" class="form-control" name="support_number"
                                                            value="<?= isset($web_settings['support_number']) ? outputEscaping($web_settings['support_number']) : '' ?>"
                                                            placeholder="Customer support mobile number" />
                                                    </div>
                                                    <div class="form-group col-md-4">
                                                        <label
                                                            for="support_email">{{ labels('admin_labels.support_email', 'Support Email') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <input type="text" class="form-control" name="support_email"
                                                            value="<?= isset($web_settings['support_email']) ? outputEscaping($web_settings['support_email']) : '' ?>"
                                                            placeholder="Customer support email" />
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label
                                                            for="address">{{ labels('admin_labels.copyright_details', 'Copyright Details') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <textarea name="copyright_details" id="copyright_details" class="form-control" cols="30" rows="3"><?= isset($web_settings['copyright_details']) ? outputEscaping($web_settings['copyright_details']) : '' ?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label
                                                            for="address">{{ labels('admin_labels.address', 'Address') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <textarea name="address" id="address" class="form-control" cols="30" rows="5"><?= isset($web_settings['address']) ? outputEscaping($web_settings['address']) : '' ?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label
                                                            for="app_short_description">{{ labels('admin_labels.short_description', 'Short Description') }}
                                                            <span class='text-asterisks text-xs'>*</span></label>
                                                        <textarea name="app_short_description" id="app_short_description" class="form-control" cols="30" rows="5"><?= isset($web_settings['app_short_description']) ? outputEscaping($web_settings['app_short_description']) : '' ?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label
                                                            for="map_iframe">{{ labels('admin_labels.map_iframe', 'Map Iframe') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <textarea name="map_iframe" id="map_iframe" class="form-control" cols="30" rows="5"><?= isset($web_settings['map_iframe']) ? outputEscaping($web_settings['map_iframe']) : '' ?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6 form-group">
                                                                <label
                                                                    for="image">{{ labels('admin_labels.logo', 'Logo') }}
                                                                    <span
                                                                        class='text-asterisks text-sm'>*</span><small>(Recommended
                                                                        Size
                                                                        : larger than 120 x 120 & smaller than 150 x 150
                                                                        pixels.)</small></label>
                                                                <div class="col-sm-10">
                                                                    <div class='col-md-12'>
                                                                        <a class="uploadFile img btn btn-secondary btn-sm"
                                                                            data-input='logo' data-isremovable='0'
                                                                            data-is-multiple-uploads-allowed='0'
                                                                            data-bs-toggle="modal"
                                                                            data-bs-target="#media-upload-modal"
                                                                            value="Upload Photo">
                                                                            <i class='fa fa-upload text-sm'></i> Upload
                                                                            Image
                                                                        </a>
                                                                    </div>

                                                                    @if (!empty($web_settings['logo']))
                                                                        <label for=""
                                                                            class="text-danger mt-3">*Only Choose When
                                                                            Update is necessary</label>
                                                                        <div
                                                                            class="container-fluid row image-upload-section">
                                                                            <div
                                                                                class="col-md-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                                <div>
                                                                                    <div class="upload-media-div">
                                                                                        <img class="img-fluid mb-2"
                                                                                            src="{{ route('admin.dynamic_image', [
                                                                                                'url' => getMediaImageUrl($web_settings['logo']),
                                                                                                'width' => 150,
                                                                                                'quality' => 90,
                                                                                            ]) }}"
                                                                                            alt="Not Found">
                                                                                    </div>
                                                                                    <input type="hidden" name="logo"
                                                                                        id="logo"
                                                                                        value="{{ $web_settings['logo'] }}">
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div
                                                                            class="container-fluid row image-upload-section">
                                                                            <div
                                                                                class="col-md-4 shadow p-2 mb-5 bg-white rounded m-2 text-center grow image d-none">
                                                                            </div>
                                                                        </div>
                                                                    @endif

                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="form-group">
                                                                        <label
                                                                            for="image">{{ labels('admin_labels.favicon', 'Favicon') }}
                                                                            <span
                                                                                class='text-asterisks text-sm'>*</span></label>
                                                                        <div class="col-sm-10">
                                                                            <div class='col-md-12'>
                                                                                <a class="uploadFile img btn btn-secondary btn-sm"
                                                                                    data-input='favicon'
                                                                                    data-isremovable='0'
                                                                                    data-is-multiple-uploads-allowed='0'
                                                                                    data-bs-toggle="modal"
                                                                                    data-bs-target="#media-upload-modal"
                                                                                    value="Upload Photo">
                                                                                    <i class='fa fa-upload text-sm'></i>
                                                                                    Upload
                                                                                    Image
                                                                                </a>
                                                                            </div>
                                                                            @if (!empty($web_settings['favicon']))
                                                                                <label for=""
                                                                                    class="text-danger mt-3">*Only Choose
                                                                                    When Update is necessary</label>
                                                                                <div
                                                                                    class="container-fluid row image-upload-section">
                                                                                    <div
                                                                                        class="col-md-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                                                        <div>
                                                                                            <div class="upload-media-div">
                                                                                                <img class="img-fluid mb-2"
                                                                                                    src="{{ route('admin.dynamic_image', [
                                                                                                        'url' => getMediaImageUrl($web_settings['favicon']),
                                                                                                        'width' => 150,
                                                                                                        'quality' => 90,
                                                                                                    ]) }}"
                                                                                                    alt="Not Found">
                                                                                            </div>
                                                                                            <input type="hidden"
                                                                                                name="favicon"
                                                                                                id="favicon"
                                                                                                value="{{ $web_settings['favicon'] }}">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div
                                                                                    class="container-fluid row image-upload-section">
                                                                                    <div
                                                                                        class="col-md-4 shadow p-2 mb-5 bg-white rounded m-2 text-center grow image d-none">
                                                                                    </div>
                                                                                </div>
                                                                            @endif

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label
                                                            for="support_email">{{ labels('admin_labels.meta_keywords', 'Meta Keywords') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <textarea name="meta_keywords" id="meta_keywords" class="form-control" cols="30" rows="5"><?= isset($web_settings['meta_keywords']) ? str_replace(["\n\r", "\n", "\r", '\\'], '', $web_settings['meta_keywords']) : '' ?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label
                                                            for="support_email">{{ labels('admin_labels.meta_description', 'Meta Description') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <textarea name="meta_description" id="meta_description" class="form-control" cols="30" rows="5"><?= isset($web_settings['meta_description']) ? $web_settings['meta_description'] : '' ?></textarea>
                                                    </div>
                                                </div>
                                                <hr>
                                                <h4>{{ labels('admin_labels.app_download_section', 'App Download Section') }}
                                                </h4>
                                                <div class="row">
                                                    <div class="form-group col-md-2">
                                                        <label for="app_download_section"> Enable / Disable</label>
                                                        <div class="card-body">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="app_download_section" name="app_download_section"
                                                                    <?= isset($web_settings['app_download_section']) && $web_settings['app_download_section'] == '1' ? 'Checked' : '' ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="app_download_section_title">{{ labels('admin_labels.title', 'Title') }}
                                                            <span class='text-asterisks text-xs'>*</span></label>
                                                        <input type="text" class="form-control"
                                                            name="app_download_section_title"
                                                            value="<?= isset($web_settings['app_download_section_title']) ? outputEscaping($web_settings['app_download_section_title']) : '' ?>"
                                                            placeholder="App download section title. " />
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="app_download_section_tagline">{{ labels('admin_labels.app_download_section_tagline', 'APP Download Section Tagline') }}<span
                                                                class='text-danger text-xs'>*</span></label>
                                                        <input type="text" class="form-control"
                                                            name="app_download_section_tagline"
                                                            value="<?= isset($web_settings['app_download_section_tagline']) ? outputEscaping($web_settings['app_download_section_tagline']) : '' ?>"
                                                            placeholder="App download section Tagline." />
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        <label
                                                            for="app_download_section_short_description">{{ labels('admin_labels.short_description', 'Short Description') }}
                                                            <span class='text-asterisks text-xs'>*</span></label>
                                                        <textarea name="app_download_section_short_description" id="app_download_section_short_description"
                                                            class="form-control" cols="30" rows="5"><?= isset($web_settings['app_download_section_short_description']) ? outputEscaping($web_settings['app_download_section_short_description']) : '' ?></textarea>
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label
                                                            for="app_download_section_title">{{ labels('admin_labels.playstore_url', 'Playstore URL') }}
                                                            <span class='text-danger text-xs'>*</span></label>
                                                        <input type="text" class="form-control"
                                                            name="app_download_section_playstore_url"
                                                            value="<?= isset($web_settings['app_download_section_playstore_url']) ? outputEscaping($web_settings['app_download_section_playstore_url']) : '' ?>"
                                                            placeholder="Playstore URL. " />
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label
                                                            for="app_download_section_tagline">{{ labels('admin_labels.app_store_url', 'App Store URL') }}<span
                                                                class='text-danger text-xs'>*</span></label>
                                                        <input type="text" class="form-control"
                                                            name="app_download_section_appstore_url"
                                                            value="<?= isset($web_settings['app_download_section_appstore_url']) ? outputEscaping($web_settings['app_download_section_appstore_url']) : '' ?>"
                                                            placeholder="Appstore URL." />
                                                    </div>
                                                </div>
                                                <hr>
                                                <h4>{{ labels('admin_labels.social_media_links', 'Social Media Links') }}
                                                </h4>
                                                <div class="row">
                                                    <div class="form-group col-md-6">
                                                        <label
                                                            for="twitter_link">{{ labels('admin_labels.twitter', 'Twitter') }}</label>
                                                        <input type="text" class="form-control" name="twitter_link"
                                                            value="<?= isset($web_settings['twitter_link']) ? outputEscaping($web_settings['twitter_link']) : '' ?>"
                                                            placeholder="Twitter Link" />
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label
                                                            for="facebook_link">{{ labels('admin_labels.facebook', 'Facebook') }}</label>
                                                        <input type="text" class="form-control" name="facebook_link"
                                                            value="<?= isset($web_settings['facebook_link']) ? outputEscaping($web_settings['facebook_link']) : '' ?>"
                                                            placeholder="Facebook Link" />
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label
                                                            for="instagram_link">{{ labels('admin_labels.instagram', 'Instagram') }}</label>
                                                        <input type="text" class="form-control" name="instagram_link"
                                                            value="<?= isset($web_settings['instagram_link']) ? outputEscaping($web_settings['instagram_link']) : '' ?>"
                                                            placeholder="Instagram Link" />
                                                    </div>
                                                    <div class="form-group col-md-6">
                                                        <label
                                                            for="youtube_link">{{ labels('admin_labels.youtube', 'Youtube') }}</label>
                                                        <input type="text" class="form-control" name="youtube_link"
                                                            value="<?= isset($web_settings['youtube_link']) ? outputEscaping($web_settings['youtube_link']) : '' ?>"
                                                            placeholder="Youtube Link" />
                                                    </div>
                                                </div>
                                                <hr>
                                                <div class="row">
                                                    <h4 class="h4 col-md-12">
                                                        {{ labels('admin_labels.shipping', 'Shipping') }}
                                                    </h4>
                                                    <div class="form-group col-md-2 col-sm-4">
                                                        <label for="shipping_mode"> Enable / Disable</label>
                                                        <div class="card-body">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="shipping_mode" name="shipping_mode"
                                                                    <?= isset($web_settings['shipping_mode']) && $web_settings['shipping_mode'] == '1' ? 'Checked' : '' ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="shipping_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                                        <input type="text" class="form-control" name="shipping_title"
                                                            value="<?= isset($web_settings['shipping_title']) ? outputEscaping($web_settings['shipping_title']) : '' ?>"
                                                            placeholder="Shipping Title" />
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="shipping_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                                        <textarea name="shipping_description" class="form-control" id="shipping_description" cols="30" rows="4"
                                                            placeholder="Shipping Description"><?= isset($web_settings['shipping_description']) ? outputEscaping($web_settings['shipping_description']) : '' ?></textarea>
                                                    </div>

                                                    <h4 class="h4 col-md-12">
                                                        {{ labels('admin_labels.returns', 'Returns') }}
                                                    </h4>
                                                    <div class="form-group col-md-2 col-sm-4">
                                                        <label for="return_mode"> Enable / Disable</label>
                                                        <div class="card-body">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="return_mode" name="return_mode"
                                                                    <?= isset($web_settings['return_mode']) && $web_settings['return_mode'] == '1' ? 'Checked' : '' ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="return_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                                        <input type="text" class="form-control" name="return_title"
                                                            value="<?= isset($web_settings['return_title']) ? outputEscaping($web_settings['return_title']) : '' ?>"
                                                            placeholder="Return Title" />
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="return_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                                        <textarea name="return_description" class="form-control" id="return_description" cols="30" rows="4"
                                                            placeholder="Return Description"><?= isset($web_settings['return_description']) ? outputEscaping($web_settings['return_description']) : '' ?></textarea>
                                                    </div>

                                                    <h4 class="h4 col-md-12">
                                                        {{ labels('admin_labels.support', 'Support') }}
                                                    </h4>
                                                    <div class="form-group col-md-2 col-sm-4">
                                                        <label for="support_mode"> Enable / Disable</label>
                                                        <div class="card-body">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="support_mode" name="support_mode"
                                                                    <?= isset($web_settings['support_mode']) && $web_settings['support_mode'] == '1' ? 'Checked' : '' ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="support_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                                        <input type="text" class="form-control" name="support_title"
                                                            value="<?= isset($web_settings['support_title']) ? outputEscaping($web_settings['support_title']) : '' ?>"
                                                            placeholder="Support Title" />
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="shipping_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                                        <textarea name="support_description" class="form-control" id="support_description" cols="30" rows="4"
                                                            placeholder="Support Description"><?= isset($web_settings['support_description']) ? outputEscaping($web_settings['support_description']) : '' ?></textarea>
                                                    </div>

                                                    <h4 class="h4 col-md-12">
                                                        {{ labels('admin_labels.safety_and_security', 'Safety & Security') }}
                                                    </h4>
                                                    <div class="form-group col-md-2 col-sm-4">
                                                        <label for="safety_security_mode"> Enable / Disable</label>
                                                        <div class="card-body">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox"
                                                                    id="safety_security_mode" name="safety_security_mode"
                                                                    <?= isset($web_settings['safety_security_mode']) && $web_settings['safety_security_mode'] == '1' ? 'Checked' : '' ?>>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="safety_security_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                                        <input type="text" class="form-control"
                                                            name="safety_security_title"
                                                            value="<?= isset($web_settings['safety_security_title']) ? outputEscaping($web_settings['safety_security_title']) : '' ?>"
                                                            placeholder="Safety & Security Title" />
                                                    </div>
                                                    <div class="form-group col-md-5">
                                                        <label
                                                            for="safety_security_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                                        <textarea name="safety_security_description" class="form-control" id="safety_security_description" cols="30"
                                                            rows="4" placeholder="Safety & Security Description"><?= isset($web_settings['safety_security_description']) ? outputEscaping($web_settings['safety_security_description']) : '' ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-center">
                                                    <div class="form-group" id="error_box">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <button type="reset" class="btn reset-btn"
                                                    id="">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                                <button type="submit" class="btn btn-dark"
                                                    id="">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                                            </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card m-2 tab-pane" id="themes">
                                <div class="card-body">
                                    <h5 class="card-title">{{ labels('admin_labels.themes', 'Themes') }}</h5>
                                    <!-- Themes form fields go here -->
                                    <form id="themes_form" action="" class="submit_form"
                                        enctype="multipart/form-data" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <button type="reset" class="btn reset-btn"
                                                id="">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                            <button type="submit" class="btn btn-dark"
                                                id="">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card m-2 tab-pane" id="languages">
                                <div class="card-body">
                                    <h5 class="card-title">{{ labels('admin_labels.language', 'Language') }}</h5>
                                    <!-- Languages form fields go here -->
                                    <form id="languages_form" action="" class="submit_form"
                                        enctype="multipart/form-data" method="POST">
                                        @csrf
                                        <div class="form-group">
                                            <button type="reset" class="btn reset-btn"
                                                id="">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                            <button type="submit" class="btn btn-dark"
                                                id="">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="card m-2 tab-pane" id="firebase_setting">
                                <div class="card-body">
                                    <form id="general_setting_form" action="{{ route('firebase_settings.store') }}"
                                        class="submit_form" enctype="multipart/form-data" method="POST">
                                        @csrf
                                        <h5 class="card-title">
                                            {{ labels('admin_labels.firebase_settings', 'Firebase Settings') }}
                                        </h5>
                                        <div class="row">
                                            <div class="form-group col-md-6">
                                                <label for="apiKey">{{ labels('admin_labels.api_key', 'APIkey') }} <span
                                                        class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="apiKey"
                                                    value="<?= isset($firebase_settings['apiKey']) ? $firebase_settings['apiKey'] : '' ?>"
                                                    placeholder="apiKey" />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label
                                                    for="authDomain">{{ labels('admin_labels.auth_domain', 'Auth Domain') }}
                                                    <span class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="authDomain"
                                                    value="<?= isset($firebase_settings['authDomain']) ? $firebase_settings['authDomain'] : '' ?>"
                                                    placeholder="authDomain" />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label
                                                    for="databaseURL">{{ labels('admin_labels.database_url', 'Database URL') }}
                                                    <span class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="databaseURL"
                                                    value="<?= isset($firebase_settings['databaseURL']) ? $firebase_settings['databaseURL'] : '' ?>"
                                                    placeholder="databaseURL" />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label
                                                    for="projectId">{{ labels('admin_labels.project_id', 'Project ID') }}
                                                    <span class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="projectId"
                                                    value="<?= isset($firebase_settings['projectId']) ? $firebase_settings['projectId'] : '' ?>"
                                                    placeholder="projectId" />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label
                                                    for="storageBucket">{{ labels('admin_labels.storage_bucket', 'Storage Bucket') }}
                                                    <span class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="storageBucket"
                                                    value="<?= isset($firebase_settings['storageBucket']) ? $firebase_settings['storageBucket'] : '' ?>"
                                                    placeholder="storageBucket" />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label
                                                    for="messagingSenderId">{{ labels('admin_labels.messaging_sender_id', 'Messaging Sender ID') }}
                                                    <span class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="messagingSenderId"
                                                    value="<?= isset($firebase_settings['messagingSenderId']) ? $firebase_settings['messagingSenderId'] : '' ?>"
                                                    placeholder="messagingSenderId" />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="appId">{{ labels('admin_labels.app_id', 'APP ID') }} <span
                                                        class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="appId"
                                                    value="<?= isset($firebase_settings['appId']) ? $firebase_settings['appId'] : '' ?>"
                                                    placeholder="appId" />
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label
                                                    for="measurementId">{{ labels('admin_labels.measurement_id', 'Measurement ID') }}
                                                    <span class='text-danger text-xs'>*</span></label>
                                                <input type="text" class="form-control" name="measurementId"
                                                    value="<?= isset($firebase_settings['measurementId']) ? $firebase_settings['measurementId'] : '' ?>"
                                                    placeholder="measurementId" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <button type="reset" class="btn reset-btn"
                                                id="">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                            <button type="submit" class="btn btn-dark"
                                                id="">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
