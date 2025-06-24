<div id="page-content">
    {{-- @dd($customer_reviews); --}}
    <x-utility.breadcrumbs.breadcrumbTwo />
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-item-center pb-2">
            <h3 class="spr-form-title">{{ labels('front_messages.customer_reviews', 'Customer Reviews') }}</h3>
            <p class="text-uppercase fw-600 fs-6">{{ labels('front_messages.total_reviews', 'Total Reviews') }}{{ count($customer_reviews) }} </p>
        </div>
        <x-utility.others.ratingCard :$customer_reviews />
    </div>
</div>
