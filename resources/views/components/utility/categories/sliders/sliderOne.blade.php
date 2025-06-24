@props(['categories'])
<div class="collection-slider-5items gp15 arwOut5 hov-arrow">
    @foreach ($categories as $category)
    <x-utility.categories.cards.cardOne :$category />
    @endforeach
</div>
