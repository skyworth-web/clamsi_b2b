@props(['user_info'])
<?php
$bread_crumb['page_main_bread_crumb'] = labels('front_messages.addresses', 'Addresses');
$language_code = get_language_code();
?>

<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="row">
            <x-utility.my_account_slider.account_slider :$user_info />
            <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                <div class="dashboard-conten h-100">
                    <!-- Address Book -->
                    <div class="h-100" id="address">
                        <div class="address-card mt-0 h-100">
                            <div class="top-sec d-flex-justify-center justify-content-between mb-4">
                                <h2 class="mb-0">{{ labels('front_messages.address_book', 'Address Book') }}</h2>
                                <button wire:ignore type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#addNewModal"><ion-icon name="add-outline"
                                        class="me-1 fs-5"></ion-icon>
                                    {{ labels('front_messages.add_new', 'Add New') }}</button>
                            </div>

                            <div class="address-book-section dashboard-content">
                                @if (count($addresses) < 1)
                                    <div class="d-flex flex-column justify-content-center align-items-center py-5">
                                        <div class="opacity-50"><ion-icon name="location-outline"
                                                class="address-location-icon text-muted"></ion-icon></div>
                                        <div class="fs-6 fw-500">
                                            {{ labels('front_messages.delivery_address_not_added', 'Delivery Address is Not Added Yet') }}
                                        </div>
                                    </div>
                                @endif
                                <div class="row g-4 row-cols-lg-3 row-cols-md-2 row-cols-sm-2 row-cols-1">
                                    @foreach ($addresses as $address)
                                        @php
                                            $address = json_decode(json_encode($address), true);
                                            // dD($address);
                                        @endphp
                                        <div
                                            class="address-select-box {{ $address['is_default'] == 1 ? 'active' : '' }}">
                                            <div class="address-box bg-block">
                                                <div class="top d-flex-justify-center justify-content-between mb-3">
                                                    <h5 class="m-0">{{ $address['name'] }}</h5>
                                                    <span class="product-labels start-auto end-0"><span
                                                            class="lbl pr-label1">{{ $address['type'] }}</span>
                                                </div>
                                                <div class="middle">
                                                    <div class="address mb-2 text-muted">
                                                        <address class="m-0">{{ $address['landmark'] }}
                                                            <br />{{ $address['address'] }},
                                                            {{ getDynamicTranslation('cities', 'name', $address['city_id'], $language_code) }},
                                                            <br />{{ $address['state'] }} , {{ $address['pincode'] }}
                                                        </address>
                                                    </div>
                                                    <div class="number">
                                                        <p>{{ labels('front_messages.mobile', 'Mobile') }}: <a
                                                                href="tel:{{ $address['country_code'] }}{{ $address['mobile'] }}">(+{{ $address['country_code'] }})
                                                                &nbsp; {{ $address['mobile'] }}</a></p>
                                                    </div>
                                                </div>
                                                <div class="bottom d-flex-justify-center justify-content-between">
                                                    <button type="button"
                                                        class="bottom-btn btn btn-gray btn-sm edit-address-btn"
                                                        data-address-id="{{ $address['id'] }}" data-bs-toggle="modal"
                                                        data-bs-target="#addNewModal">
                                                        {{ labels('front_messages.edit', 'Edit') }}
                                                    </button>
                                                    <button wire:click.prevent="setDefault({{ $address['id'] }})"
                                                        class="bottom-btn btn btn-sm {{ $address['is_default'] == 1 ? '' : 'btn-gray' }} ">{{ labels('front_messages.default', 'Default') }}</button>
                                                    <button class="bottom-btn btn btn-gray btn-sm delete_address"
                                                        data-address-id="{{ $address['id'] }}">{{ labels('front_messages.remove', 'Remove') }}</button>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <!-- New Address Modal -->
                            <div wire:ignore.self class="modal fade" id="addNewModal" tabindex="-1"
                                aria-labelledby="addNewModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title" id="addNewModalLabel">
                                                {{ labels('front_messages.address_details', 'Address details') }}</h2>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            {{-- <form class="add-address-from"> --}}
                                            <div class="form-row row-cols-lg-2 row-cols-md-2 row-cols-sm-1 row-cols-1">
                                                <div class="form-group">
                                                    <label for="name"
                                                        class="d-none">{{ labels('front_messages.name', 'Name') }}</label>
                                                    <input wire:model='name' name="name" placeholder="Name"
                                                        value="" id="name" type="text" />
                                                </div>
                                                <div class="form-group" wire:ignore>
                                                    <label for="address-type"
                                                        class="d-none">{{ labels('front_messages.address_type', 'Address type') }}
                                                        <span class="required">*</span></label>
                                                    <select name="type" id="type">
                                                        <option value="">
                                                            {{ labels('front_messages.select_address_type', 'Select Address type') }}
                                                        </option>
                                                        <option value="home">
                                                            {{ labels('front_messages.home', 'Home') }}</option>
                                                        <option value="office">
                                                            {{ labels('front_messages.office', 'Office') }}</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <label for="mobile"
                                                        class="d-none">{{ labels('front_messages.mobile_number', 'Mobile number') }}
                                                        <span class="required">*</span></label>
                                                    <input wire:model='mobile' name="mobile"
                                                        placeholder="Mobile number" value="" id="mobile"
                                                        type="number">
                                                </div>
                                                <div class="form-group">
                                                    <label for="alternate_mobile"
                                                        class="d-none">{{ labels(
                                                            'front_messages.alternative_mobile_number',
                                                            'Alternative
                                                                                                                                                                                                                                    mobile number',
                                                        ) }}
                                                        <span class="required">*</span></label>
                                                    <input wire:model='alternate_mobile' name="alternate_mobile"
                                                        placeholder="Alternative mobile number" value=""
                                                        id="alternate_mobile" type="number">
                                                </div>
                                                <div class="form-group">
                                                    <label for="address"
                                                        class="d-none">{{ labels('front_messages.address', 'Address') }}
                                                        <span class="required">*</span></label>
                                                    <input wire:model='address' name="address" placeholder="Address"
                                                        id="form_address" type="text" />
                                                </div>
                                                <div class="form-group">
                                                    <label for="landmark"
                                                        class="d-none">{{ labels('front_messages.landmark', 'Landmark') }}</label>
                                                    <input wire:model='landmark' name="landmark"
                                                        placeholder="Landmark" value="" id="landmark"
                                                        type="text" />
                                                </div>
                                                <div class="form-group city_list_div">
                                                    <div wire:ignore>
                                                        <label for="city"
                                                            class="d-none">{{ labels('front_messages.city', 'City') }}
                                                            <span class="required">*</span></label>
                                                        <select class="col-md-12 form-control city_list"
                                                            id="city_list" name="city">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="postcode"
                                                        class="d-none">{{ labels('front_messages.post_code', 'Post Code') }}
                                                        <span class="required">*</span></label>
                                                    <input wire:model='pincode' name="pincode"
                                                        placeholder="Post Code" value="" id="postcode"
                                                        type="text" />
                                                </div>
                                                <div class="form-group ">
                                                    <label for="zone"
                                                        class="d-none">{{ labels('front_messages.state', 'Region / State') }}
                                                        <span class="required">*</span></label>
                                                    <input wire:model='state' name="state" placeholder="State"
                                                        value="" id="state" type="text" />
                                                </div>
                                                <div class="form-group country_list_div">
                                                    <div wire:ignore>
                                                        <label for="country"
                                                            class="d-none">{{ labels('front_messages.country', 'Country') }}
                                                            <span class="required">*</span></label>
                                                        <select class="col-md-12 form-control country_list"
                                                            id="country_list" name="country">
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label for="latitude"
                                                        class="d-none">{{ labels('front_messages.latitude', 'Latitude') }}
                                                        <span class="required">*</span></label>
                                                    <input wire:model='latitude' name="latitude"
                                                        placeholder="Latitude" value="" id="latitude"
                                                        type="text">
                                                </div>
                                                <div class="form-group">
                                                    <label for="longitude"
                                                        class="d-none">{{ labels('front_messages.longitude', 'Longitude') }}
                                                        <span class="required">*</span></label>
                                                    <input wire:model='longitude' name="longitude"
                                                        placeholder="Longitude" value="" id="longitude"
                                                        type="text">
                                                </div>
                                            </div>
                                            <input type="hidden" name="edit_address_id" id="edit_address_id"
                                                value="">
                                            <div class="modal-footer justify-content-center">
                                                <button type="submit"
                                                    class="btn btn-primary m-0 add_address"><span>{{ labels('front_messages.add_address', 'Add Address') }}</span></button>
                                            </div>
                                            {{-- </form> --}}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End New Address Modal -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
