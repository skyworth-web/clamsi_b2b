@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.blogs', 'Blogs');
    $language_code = get_language_code();
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="toolbar toolbar-wrapper blog-toolbar">
            <div class="row align-items-center">
                <div
                    class="col-12 col-sm-6 col-md-6 col-lg-6 text-left filters-toolbar-item d-flex justify-content-center justify-content-sm-start">
                    <div class="search-form mb-3 mb-sm-0">
                        <input wire:model.live.debounce.250ms="search" class="search-input" type="text"
                            placeholder="Blog search..." value="{{ $search }}">
                        <button wire:ignore class="search-btn"><ion-icon name="search-outline"
                                class="icon fs-5"></ion-icon></button>
                    </div>
                </div>
                <div
                    class="col-12 col-sm-6 col-md-6 col-lg-6 text-right filters-toolbar-item d-flex justify-content-between justify-content-sm-end">
                    <div class="filters-item d-flex align-items-center">
                        <label for="ShowBy" class="mb-0 me-2">{{ labels('front_messages.show', 'Show') }}:</label>
                        <select name="ShowBy" id="perPage" class="filters-toolbar-sort">
                            <option value="9" {{ $perPage == '9' ? 'selected' : '' }}>9</option>
                            <option value="18" {{ $perPage == '18' ? 'selected' : '' }}>18</option>
                            <option value="27" {{ $perPage == '27' ? 'selected' : '' }}>27</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        @if ($blogs_count >= 1)
            <div class="container my-5">
                <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-4">
                    @foreach ($blogs['listing'] as $blog)
                        {{-- @dd($blog); --}}
                        {{-- <div class="blog-article zoomscal-hov">
                            <div class="col">
                                <div class="card h-100">
                                    <div class="blog-img">
                                        @php
                                            $image = dynamic_image($blog->image, 600);
                                        @endphp
                                        <a wire:navigate
                                            class="featured-image rounded-0 zoom-scal d-flex justify-content-center align-items-center"
                                            href="{{ customUrl('blogs/' . $blog->slug) }}"><img
                                                class="rounded-0 blur-up lazyload" data-src="{{ $image }}"
                                                src="{{ $image }}"
                                                alt="{{ getDynamicTranslation('blogs', 'title', $blog->id, $language_code) }}" /></a>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <h2 class="h3"><a wire:navigate
                                                href="{{ customUrl('blogs/' . $blog->slug) }}">{{ getDynamicTranslation('blogs', 'title', $blog->id, $language_code) }}</a>
                                        </h2>
                                        <ul class="list-unstyled small text-muted mb-3">
                                            <li><i class="icon anm anm-clock-r"></i> <time
                                                    datetime="{{ $blog->created_at }}">{{ $blog->created_at }}</time>
                                            </li>
                                        </ul>
                                        <p class="card-text flex-grow-1">
                                            {{ \Illuminate\Support\Str::limit(strip_tags($blog->description), 100) }}
                                        </p>
                                        <a wire:navigate href="{{ customUrl('blogs/' . $blog->slug) }}"
                                            class="btn btn-outline-secondary mt-auto">Read more</a>
                                    </div>
                                </div>
                            </div>
                        </div> --}}
                        <div class="col">
                            <div class="card h-100 d-flex flex-column">
                                <!-- Uniform image height using ratio -->
                                <div class="ratio ratio-4x3">
                                    @php
                                        $image = dynamic_image($blog->image, 600);
                                    @endphp
                                    <a wire:navigate
                                        class="d-flex justify-content-center align-items-center w-100 h-100"
                                        href="{{ customUrl('blogs/' . $blog->slug) }}">
                                        <img class="img-fluid w-100 h-100 object-fit-cover" src="{{ $image }}"
                                            alt="{{ getDynamicTranslation('blogs', 'title', $blog->id, $language_code) }}">
                                    </a>
                                </div>

                                <!-- Blog content -->
                                <div class="card-body d-flex flex-column">
                                    <h2 class="h5">
                                        <a wire:navigate href="{{ customUrl('blogs/' . $blog->slug) }}">
                                            {{ getDynamicTranslation('blogs', 'title', $blog->id, $language_code) }}
                                        </a>
                                    </h2>
                                    <ul class="list-unstyled small text-muted mb-2">
                                        <li>
                                            <i class="icon anm anm-clock-r"></i>
                                            <time datetime="{{ $blog->created_at }}">{{ $blog->created_at }}</time>
                                        </li>
                                    </ul>
                                    <p class="card-text flex-grow-1">
                                        {{ \Illuminate\Support\Str::limit(strip_tags($blog->description), 100) }}
                                    </p>
                                    <a wire:navigate href="{{ customUrl('blogs/' . $blog->slug) }}"
                                        class="btn btn-outline-secondary mt-auto">Read more</a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <nav class="mt-5">
                    {!! $blogs['links'] !!}
                </nav>
            </div>
        @else
            @php
                $title = labels('front_messages.no_blog_found', 'No Blog Found!');
            @endphp
            <x-utility.others.not-found :$title />
        @endif
    </div>
</div>
