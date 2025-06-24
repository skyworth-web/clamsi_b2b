@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.delivery_boy_policies', 'Delivery Boy Policies') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp
    <x-admin.breadcrumb :title="labels('admin_labels.delivery_boy_policies', 'Delivery Boy Policies')" :subtitle="labels(
        'admin_labels.ensure_compliance_and_excellence_with_delivery_boy_guidelines',
        'Ensure Compliance and Excellence with Delivery Boy Guidelines',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.policies', 'Policies')],
        ['label' => labels('admin_labels.delivery_boy_policies', 'Delivery Boy Policies')],
    ]" />

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 col-xxl-6">
                            <div class="form-group">
                                <form id="" action="{{ route('delivery_boy_privacy_policy.store') }}"
                                    class="submit_form" enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <div class="card-body p-0">
                                            <div class="form-group">
                                                <h5 class="mb-0">
                                                    {{ labels('admin_labels.delivery_boy_privacy_policy', 'Delivery Boy Privacy Policy') }}
                                                </h5>
                                                <a href="{{ route('delivery_boy_privacy_policy.view') }}" target="_blank"
                                                    class="p-2 badge bg-gradient-info" title="View Privacy Policy"><i
                                                        class="fa fa-eye"></i></a>
                                                <textarea class="form-control addr_editor"name="delivery_boy_privacy_policy" placeholder="Privacy Policy"
                                                    rows="5">{{ isset($delivery_boy_privacy_policy['delivery_boy_privacy_policy']) ? $delivery_boy_privacy_policy['delivery_boy_privacy_policy'] : '' }}</textarea>
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
                                <form id="" action="{{ route('delivery_boy_terms_and_conditions.store') }}"
                                    class="submit_form" enctype="multipart/form-data" method="POST">
                                    @csrf
                                    <div class="form-group">
                                        <div class="card-body p-0">
                                            <div class="form-group">
                                                <h5 class="mb-0">
                                                    {{ labels('admin_labels.delivery_boy_terms_and_conditions', 'Delivery Boy Terms & Conditions') }}
                                                </h5>
                                                <a href="{{ route('delivery_boy_terms_and_conditions.view') }}"
                                                    target="_blank" class="p-2 badge bg-gradient-info"
                                                    title="View Terms & Condition"><i class="fa fa-eye"></i></a>
                                                <textarea class="form-control addr_editor"name="delivery_boy_terms_and_conditions" placeholder="Terms and Conditions"
                                                    rows="5">{{ isset($delivery_boy_terms_and_conditions['delivery_boy_terms_and_conditions']) ? $delivery_boy_terms_and_conditions['delivery_boy_terms_and_conditions'] : '' }}</textarea>
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
