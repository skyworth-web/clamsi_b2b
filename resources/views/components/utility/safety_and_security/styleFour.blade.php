@if (
    $settings->shipping_mode == 1 &&
        $settings->return_mode == 1 &&
        $settings->support_mode == 1 &&
        $settings->safety_security_mode == 1)
    <div class="top-info-bar bg-dark py-2">
        <div class="container">
            <div class="row justify-content-center align-items-center text-uppercase text-white text-center">
                <!-- Large screens: Show all 4 items -->
                <div class="col-lg-3 d-none d-lg-block">
                    <a class="text-white text-decoration-none">{{ $settings->support_description }}</a>
                </div>
                <div class="col-lg-3 d-none d-lg-block">
                    <a class="text-white text-decoration-none">{{ $settings->safety_security_description }}</a>
                </div>
                <div class="col-lg-3 d-none d-lg-block">
                    <a class="text-white text-decoration-none">{{ $settings->return_description }}</a>
                </div>
                <div class="col-lg-3 d-none d-lg-block">
                    <a class="text-white text-decoration-none">{{ $settings->shipping_description }}</a>
                </div>

                <!-- Medium screens: Show only the first two items -->
                <div class="col-md-6 d-none d-md-block d-lg-none">
                    <a class="text-white text-decoration-none">{{ $settings->support_description }}</a>
                </div>
                <div class="col-md-6 d-none d-md-block d-lg-none">
                    <a class="text-white text-decoration-none">{{ $settings->safety_security_description }}</a>
                </div>

                <!-- Small screens: Show only the first item -->
                <div class="col-12 d-md-none">
                    <a class="text-white text-decoration-none">{{ $settings->support_description }}</a>
                </div>
            </div>
        </div>
    </div>
@endif
