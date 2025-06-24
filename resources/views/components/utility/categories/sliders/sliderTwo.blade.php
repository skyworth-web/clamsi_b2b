@props(['categories'])
<div class="collection-slider-4items gp15 arwOut5 hov-arrow dots-hide">
    @foreach ($categories as $category)
        <x-utility.categories.cards.cardTwo :$category />
    @endforeach
</div>
