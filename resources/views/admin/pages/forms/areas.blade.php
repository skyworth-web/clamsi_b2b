@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.areas', 'Areas') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="card mb-1">
            <div class="card-body">
                <h4 class="card-title">Area</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card card-info">
                    <!-- form start -->
                    <div class="card-header">
                        <h5 class="mb-0">Add Area</h5>
                    </div>
                    <form class="form-horizontal form-submit-event submit_form" action="{{ route('admin.area.store') }}"
                        method="POST" id="" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="area_name" class="control-label">Area Name <span
                                                class='text-danger text-xs'>*</span></label>
                                        <input type="text" class="form-control" name="area_name" id="area_name"
                                            value="">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="city" class="control-label">{{ labels('admin_labels.city', 'City') }}</th> <span
                                                class='text-danger text-xs'>*</span></label>
                                        <select class="form-select city_list" name="city" id="">
                                            <option value=" ">{{ labels('admin_labels.select_city', 'Select City') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="zipcode"
                                            class="control-label">{{ labels('admin_labels.zipcodes', 'ZipCode') }}
                                            <span class='text-asterisks text-xs'>*</span></label>
                                        <select class="form-select zipcode_list" name="zipcode" id="">
                                            <option value=" ">Select Pincode</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="minimum_free_delivery_order_amount" class="control-label">Minimum Free
                                            Delivery Order Amount <span class='text-asterisks text-xs'>*</span></label>
                                        <input type="number" class="form-control" name="minimum_free_delivery_order_amount"
                                            id="minimum_free_delivery_order_amount" min="0" value="">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="delivery_charges" class="control-label">Delivery Charges <span
                                                class='text-danger text-xs'>*</span></label>
                                        <input type="number" class="form-control" name="delivery_charges"
                                            id="delivery_charges" min="0" value="">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <button type="reset"
                                    class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                <button type="submit" class="btn btn-primary">Add Area</button>
                            </div>
                            <div class="d-flex justify-content-center">
                                <div class="form-group" id="error_box">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-md-12 main-content mt-4">
                <div class="card content-area p-4">
                    <div class="card-head">
                        <h4 class="card-title">Area Details</h4>
                    </div>
                    <div class="card-innr">
                        <div class="gaps-1-5x"></div>
                        <table class='table table-striped' data-toggle="table" data-loading-template="loadingTemplate" data-url="{{ route('admin.area.list') }}"
                            data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                            data-page-list="[5, 10, 20, 50, 100, 200]" data-search="true" data-show-columns="true"
                            data-show-refresh="true" data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                            data-mobile-responsive="true" data-toolbar="" data-show-export="true"
                            data-maintain-selected="true" data-export-types='["txt","excel"]'
                            data-query-params="queryParams">
                            <thead>
                                <tr>
                                    <th data-field="id" data-sortable="true">
                                        {{ labels('admin_labels.id', 'ID') }}
                                    <th data-field="name" data-disabled="1" data-sortable="false">
                                        {{ labels('admin_labels.name', 'Name') }}</th>
                                    <th data-field="city_name" data-sortable="false">
                                        {{ labels('admin_labels.city_name', 'City Name') }}
                                    </th>
                                    <th data-field="zipcode" data-sortable="false">Zipcode</th>
                                    <th data-field="minimum_free_delivery_order_amount" data-sortable="false">
                                        Minimum Free Delivery Order Amount</th>
                                    <th data-field="delivery_charges" data-sortable="false">Delivery Charges</th>
                                    <th data-field="operate" data-sortable="false">
                                        {{ labels('admin_labels.action', 'Action') }}
                                    </th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
