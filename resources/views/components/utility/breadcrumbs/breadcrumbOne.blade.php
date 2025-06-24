@props(['breadcrumb'])
<div class="page-header text-center">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 col-sm-12 col-md-12 col-lg-12 d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h1>{{ labels('front_messages.' . $breadcrumb . '', $breadcrumb) }}</h1>
                </div>
                <div class="breadcrumbs">
                    <a href="{{ route('home') }}"
                        title="Back to the home page">{{ labels('front_messages.home', 'Home') }}</a>
                    <span class="main-title"><ion-icon wire:ignore class="icon"
                            name="chevron-forward-outline"></ion-icon>{{ labels('front_messages.' . $breadcrumb . '', $breadcrumb) }}</span>
                </div>
                <!--End Breadcrumbs-->
            </div>
        </div>
    </div>
</div>
