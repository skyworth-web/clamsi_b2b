@if (
    $settings->shipping_mode == 1 &&
        $settings->return_mode == 1 &&
        $settings->support_mode == 1 &&
        $settings->safety_security_mode == 1)
    <section class="section service-section pb-0">
        <div class="container-fluid">
            <div
                class="service-info home_theme_two_service_icon row col-row row-cols-lg-4 row-cols-md-4 row-cols-sm-2 row-cols-2 text-center">
                <div class="service-wrap col-item">
                    <div class="service-icon mb-3">
                        <i class="icon anm anm-phone-call-l"></i>
                    </div>
                    <div class="service-content">
                        <h3 class="title mb-2">{{ $settings->support_title }}</h3>
                        <span class="text-muted">{{ $settings->support_description }}</span>
                    </div>
                </div>
                <div class="service-wrap col-item">
                    <div class="service-icon mb-3">
                        <i class="icon anm anm-truck-l"></i>
                    </div>
                    <div class="service-content">
                        <h3 class="title mb-2">{{ $settings->shipping_title }}</h3>
                        <span class="text-muted">{{ $settings->app_short_description }}</span>
                    </div>
                </div>
                <div class="service-wrap col-item">
                    <div class="service-icon mb-3">
                        <i class="icon anm anm-credit-card-l"></i>
                    </div>
                    <div class="service-content">
                        <h3 class="title mb-2">{{ $settings->safety_security_title }}</h3>
                        <span class="text-muted">{{ $settings->safety_security_description }}</span>
                    </div>
                </div>
                <div class="service-wrap col-item">
                    <div class="service-icon mb-3">
                        <i class="icon anm anm-redo-l"></i>
                    </div>
                    <div class="service-content">
                        <h3 class="title mb-2">{{ $settings->return_title }}</h3>
                        <span class="text-muted">{{ $settings->return_description }}</span>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endif
