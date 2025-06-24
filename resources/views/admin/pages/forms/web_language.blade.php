@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.language', 'Language') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.language', 'Language')" :subtitle="labels('admin_labels.track_and_manage_language', 'Track and Manage Language')" :breadcrumbs="[['label' => labels('admin_labels.language', 'Language')]]" />

    <div class="row">
        <!-- Card for Adding Language -->
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">{{ labels('admin_labels.add_language', 'Add Language') }}</h5>
                    <form action="{{ route('language.store') }}" class="submit_form" enctype="multipart/form-data"
                        method="POST">
                        @csrf
                        <div class="col-xl">
                            <div class="row">
                                <div class="form-group col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="language">{{ labels('admin_labels.language', 'Language') }}</label>
                                        <input type="text" class="form-control" id="basic-default-fullname"
                                            placeholder="English" name="language" value="{{ old('language') }}">
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="code">{{ labels('admin_labels.code', 'Code') }}</label>
                                        <input type="text" class="form-control" id="basic-default-fullname"
                                            placeholder="en" name="code" value="{{ old('code') }}">
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label"
                                            for="native_language">{{ labels('admin_labels.native_language', 'Native Language') }}</label>
                                        <input type="text" class="form-control" id="basic-default-fullname"
                                            placeholder="Ex. English , हिन्दी" name="native_language"
                                            value="{{ old('native_language') }}">
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="is_rtl"
                                        class="col-form-label">{{ labels('admin_labels.is_rtl', 'Is RTL') }}? <span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_rtl" class="form-check-input mx-2">
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button type="submit"
                                        class="btn btn-primary submit_button">{{ labels('admin_labels.add_language', 'Add Language') }}</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Card for Updating php File -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">{{ labels('admin_labels.upload_file', 'Upload Translation File') }}</h5>
                    <input type="hidden" id="current-lang" value="{{ $language_code }}" />
                    <form class="mb-3 submit_form" action="languages/savelabel" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label
                                for="translation_file">{{ labels('admin_labels.upload_file', 'Upload Translation File') }}</label>
                            <input type="file" name="translation_file" id="translation_file" accept=".php"
                                class="form-control">
                        </div>
                        <div class="mt-2 d-flex justify-content-end">
                            <button type="submit"
                                class="btn btn-primary me-2">{{ labels('admin_labels.save', 'Save') }}</button>
                        </div>
                    </form>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mt-2">
                                <a href="{{ route('admin.web-download-language-labels') }}"
                                    class="btn btn-primary btn-sm instructions_files">
                                    {{ labels('admin_labels.download_labels_file', 'Download labels file') }}
                                    <i class="fas fa-download mx-2"></i>
                                </a>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mt-2">
                                <a href="{{ route('admin.web-download-language-sample-file') }}"
                                    class="btn btn-primary btn-sm instructions_files">
                                    {{ labels('admin_labels.sample_file', 'Sample File') }}
                                    <i class="fas fa-download mx-2"></i>
                                </a>
                            </div>
                        </div>
                        @php
                            $language_file = base_path("resources/lang/{$language_code}/front_messages.php");
                            // dd(file_exists($language_file));
                        @endphp

                        @if (file_exists($language_file))
                            <div class="col-md-5">
                                <div class="form-group mt-2">
                                    <a href="{{ route('web.download.language.file', ['language_code' => $language_code]) }}"
                                        class="btn btn-primary btn-sm instructions_files">
                                        {{ labels('admin_labels.download_your_file', 'Download Your file') }}
                                        <i class="fas fa-download mx-2"></i>
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
