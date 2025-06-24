@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}
@endsection
@section('content')
    <div class="container-fluid mt-5 mb-5 px-6">
        <div class="d-flex row align-items-center">
            <div class="col-md-6 page-info-title">
                <h3>{{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}</h3>
                <p class="sub_title">
                    {{ labels('admin_labels.simplify_tasks_with_powerful_bulk_upload_capabilities', 'Simplify Tasks with Powerful Bulk Upload Capabilities.') }}
                </p>
            </div>
            <div class="col-md-6 d-flex justify-content-end">
                <nav aria-label="breadcrumb" class="float-end">
                    <ol class="breadcrumb">
                        <i class='bx bx-home-smile'></i>
                        <li class="breadcrumb-item"><a
                                href="{{ route('seller.home') }}">{{ labels('admin_labels.home', 'Home') }}</a>
                        </li>
                        <li class="breadcrumb-item second_breadcrumb_item">
                            {{ labels('admin_labels.product', 'Product') }}
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12 col-lg-6">
                <div class="card">
                    <form class="form-horizontal" action="{{ route('seller.combo.product.process_bulk_upload') }}"
                        method="POST" id="bulk_upload_form">
                        @csrf
                        <div class="card-body">
                            <h5 class="mb-3">
                                {{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}
                            </h5>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="type"
                                            class="form-label">{{ labels('admin_labels.type', 'Type') }}
                                            <small>[upload/update]</small>
                                            <span class='text-asterisks text-sm'>*</span></label>
                                        <select class='form-control form-select' name='type' id='type'>
                                            <option value=''>Select</option>
                                            <option value='upload'>Upload</option>
                                            <option value='update'>Update</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label
                                        for="file">{{ labels('admin_labels.file', 'File') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <input type="file" name="upload_file" class="form-control" accept=".csv" />
                                </div>
                            </div>
                            <div class="row mt-4">
                                <div class="d-flex justify-content-end">
                                    <button type="reset"
                                        class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                                    <button type="submit"
                                        class="btn btn-primary submit_button">{{ labels('admin_labels.submit', 'Submit') }}</button>
                                </div>
                            </div>
                            <div class="mt-4">
                                <div class="row">
                                    <label
                                        for="file">{{ labels('admin_labels.instruction_files', 'Instructions Files') }}</label>
                                    <div class="col-md-3">
                                        <div class="form-group mt-2">
                                            <a href="{{ asset('storage/combo-product-bulk-upload.zip') }}"
                                                class="btn btn-primary btn-sm instructions_files"
                                                download="combo-product-bulk-upload.zip">{{ labels('admin_labels.download', 'Download') }}
                                                <i class="fas fa-download mx-2"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 d-flex justify-content-center form-group">
                                        <div id="upload_result" class="p-3"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-12 col-lg-6 mt-md-2 mt-lg-0">
                <div class="bulk_upload_instruction_card">
                    <ul>
                        <li>Read and follow instructions carefully while preparing data</li>
                        <li>Download and save the sample file to reduce errors</li>
                        <li>For adding bulk products, the file should be in .csv format</li>
                        <li>You can copy the image path from the media section</li>
                        <li><b>Make sure you enter valid data as per instructions before proceeding</b></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
