@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.pusher_setting', 'Pusher Setting') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.pusher_setting', 'Pusher Setting')" :subtitle="labels(
        'admin_labels.ensure_seamless_chat_integration_with_advanced_pusher_settings',
        'Ensure Seamless Chat Integration with Advanced Pusher Settings',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.pusher_setting', 'Pusher Setting')],
    ]" />

    <div class="row">
        <div class="col-md-12 col-xl-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">
                        {{ labels('admin_labels.pusher_setting', 'Pusher Setting') }}
                    </h5>
                    <div class="row">
                        <div class="form-group">
                            <form id="" action="{{ route('pusher_setting.store') }}" class="submit_form"
                                enctype="multipart/form-data" method="POST">
                                @csrf
                                <div class="m-2">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.pusher_channel_name', 'Pusher Channel Name') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="pusher_channel_name"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'pusher_channel_name') ? $settings['pusher_channel_name'] : '' ?>">

                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.pusher_app_id', 'Pusher App ID') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="pusher_app_id"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'pusher_app_id') ? $settings['pusher_app_id'] : '' ?>">

                                            </div>

                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.pusher_app_key', 'Pusher App Key') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="pusher_app_key"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'pusher_app_key') ? $settings['pusher_app_key'] : '' ?>">

                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.pusher_app_secret', 'Pusher App Secret') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="pusher_app_secret"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'pusher_app_secret') ? $settings['pusher_app_secret'] : '' ?>">

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.pusher_port', 'Pusher Port') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="pusher_port" value="443" readonly>

                                            </div>

                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.pusher_scheme', 'Pusher Scheme') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="pusher_scheme" value="https" readonly>

                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.pusher_app_cluster', 'Pusher App Cluster') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="pusher_app_cluster"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'pusher_app_cluster') ? $settings['pusher_app_cluster'] : '' ?>">

                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="reset"
                                        class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                    <button type="submit"
                                        class="btn btn-primary submit_button">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
