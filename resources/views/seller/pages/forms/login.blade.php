<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ getMediaImageUrl($system_settings['favicon']) }}">
    <title>Login | {{ $system_settings['app_name'] }}</title>
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('assets/admin/css/iziToast.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dropzone.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-table.min.css') }}">
    <!-- CSS Files -->

    <link id="pagestyle" href="{{ asset('/assets/css/argon-dashboard.css?v=2.0.4') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/select2.min.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/tagify.min.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/sweetalert2.min.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/style.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/boxicons/css/boxicons.min.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/admin/custom/custome_new.css') }}">
</head>

<body class="">
    <div class="page-header min-vh-100">
        <div class="col-md-12">
            <div class="d-flex flex-column justify-content-center align-items-center">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center text-center">
                            <div class="login-img-box mb-3">
                                @php
                                    $store_logo =
                                        !empty($system_settings['logo']) &&
                                        file_exists(
                                            public_path(config('constants.MEDIA_PATH') . $system_settings['logo']),
                                        )
                                            ? getMediaImageUrl($system_settings['logo'])
                                            : asset('assets/img/default_full_logo.png');
                                @endphp
                                <img src="{{ $store_logo }}" alt="logo" class="img-fluid">
                            </div>
                            <h1 class="font-weight-bolder">
                                {{ labels('admin_labels.admin_login', 'Seller Login') }}
                            </h1>
                            <p class="mb-4 order_page_title">Hey, Enter your details to get sign in to your account</p>
                        </div>

                        <form class="form_authentication" action="{{ route('admin.authenticate') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="form-label"
                                    for="">{{ labels('admin_labels.mobile', 'Mobile') }}<span
                                        class='text-asterisks text-sm'>*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class='bx bx-mobile-alt fs-4'></i>

                                    </span>
                                    <input type="text" maxlength="16" oninput="validateNumberInput(this)"
                                        class="form-control copied_mobile" name="mobile"
                                        placeholder="Enter Your Mobile Number" value={{(config('constants.ALLOW_MODIFICATION') === 0)?'8140535858':''}}>
                                </div>
                            </div>
                            <label class="form-label"
                                for="">{{ labels('admin_labels.password', 'Password') }}<span
                                    class='text-asterisks text-sm'>*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class='bx bx-lock fs-4'></i>
                                </span>
                                <input type="password" class="form-control copied_password" name="password"
                                    id="show_password" placeholder="Enter Your Password" value={{(config('constants.ALLOW_MODIFICATION') === 0)?'12345678':''}}>
                                <span class="input-group-text password_show" onclick="show_password()">
                                    <i class='bx bx-show fs-4'></i>
                                </span>
                                <span class="input-group-text low_vision" onclick="show_password()">
                                    <i class='bx bx-low-vision fs-4'></i>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between mt-4">

                                <a class="view_all"
                                    href="{{ route('password.request') }}">{{ labels('admin_labels.forgot_password', 'Forgot Password') }}?</a>
                            </div>
                            <button type="submit"
                                class="btn btn-lg btn-primary login_button w-100 mt-4 mb-0">{{ labels('admin_labels.sign_in', 'Sign In') }}</button>

                            {{-- show only in demo mode  --}}
                            @if (config('constants.ALLOW_MODIFICATION') === 0)
                                <div class="credential_box mt-4 p-2 d-flex align-items-center">
                                    <div class="flex-grow-1">
                                        <div class="d-flex gap-2">
                                            <p class="data_total_font mb-1">mobile :</p>
                                            <p id="mobileInfo" class="mb-1 data_total_font">8140535858</p>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <p class="data_total_font mb-1">password :</p>
                                            <p id="passwordInfo" class="mb-1 data_total_font">12345678</p>
                                        </div>
                                    </div>
                                    <div class="credential_copy_box">
                                        <i class='bx bx-copy-alt' onclick="copyCombinedInfo()"></i>
                                    </div>
                                </div>
                            @endif
                        </form>
                        <div class="d-flex justify-content-center">
                            <span>Don't have any account?</span>
                            <a target="_blank" href="{{ route('seller.register') }}" class="mx-2">Sign Up</a>
                        </div>
                    </div>
                </div>
                <div class="copyright mt-4">
                    Copyright © {{ date('Y') }} <a href="{{ config('app.url').'admin/home' }}">{{$system_settings['app_name']}}.</a> All rights reserved.
                </div>
            </div>
        </div>
    </div>

    <!--   Core JS Files   -->
    <script src="{{ asset('/assets/admin/js/jquery.min.js') }}"></script>
    <script src="{{ asset('/assets/admin/js/jquery.js') }}"></script>
    <script src="{{ asset('/assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('/assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('/assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('/assets/js/plugins/smooth-scrollbar.min.js') }}"></script>
    <script src="{{ asset('/assets/js/plugins/chartjs.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/iziToast.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dropzone.js') }}"></script>
    <script src="{{ asset('assets/admin/js/bootstrap-table.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tagify.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jstree.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jquery.blockUI.js') }}"></script>
    <script src="{{ asset('assets/admin/js/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tinymce.min.js') }}"></script>
    <script src="{{ asset('/assets/js/boxicons.js') }}">
        < script >
            var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{ asset('/assets/js/argon-dashboard.min.js?v=2.0.4') }}"></script>

    <script src="{{ asset('assets/admin/custom/custom.js') }}"></script>
</body>

</html>
