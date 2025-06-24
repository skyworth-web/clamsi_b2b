@if (
    $settings->shipping_mode == 1 &&
        $settings->return_mode == 1 &&
        $settings->support_mode == 1 &&
        $settings->safety_security_mode == 1)
        <section class="section service-section pb-4">
            {{-- <div class="container-fluid">
                <div
                    class="service-info brd-box text-center home_theme_five_service_icon service-slider-4items gp15 arwOut5 hov-arrow slick-initialized slick-slider">
                    <div class="slick-list draggable">
                        <div class="slick-track">
                            <div class="slick-slide slick-active home_theme_five_service_box" data-slick-index="2" aria-hidden="false">
                                <div>
                                    <div class="service-wrap col-item">
                                        <div class="box border rounded-5 p-4">
                                            <div class="service-icon mb-3">
                                                <i class="icon anm anm-chat"></i>
                                            </div>
                                            <div class="service-content">
                                                <h3 class="text-uppercase mb-2">{{ $settings->support_title }}</h3>
                                                <span class="text-muted">{{ $settings->support_description }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="slick-slide slick-active home_theme_five_service_box" data-slick-index="3" aria-hidden="false">
                                <div>
                                    <div class="service-wrap col-item">
                                        <div class="box border rounded-5 p-4">
                                            <div class="service-icon mb-3">
                                                <i class="icon anm anm-vh-bus-l"></i>
                                            </div>
                                            <div class="service-content">
                                                <h3 class="text-uppercase mb-2">{{ $settings->shipping_title }}</h3>
                                                <span class="text-muted">{{ $settings->shipping_description }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="slick-slide slick-current slick-active home_theme_five_service_box" data-slick-index="0"
                                aria-hidden="false">
                                <div>
                                    <div class="service-wrap col-item">
                                        <div class="box border rounded-5 p-4">
                                            <div class="service-icon mb-3">
                                                <i class="icon anm anm-redo-l"></i>
                                            </div>
                                            <div class="service-content">
                                                <h3 class="text-uppercase mb-2">{{ $settings->return_title }}</h3>
                                                <span class="text-muted">{{ $settings->return_description }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="slick-slide slick-active home_theme_five_service_box" data-slick-index="1" aria-hidden="false">
                                <div>
                                    <div class="service-wrap col-item">
                                        <div class="box border rounded-5 p-4">
                                            <div class="service-icon mb-3">
                                                <i class="icon anm anm-shield"></i>
                                            </div>
                                            <div class="service-content">
                                                <h3 class="text-uppercase mb-2">{{ $settings->safety_security_title }}</h3>
                                                <span class="text-muted">{{ $settings->safety_security_description }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div> --}}
            <div class="container-fluid">
                <div class="service-info brd-box text-center home_theme_five_service_icon service-slider slick-slider">
                    <div class="row">
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
                            <div class="service-wrap col-item">
                                <div class="box border rounded-5 p-4">
                                    <div class="service-icon mb-3">
                                        <i class="icon anm anm-chat"></i>
                                    </div>
                                    <div class="service-content">
                                        <h3 class="text-uppercase mb-2">{{ $settings->support_title }}</h3>
                                        <span class="text-muted">{{ $settings->support_description }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
                            <div class="service-wrap col-item">
                                <div class="box border rounded-5 p-4">
                                    <div class="service-icon mb-3">
                                        <i class="icon anm anm-vh-bus-l"></i>
                                    </div>
                                    <div class="service-content">
                                        <h3 class="text-uppercase mb-2">{{ $settings->shipping_title }}</h3>
                                        <span class="text-muted">{{ $settings->shipping_description }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
                            <div class="service-wrap col-item">
                                <div class="box border rounded-5 p-4">
                                    <div class="service-icon mb-3">
                                        <i class="icon anm anm-redo-l"></i>
                                    </div>
                                    <div class="service-content">
                                        <h3 class="text-uppercase mb-2">{{ $settings->return_title }}</h3>
                                        <span class="text-muted">{{ $settings->return_description }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6 col-md-6 col-lg-3 mb-3">
                            <div class="service-wrap col-item">
                                <div class="box border rounded-5 p-4">
                                    <div class="service-icon mb-3">
                                        <i class="icon anm anm-shield"></i>
                                    </div>
                                    <div class="service-content">
                                        <h3 class="text-uppercase mb-2">{{ $settings->safety_security_title }}</h3>
                                        <span class="text-muted">{{ $settings->safety_security_description }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </section>
@endif
