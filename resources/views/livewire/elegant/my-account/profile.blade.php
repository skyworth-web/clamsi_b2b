<?php
$img = !empty($user_info->image) && file_exists(public_path(config('constants.USER_IMG_PATH') . $user_info->image)) ? getImageUrl($user_info->image, '', '', 'image', 'USER_IMG_PATH') : getImageUrl('no-user-img.jpeg', '', '', 'image', 'NO_USER_IMAGE');
// dd($cities);
$language_code = get_language_code();
$city_details = fetchDetails('cities', ['id' => $user_info->city], ['id', 'name']);
$city_id = isset($city_details) && !empty($city_details) ? $city_details[0]->id : '';
$city_details = getDynamicTranslation('cities', 'name', $city_id, $language_code);
// dd($city_details);
foreach ($cities as $key => $city) {
    $city_name[$key] = $city->name;
}
foreach ($countries as $key => $country) {
    $country_name[$country->iso2] = $country->name;
}
$bread_crumb['page_main_bread_crumb'] = labels('front_messages.profile', 'Profile');
?>
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="row">
            <x-utility.my_account_slider.account_slider :$user_info :$cities />
            <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                <div class="dashboard-content h-100">
                    <!-- Profile -->
                    <div class="h-100" id="profile">
                        <div class="profile-card mt-0 h-100">
                            <div class="top-sec d-flex-justify-center justify-content-between mb-4">
                                <h2 class="mb-0">{{ labels('front_messages.profile', 'Profile') }}</h2>
                                <button wire:ignore type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#editProfileModal"><ion-icon name="add-outline"
                                        class="me-1 fs-5"></ion-icon>
                                    {{ labels('front_messages.edit', 'Edit') }}</button>
                            </div>
                            <div class="profile-book-section mb-4">
                                <div class="details d-flex align-items-center mb-2">
                                    <div class="left">
                                        <h6 class="mb-0 body-font fw-500">
                                            {{ labels('front_messages.name', 'Name') }}</h6>
                                    </div>
                                    <div class="right">
                                        <p>{{ $user_info->username }}</p>
                                    </div>
                                </div>
                                <div class="details d-flex align-items-center mb-2">
                                    <div class="left">
                                        <h6 class="mb-0 body-font fw-500">{{ labels('front_messages.city', 'City') }}
                                        </h6>
                                    </div>
                                    <div class="right">
                                        {{-- <p>{{ $user_info->city }}</p> --}}
                                        <p>{{ $city_details }}</p>
                                    </div>
                                </div>
                                <div class="details d-flex align-items-center mb-2">
                                    <div class="left">
                                        <h6 class="mb-0 body-font fw-500">
                                            {{ labels('front_messages.country', 'Country') }}</h6>
                                    </div>
                                    <div class="right">
                                        <p>{{ $user_info->country_name }}</p>
                                    </div>
                                </div>
                                <div class="details d-flex align-items-center mb-2">
                                    <div class="left">
                                        <h6 class="mb-0 body-font fw-500">
                                            {{ labels('front_messages.address', 'Street address') }}</h6>
                                    </div>
                                    <div class="right">
                                        <p>{{ $user_info->street }}</p>
                                    </div>
                                </div>
                                <div class="details d-flex align-items-center mb-2">
                                    <div class="left">
                                        <h6 class="mb-0 body-font fw-500">
                                            {{ labels('front_messages.post_code', 'Pin code') }}</h6>
                                    </div>
                                    <div class="right">
                                        <p>{{ $user_info->pincode }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Profile Modal -->
                            @php
                                $user_img = getMediaImageUrl($user_info->image);
                                $user_img = dynamic_image($user_img, 140);
                            @endphp
                            <div wire:ignore.self class="modal fade" id="editProfileModal" tabindex="-1"
                                aria-labelledby="editProfileModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title" id="editProfileModalLabel">
                                                {{ labels('front_messages.edit_profile_details', 'Edit Profile details') }}
                                            </h2>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form id="edit-profile-form" enctype="multipart/form-data">
                                                <div class="form-row">
                                                    <div class="form-group col-lg-12 col-md-12 col-sm-12 col-12 mb-4">
                                                        <div
                                                            class="profileImg img-thumbnail shadow bg-white rounded-circle d-flex-justify-center position-relative mx-auto">
                                                            <img src="{{ $user_img }}" class="rounded-circle"
                                                                alt="profile" />
                                                            <div class="thumb-edit">
                                                                <label for="profile_upload"
                                                                    class="d-flex-center justify-content-center position-absolute top-0 start-100 translate-middle p-2 rounded-circle shadow btn btn-secondary mt-3"
                                                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                                                    title="Edit"><ion-icon wire:ignore
                                                                        name="create-outline"></ion-icon></label>
                                                                <input type="file"
                                                                    accept="image/png, image/jpg, image/jpeg"
                                                                    id="profile_upload" class="image-upload d-none" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-lg-6 col-md-6 col-sm-12 col-12">
                                                        <label for="edit-username"
                                                            class="d-none">{{ labels('front_messages.name', 'Name') }}</label>
                                                        <input name="edit-username" placeholder="name"
                                                            value="{{ $user_info->username }}" id="edit-username"
                                                            type="text" />
                                                    </div>
                                                    <div wire:ignore
                                                        class="form-group col-lg-6 col-md-6 col-sm-12 col-12 city_list_div">
                                                        <label for="editProfile-zone"
                                                            class="d-none">{{ labels('front_messages.city', 'City') }}
                                                            <span class="required">*</span></label>
                                                        <select class="col-md-12 form-control city_list" id="city_list"
                                                            name="edit-city">
                                                            @if ($user_info['city'] != null)
                                                                <option selected="selected">{{ $user_info['city'] }}
                                                                </option>
                                                            @endif
                                                        </select>

                                                    </div>
                                                    <div wire:ignore
                                                        class="form-group col-lg-6 col-md-6 col-sm-12 col-12 country_list_div">
                                                        <label for="country"
                                                            class="d-none">{{ labels('front_messages.country', 'Country') }}<span
                                                                class="required">*</span></label>

                                                        <select class="col-md-12 form-control country_list"
                                                            id="country_list" name="country">
                                                            @if ($user_info['country_name'] != null)
                                                                <option selected="selected">
                                                                    {{ $user_info['country_name'] }}
                                                                </option>
                                                            @endif
                                                        </select>
                                                    </div>
                                                    <div class="form-group col-lg-6 col-md-6 col-sm-12 col-12">
                                                        <label for="editProfile-streetaddress"
                                                            class="d-none">{{ labels('front_messages.address', 'Address') }}</label>
                                                        <input name="editProfile-streetaddress"
                                                            placeholder="Street address"
                                                            value="{{ $user_info->street }}" id="edit-streetaddress"
                                                            type="text" />
                                                    </div>
                                                    <div class="form-group col-lg-6 col-md-6 col-sm-12 col-12">
                                                        <label for="edit-zipcode"
                                                            class="d-none">{{ labels('front_messages.post_code', 'Pin Code') }}</label>
                                                        <input name="edit-zipcode" placeholder="Zip code"
                                                            value="{{ $user_info->pincode }}" id="edit-zipcode"
                                                            type="text" />
                                                    </div>
                                                </div>
                                                <div class="modal-footer justify-content-center">
                                                    <button type="submit"
                                                        class="btn btn-primary m-0 submit_profile_btn"><span>{{ labels('front_messages.save_profile', 'Save Profile') }}</span></button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Edit Profile Modal -->

                            <div class="top-sec d-flex-justify-center justify-content-between mb-4">
                                <h2 class="mb-0">{{ labels('front_messages.login_details', 'Login details') }}</h2>
                                <button wire:ignore type="button" class="btn btn-primary btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#editLoginModal"><ion-icon
                                        name="add-outline" class="me-1 fs-5"></ion-icon>
                                    {{ labels('front_messages.change_password', 'Change Password') }}</button>
                            </div>
                            <div class="profile-login-section">
                                <div class="details d-flex align-items-center mb-2">
                                    <div class="left">
                                        <h6 class="mb-0 body-font fw-500">
                                            {{ labels('front_messages.email', 'Email address') }}</h6>
                                    </div>
                                    <div class="right">
                                        <p>{{ $user_info->email }}</p>
                                    </div>
                                </div>
                                <div class="details d-flex align-items-center mb-2">
                                    <div class="left">
                                        <h6 class="mb-0 body-font fw-500">
                                            {{ labels('front_messages.mobile_number', 'Phone number') }}</h6>
                                    </div>
                                    <div class="right">
                                        <p>+({{ $user_info->country_code }}) &nbsp;{{ $user_info->mobile }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Login details Modal -->
                            <div class="modal fade" id="editLoginModal" tabindex="-1"
                                aria-labelledby="editLoginModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h2 class="modal-title" id="editLoginModalLabel">
                                                {{ labels('front_messages.save_profile', 'Change Password') }}</h2>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <form class="edit-Loginprofile-from">
                                                <div
                                                    class="form-row row-cols-lg-1 row-cols-md-1 row-cols-sm-1 row-cols-1">
                                                    <div class="form-group">
                                                        <label for="editLogin-Password"
                                                            class="d-none">{{ labels('front_messages.current_password', 'Current Password') }}
                                                            <span class="required">*</span></label>
                                                        <input name="editLogin-Password"
                                                            placeholder="Current Password" value=""
                                                            id="current_password" type="password" />
                                                        <ion-icon name="eye-off-outline" class="eye-icon"></ion-icon>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="editLogin-NewPassword"
                                                            class="d-none">{{ labels('front_messages.new_password', 'New Password') }}
                                                            <span class="required">*</span></label>
                                                        <input name="editLogin-NewPassword" placeholder="New Password"
                                                            value="" id="new_password" type="password" />
                                                        <ion-icon name="eye-off-outline" class="eye-icon"></ion-icon>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label for="editLogin-Verify"
                                                            class="d-none">{{ labels('front_messages.verify_password', 'Verify Password') }}<span
                                                                class="required">*</span></label>
                                                        <input name="editLogin-Verify" placeholder="Verify Password"
                                                            value="" id="verify_password" type="password" />
                                                        <ion-icon name="eye-off-outline" class="eye-icon"></ion-icon>

                                                        <small
                                                            class="form-text text-muted">{{ labels('front_messages.confirm_password', 'To confirm, please type your new password again.') }}</small>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="submit"
                                                class="btn btn-primary m-0 change_password"><span>{{ labels('front_messages.save_changes', 'Save changes') }}</span></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
