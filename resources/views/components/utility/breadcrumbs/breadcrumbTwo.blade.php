@props(['bread_crumb'])
<div class="template-product">
    <div class="page-header text-center">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                    <!--Breadcrumbs-->
                    <div class="breadcrumbs"><a wire:navigate href="{{ customUrl('/') }}"
                            title="Back to the home page">{{ labels('front_messages.home', 'Home') }}</a><span class="main-title fw-bold"><ion-icon
                                class="align-text-top icon"
                                name="chevron-forward-outline"></ion-icon>{!! $bread_crumb['page_main_bread_crumb'] ?? '' !!}</span>
                        @if (isset($bread_crumb['right_breadcrumb']) && !empty($bread_crumb['right_breadcrumb']))
                            @foreach ($bread_crumb['right_breadcrumb'] as $right_breadcrumb)
                                <span class="main-title fw-bold"><ion-icon class="align-text-top icon"
                                        name="chevron-forward-outline"></ion-icon>{!! $right_breadcrumb !!}</span>
                            @endforeach
                        @endif
                    </div>
                    <!--End Breadcrumbs-->
                </div>
            </div>
        </div>
    </div>
</div>
