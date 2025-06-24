@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.system_settings', 'System Settings') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;

    @endphp

    <x-admin.breadcrumb :title="labels('admin_labels.system_update', 'System Update')" :subtitle="labels('admin_labels.update_web_and_admin_panel_from_here', 'Update Web and Admin Panel From here')" :breadcrumbs="[
        ['label' => labels('admin_labels.settings', 'Settings'), 'url' => route('settings.index')],
        ['label' => labels('admin_labels.system_update', 'System Update')],
    ]" />


    <div class="row">
        <div class="alert alert-primary alert-dismissible" role="alert">
            <?= labels('post_update_clear_browser_cache', 'Clear your browser cache by pressing CTRL+F5 after updating the system.') ?><button
                type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="text-center"><span
                                class="badge bg-primary"><?= labels('current_version', 'Current version') . ' - ' ?>
                                {{ get_current_version() }}</span>
                        </div>
                        <form class="form-horizontal" id="system-update" action="{{ url('admin/settings/system-update') }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <div class="dropzone w-100 d-flex justify-content-center align-items-center"
                                    id="system-update-dropzone">

                                </div>
                                <div class="form-group mt-4 text-center">
                                    <button type="submit" class="btn btn-primary"
                                        id="system_update_btn"><?= labels('update_the_system', 'Update the system') ?></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
