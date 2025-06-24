@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.no_data_found', 'No Data Found') }}
@endsection
@section('content')
    <div class="d-flex justify-content-center align-items-center">
        <img alt="" src="{{ getimageurl('system_images/no_data_found.png') }}" />
    </div>
@endsection
