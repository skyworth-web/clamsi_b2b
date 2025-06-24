@php
    $bread_crumb['page_main_bread_crumb'] = labels('front_messages.favorites', 'Favorites');
@endphp
<div id="page-content">
    @php
        $store_settings = getStoreSettings();
        $language_code = get_language_code();
    @endphp
    <x-utility.breadcrumbs.breadcrumbTwo :$bread_crumb />

    <div class="container-fluid">
        <div class="row">
            <x-utility.my_account_slider.account_slider :$user_info />
            @php
                $component = getWishlistDisplayComponent($store_settings);
            @endphp
            <x-dynamic-component :component="$component" :regular_wishlist="$regular_wishlist" :combo_wishlist="$combo_wishlist" :favorites_count="$favorites_count" :language_code="$language_code"
                :links="$links" />
            {{-- <x-utility.wishlist.cards.listCardOne :regular_wishlist="$regular_wishlist" :combo_wishlist="$combo_wishlist" :favorites_count="$favorites_count"
                :links="$links" /> --}}
            {{-- <x-utility.wishlist.cards.listCardTwo :regular_wishlist="$regular_wishlist" :combo_wishlist="$combo_wishlist" :favorites_count="$favorites_count"
                :links="$links" /> --}}
        </div>
    </div>
</div>
