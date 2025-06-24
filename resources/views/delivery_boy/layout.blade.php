    <!DOCTYPE html>
    <html lang="en">


    @include('delivery_boy.include_css')

    <body>
        <div id="db-wrapper">

            <x-delivery_boy.side-bar />
            <!-- Page content -->
            <div id="page-content">
                <x-delivery_boy.header />
                <!-- Container fluid -->

                <div class="container-fluid mt-5 px-6" {{ session()->get('is_rtl') == 1 ? 'dir=rtl' : '' }}>
                    @yield('content')
                </div>
            </div>
        </div>
        <x-delivery_boy.footer />
        <!-- Scripts -->
    </body>
    @include('delivery_boy.include_script')

    </html>
