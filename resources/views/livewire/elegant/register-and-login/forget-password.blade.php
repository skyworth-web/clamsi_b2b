@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.password_recovery', 'Password Recovery');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="login-register">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                    <div class="inner h-100">
                        <h2 class="text-center fs-4 mb-3">
                            {{ labels('front_messages.forgot_password', 'Forgot Password') }}</h2>
                        <div class="send-otp-box">
                            <p class="text-center mb-4">{{ labels('front_messages.enter_mobile_number', 'Please enter your Mobile Number below.') }} {{ labels('front_messages.receive_otp', 'You will receive an OTP to verify your mobile number.') }}
                            </p>
                            <div class="form-row">
                                <div class="form-group col-12 mb-4 d-flex flex-column">
                                    <label for="number"
                                        class="d-none">{{ labels('front_messages.enter_your_mobile_number', 'Enter your Mobile Number') }}
                                        <span class="required">*</span></label>
                                    <input type="number" name="number" placeholder="Enter your Mobile Number"
                                        id="number" value="" required />
                                    <div class="d-flex justify-content-center align-content-center my-2">
                                        <div id="recaptcha-container"></div>
                                    </div>
                                    <input type="hidden" name="type" id="type" value="password-recovery">
                                </div>
                                <div class="form-group col-12 mb-0">
                                    <div
                                        class="login-remember-forgot d-flex justify-content-between align-items-center">
                                        <input type="submit" id="send_otp" class="btn btn-primary btn-lg"
                                            value="{{ labels('front_messages.password_reset', 'Password Reset') }}" />
                                        <a href="{{ customUrl('login') }}" wire:navigate
                                            class="d-flex-justify-center btn-link">
                                            <ion-icon name="chevron-back-outline" class="me-1"></ion-icon>
                                            {{ labels('front_messages.back_to_login', 'Back to Login') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="verify-otp-box d-none">
                            <div class="form-row">
                                <div class="form-group col-12 mb-4 d-flex flex-column">
                                    <label for="number"
                                        class="d-none">{{ labels('front_messages.enter_verification_code', 'Enter Verification Code You have Received') }}<span
                                            class="required">*</span></label>
                                    <input type="number" name="verificationCode" placeholder="Verification Code"
                                        id="verificationCode" value="" required />
                                    <input type="hidden" name="type" id="type" value="password-recovery">
                                </div>
                                <div class="form-group col-12 mb-0">
                                    <div
                                        class="login-remember-forgot d-flex justify-content-between align-items-center">
                                        <input type="submit" id="verify_otp" class="btn btn-primary btn-lg"
                                            value="Verify Otp" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <form class="reset-password-form d-none" method="POST"
                            action="{{ url('password-recovery/set-new-password') }}">
                            <p class="text-center mb-4">
                                {{ labels('front_messages.enter_new_password', 'Please enter New Password') }}</p>
                            <div class="form-row">
                                <div class="form-group col-12 mb-4 d-flex flex-column">
                                    <div class="form-group col-12">
                                        <label for="password"
                                            class="d-none">{{ labels('front_messages.password', 'Password') }}
                                            <span class="required">*</span></label>
                                        <input type="password" name="password" placeholder="Password" id="password"
                                            value="" />
                                    </div>
                                    <div class="form-group col-12">
                                        <label for="password_confirmation"
                                            class="d-none">{{ labels('front_messages.confirm_password', 'Confirm Password') }}
                                            <span class="required">*</span></label>
                                        <input type="Password" id="password_confirmation" name="password_confirmation"
                                            placeholder="Confirm Password" />
                                    </div>
                                    <input type="hidden" name="type" id="type" value="password-recovery">
                                </div>
                                <div class="form-group col-12 mb-0">
                                    <div
                                        class="login-remember-forgot d-flex justify-content-between align-items-center">
                                        <input type="submit" id="changePassword" class="btn btn-primary btn-lg"
                                            value="Change Password" />
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <input type="hidden" name="authentication_method" id="authentication_method"
                        value="{{ $authentication_method }}">
                </div>
            </div>
        </div>
    </div>
</div>
