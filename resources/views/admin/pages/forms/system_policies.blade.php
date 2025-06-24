@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.system_policies', 'System Policies') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.system_policies', 'System Policies')" :subtitle="labels(
        'admin_labels.effortlessly_manage_and_enforce_system_policies',
        'Effortlessly Manage and Enforce System Policies',
    )" :breadcrumbs="[['label' => labels('admin_labels.system_policies', 'System Policies')]]" />
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 col-xxl-6">
                            <div class="form-group">
                                <form id="" action="{{ route('privacy_policy.store') }}" class="submit_form"
                                    enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <div class="card-body p-0">
                                            <div class="form-group">
                                                <h5 class="mb-4">
                                                    {{ labels('admin_labels.privacy_policy', 'Privacy Policy') }}
                                                </h5>
                                                <a href="{{ route('privacy_policy.view') }}" target="_blank"
                                                    class="p-2 badge bg-gradient-info" title="View Privacy Policy"><i
                                                        class="fa fa-eye"></i></a>
                                                <textarea class="form-control addr_editor"name="privacy_policy" placeholder="Privacy Policy" rows="5">{{ isset($privacy_policy['privacy_policy']) ? $privacy_policy['privacy_policy'] : '' }}</textarea>
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
                        <div class="col-md-12 col-xxl-6">
                            <div class="form-group">
                                <form id="" action="{{ route('terms_and_conditions.store') }}" class="submit_form"
                                    enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <div class="card-body p-0">
                                            <div class="form-group">
                                                <h5 class="mb-4">
                                                    {{ labels('admin_labels.terms_and_conditions', 'Terms & Conditions') }}
                                                </h5>
                                                <a href="{{ route('terms_and_conditions.view') }}" target="_blank"
                                                    class="p-2 badge bg-gradient-info" title="View Terms & Condition"><i
                                                        class="fa fa-eye"></i></a>
                                                <textarea class="form-control addr_editor"name="terms_and_conditions" placeholder="Terms and Conditions" rows="5">{{ isset($terms_and_conditions['terms_and_conditions']) ? $terms_and_conditions['terms_and_conditions'] : '' }}</textarea>
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
                    </div>
                    <div class="row">
                        <div class="col-md-12 col-xxl-6">
                            <div class="form-group">
                                <form id="" action="{{ route('shipping_policy.store') }}" class="submit_form"
                                    enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <div class="card-body p-0">
                                            <div class="form-group">
                                                <h5 class="mb-4">
                                                    {{ labels('admin_labels.shipping_policy', 'Shipping Policy') }}
                                                </h5>
                                                <a href="{{ route('shipping_policy.view') }}" target="_blank"
                                                    class="p-2 badge bg-gradient-info" title="View Shipping Policy"><i
                                                        class="fa fa-eye"></i></a>
                                                <textarea class="form-control addr_editor"name="shipping_policy" placeholder="Shipping Policy" rows="5">{{ isset($shipping_policy['shipping_policy']) ? $shipping_policy['shipping_policy'] : '' }}</textarea>
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
                        <div class="col-md-12 col-xxl-6">
                            <div class="form-group">
                                <form id="" action="{{ route('return_policy.store') }}" class="submit_form"
                                    enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <div class="card-body p-0">
                                            <div class="form-group">
                                                <h5 class="mb-4">
                                                    {{ labels('admin_labels.return_and_refund_policy', 'Return and Refund Policy') }}
                                                </h5>
                                                <a href="{{ route('return_policy.view') }}" target="_blank"
                                                    class="p-2 badge bg-gradient-info" title="View Return Policy"><i
                                                        class="fa fa-eye"></i></a>
                                                <textarea class="form-control addr_editor"name="return_policy" placeholder="Return Policy" rows="5">{{ isset($return_policy['return_policy']) ? $return_policy['return_policy'] : '' }}</textarea>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
