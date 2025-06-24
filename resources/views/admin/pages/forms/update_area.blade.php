@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_area', 'Update Area') }}
@endsection
@section('content')
    <div class="container-fluid flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Update Area</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="form-group">
                                <form action="{{ url('/admin/area/update/' . $data->id) }}" enctype="multipart/form-data"
                                    method="POST" class="submit_form">
                                    @method('PUT')
                                    @csrf
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="area_name" class="control-label">Area Name <span
                                                            class='text-danger text-xs'>*</span></label>
                                                    <input type="text" class="form-control" name="area_name"
                                                        id="area_name" value="{{ isset($data->name) ? $data->name : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="city"
                                                        class="control-label">{{ labels('admin_labels.city', 'City') }}
                                                        </th> <span class='text-asterisks text-xs'>*</span></label>
                                                    <select class="form-select city_list" name="city" id="">
                                                        @foreach ($city as $row)
                                                            <option value="{{ $row['id'] }}"
                                                                {{ isset($data['city_id']) && $row['id'] == $data['city_id'] ? 'selected' : '' }}>
                                                                {{ $row['name'] }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="zipcode" class="control-label">Zipcode <span
                                                            class='text-danger text-xs'>*</span></label>
                                                    <select class="form-select zipcode_list" name="zipcode" id="">
                                                        @foreach ($zipcodes as $zipcode)
                                                            <option value="{{ $zipcode['id'] }}"
                                                                {{ isset($data['zipcode_id']) && $zipcode['id'] == $data['zipcode_id'] ? 'selected' : '' }}>
                                                                {{ $zipcode['zipcode'] }}
                                                            </option>
                                                        @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="minimum_free_delivery_order_amount"
                                                        class="control-label">Minimum Free
                                                        Delivery Order Amount <span
                                                            class='text-danger text-xs'>*</span></label>
                                                    <input type="number" class="form-control"
                                                        name="minimum_free_delivery_order_amount"
                                                        id="minimum_free_delivery_order_amount" min="0"
                                                        value="{{ isset($data->minimum_free_delivery_order_amount) ? $data->minimum_free_delivery_order_amount : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="delivery_charges" class="control-label">Delivery Charges
                                                        <span class='text-asterisks text-xs'>*</span></label>
                                                    <input type="number" class="form-control" name="delivery_charges"
                                                        id="delivery_charges" min="0"
                                                        value="{{ isset($data->delivery_charges) ? $data->delivery_charges : '' }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3 d-flex justify-content-start">
                                        <button type="submit" class="btn btn-sm btn-primary" id="">Update</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
