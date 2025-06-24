@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.pwa_setting', 'PWA Setting') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.pwa_setting', 'PWA Setting')" :subtitle="labels(
        'admin_labels.unlock_the_future_of_web_apps_with_advance_pwa_settings',
        'Unlock the Future of Web Apps with Advanced PWA Settings',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.pwa_setting', 'PWA Setting')],
    ]" />

    <div class="card m-2 tab-pane">
        <div class="card-body">
            <form id="general_setting_form" action="{{ route('pwa_settings.store') }}" class="submit_form"
                enctype="multipart/form-data" method="POST">
                @csrf
                <h5 class="card-title">
                    {{ labels('admin_labels.pwa_settings', 'PWA Settings') }}
                </h5>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3" for="name">{{ labels('admin_labels.name', 'Name') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="name"
                            value="<?= isset($pwa_settings['name']) ? $pwa_settings['name'] : '' ?>"
                            placeholder="Eshop Plus" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="short_name">{{ labels('admin_labels.short_name', 'Short Name') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="short_name"
                            value="<?= isset($pwa_settings['short_name']) ? $pwa_settings['short_name'] : '' ?>"
                            placeholder="Eshop Plus" />
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-lg-3">
                        <div class="form-group d-flex col-md-12 align-items-center justify-content-between">
                            <label for="">{{ labels('admin_labels.theme_color', 'Theme Color') }}<span
                                    class="text-asterisks text-sm">*</span></label>
                            <div class="col-md-4 d-flex justify-content-end">
                                <input type="color" id="light_theme_color"
                                    value="{{ !empty($pwa_settings['theme_color']) ? $pwa_settings['theme_color'] : '#e0ffee' }}"
                                    oninput="updateColorCode('light_theme_color')" class="color_picker mx-2">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <input type="text" id="light_theme_color_code" name="theme_color" class="form-control mx-2"
                            oninput="updateColorPicker('theme_color', this.value)"
                            value="{{ !empty($pwa_settings['theme_color']) ? $pwa_settings['theme_color'] : '#e0ffee' }}">
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group d-flex col-md-12 align-items-center justify-content-between">
                            <label for="">{{ labels('admin_labels.background_color', 'Background Color') }}<span
                                    class="text-asterisks text-sm">*</span></label>
                            <div class="col-md-4 d-flex justify-content-end">
                                <input type="color" id="dark_theme_color"
                                    value="{{ !empty($pwa_settings['background_color']) ? $pwa_settings['background_color'] : '#e0ffee' }}"
                                    oninput="updateColorCode('dark_theme_color')" class="color_picker mx-2">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <input type="text" id="dark_theme_color_code"
                            oninput="updateColorPicker('background_color', this.value)" name="background_color"
                            class="form-control mx-2"
                            value="{{ !empty($pwa_settings['background_color']) ? $pwa_settings['background_color'] : '#e0ffee' }}">
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-12">
                        <label class="form-label" for="description">{{ labels('admin_labels.description', 'Description') }}
                            <span class="text-asterisks text-sm">*</span></label>
                        <textarea name="description" class="form-control" placeholder="Write here your description">{{ old('description') }}{{ !empty($pwa_settings['description']) ? $pwa_settings['description'] : '' }}</textarea>
                    </div>
                </div>
                <div class="col-md-12 form-group">
                    <label for="image">{{ labels('admin_labels.logo', 'Logo') }}
                        <span class='text-asterisks text-sm'>*</span><small class="text-danger fs-6"> (Please upload minimum
                            512 x 512 logo or else it will not work.)</small></label>
                    <div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="file_upload_box border file_upload_border mt-4">
                                    <div class="mt-2 text-center">
                                        <a class="media_link" data-input="logo" data-isremovable="0"
                                            data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                            data-bs-target="#media-upload-modal" value="Upload Photo">
                                            <h4><i class='bx bx-upload'></i> Upload</h4>
                                        </a>
                                        <p class="image_recommendation">Recommended
                                            Size
                                            : larger than 512 x 512 </p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                @if (!empty($pwa_settings['logo']))
                                    <label for="" class="text-danger mt-3">*Only Choose When
                                        Update
                                        is necessary</label>
                                    <div class="container-fluid row image-upload-section pwa_logo_box">
                                        <div class="col-md-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                            <div class=''>
                                                <div class='upload-media-div'><img class="img-fluid mb-2"
                                                        src="{{ asset('storage' . $pwa_settings['logo']) }}"
                                                        alt="Not Found"></div>
                                                <input type="hidden" name="logo" id='logo'
                                                    value='<?= $pwa_settings['logo'] ?>'>
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
                <div class="d-flex justify-content-end mt-4">
                    <button type="reset"
                        class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                    <button type="submit"
                        class="btn btn-primary submit_button">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                </div>
            </form>
        </div>
    </div>
@endsection
