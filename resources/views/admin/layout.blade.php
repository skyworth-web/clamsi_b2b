<!DOCTYPE html>
<html lang="en">

<meta name="csrf-token" content="{{ csrf_token() }}">

@include('admin.include_css')

<body>
    <div id="db-wrapper">

        <x-admin.side-bar />
        <div id="page-content">
            <x-admin.header />
            <div class="container-fluid mt-5 px-6" {{ session()->get('is_rtl') == 1 ? 'dir=rtl' : '' }}>
                @yield('content')
            </div>
        </div>
    </div>
    <x-admin.footer />
    <!-- Scripts -->
</body>
@include('admin.include_script')

</html>
