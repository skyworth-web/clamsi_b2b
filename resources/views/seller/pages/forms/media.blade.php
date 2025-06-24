@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.media', 'Media') }}
@endsection
@section('content')
    <section class="main-content">
        <div class="container-fluid mt-5 mb-5 px-6">
            <x-seller.breadcrumb :title="labels('admin_labels.media', 'Media')" :subtitle="labels('admin_labels.take_command_of_your_media', 'Take Command Of Your Media')" :breadcrumbs="[
                ['label' => labels('admin_labels.media_management', 'Media Management')],
                ['label' => labels('admin_labels.add_media', 'Add Media')],
            ]" />

            <section class="overview-data">
                <div class="card content-area p-4 ">

                    <div class="mt-4 col-md-12 additional-info-nav-header d-flex">
                        <div class="col-md-8">
                            <nav class="w-100">
                                <div class="nav nav-tabs" id="media-tab" role="tablist">
                                    <a class="nav-item nav-link active" data-bs-toggle="tab" href="#media-list"
                                        role="tab" aria-controls="media-list"
                                        aria-selected="true">{{ labels('admin_labels.select_file', 'Select File') }}</a>
                                    <a class="nav-item nav-link" data-bs-toggle="tab" href="#media-upload" role="tab"
                                        aria-controls="media-upload"
                                        aria-selected="false">{{ labels('admin_labels.upload_new', 'Upload New') }}</a>
                                </div>
                            </nav>
                        </div>
                        <div class="col-md-4">
                            <div class="align-items-center d-flex form-group justify-content-end gap-3">
                                <div class="col-md-6">
                                    <select class="form-select" id="media-type">
                                        <option value="">Media Type</option>
                                        <option value="image">Images</option>
                                        <option value="audio">Audio</option>
                                        <option value="video">Video</option>
                                        <option value="archive">Archive</option>
                                        <option value="spreadsheet">Spreadsheet</option>
                                        <option value="document">Documents</option>
                                    </select>
                                </div>
                                <div class="input-group search-input-grp product-search ">
                                    <span class="search-icon"><i class='bx bx-search-alt'></i></span>
                                    <input type="text" name="search_products" class="form-control" id="search_products"
                                        value="" placeholder="Search Media">
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="tab-content p-3 col-md-12" id="nav-tabContent">
                        <div class="tab-pane fade active show" id="media-list" role="tabpanel"
                            aria-labelledby="media-list-tab">

                            <div class="row media-card-container">
                                @foreach ($media as $row)
                                    <div class="col-md-6 col-xl-3 col-xxl-2 col-sm-6 col-xxs-12 col-xs-6 mt-5">
                                        <div class="media-card">
                                            <div class="media-image-box">

                                                @php

                                                    $isPublicDisk = $row->disk == 'public' ? 1 : 0;
                                                    $imagePath = $isPublicDisk
                                                        ? getImageUrl(
                                                            $row->sub_directory . '/' . $row->file_name,
                                                            '',
                                                            '',
                                                            $row->type,
                                                        )
                                                        : $row->object_url;
                                                $delete_url = route('seller.media.destroy', $row->id); @endphp
                                                <a href="{{ $imagePath }}" data-lightbox="image-' . $row->id . '">
                                                    <img src="{{ route('seller.dynamic_image', [
                                                        'url' => $imagePath,
                                                        'width' => 120,
                                                        'quality' => 90,
                                                    ]) }}"
                                                        alt="Avatar" class="rounded">
                                                </a>
                                            </div>
                                            <div class="media-title">
                                                <h6>{{ Str::limit($row->name, 22, '...') }}</h6>
                                            </div>
                                            <div class="media-details d-flex justify-content-between">
                                                <p class="text-muted">{{ $row->size }} KB</p>
                                                <div class="d-flex">
                                                    <div
                                                        class="align-items-center d-flex delete-media justify-content-center me-1">
                                                        <a class="delete-data" data-url="{{ $delete_url }}"><i
                                                                class='bx bx-trash'></i></a>
                                                    </div>
                                                    <span
                                                        class='path d-none'>{{ config('app.url') . 'storage' . $row->sub_directory . '/' . $row->file_name }}</span>
                                                    <div
                                                        class="copy-to-clipboard me-1 align-items-center d-flex justify-content-center">
                                                        <a><i class='bx bx-copy-alt'></i></a>
                                                    </div>
                                                    <span
                                                        class='relative-path d-none'>{{ $row->sub_directory . '/' . $row->file_name }}</span>
                                                    <div
                                                        class="copy-relative-path me-1 align-items-center d-flex justify-content-center">
                                                        <a><i class='bx bx-images'></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="card-footer text-muted">
                                <div class="align-items-center d-flex justify-content-between">
                                    <div class="float-left pagination-detail">

                                        <div class="page-list">
                                            <div class="btn-group dropup">
                                                <button class="btn btn-undefined dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-expanded="false">
                                                    <span class="page-size">{{ request('limit', 25) }}</span>
                                                    <i class="bx bx-chevron-up"></i>
                                                </button>
                                                <div class="dropdown-menu media-pagination">
                                                    <a class="dropdown-item {{ request('limit', 25) == 25 ? 'active' : '' }}"
                                                        href="{{ route('seller.media', ['limit' => 25]) }}">25</a>
                                                    <a class="dropdown-item {{ request('limit', 25) == 50 ? 'active' : '' }}"
                                                        href="{{ route('seller.media', ['limit' => 50]) }}">50</a>
                                                    <a class="dropdown-item {{ request('limit', 25) == 75 ? 'active' : '' }}"
                                                        href="{{ route('seller.media', ['limit' => 75]) }}">75</a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 float-right ms-2 pagination-sm pe-6">
                                        {{ $media->onEachSide(1)->appends(request()->query())->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade " id="media-upload" role="tabpanel" aria-labelledby="media-upload-tab">
                            <form action="{{ route('seller.media.upload') }}" class="submit_form"
                                enctype="multipart/form-data" method="POST">
                                @csrf
                                <input type="file" class="filepond" name="documents[]" multiple
                                    data-max-file-size="300MB" data-max-files="100" />
                                <button type="submit" class="btn btn-primary float-end submit_button">Upload</button>
                            </form>
                        </div>
                    </div>
            </section>
        </div>
    </section>
@endsection
