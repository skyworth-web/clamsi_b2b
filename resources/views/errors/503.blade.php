<!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="description">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Coming Soon - {{config('app.name')}}</title>
    <link rel="shortcut icon" href="assets/images/favicon.png" />
    <link rel="stylesheet" href="{{ asset('frontend/elegant/css/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/elegant/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('frontend/elegant/css/responsive.css') }}">

</head>

<body class="template-password coming-soon-page">
    <!--Page Wrapper-->
    <div class="page-wrapper">
        <!-- Body Container -->
        <div id="page-content" class="p-0 m-0 vh-100 min-vh-100">
            <!-- Coming-soon -->
            <div class="password-page container">
                <!-- Main Content -->
                @php
                    $logo = asset(config('constants.DEFAULT_LOGO'));
                @endphp
                <div class="password-main d-flex-justify-center flex-column flex-nowrap text-center vh-100">
                    <a href="index.html" class="password-logo mb-4"><img src="{{$logo}}"
                            alt="logo" width="149" height="39" /></a>
                    <h2 class="password-title">We're Coming Soon</h2>
                    <p class="password-message fs-6 mb-4">We will launch it Very soon</p>
                    <!--End Countdown Timer-->
                </div>
                <!-- End Main Content -->
                <!-- Login Modal -->
                <div class="modal fade password-loginModal" id="LoginModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <button type="button" class="btn-close modal-close" data-bs-dismiss="modal"
                                aria-label="Close" data-bs-toggle="tooltip" data-bs-placement="top"
                                title="Close"></button>
                            <div class="modal-body p-4">
                                <h3 class="password-form-heading mb-3 text-center">Enter store using password</h3>
                                <!-- Login Form -->
                                <form method="post" action="#" id="login_password_form" accept-charset="UTF-8"
                                    class="login-password-form">
                                    <label for="" class="d-none">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control input-group-field" name="password"
                                            value="" placeholder="Your password" required />
                                        <button type="submit" class="action d-flex-justify-center btn">Enter</button>
                                    </div>
                                </form>
                                <p class="text-center mt-4">Are you the store owner? <a href="/admin"
                                        class="text-link">Log in here</a></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('frontend/elegant/js/plugins.js') }}"></script>
        <script src="{{ asset('frontend/elegant/js/main.js') }}"></script>

    </div>
    <!--End Page Wrapper-->
</body>

</html>
