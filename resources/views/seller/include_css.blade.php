<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="apple-touch-icon" sizes="76x76" href="./assets/img/apple-icon.png">
    <link rel="icon" type="image/png" href="{{ getMediaImageUrl($system_settings['favicon']) }}">
    <title>@yield('title') | {{ $system_settings['app_name'] }}</title>
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />


    <!-- Nucleo Icons -->
    <link href="{{ asset('/assets/css/nucleo-icons.css') }}" rel="stylesheet" />
    <link href="{{ asset('/assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <!-- Font Awesome Icons -->

    <link href="{{ asset('/assets/css/nucleo-svg.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/iziToast.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/dropzone.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/fontawesome/css/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/boxicons/css/boxicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap-table.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/bootstrap.min.css') }}">



    <link rel="stylesheet" href="{{ asset('assets/admin/css/daterangepicker.css') }}">



    <!-- =================== css files for stepper ========================================= -->

    <link id="pagestyle" href="{{ asset('/assets/admin/css/nouislider.min.css') }}" rel="stylesheet" />


    <!-- filepond Css -->
    <link href="/assets/filepond/dist/filepond.css" rel="stylesheet" type="text/css" />
    <link href="/assets/filepond/dist/filepond-plugin-image-preview.css" rel="stylesheet" type="text/css" />
    <link href="/assets/filepond/dist/filepond-plugin-pdf-preview.min.css" rel="stylesheet" type="text/css" />
    <link href="/assets/filepond/dist/filepond-plugin-media-preview.css" rel="stylesheet" type="text/css" />
    <link href="/assets/filepond/dist/filepond-plugin-media-preview.min.css" rel="stylesheet" type="text/css" />


    <!-- Rating css -->
    <link id="pagestyle" href="{{ asset('/assets/css/jquery.rateyo.min.css') }}" rel="stylesheet" />

    <link id="pagestyle" href="{{ asset('/assets/admin/css/select2.min.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/tagify.min.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/sweetalert2.min.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/style.min.css') }}" rel="stylesheet" />
    <link id="pagestyle" href="{{ asset('/assets/admin/css/lightbox.min.css') }}" rel="stylesheet" />


    <link rel="stylesheet" href="{{ asset('/assets/css/theme.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('/assets/admin/custom/custom.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/admin/custom/custom.css') }}?v={{ \Illuminate\Support\Str::random(10) }}">

</head>
