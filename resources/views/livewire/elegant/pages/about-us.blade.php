@php
    $aboutus = [
        'logo' => asset('frontend/elegant/images/products/800x800.jpg'),
        'title' => 'Largest Online Fashion destination',
        'description' => 'There are many variations of passages of Lorem Ipsum available, but the majority have
                            suffered alteration in some form, by injected humour, even slightly believable.',
    ];
@endphp
<div>
    <x-utility.breadcrumbs.breadcrumbTwo />
    {{-- @dd($about_us) --}}
    <div id="page-content" class="destination-section section pt-0">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-sm-12 col-md-4">
                    <div class="about-images mb-4 mb-md-0">
                        <div class="row g-3">
                            <img class="rounded-0 blur-up lazyload" data-src="{{ asset('storage/' . $settings->logo) }}"
                                src="{{ asset('storage/' . $settings->logo) }}" alt="about" width="700"
                                height="827" />
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-8">
                    <div class="about-details px-50 py-5">
                        <h2 class="fs-4 mb-4">{{ labels('front_messages.about_us', 'About Us') }}</h2>
                        <p>{!! nl2br($about_us) !!}</p>
                        <a href="/products" wire:navigate class="btn btn-lg mt-md-2">{{ labels('front_messages.explore_now', 'Explore Now') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="service-section section section-color-light">
        <x-utility.others.serviceSection />
    </div>
</div>
