@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.account', 'Account') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.account_setting', 'Account Setting')" :subtitle="labels(
        'admin_labels.efficiently_manage_account_with_precision',
        'Efficiently Manage Account With Precision',
    )" :breadcrumbs="[['label' => labels('admin_labels.account_setting', 'Account Setting')]]" />



    <div class="col-md-12 col-xxl-6">
        <div class="card">
            <div class="card-body">
                <form id="validationForm" method="POST" action="/admin/users/update/{{ auth()->user()->id }}"
                    enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12">
                            @php
                                $isPublicDisk = $user->disk == 'public' ? 1 : 0;
                                $imagePath = $isPublicDisk
                                    ? getMediaImageUrl($user->image, 'USER_IMG_PATH')
                                    : $user->image;
                            @endphp

                        </div>
                    </div>

                    <div class="row">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="edit_image" value="{{ $user->image }}">
                        <div class="col-md-12 text-center">
                            <img class="rounded-circle img-fluid"
                                src="{{ route('admin.dynamic_image', [
                                    'url' => $imagePath,
                                    'width' => 120,
                                    'quality' => 90,
                                ]) }}"
                                alt="">
                        </div>
                    </div>
                    <div class="row mt-8">
                        <div class="col-md-12 text-center">
                            <input type="file" class="filepond" name="image" multiple data-max-file-size="30MB"
                                data-max-files="20" accept="image/*,.webp"/ />
                        </div>
                    </div>
                    <div class="row">
                        @csrf
                        @method('PUT')
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="example-text-input"
                                    class="form-control-label mb-2">{{ labels('admin_labels.user_name', 'User Name') }}</label>
                                <input class="form-control" type="text" name="username"
                                    value="{{ $user->username !== 'null' ? $user->username : '' }}" onfocus="focused(this)"
                                    onfocusout="defocused(this)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="example-text-input"
                                    class="form-control-label mb-2">{{ labels('admin_labels.mobile', 'Mobile') }}</label>
                                <input class="form-control" readonly type="number" name="mobile"
                                    value="{{ $user->mobile !== 'null' ? $user->mobile : '' }}" onfocus="focused(this)"
                                    onfocusout="defocused(this)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="example-text-input"
                                    class="form-control-label mb-2">{{ labels('admin_labels.email', 'Email') }}</label>
                                <input class="form-control" readonly type="email" name="email"
                                    value="{{ $user->email !== 'null' ? $user->email : '' }}" onfocus="focused(this)"
                                    onfocusout="defocused(this)">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="old_password"
                                    class="form-control-label mb-2">{{ labels('admin_labels.old_password', 'Old Password') }}</label>
                                <div class="input-group">
                                    <input type="password" class="form-control show_profile_password" name="old_password"
                                        placeholder="Enter Your Password">
                                    <span class="input-group-text cursor-pointer toggle_profile_password"><i
                                            class="bx bx-hide"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_password"
                                    class="form-control-label mb-2">{{ labels('admin_labels.new_password', 'New Password') }}</label>
                                <div class="input-group">
                                    <input type="password" class="form-control show_profile_password" name="new_password"
                                        placeholder="Enter Your Password">
                                    <span class="input-group-text cursor-pointer toggle_profile_password"><i
                                            class="bx bx-hide"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_password_confirmation"
                                    class="form-control-label mb-2">{{ labels('admin_labels.confirm_password', 'Confirm Password') }}</label>
                                <div class="input-group">
                                    <input type="password" class="form-control show_profile_password"
                                        name="new_password_confirmation" placeholder="Confirm Your Password">
                                    <span class="input-group-text cursor-pointer toggle_profile_password"><i
                                            class="bx bx-hide"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="example-text-input"
                                    class="form-control-label mb-2">{{ labels('admin_labels.address', 'Address') }}</label>
                                <textarea name="address" class="form-control" placeholder="Write here your address">{{ $user->address !== 'null' ? $user->address : '' }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="mt-2 d-flex justify-content-end">
                        <button type="submit"
                            class="btn btn-primary submit_button">{{ labels('admin_labels.save_changes', 'Save Changes') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
