{{-- @dd($settings); --}}
@if (
    $settings->shipping_mode == 1 &&
        $settings->return_mode == 1 &&
        $settings->support_mode == 1 &&
        $settings->safety_security_mode == 1)
    <div class="footer footer-11 ftr-bg-black">
        <!-- Service section -->
        <div class="section-sm service-section">
            <div class="container-fluid">
                <div class="service-info sp-row row row-cols-lg-4 row-cols-md-2 row-cols-sm-2 row-cols-2 text-white">
                    <div class="service-wrap sp-col d-flex-justify-center flex-nowrap">
                        <div class="service-icon d-flex align-items-center">
                            <i class="w-auto h-auto bg-transparent lh-sm icon anm anm-truck-l"></i>
                        </div>
                        <div class="service-content ms-3">
                            <h4 class="mb-1 clr-none">{{ $settings->support_title }}</h4>
                        </div>
                    </div>
                    <div class="service-wrap sp-col d-flex-justify-center flex-nowrap">
                        <div class="service-icon d-flex align-items-center">
                            <i class="w-auto h-auto bg-transparent lh-sm icon anm anm-dollar"></i>
                        </div>
                        <div class="service-content ms-3">
                            <h4 class="mb-1 clr-none">{{ $settings->shipping_title }}</h4>
                        </div>
                    </div>
                    <div class="service-wrap sp-col d-flex-justify-center flex-nowrap">
                        <div class="service-icon d-flex align-items-center">
                            <i class="w-auto h-auto bg-transparent lh-sm icon anm anm-customer-service"></i>
                        </div>
                        <div class="service-content ms-3">
                            <h4 class="mb-1 clr-none">{{ $settings->safety_security_title }}</h4>
                        </div>
                    </div>
                    <div class="service-wrap sp-col d-flex-justify-center flex-nowrap">
                        <div class="service-icon d-flex align-items-center">
                            <i class="w-auto h-auto bg-transparent lh-sm icon anm anm-credit-card1"></i>
                        </div>
                        <div class="service-content ms-3">
                            <h4 class="mb-1 clr-none">{{ $settings->return_title }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End Service section -->
    </div>
@endif
