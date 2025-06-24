@php
    $title = labels('front_messages.shipping_policy', 'Shipping policy');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbOne :breadcrumb="$title" />
    <div class="container-fluid">
        <div class="text-content">
            {!! $shipping_policy !!}
        </div>
    </div>
</div>
