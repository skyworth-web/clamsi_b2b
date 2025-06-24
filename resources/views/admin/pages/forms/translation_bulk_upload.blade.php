@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.bulk_upload', 'Multi Language Bulk Import') }}
@endsection
@section('content')
    <x-admin.breadcrumb :title="labels('admin_labels.bulk_upload', 'Multi Language Bulk Import')" :subtitle="labels(
        'admin_labels.simplify_tasks_with_powerful_bulk_upload_capabilities',
        'Simplify Tasks with Powerful Multi Language Bulk Import Capabilities.',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.language', 'Language')],
        ['label' => labels('admin_labels.bulk_upload', 'Multi Language Bulk Import')],
    ]" />

    <div class="row">
        <div class="col-md-12 col-lg-6">
            <div class="card">
                <form class="form-horizontal" action="{{ route('admin.translation_bulk_upload') }}" method="POST"
                    id="translation_bulk_upload_form">
                    @csrf
                    <div class="card-body">
                        <h5 class="mb-3">
                            {{ labels('admin_labels.bulk_upload', 'Multi Language Bulk Import') }}
                        </h5>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="type" class="form-label">{{ labels('admin_labels.type', 'Type') }}
                                        <span class='text-asterisks text-sm'>*</span></label>
                                    <select class="form-control form-select" name="type" id="type">
                                        <option value="">Select</option>
                                        <option value="brands">Brands</option>
                                        <option value="categories">Categories</option>
                                        <option value="cities">Cities</option>
                                        <option value="stores">Stores</option>
                                        <option value="taxes">Taxes</option>
                                        <option value="products">Products</option>
                                        <option value="combo_products">Combo Products</option>
                                        <option value="offers">Offers</option>
                                        <option value="offer_sliders">Offer Sliders</option>
                                        <option value="promo_codes">Promo Codes</option>
                                        <option value="sections">Featured Sections</option>
                                        <option value="zones">Zones</option>
                                        <option value="blog_categories">Blog Categories</option>
                                        <option value="blogs">Blogs</option>
                                        <option value="category_sliders">Category Sliders</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <label for="file">{{ labels('admin_labels.file', 'File') }}
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
                                <label for="file">{{ labels('admin_labels.instruction_files', 'Sample Files') }}</label>
                                <div class="col-md-3">
                                    <div class="form-group mt-2">
                                        <a href="{{ asset('storage/bulk_translation.zip') }}"
                                            class="btn btn-primary btn-sm instructions_files"
                                            download="bulk_translation.zip">{{ labels('admin_labels.download', 'Download') }}
                                            <i class="fas fa-download mx-2"></i></a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mt-2">
                                        <a href="{{ url('admin/export/translation_csv') }}"
                                            class="btn btn-primary btn-sm instructions_files">
                                            {{ labels('admin_labels.download', 'Download Data for Translation') }}<i
                                                class="fas fa-download mx-2"></i>
                                        </a>
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
                    <li>For adding bulk translation, the file should be in .csv format</li>
                    <li>When you download data for translation using the "Download Data for Translation" button, the ZIP
                        file contains all the data, not store-wise.</li>
                    <li><b>Make sure you enter valid data as per instructions before proceeding</b></li>
                </ul>
            </div>
        </div>
    </div>
@endsection
