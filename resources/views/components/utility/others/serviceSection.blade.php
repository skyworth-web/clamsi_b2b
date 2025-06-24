<div class="container">
    <div class="service-info row col-row row-cols-lg-4 row-cols-md-4 row-cols-sm-2 row-cols-2 text-center">
        @if ($web_settings['support_mode'] == 1)
            <div class="service-wrap col-item">
                <div class="service-icon mb-3">

                    <i class="icon anm anm-phone-call-l"></i>
                </div>
                <div class="service-content">
                    <h3 class="title mb-2">{{ $web_settings['support_title'] }}</h3>
                    <span class="text-muted">{{ $web_settings['support_description'] }}</span>
                </div>
            </div>
        @endif
        @if ($web_settings['shipping_mode'] == 1)
            <div class="service-wrap col-item">
                <div class="service-icon mb-3">
                    <i class="icon anm anm-truck-l"></i>
                </div>
                <div class="service-content">
                    <h3 class="title mb-2">{{ $web_settings['shipping_title'] }}</h3>
                    <span class="text-muted">{{ $web_settings['shipping_description'] }}</span>
                </div>
            </div>
        @endif
        @if ($web_settings['safety_security_mode'] == 1)
            <div class="service-wrap col-item">
                <div class="service-icon mb-3">
                    <i class="icon anm anm-credit-card-l"></i>
                </div>
                <div class="service-content">
                    <h3 class="title mb-2">{{ $web_settings['safety_security_title'] }}</h3>
                    <span class="text-muted">{{ $web_settings['safety_security_description'] }}</span>
                </div>
            </div>
        @endif
        @if ($web_settings['return_mode'] == 1)
            <div class="service-wrap col-item">
                <div class="service-icon mb-3">
                    <i class="icon anm anm-redo-l"></i>
                </div>
                <div class="service-content">
                    <h3 class="title mb-2">{{ $web_settings['return_title'] }}</h3>
                    <span class="text-muted">{{ $web_settings['return_description'] }}</span>
                </div>
            </div>
        @endif
    </div>
</div>
