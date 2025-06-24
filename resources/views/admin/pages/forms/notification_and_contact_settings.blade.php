@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.notification_and_contact_settings', 'Notification & Contact Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.notification_and_contact', 'Notification & Contact')" :subtitle="labels(
        'admin_labels.unify_communication_with_notifications_contact_and_about_us',
        'Unify Communication with Notifications, Contact, and About Us',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.notification_and_contact_settings', 'Notification & Contact Settings')],
    ]" />


    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 col-xxl-6">
                <div class="card">
                    <div class="card-body">
                        <form id="" action="{{ route('contact_us.store') }}" class="submit_form"
                            enctype="multipart/form-data" method="POST">
                            @csrf
                            <h5 class="mb-3">
                                {{ labels('admin_labels.contact_us', 'Contact Us') }}
                            </h5>
                            <textarea class="form-control" name="contact_us" placeholder="Contact Us" rows="5">{{ isset($contact_us['contact_us']) ? $contact_us['contact_us'] : '' }}</textarea>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="reset" class="btn mx-2 reset_button"
                                    id="">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xxl-6 mt-md-4 mt-xxl-0">
                <div class="card">
                    <div class="card-body">
                        <form id="" action="{{ route('about_us.store') }}" class="submit_form"
                            enctype="multipart/form-data" method="POST">
                            @csrf
                            <h5 class="mb-3">
                                {{ labels('admin_labels.about_us', 'About Us') }}
                            </h5>
                            <textarea class="form-control" name="about_us" placeholder="About Us" rows="5">{{ isset($about_us['about_us']) ? $about_us['about_us'] : '' }}</textarea>
                            <div class="d-flex justify-content-end mt-4">
                                <button type="reset" class="btn mx-2 reset_button"
                                    id="">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit"
                                    class="btn btn-primary submit_button">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12 col-xxl-6">
            <div class="card">
                <div class="card-body">
                    <form id="" action="{{ route('notification_settings.store') }}" class="submit_form"
                        enctype="multipart/form-data" method="POST">
                        @csrf
                        <h5 class="mb-3">
                            {{ labels('admin_labels.notification_setting', 'Notification Setting') }}
                        </h5>
                        <div class="form-group">
                            <div class="form-group mb-0">
                                <label
                                    for="firebase_project_id">{{ labels('admin_labels.firebase_project_id', 'Firebase Project ID') }}
                                </label>
                                <input type="text" id="firebase_project_id" class="form-control mt-2"
                                    name="firebase_project_id" placeholder='Firebase Project ID'
                                    value="<?= isset($firebase_project_id) ? outputEscaping($firebase_project_id) : '' ?>">
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="firebase_project_id">
                                {{ labels('admin_labels.service_account_file', 'Service Account File') }}
                                <span class="text-danger fs-12">*(Only JSON File is allowed)</span> :
                            </label>
                            <input type="file" name="service_account_file" id="service_account_file"
                                class="form-control mt-2" placeholder="Service Account File" accept=".json">
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="reset" class="btn mx-2 reset_button"
                                id="">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit"
                                class="btn btn-primary submit_button">{{ labels('admin_labels.update_settings', 'Update Settings') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
