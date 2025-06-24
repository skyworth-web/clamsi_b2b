@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.add_seller', 'Add Seller') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.add_seller', 'Add Seller')" :subtitle="labels(
        'admin_labels.empower_your_marketplace_with_seamless_seller_integration',
        'Empower Your Marketplace with Seamless Seller Integration.',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.sellers', 'Sellers'), 'url' => route('sellers.index')],
        ['label' => labels('admin_labels.add_seller', 'Add Seller')],
    ]" />

    <div>
        <form action="{{ route('sellers.store') }}" enctype="multipart/form-data" class="submit_form" method="POST">
            @csrf
            <textarea cols="20" rows="20" id="cat_data" name="commission_data" class="image-upload-btn"></textarea>
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-12 col-xxl-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.seller_details', 'Seller Details') }}
                                </h5>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label for="firstName" class="form-label">{{ labels('admin_labels.name', 'Name') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <input class="form-control" type="text" placeholder="John Doe" id="name"
                                            name="name" value="{{ old('name') }}" autofocus />
                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="phone">{{ labels('admin_labels.mobile', 'Mobile') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="phone" name="mobile" maxlength="16"
                                                oninput="validateNumberInput(this)" class="form-control"
                                                placeholder="8787878787" value="{{ old('phone') }}" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label" for="email">{{ labels('admin_labels.email', 'Email') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <input class="form-control" placeholder="johndoe@gmail.com" type="email"
                                                name="email" value="{{ old('email') }}">
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6 form-password-toggle">
                                        <label class="form-label"
                                            for="password">{{ labels('admin_labels.password', 'Password') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">

                                            <input type="password" class="form-control show_seller_password" name="password"
                                                placeholder="Enter Your Password">
                                            <span class="input-group-text cursor-pointer toggle_password"><i
                                                    class="bx bx-hide"></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="password">{{ labels('admin_labels.confirm_password', 'Confirm Password') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="form-control" name="confirm_password"
                                                placeholder="Enter your password" aria-describedby="password" />
                                            <span class="input-group-text cursor-pointer toggle_confirm_password"><i
                                                    class="bx bx-hide"></i></span>
                                        </div>
                                    </div>
                                    <div class="mb-3 col-md-6 form-password-toggle">
                                        <label class="form-label"
                                            for="address">{{ labels('admin_labels.address', 'Address') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <textarea name="address" class="form-control" placeholder="Write here your address">{{ old('address') }}</textarea>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.profile_image', 'Profile image') }}
                                                <span class="text-asterisks text-sm">*</span></label>

                                            <input type="file" class="filepond" name="profile_image"
                                                data-max-file-size="300MB" data-max-files="200" accept="image/*,.webp" />

                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.address_proof', 'Address Proof') }}
                                                <span class="text-asterisks text-sm">*</span></label>

                                            <input type="file" class="filepond" name="address_proof"
                                                data-max-file-size="300MB" data-max-files="200" accept="image/*,.webp" />

                                        </div>
                                    </div>
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.authorized_signature', 'Authorized Signature') }}
                                                <span class="text-asterisks text-sm">*</span></label>

                                            <input type="file" class="filepond" name="authorized_signature"
                                                data-max-file-size="300MB" data-max-files="200" accept="image/*,.webp" />

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xxl-6 mt-md-2 mt-xxl-0">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.comission', 'Commission') }}
                                </h5>
                                <div class="form-group col-md-12">
                                    <label for="commission"
                                        class="col-sm-12 form-label">{{ labels('admin_labels.comission', 'Commission') }}(%)
                                        <small>(Commission(%) to be given to the Super Admin on order item
                                            globally.)</small> </label>

                                    <input type="number" class="form-control" min=0 max=100 id="global_commission"
                                        placeholder="Enter Commission(%) to be given to the Super Admin on order item."
                                        name="global_commission" value="">
                                </div>
                                @php
                                    $category_html = getCategoriesOptionHtml($categories);
                                @endphp
                                <div class="form-group row">
                                    <label for="commission"
                                        class="col-sm-12 form-label">{{ labels('admin_labels.choose_categories_and_commission', 'Choose Categories & Commission') }}(%)
                                    </label>
                                    <div class="image-upload-btn" id="cat_html">
                                        <?= $category_html ?>
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <div class="">
                                        <a href="javascript:void(0)" id="seller_model" data-seller_id="" data-cat_ids=""
                                            class=" btn btn-primary text-white btn-sm"
                                            title="Manage Categories & Commission" data-bs-target="#set_commission_model"
                                            data-bs-toggle="modal">{{ labels('admin_labels.add_category_comission', 'Add Category Commission') }}</a>
                                    </div>
                                </div>
                                <small>(Commission(%) to be given to the Super Admin on order item
                                    by
                                    Category you select.If you do not set the commission beside category then it
                                    will get global commission other wise particular category commission will be
                                    consider.)</small>

                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.bank_details', 'Bank Details') }}
                                </h5>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-12 form-label">{{ labels('admin_labels.account_number', 'Account Number') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="account_number"
                                                placeholder="Account Number" name="account_number"
                                                value="{{ old('account_number') }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-4 form-label">{{ labels('admin_labels.account_name', 'Account Name') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="account_name"
                                                placeholder="Account Name" name="account_name"
                                                value="{{ old('account_name') }}">

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-4 form-label">{{ labels('admin_labels.bank_name', 'Bank Name') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="bank_name"
                                                placeholder="Bank Name" name="bank_name" value="{{ old('bank_name') }}">

                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label for="tax_name"
                                                class="col-sm-4 form-label">{{ labels('admin_labels.bank_code', 'Bank Code') }}
                                                <span class='text-asterisks text-sm'>*</span></label>

                                            <input type="text" class="form-control" id="bank_code"
                                                placeholder="Bank Code" name="bank_code" value="{{ old('bank_code') }}">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xxl-6 mt-md-2 mt-xxl-0">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.store_details', 'Store Details') }}
                                </h5>
                                <div class="row">
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="store_name">{{ labels('admin_labels.store_name', 'Store Name') }}
                                            <span class="text-asterisks text-sm">*</span></label>
                                        <div class="input-group input-group-merge">
                                            <input type="text" name="store_name" class="form-control"
                                                placeholder="starbucks" value="{{ old('store_name') }}" />
                                        </div>

                                    </div>
                                    <div class="mb-3 col-md-6">
                                        <label class="form-label"
                                            for="store_url">{{ labels('admin_labels.store_url', 'Store URL') }}
                                        </label>
                                        <div class="input-group input-group-merge">
                                            <input type="text" name="store_url" class="form-control"
                                                placeholder="starbucks" value="{{ old('store_url') }}" />
                                        </div>

                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.logo', 'Logo') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <input type="file" class="filepond" name="store_logo"
                                                data-max-file-size="300MB" data-max-files="200" accept="image/*,.webp" />


                                        </div>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-phone">{{ labels('admin_labels.store_thumbnail', 'Store Thumbnail') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <input type="file" class="filepond" name="store_thumbnail"
                                                data-max-file-size="300MB" data-max-files="200" accept="image/*,.webp" />

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-company">{{ labels('admin_labels.other_documents', 'Other Documents') }}</label>
                                            <small>({{ $note_for_necessary_documents }})</small>
                                            <input type="file" class="filepond" name="other_documents[]" multiple
                                                data-max-file-size="300MB" data-max-files="200" />
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="form-group col-md-12">
                                        <div class="mb-3">
                                            <label class="form-label"
                                                for="basic-default-company">{{ labels('admin_labels.description', 'Description') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <textarea id="basic-default-message" value="" name="description" class="form-control"
                                                placeholder="Write some description here">{{ old('description') }}</textarea>

                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group city_list_parent">
                                            <label for="city"
                                                class="control-label mb-2 mt-2">{{ labels('admin_labels.city', 'City') }}
                                                <span class='text-asterisks text-xs'>*</span></label>
                                            <select class="form-select city_list" name="city" id="">
                                                <option value=" ">
                                                    {{ labels('admin_labels.select_city', 'Select City') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="city"
                                                class="control-label mb-2 mt-2">{{ labels('admin_labels.zipcode', 'Zipcode') }}
                                                <span class='text-asterisks text-xs'>*</span></label>
                                            <select class="form-select zipcode_list" name="zipcode" id="">
                                                <option value=" ">
                                                    {{ labels('admin_labels.select_zipcode', 'Select Zipcode') }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 ">
                                        <div class="form-group">
                                            <label for="zipcode"
                                                class="form-label">{{ labels('admin_labels.deliverable_type', 'Deliverable Type') }}</label>
                                            <select class="form-select" name="deliverable_type" id="deliverable_type">
                                                <option value="1" selected>All</option>
                                                <option value="2">specific</option>
                                                {{-- <option value="3">Excluded</option> --}}
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cities"
                                                class="form-label">{{ labels('admin_labels.deliverable_zones', 'Deliverable Zones') }}
                                                <span class="text-asterisks text-sm">*</span></label>
                                            <select name="deliverable_zones[]" class="search_zone form-select w-100"
                                                multiple id="deliverable_zones" disabled>
                                                <option value="">
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        class="form-label">{{ labels('admin_labels.status', 'Status') }}</label>
                                    <div class="mt-2">
                                        <div id="stsatus" class="btn-group d-flex justify-content-center"
                                            role="group" aria-label="Status">
                                            <label class="btn status_button btn-outline-secondary flex-fill">
                                                <input type="radio" name="status" class="mx-1" value="0">
                                                Deactive
                                            </label>
                                            <label class="btn status_button btn-outline-primary flex-fill">
                                                <input type="radio" name="status" class="mx-1" value="1">
                                                Approved
                                            </label>
                                            <label class="btn btn-outline-danger flex-fill">
                                                <input type="radio" name="status" class="mx-1" value="2">
                                                Not-Approved
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for=""
                                        class="form-label">{{ labels('admin_labels.store_status', 'Store Status') }}
                                    </label>
                                    <div class="mt-2">
                                        <div id="stsatus" class="btn-group" role="group" aria-label="Status">
                                            <label class="btn btn-outline-primary flex-fill">
                                                <input type="radio" name="store_status" class="mx-1" value="1">
                                                Approved
                                            </label>
                                            <label class="btn btn-outline-danger flex-fill">
                                                <input type="radio" name="store_status" class="mx-1" value="2">
                                                Not-Approved
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 col-xxl-6 mt-md-2 mt-xxl-0">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">
                                    {{ labels('admin_labels.other_details', 'Other Details') }}
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_name"
                                                class="form-label">{{ labels('admin_labels.tax_name', 'Tax Name') }}</label>
                                            <div>
                                                <input type="text" class="form-control" id="tax_name"
                                                    placeholder="Tax Name" name="tax_name">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="tax_number"
                                                class="form-label">{{ labels('admin_labels.tax_number', 'Tax Number') }}
                                            </label>
                                            <div>
                                                <input type="text" class="form-control" id="tax_number"
                                                    placeholder="Tax Number" name="tax_number">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="pan_number"
                                                class="form-label">{{ labels('admin_labels.pan_number', 'Pan Number') }}</label>
                                            <div>
                                                <input type="text" class="form-control" id="pan_number"
                                                    placeholder="Pan Number" name="pan_number">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="latitude"
                                                class="form-label">{{ labels('admin_labels.latitude', 'Latitude') }}</label>
                                            <div>
                                                <input type="text" class="form-control" id="latitude"
                                                    placeholder="Latitude" name="latitude">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="longitude"
                                                class="form-label">{{ labels('admin_labels.longitude', 'Longitude') }}</label>
                                            <div>
                                                <input type="text" class="form-control" id="longitude"
                                                    placeholder="Longitude" name="longitude">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label for="national_identity_card"
                                                class="form-label">{{ labels('admin_labels.national_identity_card', 'National Identity Card') }}
                                            </label>
                                            <div>
                                                <input type="file" class="filepond" name="national_identity_card"
                                                    data-max-file-size="300MB" data-max-files="200"
                                                    accept="image/*,.webp" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="mb-3">{{ labels('admin_labels.permissions', 'Permissions') }}</h5>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="require_products_approval"
                                                class="col-sm-6 col-form-label">{{ labels('admin_labels.require_product_approvel', 'Require Product Approvel') }}?
                                            </label>
                                            <div class="col-sm-6 form-check form-switch">
                                                <input type="checkbox" class="form-check-input mx-2 float-end"
                                                    id="require_products_approval" name="require_products_approval">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="customer_privacy"
                                                class="col-sm-5 col-form-label">{{ labels('admin_labels.view_customer_details', 'View Customer Details') }}?
                                            </label>
                                            <div class="col-sm-7 form-check form-switch">
                                                <input type="checkbox" name="customer_privacy"
                                                    class="form-check-input mx-2 float-end">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row d-none">
                                    <div class="col-md-6">
                                        <div class="form-group row">
                                            <label for="view_order_otp"
                                                class="col-sm-8 col-form-label">{{ labels('admin_labels.view_order_otp_and_change_delivery_status', 'View Order OTP & Can Change Delivery Status') }}?
                                            </label>
                                            <div class="col-sm-4 form-check form-switch">
                                                <input type="checkbox" name="view_order_otp"
                                                    class="form-check-input mx-2 float-end">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="reset" class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                <button type="submit"
                    class="btn btn-primary submit_button">{{ labels('admin_labels.add_seller', 'Add Seller') }}</button>
            </div>
        </form>
    </div>


    {{-- commission modal --}}

    <div class="modal fade" id="set_commission_model" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Categories & Commission(%)</h5>
                    </h5>
                    <div class="d-flex justify-content-end">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                <form class="form-horizontal" id="add-seller-commission-form" action="" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" class="edit_faq_id" name="edit_faq_id">
                    <div class="modal-body">

                        <label for="Categories"
                            class="col-sm-2 form-label">{{ labels('admin_labels.categories', 'Categories') }}</label>

                        <div id="category_section"> </div>

                        <div class="form-group col-md-12">
                            <button type="button" id="add_category" class="btn btn-primary btn-xs">
                                <i class="fa fa-plus"></i> Add More Category </button>
                        </div>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="reset"
                            class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                        <button type="submit" class="btn btn-primary"
                            id="save_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
