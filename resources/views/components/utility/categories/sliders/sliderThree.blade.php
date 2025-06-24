@props(['categories'])
<div class="gp15 arwOut5 hov-arrow circle-arrow">
    <div class="swiper category-mySwiper">
        <div class="swiper-wrapper">
            @foreach ($categories as $category)
                <x-utility.categories.cards.cardThree :$category />
            @endforeach
        </div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </div>
</div>
