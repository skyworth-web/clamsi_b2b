<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $pwa_settings = getSettings('pwa_settings', true);
    $pwa_settings = $pwa_settings ? json_decode($pwa_settings, true) : null;
    $background_color =
        $pwa_settings && isset($pwa_settings['background_color']) ? $pwa_settings['background_color'] : '#b52046';
@endphp

<head>
    <meta name="theme-color" content="{{ $background_color }}" />
    <link rel="apple-touch-icon" href="{{ asset('storage/' . $web_settings['logo']) }}">
    <link rel="manifest" href="{{ route('manifest') }}">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if (!file_exists($sqlDumpPath) && !file_exists($installViewPath))
        <meta name="keywords" content='{{ $metaKeys ?? $system_settings['app_name'] }}'>
        <meta name="description" content='{{ $metaDescription ?? $system_settings['app_name'] }}'>
        <meta name="product_image" property="og:image"
            content='{{ $metaImage ?? asset('storage/' . $web_settings['logo']) }}'>
        <link rel="shortcut icon" href="{{ asset('storage/' . $web_settings['favicon']) }}" type="image/x-icon">
        <title>
            {{ $title ?? '' }} {{ $system_settings['app_name'] }}
        </title>
    @endif
    <meta property="og:image:type" content="image/jpg,png,jpeg,gif,bmp,eps">
    <meta property="og:image:width" content="1024">
    <meta property="og:image:height" content="1024">
    <!-- Livewire scripts -->
    @vite([
        'frontend/elegant/css/plugins.css',
        'frontend/elegant/css/vendor/photoswipe.min.css',
        'frontend/elegant/css/bootstrap-table.min.css',
        'frontend/elegant/css/style.css',
        'frontend/elegant/css/theme.min.css',
        'frontend/elegant/css/theme.min.css',
        'frontend/elegant/css/star-rating.css',
        'frontend/elegant/css/star-rating.min.css',
        'frontend/elegant/css/intlTelInput.css',
        'frontend/elegant/css/select2.min.css',
        'frontend/elegant/css/iziToast.css',
        'frontend/elegant/css/daterangepicker.css',
        'frontend/elegant/css/responsive.css',
        // 'frontend/elegant/css/lightbox.css',
        'frontend/elegant/css/shareon.min.css',
        'frontend/elegant/css/app.css',
        'frontend/elegant/js/firebase-app.js',
        'frontend/elegant/js/firebase-auth.js',
        'frontend/elegant/js/firebase-firestore.js',
        'frontend/elegant/js/bootstrap-table.min.js',
        'frontend/elegant/js/bootstrap-table-export.min.js',
        'frontend/elegant/js/main.js',
        'frontend/elegant/js/daterangepicker.js',
        'frontend/elegant/js/ionicons.js',
        'frontend/elegant/js/star-rating.js',
        'frontend/elegant/js/intlTelInput.js',
        'frontend/elegant/js/iziToast.min.js',
        'frontend/elegant/js/star-rating.min.js',
        'frontend/elegant/js/select2.min.js',
        'frontend/elegant/js/checkout.js',
        'frontend/elegant/js/wallet.js',
        'frontend/elegant/js/custom.js',
    ])

    <script src="{{ asset('frontend/elegant/js/plugins.js') }}"></script>
    <script src="{{ asset('frontend/elegant/js/vendor/jquery.elevatezoom.js') }}"></script>
    <script src="{{ asset('frontend/elegant/js/moment.min.js') }}"></script>
    <script src="{{ asset('frontend/elegant/js/sweetalert2.all.min.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('frontend/elegant/css/swiper-bundle.min.css') }}">
    <script src="{{ asset('frontend/elegant/js/swiper-bundle.min.js') }}"></script>

    <script src="{{ asset('frontend/elegant/js/shareon.iife.js') }}" data-navigate-track="reload"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js" defer></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
</head>

@php
    $is_rtl = session('is_rtl') ?? 0;
@endphp

<body {{ $is_rtl == 1 ? 'dir=rtl' : '' }}>
    <div class="loading-state screen">
        <div class="loader">
            <div class="dot"></div>
            <div class="dot"></div>
            <div class="dot"></div>
        </div>
    </div>
    <input type="hidden" id="user_id" name="user_id" value="{{ auth()->id() ?? '' }}">
    <input type="hidden" id="custom_url" name="custom_url" value="{{ url()->full() }}">
    <input type="hidden" id="current_url" name="current_url" value="{{ url()->current() }}">
    <input type="hidden" id="store_slug" name="store_slug" value="{{ session('store_slug') }}">
    <input type="hidden" id="current_store_id" name="current_store_id" value="{{ session('store_id') }}">
    <input type="hidden" id="default_store_slug" name="default_store_slug"
        value="{{ session('default_store_slug') }}">
    @if (!file_exists($sqlDumpPath) && !file_exists($installViewPath))
        @php
            $currency_code = session('currency') ?? $system_settings['currency_setting']['code'];
            $currency_details = fetchDetails('currencies', ['code' => $currency_code]);
            $currency_symbol = $currency_details[0]->symbol ?? $system_settings['currency_setting']['symbol'];
        @endphp
        <input type="hidden" id="currency" name="currency" value="{{ $currency_symbol }}">

        <livewire:header.header />
    @endif
    {{ $slot }}
    @if (!file_exists($sqlDumpPath) && !file_exists($installViewPath))
        <livewire:footer.footer />
    @endif
    <x-include-modal.modals />
    <link rel="stylesheet" href="{{ asset('frontend/elegant/css/lightbox.css') }}">
    <script src="{{ asset('/sw.js') }}"></script>
    <script>
        if ("serviceWorker" in navigator) {
            // Register a service worker hosted at the root of the
            // site using the default scope.
            navigator.serviceWorker.register("/sw.js").then(
                (registration) => {
                    console.log("Service worker registration succeeded:", registration);
                },
                (error) => {
                    console.error(`Service worker registration failed: ${error}`);
                },
            );
        } else {
            console.error("Service workers are not supported.");
        }
    </script>
</body>


</html>
