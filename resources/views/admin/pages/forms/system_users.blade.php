@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.system_users', 'System Users') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.system_users', 'System Users')" :subtitle="labels(
        'admin_labels.effortlessly_administer_and_control_system_users',
        'Effortlessly Administer and Control System Users',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.home', 'Home'), 'url' => route('admin.home')],
        ['label' => labels('admin_labels.system_users', 'System Users')],
        ['label' => labels('admin_labels.add_user', 'Add User')],
    ]" />

    <div>
        <form class="form-horizontal form-submit-event submit_form" action="{{ route('system_users.store') }}" method="POST"
            id="" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-12 col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.manage_system_users', 'Manage System Users') }}
                            </h5>
                            <div class="card-body row">
                                <div class="">
                                    <!-- form start -->
                                    <div class="form-group">
                                        <label for="username"
                                            class="control-label">{{ labels('admin_labels.user_name', 'User Name') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <input type="text" class="form-control" name="username" id="username"
                                                value=" ">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="mobile"
                                            class="control-label">{{ labels('admin_labels.mobile', 'Mobile') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <input type="number" maxlength="16" oninput="validateNumberInput(this)"
                                                class="form-control" name="mobile" id="mobile" value=" ">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="email"
                                            class="control-label">{{ labels('admin_labels.email', 'Email') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <input type="email" class="form-control" name="email" id="email"
                                                value=" ">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="password"
                                            class="control-label">{{ labels('admin_labels.password', 'Password') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <div class="input-group">
                                                <input type="password" class="form-control show_seller_password"
                                                    name="password" placeholder="Enter Your Password">
                                                <span class="input-group-text cursor-pointer toggle_password"><i
                                                        class="bx bx-hide"></i></span>
                                            </div>
                                        </div>




                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password"
                                            class="control-label">{{ labels('admin_labels.confirm_password', 'Confirm Password') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <div class="input-group">
                                                <input type="password" class="form-control" name="confirm_password"
                                                    placeholder="Enter your password" aria-describedby="password">
                                                <span class="input-group-text cursor-pointer toggle_confirm_password"><i
                                                        class="bx bx-hide"></i></span>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="form-group">
                                        <label for="role"
                                            class="control-label">{{ labels('admin_labels.role', 'Role') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="col-md-12">
                                            <select class="form-control system-user-role form-select" name="role">
                                                <option value=" ">---Select role---</option>
                                                <option value="1">Super Admin</option>
                                                <option value="5">Admin</option>
                                                <option value="6">Editor</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <div class="form-group" id="error_box">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="reset"
                                            class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                        <button type="submit"
                                            class="btn btn-primary submit_button">{{ labels('admin_labels.add_user', 'Add User') }}</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-8 col-md-12 permission-table mt-md-0 mt-sm-2">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="mb-4">
                                {{ labels('admin_labels.permissions', 'Permissions') }}
                            </h5>
                            <div class="row">
                                <div class="col-xl">
                                    <div class="mb-4">
                                        <div class="p-0">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-hover">
                                                    <thead>
                                                        <tr>
                                                            <th>Permissions/Modules</th>
                                                            <th>View</th>
                                                            <th>Create</th>
                                                            <th>Edit</th>
                                                            <th>Delete</th>
                                                        </tr>
                                                    </thead>

                                                    @foreach ($permissions as $section => $permission)
                                                        <tbody>

                                                            <tr>
                                                                <td>{{ ucfirst($section) }}</td>
                                                                <td>
                                                                    <div class="form-check ">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $section }}][view]"
                                                                            value="view {{ $section }}">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $section }}][create]"
                                                                            value="create {{ $section }}">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $section }}][edit]"
                                                                            value="edit {{ $section }}">
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="form-check">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $section }}][delete]"
                                                                            value="delete {{ $section }}">
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
