<div id="page-content">
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />
    <div class="container-fluid">
        @php
            $title =
                '<strong>' .
                labels('front_messages.sorry', 'SORRY ') .
                '</strong>' .
                labels('front_messages.compare_is_currently_empty', 'Compare List is currently empty');
        @endphp
        <x-utility.others.not-found :$title />
    </div>
</div>
