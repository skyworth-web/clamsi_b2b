@php
    $title = labels('front_messages.terms_and_conditions', 'Terms and conditions');
@endphp

<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbOne :breadcrumb="$title" />
    <div class="container-fluid my-5">
        <div class="text-content">
            {!! $terms_and_conditions !!}
        </div>
    </div>
</div>
