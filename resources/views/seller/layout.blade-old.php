@include('seller.include_css')

<body class="g-sidenav-show">

    <div class="bg-white min-height-100 position-absolute w-100 border-style"></div>
    <x-seller.side-bar />
    <x-seller.header />
    <div id="db-wrapper">
        <main class="main-content border-radius-lg  ps-12">
            <div class="container-fluid py-4">
                <div class="row">
                    @yield('content')
                </div>
            </div>
            <x-seller.footer />
        </main>
    </div>
    <!-- / Layout wrapper -->
    @include('seller.include_script')