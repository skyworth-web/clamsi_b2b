@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.general_settings', 'General Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.general_settings', 'General Settings')" :subtitle="labels(
        'admin_labels.customoize_your_platform_with_essential_general_settings',
        'Customize Your Platform with Essential General Settings',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.web_settings', 'Web Settings')],
        ['label' => labels('admin_labels.general_settings', 'General Settings')],
    ]" />

    <div class="row">
        <div class="col-md-12">
            <form id="general_setting_form" action="{{ route('web_settings.store') }}" class="submit_form"
                enctype="multipart/form-data" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.general_settings', 'General Settings') }}
                                </h5>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="site_title">{{ labels('admin_labels.site_title', 'Site Title') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="site_title"
                                        value="<?= isset($web_settings['site_title']) ? outputEscaping($web_settings['site_title']) : '' ?>"
                                        placeholder="Prefix title for the website. " />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="support_number">{{ labels('admin_labels.support_number', 'Support Number') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="support_number"
                                        value="<?= isset($web_settings['support_number']) ? outputEscaping($web_settings['support_number']) : '' ?>"
                                        placeholder="Customer support mobile number" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="support_email">{{ labels('admin_labels.support_email', 'Support Email') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="support_email"
                                        value="<?= isset($web_settings['support_email']) ? outputEscaping($web_settings['support_email']) : '' ?>"
                                        placeholder="Customer support email" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="address">{{ labels('admin_labels.copyright_details', 'Copyright Details') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <textarea name="copyright_details" id="copyright_details" class="form-control" cols="30" rows="3"><?= isset($web_settings['copyright_details']) ? outputEscaping($web_settings['copyright_details']) : '' ?></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3" for="address">{{ labels('admin_labels.address', 'Address') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <textarea name="address" id="address" class="form-control" cols="30" rows="5"><?= isset($web_settings['address']) ? outputEscaping($web_settings['address']) : '' ?></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="app_short_description">{{ labels('admin_labels.short_description', 'Short Description') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <textarea name="app_short_description" id="app_short_description" class="form-control" cols="30" rows="5"><?= isset($web_settings['app_short_description']) ? outputEscaping($web_settings['app_short_description']) : '' ?></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="map_iframe">{{ labels('admin_labels.map_iframe', 'Map Iframe') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <textarea name="map_iframe" id="map_iframe" class="form-control" cols="30" rows="5"><?= isset($web_settings['map_iframe']) ? outputEscaping($web_settings['map_iframe']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-6 mt-md-2 mt-lg-0">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.logo_and_meta_data', 'Logo & Meta Data') }}
                                </h5>

                                <div class="col-md-12 form-group">
                                    <label for="image">{{ labels('admin_labels.logo', 'Logo') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="file_upload_box border file_upload_border mt-4">
                                                    <div class="mt-2 text-center">
                                                        <a class="media_link" data-input="logo" data-isremovable="0"
                                                            data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
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
                                                @if (!empty($web_settings['logo']))
                                                    <label for="" class="text-danger mt-3">*Only Choose When
                                                        Update
                                                        is necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                            <div class=''>
                                                                <div class='upload-media-div'><img class="img-fluid mb-2"
                                                                        src="{{ asset('storage' . $web_settings['logo']) }}"
                                                                        alt="Not Found"></div>
                                                                <input type="hidden" name="logo" id='logo'
                                                                    value='<?= $web_settings['logo'] ?>'>
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
                                    <div>
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
                                                @if (!empty($web_settings['favicon']))
                                                    <label for="" class="text-danger mt-3">*Only Choose When
                                                        Update
                                                        is necessary</label>
                                                    <div class="container-fluid row image-upload-section">
                                                        <div
                                                            class="col-md-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                            <div class=''>
                                                                <div class='upload-media-div'><img class="img-fluid mb-2"
                                                                        src="{{ asset('storage' . $web_settings['favicon']) }}"
                                                                        alt="Not Found"></div>
                                                                <input type="hidden" name="favicon" id='favicon'
                                                                    value='<?= $web_settings['favicon'] ?>'>
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
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="support_email">{{ labels('admin_labels.meta_keywords', 'Meta Keywords') }}<span
                                            class='text-danger text-xs'>*</span></label>
                                    <textarea name="meta_keywords" id="meta_keywords" class="form-control" cols="30" rows="5"><?= isset($web_settings['meta_keywords']) ? str_replace(["\n\r", "\n", "\r", '\\'], '', $web_settings['meta_keywords']) : '' ?></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="support_email">{{ labels('admin_labels.meta_description', 'Meta Description') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <textarea name="meta_description" id="meta_description" class="form-control" cols="30" rows="5"><?= isset($web_settings['meta_description']) ? $web_settings['meta_description'] : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-lg-6 mt-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.social_media_links', 'Social Media Links') }}
                                </h5>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="twitter_link">{{ labels('admin_labels.twitter', 'Twitter') }}</label>
                                    <input type="text" class="form-control" name="twitter_link"
                                        value="<?= isset($web_settings['twitter_link']) ? outputEscaping($web_settings['twitter_link']) : '' ?>"
                                        placeholder="Twitter Link" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="facebook_link">{{ labels('admin_labels.facebook', 'Facebook') }}</label>
                                    <input type="text" class="form-control" name="facebook_link"
                                        value="<?= isset($web_settings['facebook_link']) ? outputEscaping($web_settings['facebook_link']) : '' ?>"
                                        placeholder="Facebook Link" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="instagram_link">{{ labels('admin_labels.instagram', 'Instagram') }}</label>
                                    <input type="text" class="form-control" name="instagram_link"
                                        value="<?= isset($web_settings['instagram_link']) ? outputEscaping($web_settings['instagram_link']) : '' ?>"
                                        placeholder="Instagram Link" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="youtube_link">{{ labels('admin_labels.youtube', 'Youtube') }}</label>
                                    <input type="text" class="form-control" name="youtube_link"
                                        value="<?= isset($web_settings['youtube_link']) ? outputEscaping($web_settings['youtube_link']) : '' ?>"
                                        placeholder="Youtube Link" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-6 mt-md-2 mt-lg-0">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-3">
                                            {{ labels('admin_labels.app_download_section', 'App Download Section') }}
                                        </h5>
                                        <div class="card-body d-flex justify-content-end">
                                            <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                                href="javascript:void(0)">
                                                <input class="form-check-input" type="checkbox" id="app_download_section"
                                                    name="app_download_section"
                                                    <?= isset($web_settings['app_download_section']) && $web_settings['app_download_section'] == '1' ? 'Checked' : '' ?>>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="app_download_section_title">{{ labels('admin_labels.title', 'Title') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="app_download_section_title"
                                        value="<?= isset($web_settings['app_download_section_title']) ? outputEscaping($web_settings['app_download_section_title']) : '' ?>"
                                        placeholder="App download section title. " />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="app_download_section_tagline">{{ labels('admin_labels.tagline', 'Tagline') }}<span
                                            class='text-danger text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="app_download_section_tagline"
                                        value="<?= isset($web_settings['app_download_section_tagline']) ? outputEscaping($web_settings['app_download_section_tagline']) : '' ?>"
                                        placeholder="App download section Tagline." />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="app_download_section_short_description">{{ labels('admin_labels.short_description', 'Short Description') }}
                                        <span class='text-asterisks text-xs'>*</span></label>
                                    <textarea name="app_download_section_short_description" id="app_download_section_short_description"
                                        class="form-control" cols="30" rows="5"><?= isset($web_settings['app_download_section_short_description']) ? outputEscaping($web_settings['app_download_section_short_description']) : '' ?></textarea>
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="app_download_section_title">{{ labels('admin_labels.playstore_url', 'Playstore URL') }}<span
                                            class='text-danger text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="app_download_section_playstore_url"
                                        value="<?= isset($web_settings['app_download_section_playstore_url']) ? outputEscaping($web_settings['app_download_section_playstore_url']) : '' ?>"
                                        placeholder="Playstore URL. " />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="app_download_section_tagline">{{ labels('admin_labels.app_store_url', 'App Store URL') }}<span
                                            class='text-danger text-xs'>*</span></label>
                                    <input type="text" class="form-control" name="app_download_section_appstore_url"
                                        value="<?= isset($web_settings['app_download_section_appstore_url']) ? outputEscaping($web_settings['app_download_section_appstore_url']) : '' ?>"
                                        placeholder="Appstore URL." />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-3">
                                            {{ labels('admin_labels.support', 'Support') }}
                                        </h5>
                                        <div class="card-body d-flex justify-content-end">
                                            <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                                href="javascript:void(0)">
                                                <input class="form-check-input" type="checkbox" id="support_mode"
                                                    name="support_mode"
                                                    <?= isset($web_settings['support_mode']) && $web_settings['support_mode'] == '1' ? 'Checked' : '' ?>>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="support_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                    <input type="text" class="form-control" name="support_title"
                                        value="<?= isset($web_settings['support_title']) ? outputEscaping($web_settings['support_title']) : '' ?>"
                                        placeholder="Support Title" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="shipping_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                    <textarea name="support_description" class="form-control" id="support_description" cols="30" rows="4"
                                        placeholder="Support Description"><?= isset($web_settings['support_description']) ? outputEscaping($web_settings['support_description']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-6 mt-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-3">
                                            {{ labels('admin_labels.shipping', 'Shipping') }}
                                        </h5>
                                        <div class="card-body d-flex justify-content-end">
                                            <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                                href="javascript:void(0)">
                                                <input class="form-check-input" type="checkbox" id="shipping_mode"
                                                    name="shipping_mode"
                                                    <?= isset($web_settings['shipping_mode']) && $web_settings['shipping_mode'] == '1' ? 'Checked' : '' ?>>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="shipping_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                    <input type="text" class="form-control" name="shipping_title"
                                        value="<?= isset($web_settings['shipping_title']) ? outputEscaping($web_settings['shipping_title']) : '' ?>"
                                        placeholder="Shipping Title" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="shipping_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                    <textarea name="shipping_description" class="form-control" id="shipping_description" cols="30" rows="4"
                                        placeholder="Shipping Description"><?= isset($web_settings['shipping_description']) ? outputEscaping($web_settings['shipping_description']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-4">
                    <div class="col-md-12 col-lg-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-3">
                                            {{ labels('admin_labels.safety_and_security', 'Safety & Security') }}
                                        </h5>
                                        <div class="card-body d-flex justify-content-end">
                                            <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                                href="javascript:void(0)">
                                                <input class="form-check-input" type="checkbox" id="safety_security_mode"
                                                    name="safety_security_mode"
                                                    <?= isset($web_settings['safety_security_mode']) && $web_settings['safety_security_mode'] == '1' ? 'Checked' : '' ?>>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="safety_security_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                    <input type="text" class="form-control" name="safety_security_title"
                                        value="<?= isset($web_settings['safety_security_title']) ? outputEscaping($web_settings['safety_security_title']) : '' ?>"
                                        placeholder="Safety & Security Title" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="safety_security_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                    <textarea name="safety_security_description" class="form-control" id="safety_security_description" cols="30"
                                        rows="4" placeholder="Safety & Security Description"><?= isset($web_settings['safety_security_description']) ? outputEscaping($web_settings['safety_security_description']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-6 mt-md-2 mt-lg-0">
                        <div class="card">
                            <div class="card-body">
                                <div class="form-group col-md-12">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h5 class="mb-3">
                                            {{ labels('admin_labels.returns', 'Returns') }}
                                        </h5>
                                        <div class="card-body d-flex justify-content-end">
                                            <a class="toggle form-switch me-1 mb-1" title="Deactivate"
                                                href="javascript:void(0)">
                                                <input class="form-check-input" type="checkbox" id="return_mode"
                                                    name="return_mode"
                                                    <?= isset($web_settings['return_mode']) && $web_settings['return_mode'] == '1' ? 'Checked' : '' ?>>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="return_title">{{ labels('admin_labels.title', 'Title') }}</label>
                                    <input type="text" class="form-control" name="return_title"
                                        value="<?= isset($web_settings['return_title']) ? outputEscaping($web_settings['return_title']) : '' ?>"
                                        placeholder="Return Title" />
                                </div>
                                <div class="col-md-12">
                                    <label class="mb-3 mt-3"
                                        for="return_description">{{ labels('admin_labels.description', 'Description') }}</label>
                                    <textarea name="return_description" class="form-control" id="return_description" cols="30" rows="4"
                                        placeholder="Return Description"><?= isset($web_settings['return_description']) ? outputEscaping($web_settings['return_description']) : '' ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <button type="reset"
                        class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                    <button type="submit"
                        class="btn btn-primary submit_button">{{ labels('admin_labels.update_setting', 'Update Setting') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
