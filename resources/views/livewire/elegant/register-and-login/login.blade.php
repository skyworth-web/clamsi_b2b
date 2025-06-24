@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.sign_in', 'Sign In');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="login-register pt-2">
            <div class="row">
                <div class="col-12 col-sm-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                    <div class="inner h-100">
                        <form wire:submit="login" class="customer-form" wire:loading.attr="disabled">
                            <h2 class="text-center fs-4 mb-3">
                                {{ labels('front_messages.sign_in', 'Sign In') }}
                            </h2>
                            <p class="text-center mb-4">
                                {{ labels('front_messages.if_you_have_an_account_with_us_please_log_in', 'If you have an account with us, please log in.') }}
                            </p>
                            <div class="form-row justify-content-around">
                                @if ($errors->has('loginError'))
                                    <p class="fw-400 text-danger mt-1">{{ $errors->first('loginError') }}</p>
                                @endif
                                <div class="form-group col-12">
                                    <label for="mobile"
                                        class="d-none">{{ labels('front_messages.mobile_number', 'Mobile Number') }}
                                        <span class="required">*</span></label>
                                    <input wire:model="mobile" type="number" name="mobile"
                                        placeholder="{{ labels('front_messages.mobile_number', 'Mobile Number') }} "
                                        id="mobile" value="" />
                                    @error('mobile')
                                        <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group col-12">
                                    <label for="password"
                                        class="d-none">{{ labels('front_messages.password', 'Password') }}
                                        <span class="required">*</span></label>
                                    <input wire:model="password" type="password" name="password"
                                        placeholder="{{ labels('front_messages.password', 'Password') }}" id="password"
                                        value="" />
                                    <ion-icon name="eye-off-outline" class="eye-icon"></ion-icon>
                                    @error('password')
                                        <p class="fw-400 text-danger mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="form-group col-12">
                                    <div
                                        class="login-remember-forgot d-flex justify-content-between align-items-center">
                                        <div class="remember-check customCheckbox">
                                            <input id="remember" name="remember" type="checkbox" value="remember" />
                                            <label
                                                for="remember">{{ labels('front_messages.remember_me', 'Remember me') }}
                                            </label>
                                        </div>
                                        <a href="{{ customUrl('password-recovery') }}" wire:navigate>
                                            {{ labels('front_messages.forgot_password', 'Forgot your password?') }}
                                        </a>
                                    </div>
                                </div>
                                <div class="form-group col-12 mb-0">
                                    <input type="submit" class="btn btn-primary btn-lg w-100 sign-in"
                                        value="{{ labels('front_messages.sign_in', 'Sign In') }}" />
                                </div>
                            </div>
                        </form>
                        @if ($system_settings['google'] == 1 || $system_settings['facebook'] == 1)
                            <div class="login-divide"><span
                                    class="login-divide-text">{{ labels('front_messages.or', 'OR') }}</span></div>

                            <p class="text-center fs-6 text-muted mb-3">
                                {{ labels('front_messages.sign_in_with_social_account', 'Sign in with social account') }}
                            </p>
                            <div class="login-social d-flex-justify-center">
                                @if ($system_settings['facebook'] == 1)
                                    <a class="social-link facebook rounded-5 d-flex-justify-center"
                                        href="{{ url('auth/facebook') }}">
                                        <i class="anm anm-facebook hdr-icon icon me-2"></i></ion-icon>
                                        {{ labels('front_messages.facebook', 'Facebook') }}</a>
                                @endif
                                @if ($system_settings['google'] == 1)
                                    <a class="social-link google rounded-5 d-flex-justify-center"
                                        href="{{ url('auth/google') }}"><i
                                            class="anm anm-google hdr-icon icon me-2"></i>
                                        {{ labels('front_messages.google', 'Google') }}</a>
                                @endif
                            </div>
                        @endif
                        <div class="login-signup-text mt-4 mb-2 fs-6 text-center text-muted">
                            Don't have account?
                            <a href="{{ customUrl('register') }}" wire:navigate
                                class="btn-link">{{ labels('front_messages.sign_up_now', 'Sign up now') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--End Main Content-->
</div>
