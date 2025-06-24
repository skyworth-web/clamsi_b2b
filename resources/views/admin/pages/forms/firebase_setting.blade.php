@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.firebase_settings', 'Firebase Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.firebase', 'Firebase')" :subtitle="labels(
        'admin_labels.seamlessly_integrate_and_leverage_firebase_capabilities',
        'Seamlessly Integrate and Leverage Firebase Capabilities',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.general_settings', 'General Settings')],
        ['label' => labels('admin_labels.firebase_settings', 'Firebase Settings')],
    ]" />

    <div class="card m-2 tab-pane" id="firebase_setting">
        <div class="card-body">
            <form id="general_setting_form" action="{{ route('firebase_settings.store') }}" class="submit_form"
                enctype="multipart/form-data" method="POST">
                @csrf
                <h5 class="card-title">
                    {{ labels('admin_labels.firebase_settings', 'Firebase Settings') }}
                </h5>
                <div class="row">
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3" for="apiKey">{{ labels('admin_labels.api_key', 'API Key') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="apiKey"
                            value="<?= isset($firebase_settings['apiKey']) ? $firebase_settings['apiKey'] : '' ?>"
                            placeholder="apiKey" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="authDomain">{{ labels('admin_labels.auth_domain', 'Auth Domain') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="authDomain"
                            value="<?= isset($firebase_settings['authDomain']) ? $firebase_settings['authDomain'] : '' ?>"
                            placeholder="authDomain" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="databaseURL">{{ labels('admin_labels.database_url', 'Database URL') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="databaseURL"
                            value="<?= isset($firebase_settings['databaseURL']) ? $firebase_settings['databaseURL'] : '' ?>"
                            placeholder="databaseURL" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="projectId">{{ labels('admin_labels.project_id', 'Project ID') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="projectId"
                            value="<?= isset($firebase_settings['projectId']) ? $firebase_settings['projectId'] : '' ?>"
                            placeholder="projectId" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="storageBucket">{{ labels('admin_labels.storage_bucket', 'Storage Bucket') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="storageBucket"
                            value="<?= isset($firebase_settings['storageBucket']) ? $firebase_settings['storageBucket'] : '' ?>"
                            placeholder="storageBucket" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="messagingSenderId">{{ labels('admin_labels.messaging_sender_id', 'Messaging Sender ID') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="messagingSenderId"
                            value="<?= isset($firebase_settings['messagingSenderId']) ? $firebase_settings['messagingSenderId'] : '' ?>"
                            placeholder="messagingSenderId" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3" for="appId">{{ labels('admin_labels.app_id', 'App ID') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="appId"
                            value="<?= isset($firebase_settings['appId']) ? $firebase_settings['appId'] : '' ?>"
                            placeholder="appId" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="measurementId">{{ labels('admin_labels.measurement_id', 'Measurement ID') }}
                            <span class='text-asterisks text-xs'>*</span></label>
                        <input type="text" class="form-control" name="measurementId"
                            value="<?= isset($firebase_settings['measurementId']) ? $firebase_settings['measurementId'] : '' ?>"
                            placeholder="measurementId" />
                    </div>
                    <hr>
                    <p class="text-danger">"If you have web application than field below Google and Facebook Credentials"
                    </p>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="google_client_id">{{ labels('admin_labels.google_client_id', 'Google Client id') }}
                        </label>
                        <input type="text" class="form-control" name="google_client_id"
                            value="<?= isset($firebase_settings['google_client_id']) ? $firebase_settings['google_client_id'] : '' ?>"
                            placeholder="google client id" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="google_client_secret">{{ labels('admin_labels.google_client_secret', 'Google Client Secret') }}
                        </label>
                        <input type="text" class="form-control" name="google_client_secret"
                            value="<?= isset($firebase_settings['google_client_secret']) ? $firebase_settings['google_client_secret'] : '' ?>"
                            placeholder="google client secret" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="google_redirect_url">{{ labels('admin_labels.google_redirect_url', 'Google Redirect url') }}
                        </label>
                        <input type="text" class="form-control" name="google_redirect_url"
                            value="<?= isset($firebase_settings['google_redirect_url']) ? $firebase_settings['google_redirect_url'] : '' ?>"
                            placeholder="Google Redirect url" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="facebook_client_id">{{ labels('admin_labels.facebook_client_id', 'Facebook Client ID') }}
                        </label>
                        <input type="text" class="form-control" name="facebook_client_id"
                            value="<?= isset($firebase_settings['facebook_client_id']) ? $firebase_settings['facebook_client_id'] : '' ?>"
                            placeholder="Facebook Client id" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="facebook_client_secret">{{ labels('admin_labels.facebook_client_secret', 'Facebook Client Secret') }}
                        </label>
                        <input type="text" class="form-control" name="facebook_client_secret"
                            value="<?= isset($firebase_settings['facebook_client_secret']) ? $firebase_settings['facebook_client_secret'] : '' ?>"
                            placeholder="Facebook Client Secret" />
                    </div>
                    <div class="form-group col-md-6">
                        <label class="form-label mb-3 mt-3"
                            for="facebook_redirect_url">{{ labels('admin_labels.facebook_redirect_url', 'Facebook Redirect url') }}
                        </label>
                        <input type="text" class="form-control" name="facebook_redirect_url"
                            value="<?= isset($firebase_settings['facebook_redirect_url']) ? $firebase_settings['facebook_redirect_url'] : '' ?>"
                            placeholder="Facebook Redirect url" />
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
