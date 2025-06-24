@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.email_smtp_settings', 'Email SMTP Setting') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.email_smtp_settings', 'Email SMTP Setting')" :subtitle="labels(
        'admin_labels.ensure_seamless_email_integration_with_advanced_smtp_settings',
        'Ensure Seamless Email Integration with Advanced SMTP Settings',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.email_smtp_settings', 'Email SMTP Setting')],
    ]" />

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">
                        {{ labels('admin_labels.email_smtp_settings', 'Email SMTP Setting') }}
                    </h5>
                    <div class="row">
                        <div class="form-group">
                            <form id="" action="{{ route('email_settings.store') }}" class="submit_form"
                                enctype="multipart/form-data" method="POST">
                                @csrf
                                <div class="m-2">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.email', 'Email') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="email"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'email') ? $settings['email'] : '' ?>">

                                            </div>

                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.password', 'Password') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="password" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="password"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'password') ? $settings['password'] : '' ?>">

                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.smtp_host', 'SMTP Host') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="eshop@gmail.com" name="smtp_host"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'smtp_host') ? $settings['smtp_host'] : '' ?>">

                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.smtp_port', 'SMTP Port') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="" name="smtp_port"
                                                    value="<?= isKeySetAndNotEmpty($settings, 'smtp_port') ? $settings['smtp_port'] : '' ?>">

                                            </div>

                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.email_content_type', 'Email Content Type') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <select class="form-select form-select-md mb-3"
                                                    aria-label=".form-select-md example" name="email_content_type">
                                                    <option
                                                        {{ $settings['email_content_type'] != null && $settings['email_content_type'] == 'html' ? 'selected' : '' }}
                                                        value="html">HTML
                                                    </option>
                                                    <option
                                                        {{ $settings['email_content_type'] != null && $settings['email_content_type'] == 'text' ? 'selected' : '' }}
                                                        value="text">TEXT
                                                    </option>
                                                </select>

                                            </div>
                                            <div class="mb-3 col-md-12">
                                                <label class="form-label"
                                                    for="basic-default-fullname">{{ labels('admin_labels.smtp_encryption', 'SMTP Encryption') }}<span
                                                        class='text-asterisks text-sm'>*</span></label>
                                                <select class="form-select form-select-md mb-3"
                                                    aria-label=".form-select-md example" name="smtp_encryption">
                                                    <option
                                                        {{ $settings['smtp_encryption'] != null && $settings['smtp_encryption'] == 'ssl' ? 'selected' : '' }}
                                                        value="ssl">SSL
                                                    </option>
                                                    <option
                                                        {{ $settings['smtp_encryption'] != null && $settings['smtp_encryption'] == 'off' ? 'selected' : '' }}
                                                        value="off">Off
                                                    </option>
                                                    <option
                                                        {{ $settings['smtp_encryption'] != null && $settings['smtp_encryption'] == 'tls' ? 'selected' : '' }}
                                                        value="tls">TLS
                                                    </option>
                                                </select>
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
