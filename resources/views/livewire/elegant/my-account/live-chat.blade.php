@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.live_customer_support', 'Live Customer Support');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        <div class="row">
            <x-utility.my_account_slider.account_slider :$user_info />
            <div class="col-12 col-sm-12 col-md-12 col-lg-9">
                <div class="dashboard-content h-100">
                    <!-- My Wishlist -->
                    <div class="h-100" id="wishlist">
                        {{-- @dd($combo_wishlist) --}}
                        <div class="orders-card mt-0 h-100">
                            <div class="top-sec d-flex-justify-center justify-content-between mb-4">
                                <h2 class="mb-0">
                                    {{ labels('front_messages.live_customer_support', 'Live Customer Support') }}
                                </h2>
                            </div>
                            <iframe src="<?= url('chatify') ?>" class="vh-100 w-100"></iframe>
                        </div>
                    </div>
                    <!-- End My Wishlist -->
                </div>
            </div>
        </div>
    </div>
</div>
