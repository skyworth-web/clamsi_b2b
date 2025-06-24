@php
    $title = labels('front_messages.privacy_policy', 'Privacy policy');
@endphp
<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbOne :breadcrumb="$title" />
    <div class="container-fluid">
        <div class="text-content">
            {!! $privacy_policy !!}
        </div>
    </div>
</div>
