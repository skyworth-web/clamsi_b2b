@props(['category'])
<div class="category-item zoomscal-hov">
    <a href="{{ customUrl("$category['link']") }}" wire:navigate class="category-link clr-none">
        <div class="zoom-scal zoom-scal-nopb rounded-3"><img class="blur-up lazyload" data-src="{{ $category['image'] }}"
                src="{{ $category['image'] }}" alt="{!! $category['title'] !!}" title="{!! $category['title'] !!}" width="365" height="365" />
        </div>
        <div class="details mt-3 text-center">
            <h4 class="category-title mb-0">{!! $category['title'] !!}</h4>
            <p class="counts">{{ $category['short_description'] }}</p>
        </div>
    </a>
</div>
