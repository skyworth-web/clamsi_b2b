@extends('admin.layout')
@section('title')
    <?= 'Update System Users' ?>
@endsection
@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <!-- Basic Layout -->

        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3"> {{ $user->username }}</h4>
                            <form method="post"
                                class="submit_form"action="{{ route('system_users.permissions_update', $user->id) }}">
                                @csrf
                                @method('PUT')
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover">
                                        <thead>
                                            <tr>
                                                <th>Module/Permissions</th>
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
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="permissions[{{ $section }}][view]"
                                                                {{ $permission['view ' . $section] ? 'checked' : '' }}
                                                                value="view {{ $section }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="permissions[{{ $section }}][create]"
                                                                {{ $permission['create ' . $section] ? 'checked' : '' }}
                                                                value="create {{ $section }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="permissions[{{ $section }}][edit]"
                                                                {{ $permission['edit ' . $section] ? 'checked' : '' }}
                                                                value="edit {{ $section }}">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <input type="checkbox" class="form-check-input"
                                                                name="permissions[{{ $section }}][delete]"
                                                                {{ $permission['delete ' . $section] ? 'checked' : '' }}
                                                                value="delete {{ $section }}">
                                                        </div>
                                                    </td>
                                                </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-primary mt-2  align-content-end submit_button"
                                        type="submit">{{ labels('admin_labels.update_permissions', 'Update Permissions') }}</button>
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
