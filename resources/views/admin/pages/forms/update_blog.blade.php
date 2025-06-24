@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.update_blog', 'Update Blog') }}
@endsection
@section('content')
    @php
        $user = auth()->user();
        $role = auth()->user()->role->name;
    @endphp

    <x-admin.breadcrumb :title="labels('admin_labels.update_blog', 'Update Blog')" :subtitle="labels(
        'admin_labels.craft_compelling_blogs_with_user_friendly_creation_tool',
        'Craft Compelling Blogs with User-Friendly Creation Tool',
    )" :breadcrumbs="[
        ['label' => labels('admin_labels.blogs', 'Blogs')],
        ['label' => labels('admin_labels.manage_blogs', 'Manage Blogs')],
    ]" />

    <!-- Basic Layout -->
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="mb-3">
                        {{ labels('admin_labels.update_blog', 'Update Blog') }}
                    </h5>
                    <div class="form-group">
                        <form id="" action="{{ url('admin/blogs/update/' . $data->id) }}" class="submit_form"
                            enctype="multipart/form-data" method="POST">
                            @method('PUT')
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="nav nav-tabs" id="brandTabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <button class="language-nav-link nav-link active" id="tab-en"
                                                data-bs-toggle="tab" data-bs-target="#content-en" type="button"
                                                role="tab" aria-controls="content-en" aria-selected="true">
                                                {{ labels('admin_labels.default', 'Default') }}
                                            </button>
                                        </li>
                                        {!! generateLanguageTabsNav($languages) !!}
                                    </ul>
                                    <div class="tab-content mt-3" id="UpdatebrandTabsContent">
                                        <!-- Default 'en' tab content -->
                                        <div class="tab-pane fade show active" id="content-en" role="tabpanel"
                                            aria-labelledby="tab-en">
                                            <div class="mb-3">
                                                <label for=""
                                                    class="form-label">{{ labels('admin_labels.title', 'Title') }}<span
                                                        class="text-asterisks text-sm">*</span></label>

                                                <input type="text" class="form-control" id="basic-default-fullname"
                                                    placeholder="Gucci" name="title"
                                                    value="{{ isset($data->title) ? json_decode($data->title)->en : '' }}">
                                            </div>
                                        </div>
                                        {!! generateUpdateableLanguageTabs(
                                            $languages,
                                            $data->title,
                                            'admin_labels.title',
                                            'Title',
                                            'translated_blog_title',
                                        ) !!}

                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-12">
                                            <label for="category"
                                                class="form-label">{{ labels('admin_labels.select_category', 'Select Category') }}
                                                <span class='text-asterisks text-sm'>*</span></label>
                                            <select name="category_id" class="form-select get_blog_category"
                                                data-placeholder="Search Categories">
                                                <option></option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}"
                                                        {{ $category->id == $data->category_id ? 'selected' : '' }}>
                                                        {{ getDynamicTranslation('blog_categories', 'name', $category->id, $language_code) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <label class="form-label">{{ labels('admin_labels.image', 'Image') }}<span
                                                    class="text-asterisks text-sm">*</span></label>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 file_upload_box border file_upload_border mt-2">
                                            <div class="mt-2">
                                                <div class="col-md-12  text-center">
                                                    <div>
                                                        <a class="media_link" data-input="image" data-isremovable="0"
                                                            data-is-multiple-uploads-allowed="0" data-bs-toggle="modal"
                                                            data-bs-target="#media-upload-modal" value="Upload Photo">
                                                            <h4><i class='bx bx-upload'></i> Upload
                                                        </a></h4>
                                                        <p class="image_recommendation">Recommended Size : larger
                                                            than
                                                            400 x 260 &
                                                            smaller than 600 x 300 pixels.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="container-fluid row image-upload-section">
                                            <div
                                                class="col-md-3 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image">
                                                <div class='image-upload-div'><img class="img-fluid mb-2"
                                                        src="{{ route('admin.dynamic_image', [
                                                            'url' => getMediaImageUrl($data->image),
                                                            'width' => 150,
                                                            'quality' => 90,
                                                        ]) }}"
                                                        alt="Not Found">
                                                </div>
                                                <input type="hidden" name="image" value='<?= $data->image ?>'>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <label class="form-label">{{ labels('admin_labels.description', 'Description') }}<span
                                            class='text-asterisks text-sm'>*</span></label>
                                    <textarea name="description" class="form-control textarea addr_editor" placeholder="Place some text here">{{ isset($data->description) ? $data->description : '' }}</textarea>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-sm btn-primary mt-4 submit_button"
                                    id="">{{ labels('admin_labels.update_blog', 'Update Blog') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
