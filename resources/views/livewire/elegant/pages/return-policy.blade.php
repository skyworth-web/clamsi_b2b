@php
    $title = labels('front_messages.return_refund_policy', 'Return policy');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbOne :breadcrumb="$title" />
    <div class="container-fluid">
        <div class="text-content">
            {!! $return_policy !!}
        </div>
    </div>
</div>
