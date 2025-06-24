<div id="page-content">
    <!--Page Header-->
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <!--End Page Header-->

    <!--Main Content-->
    <div class="container-fluid">
        <div class="row">

            <!-- Blog Content-->
            <div class="col-12">
                <div class="blog-article">
                    <div class="blog-img mb-3">
                        @php
                            $blog_img = dynamic_image($blog[0]->image, 1500);
                            $language_code = get_language_code();
                        @endphp
                        <img class="rounded-0 blur-up lazyload" data-src="{{ $blog_img }}" src="{{ $blog_img }}"
                            alt="{{ getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) }}" />
                    </div>
                    <!-- Blog Content -->
                    <div class="blog-content">
                        <h2 class="h1">{{ getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) }}</h2>
                        <ul class="publish-detail d-flex-wrap">
                            <li><i class="icon anm anm-clock-r"></i> <time
                                    datetime="{{ $blog[0]->created_at }}">{{ $blog[0]->created_at }}</time></li>
                        </ul>
                        <hr />
                        <div class="content">
                            {!! $blog[0]->description !!}
                        </div>
                        <hr class="horizontal light m-0" />
                        <div class="row blog-action d-flex-center justify-content-between">
                            <div class="social-sharing share-icon d-flex-center mx-0 mt-3 justify-content-end">
                                <span class="sharing-lbl">{{ labels('front_messages.share', 'Share') }} :</span>
                                <div class="shareon" data-url="{{ customUrl('blogs/' . $blog[0]->slug) }}">
                                    <a class="facebook"
                                        data-text="Take a Look at this {{ getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) }} on {{ $system_settings['app_name'] }}"></a>
                                    <a class="telegram"
                                        data-text="Take a Look at this {{ getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) }} on {{ $system_settings['app_name'] }}"></a>
                                    <a class="twitter"
                                        data-text="Take a Look at this {{ getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) }} on {{ $system_settings['app_name'] }}"></a>
                                    <a class="whatsapp"
                                        data-text="Take a Look at this {{ getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) }} on {{ $system_settings['app_name'] }}"></a>
                                    <a class="email"
                                        data-text="Take a Look at this {{ getDynamicTranslation('blogs', 'title', $blog[0]->id, $language_code) }} on {{ $system_settings['app_name'] }}"></a>
                                    <a class="copy-url"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
