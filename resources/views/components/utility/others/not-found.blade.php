@props(['title'])
<div class="table-wrapper mt-4 compare-table table-responsive">
    <div id="compare_item">
        <div id="page-content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12 text-center">
                        <p><img src="{{ asset('frontend/elegant/images/empty-img.gif') }}" alt="no data found"
                                width="500" /></p>
                        <h2 class="fs-4 mt-4">{!! $title !!}</h2>
                        @if (url()->previous() == customUrl(url()->current()))
                            <p class="same-width-btn"><a href="{{ customUrl('home') }}"
                                    class="btn btn-secondary btn-lg mb-2 mx-3">{{ labels('front_messages.go_back', 'Go Back') }}</a></p>
                        @else
                            <p class="same-width-btn"><a href="{{ url()->previous() }}"
                                    class="btn btn-secondary btn-lg mb-2 mx-3">{{ labels('front_messages.go_back', 'Go Back') }}</a></p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
