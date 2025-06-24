@php
    $title = labels('front_messages.faqs', 'FAQs');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbOne :breadcrumb="$title" />
    <div class="container-fluid">
        <div class="row help-center mb-4 mb-md-0">
            <div class="col-12 col-sm-12 col-md-10 col-lg-6 mx-auto">
                <h2 class="mb-3 text-center">
                    {{ labels('front_messages.frequently_asked_questions', 'Frequently Asked Questions') }}</h2>
                <div class="input-group">
                    <input wire:model.live.debounce.250ms="search" type="text" class="form-control input-group-field"
                        name="search" value="" placeholder="Search…" />
                </div>
            </div>
        </div>
        <div wire:ignore
            class="d-none section help-store-info row g-3 justify-content-center row-cols-xl-5 row-cols-lg-5 row-cols-md-4 row-cols-sm-2 row-cols-1">
            <div class="store-info-item d-flex align-items-center">
                <div class="icon d-flex align-items-center me-3">
                    <ion-icon name="cube-outline" class="fs-3"></ion-icon>
                </div>
                <div class="content d-flex align-items-center">
                    <h5 class="title m-0 body-font">
                        {{ labels('front_messages.shipping_&_Orders', 'Shipping & Orders') }}</h5>
                </div>
            </div>
            <div class="store-info-item d-flex align-items-center">
                <div class="icon d-flex align-items-center me-3">
                    <ion-icon name="bus-outline" class="fs-3"></ion-icon>
                </div>
                <div class="content d-flex align-items-center">
                    <h5 class="title m-0 body-font">
                        {{ labels('front_messages.exchanges_&_returns', 'Exchanges & Returns') }}</h5>
                </div>
            </div>
            <div class="store-info-item d-flex align-items-center">
                <div class="icon d-flex align-items-center me-3">
                    <ion-icon name="card-outline" class="fs-3"></ion-icon>
                </div>
                <div class="content d-flex align-items-center">
                    <h5 class="title m-0 body-font">{{ labels('front_messages.payments_privacy', 'Payments Privacy') }}
                    </h5>
                </div>
            </div>
            <div class="store-info-item d-flex align-items-center">
                <div class="icon d-flex align-items-center me-3">
                    <ion-icon name="person" class="fs-3"></ion-icon>
                </div>
                <div class="content d-flex align-items-center">
                    <h5 class="title m-0 body-font">{{ labels('front_messages.account_settings', 'Account Settings') }}
                    </h5>
                </div>
            </div>
        </div>
        <div class="row faqs-style mt-4 mt-md-0">
            <div class="col-12 col-sm-12 col-md-12 col-lg-10 mx-auto">
                @if ($search == '')
                    <div class="accordion" id="accordionFaq">
                        @if (count($faqs) >= 1)
                            <div class="section pt-0">
                                <h3 class="faqttl mb-3">
                                    {{ labels('front_messages.basic_questions', 'Commonly asked questions') }}
                                </h3>
                                @foreach ($faqs as $key => $faq)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOne{{ $key }}">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseOne{{ $key }}" aria-expanded="false"
                                                aria-controls="collapseOne{{ $key }}">{{ $faq->question }}</button>
                                        </h2>
                                        <div id="collapseOne{{ $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="headingOne{{ $key }}"
                                            data-bs-parent="#accordionFaq">
                                            <div class="accordion-body">
                                                <p>{!! nl2br($faq->answer) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @else
                    @if (count($search_result) >= 1)
                        <div class="accordion" id="accordionFaq">
                            <div class="section pt-0">
                                <h3 class="faqttl mb-3">
                                    <span class="fw-500">
                                        {{ labels('front_messages.search_result_for', 'Search Result For ') }}</span><span
                                        class="fw-600 text-uppercase">{{ $search }}</span>
                                </h3>
                                @foreach ($search_result as $key => $faq)
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="headingOne{{ $key }}">
                                            <button class="accordion-button collapsed" type="button"
                                                data-bs-toggle="collapse"
                                                data-bs-target="#collapseOne{{ $key }}" aria-expanded="false"
                                                aria-controls="collapseOne{{ $key }}">{{ $faq['question'] }}</button>
                                        </h2>
                                        <div id="collapseOne{{ $key }}" class="accordion-collapse collapse"
                                            aria-labelledby="headingOne{{ $key }}"
                                            data-bs-parent="#accordionFaq">
                                            <div class="accordion-body">
                                                <p>{!! nl2br($faq['answer']) !!}</p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="d-flex justify-content-center align-items-center">
                            <p class="text-muted fs-6">{{ labels('front_messages.no_result_for', 'No Result For ') }}
                                <span class="text-uppercase">{{ $search }}</span>
                            </p>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>
