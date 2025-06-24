<div>
    {{-- @dd($categories); --}}
    @php
        $store_id = session('store_id');
        $store_details = getCurrentStoreData($store_id);
        $store_details = json_decode($store_details) ?? '';
    @endphp
    <div id="app_url" data-app-url="{{ route('home') }}"></div>
    <input type="hidden" id="store-primary-color" name="store-primary-color"
        value="{{ $store_details[0]->primary_color ?? '#041632' }}">
    <input type="hidden" id="store-secondary-color" name="store-secondary-color"
        value="{{ $store_details[0]->secondary_color ?? '#f4a51c' }}">
    <input type="hidden" id="store-link-active-color" name="store-link-active-color"
        value="{{ $store_details[0]->active_color ?? '#041632' }}">
    <input type="hidden" id="store-link-hover-color" name="store-link-hover-color"
        value="{{ $store_details[0]->hover_color ?? '#f4a51c' }}">
    <!--Top Header-->
    <div class="top-header bg-white text-uppercase">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-8 col-sm-6 col-md-4 col-lg-4 text-left">
                    <div class="select-wrap language-picker float-start">
                        <ul class="default-option">
                            <li>
                                <div class="option english">
                                    <span>{{ session('locale') ?? ($languages[0]->code ?? 'en') }}</span>
                                </div>
                            </li>
                        </ul>
                        <ul class="select-ul">
                            @foreach ($languages as $language)
                                <li class="option english changeLang" data-lang-code="{{ $language->code }}">
                                    <div>
                                        <span>{{ $language->code }}</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="select-wrap currency-picker float-start">
                        <ul class="default-option">
                            <li>
                                <div class="option USD">
                                    <span>{{ session('currency') ?? ($system_settings['currency_setting']['code'] ?? 'USD') }}</span>
                                </div>
                            </li>
                        </ul>
                        @if (count($currencies) > 1)
                            <ul class="select-ul">
                                @foreach ($currencies as $currency)
                                    <li class="option USD changeCurrency" data-currency-code="{{ $currency->code }}">
                                        <div>
                                            <span>{{ $currency->code }}</span>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
                <div class="col-12 col-sm-12 col-md-4 col-lg-4 text-center d-none d-md-block">
                    <div class="promo-items promo-slider-1items">
                        <div class="item">{{ $settings->app_short_description }}</div>
                    </div>
                </div>
                <div class="col-4 col-sm-6 col-md-4 col-lg-4 text-right d-flex align-items-center justify-content-end">
                    <span class="phone-txt me-2 d-none d-sm-inline">Need help? Call Us:</span>
                    <a href="tel:401234567890" class="phone d-flex-center float-start text-nowrap"><ion-icon wire:ignore
                            class="icon anm anm-phone-l d-none d-sm-none" name="call-outline"></ion-icon><span
                            class="phone-no d-inline d-sm-inline">{{ $settings->support_number }}</span></a>
                </div>
            </div>
        </div>
    </div>
    <!--End Top Header-->
    @php
        $cart_count = '0';
    @endphp
    @auth
        @php
            $user_id = auth()->id() ?? 0;
            $store_id = session('store_id') ?? '';
            $favorites = getFavorites(user_id: $user_id, store_id: $store_id);
            $cart_count = getCartCount($user_id, $store_id);
        @endphp
    @endauth
    <!--Header-->
    <header class="header header-7">
        <!--Header inner-->
        <div class="header-main d-flex align-items-center">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div
                        class="col-4 col-sm-4 col-md-5 col-lg-5 col-xl-6 align-self-center icons-col text-left d-xl-none">
                        <!--Mobile Toggle-->
                        <button type="button"
                            class="iconset icon-link ps-0 menu-icon js-mobile-nav-toggle mobile-nav--open d-inline-flex flex-column d-lg-none">
                            <span class="iconCot"><i class="hdr-icon icon anm anm-times-l"></i><i
                                    class="hdr-icon icon anm anm-bars-r"></i></span>
                            <span class="text d-none">Menu</span>
                            <span class="text d-none">Menu</span>
                        </button>
                        <!--End Mobile Toggle-->
                        <!--Search Mobile-->
                        <div class="search-parent iconset d-xl-none">
                            <div class="site-search">
                                <a wire:navigate class="icon-link clr-none d-flex" data-bs-toggle="offcanvas"
                                    data-bs-target="#search-drawer">
                                    <span class="iconCot"><i class="hdr-icon icon anm anm-search-l"></i></span>
                                </a>
                            </div>
                        </div>
                        <!--End Search Mobile-->
                        <!--Account Mobile-->
                        <div class="account-parent iconset d-inline-block d-xl-none">
                            <div class="account-link"><span class="iconCot"><i
                                        class="hdr-icon icon anm anm-user-al"></i></div>
                            <div id="accountBox">
                                <div class="customer-links">
                                    <ul class="m-0">
                                        @auth
                                            <li><a href="{{ customUrl('my-account') }}" wire:navigate><i
                                                        class="hdr-icon icon anm anm-user-al"></i>{{ labels('front_messages.my_account', 'My Account') }}</a>
                                            </li>
                                            <li><a href="{{ customUrl('orders') }}" wire:navigate><i
                                                        class="anm anm-bag-l hdr-icon icon"></i>{{ labels('front_messages.my_orders', 'My Orders') }}</a>
                                            </li>
                                            <li><a href="{{ customUrl('my-account/wallet') }}" wire:navigate><i
                                                        class="anm anm-pay-security hdr-icon icon"></i>{{ labels('front_messages.wallet', 'Wallet') }}</a>
                                            </li>
                                            <li><a class="logout"><i
                                                        class="anm anm-arrow-al-left hdr-icon icon"></i>{{ labels('front_messages.sign_out', 'Sign out') }}</a>
                                            </li>
                                        @else
                                            <li><a href="{{ customUrl('login') }}" wire:navigate><i
                                                        class="anm anm-arrow-al-right anm-user-al hdr-icon icon"></i>{{ labels('front_messages.sign_in', 'Sign In') }}</a>
                                            </li>
                                            <li><a href="{{ customUrl('register') }}" wire:navigate><i
                                                        class="hdr-icon icon anm anm-user-al"></i>{{ labels('front_messages.register', 'Register') }}</a>
                                            </li>
                                        @endauth
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!--End Account Mobile-->
                    </div>

                    <!--Logo-->
                    <div class="logo col-4 col-sm-4 col-md-2 col-lg-2 col-xl-2 align-self-center">
                        @php
                            $img =
                                !empty($store_details[0]->half_store_logo) &&
                                file_exists(
                                    public_path(
                                        config('constants.STORE_IMG_PATH') . $store_details[0]->half_store_logo,
                                    ),
                                )
                                    ? getImageUrl($store_details[0]->half_store_logo, '', '', 'image', 'STORE_IMG_PATH')
                                    : getImageUrl($settings->logo);
                            $img = dynamic_image($img, 150);
                        @endphp
                        <a wire:navigate class="logoImg" href="{{ customUrl('home') }}"><img src="{{ $img }}"
                                alt="{{ $settings->site_title }}" title="{{ $settings->site_title }}" /></a>
                    </div>
                    <!--End Logo-->
                    <!--Search Inline-->
                    <div class="col-1 col-sm-1 col-md-1 col-lg-4 col-xl-4 align-self-center d-none d-xl-block">

                    </div>
                    <!--End Search Inline-->
                    <!--Right Icon-->
                    <div class="col-4 col-sm-4 col-md-5 col-lg-5 col-xl-6 align-self-center icons-col text-right">
                        <!--search-->
                        {{-- <div class="compare-link iconset d-none d-sm-inline-block"> --}}
                        <div class="compare-link d-none d-xl-inline-block iconset">
                            <a wire:navigate class="icon-link clr-none d-flex" data-bs-toggle="offcanvas"
                                data-bs-target="#search-drawer">
                                <span class="iconCot"><i class="hdr-icon icon anm anm-search-l"></i></span>
                            </a>
                        </div>
                        <!--End search-->
                        <!--Wishlist-->
                        @auth
                            <div class="wishlist-link iconset">
                                <a wire:navigate href="{{ customUrl('my-account.favorites') }}"
                                    class="icon-link clr-none d-flex">
                                    <span class="iconCot"><i class="anm anm-heart anm-heart-l hdr-icon icon"></i><span
                                            class="wishlist-count d-xl-none">{{ $favorites['favorites_count'] }}</span></span>
                                    <span class="text d-none d-xl-flex flex-column text-left">Wishlist
                                        <small>{{ $favorites['favorites_count'] }}
                                            Item</small></span>
                                </a>
                            </div>
                        @endauth
                        <!--End Wishlist-->
                        <!--Account desktop-->
                        <div class="account-link iconset d-none d-xl-inline-block">
                            <div class="account-parent iconset">
                                <div class="account-link" title="Account"><span class="iconCot">
                                        <i class="hdr-icon icon anm anm-user-al"></i>
                                    </span></div>
                                <div id="accountBox">
                                    <div class="customer-links">
                                        <ul class="m-0">
                                            @auth

                                                <li><a href="{{ customUrl('my-account') }}" wire:navigate><i
                                                            class="hdr-icon icon anm anm-user-al"></i>{{ labels('front_messages.my_account', 'My Account') }}</a>
                                                </li>
                                                <li><a href="{{ customUrl('orders') }}" wire:navigate><i
                                                            class="anm anm-bag-l hdr-icon icon"></i>{{ labels('front_messages.my_orders', 'My Orders') }}</a>
                                                </li>
                                                <li><a href="{{ customUrl('my-account/wallet') }}" wire:navigate><i
                                                            class="anm anm-pay-security hdr-icon icon"></i>{{ labels('front_messages.wallet', 'Wallet') }}</a>
                                                </li>
                                                <li><a class="logout"><i
                                                            class="anm anm-arrow-al-left hdr-icon icon"></i>{{ labels('front_messages.sign_out', 'Sign out') }}</a>
                                                </li>
                                            @else
                                                <li><a href="{{ customUrl('login') }}" wire:navigate><i
                                                            class="anm anm-arrow-al-right anm-user-al hdr-icon icon"></i>{{ labels('front_messages.sign_in', 'Sign In') }}</a>
                                                </li>
                                                <li><a href="{{ customUrl('register') }}" wire:navigate><i
                                                            class="hdr-icon icon anm anm-user-al"></i>{{ labels('front_messages.register', 'Register') }}</a>
                                                </li>
                                            @endauth
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--End Account desktop-->
                        <!--Minicart-->
                        <div class="header-cart iconset pe-0">
                            <a href="#;" class="header-cart btn-minicart icon-link clr-none d-flex"
                                data-bs-toggle="offcanvas" data-bs-target="#minicart-drawer">
                                <span class="iconCot"><i class="hdr-icon icon anm anm-cart-l"></i><span
                                        class="cart-count">{{ $cart_count }}</span></span>
                            </a>
                        </div>
                        <!--End Minicart-->
                    </div>
                    <!--End Right Icon-->
                </div>
            </div>
        </div>
        <!--End Header inner-->
        <!--Navigation Desktop-->
        @php
            $categories = $categories['categories'];
            // dd($categories);
        @endphp
        <div class="main-menu-outer d-none d-lg-block header-fixed">
            <div class="container-fluid">
                <div class="menu-outer rounded-4">
                    <div class="row g-0">
                        <div class="col-1 col-sm-1 col-md-1 col-lg-3 align-self-center">
                            <div class="header-vertical-menu toggle theme_four_header_menu">
                                {{-- <h4 class="menu-title  d-flex-center body-font {{ Request::is('/') ? 'hide-arrow' : '' }}">
                                    <ion-icon name="menu-outline" class="fs-4"></ion-icon>
                                    <span class="text">Browse Categories</span>
                                </h4> --}}
                                @if (!Request::is('/'))
                                    <h4 class="menu-title theme_four_header_menu_title d-flex-center body-font">
                                        <ion-icon name="menu-outline" class="fs-4"></ion-icon>
                                        <span class="text">Browse Categories</span>
                                    </h4>
                                @endif

                                <div class="vertical-menu-content rounded-4 rounded-top-0">
                                    <ul class="menuList">
                                        @if (is_array($categories) && count($categories) >= 1)
                                            @foreach ($categories as $category)
                                                <li><a wire:navigate
                                                        href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                                                        class="nav-link">{!! $category->name !!}</a>
                                                </li>
                                            @endforeach
                                        @endif
                                    </ul>
                                    <a wire:navigate href="{{ customUrl('categories') }}"
                                        class="moreCategories border-0 d-flex justify-content-between align-items-center w-100">
                                        <span>View All Categories</span>
                                        <ion-icon name="arrow-forward-outline" class="fs-5"></ion-icon>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div
                            class="col-1 col-sm-1 col-md-1 col-lg-9 align-self-center d-menu-col hdr-menu-left menu-position-left">
                            <nav class="navigation ps-lg-3" id="AccessibleNav">
                                <ul id="siteNav" class="site-nav medium left">
                                    <li class="lvl1 parent dropdown"><a wire:navigate
                                            href="{{ customUrl('home') }}">{{ labels('front_messages.home', 'Home') }}</a>
                                    </li>
                                    <li class="lvl1 parent megamenu"><a wire:navigate
                                            href="{{ customUrl('products') }}">{{ labels('front_messages.products', 'Products') }}
                                        </a>
                                    </li>
                                    <li class="lvl1 parent megamenu"><a wire:navigate
                                            href="{{ customUrl('combo-products') }}">{{ labels('front_messages.combo_products', 'Combo Products') }}
                                        </a>
                                    </li>
                                    <li class="lvl1 parent megamenu"><a wire:navigate
                                            href="{{ customUrl('offers') }}">{{ labels('front_messages.offers', 'Offers') }}
                                        </a>
                                    </li>
                                    <li class="lvl1 parent megamenu"><a wire:navigate
                                            href="{{ customUrl('sellers') }}">{{ labels('front_messages.sellers', 'Sellers') }}
                                        </a>
                                    </li>
                                    <li class="lvl1 parent dropdown"><a wire:navigate
                                            href="{{ customUrl('compare') }}">{{ labels('front_messages.compare', 'Compare') }}
                                        </a>
                                    </li>
                                    <li class="lvl1 parent dropdown"><a wire:navigate
                                            href="{{ customUrl('blogs') }}">{{ labels('front_messages.blogs', 'Blogs') }}
                                        </a>
                                    </li>

                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--End Navigation Desktop-->
    </header>
    <!--End Header-->

    <!--Mobile Menu-->
    <div class="mobile-nav-wrapper mobileMenu-bg-black" role="navigation">
        <div class="closemobileMenu">Close Menu <i class="icon anm anm-times-l"></i></div>
        <ul id="MobileNav" class="mobile-nav">
            <li class="lvl1 parent dropdown"><a href="#">Browse Categories <i
                        class="icon anm anm-angle-down-l"></i></a>
                <ul class="lvl-2">
                    @if (is_array($categories) && count($categories) >= 1)
                        @foreach ($categories as $category)
                            <li><a wire:navigate href="{{ customUrl('categories/' . $category->slug . '/products') }}"
                                    class="nav-link">{!! $category->name !!}</a>
                            </li>
                        @endforeach
                    @endif
                </ul>
            </li>

            <li class="lvl1 parent dropdown"><a wire:navigate
                    href="{{ customUrl('home') }}">{{ labels('front_messages.home', 'Home') }}</a>
            <li class="lvl1 parent megamenu"><a wire:navigate
                    href="{{ customUrl('products') }}">{{ labels('front_messages.products', 'Products') }} </a>
            </li>
            <li class="lvl1 parent"><a wire:navigate
                    href="{{ customUrl('combo-products') }}">{{ labels('front_messages.combo_products', 'Combo Products') }}
                </a>
            </li>
            <li class="lvl1 parent megamenu"><a wire:navigate
                    href="{{ customUrl('compare') }}">{{ labels('front_messages.compare', 'Compare') }}
                </a>
            </li>
            <li class="lvl1 parent megamenu"><a wire:navigate
                    href="{{ customUrl('offers') }}">{{ labels('front_messages.offers', 'Offers') }} </a>
            </li>
            <li class="lvl1 parent megamenu"><a wire:navigate
                    href="{{ customUrl('sellers') }}">{{ labels('front_messages.sellers', 'Sellers') }} </a>
            </li>

            <li class="mobile-menu-bottom">
                <div class="mobile-links">
                    <ul class="list-inline d-inline-flex flex-column w-100">
                        @auth
                            <li><a href="{{ customUrl('my-account') }}" wire:navigate
                                    class="d-flex align-items-center"><i
                                        class="hdr-icon icon anm anm-user-al"></i>{{ labels('front_messages.my_account', 'My Account') }}</a>
                            </li>
                        @else
                            <li><a href="{{ customUrl('login') }}" wire:navigate class="d-flex align-items-center"><i
                                        class="anm anm-arrow-al-right anm-user-al hdr-icon icon"></i>{{ labels('front_messages.sign_in', 'Sign In') }}</a>
                            </li>
                            <li><a href="{{ customUrl('register') }}" wire:navigate class="d-flex align-items-center"><i
                                        class="hdr-icon icon anm anm-user-al"></i>{{ labels('front_messages.register', 'Register') }}</a>
                            </li>
                        @endauth
                        <li class="title h5">{{ labels('front_messages.need_help', 'Need Help?') }}</li>
                        <li><a href="tel:401234567890" class="d-flex align-items-center"><ion-icon
                                    name="call-outline" class="me-1"></ion-icon>
                                {{ $settings->support_number }}</a></li>
                        <li><a href="mailto:info@example.com" class="d-flex align-items-center"><ion-icon
                                    name="mail-outline" class="me-1"></ion-icon>
                                {{ $settings->support_email }}</a>
                        </li>
                    </ul>
                </div>
                <div class="mobile-follow mt-2">
                    <h5 class="title">{{ labels('front_messages.follow_us', 'Follow Us') }}</h5>
                    <ul class="list-inline social-icons mt-3">
                        @if ($settings->twitter_link !== null)
                            <li class="list-inline-item"><a href="{{ $settings->twitter_link }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Twitter"><i
                                        class="anm anm-twitter hdr-icon icon"></i></a></li>
                        @endif

                        @if ($settings->facebook_link !== null)
                            <li class="list-inline-item"><a href="{{ $settings->facebook_link }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Facebook"><i
                                        class="anm anm-facebook hdr-icon icon"></i></a></li>
                        @endif

                        @if ($settings->instagram_link !== null)
                            <li class="list-inline-item"><a href="{{ $settings->instagram_link }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Instagram"><i
                                        class="anm anm-instagram hdr-icon icon"></i></a></li>
                        @endif

                        @if ($settings->youtube_link !== null)
                            <li class="list-inline-item"><a href="{{ $settings->youtube_link }}"
                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Youtube"><i
                                        class="anm anm-youtube hdr-icon icon"></i></a></li>
                        @endif
                    </ul>
                </div>
            </li>
        </ul>
    </div>
    {{-- search --}}
    <livewire:header.SearchProduct />
    @php
        $language_code = get_language_code();
    @endphp
    @if (isset($stores) && count($stores) >= 2)
        <div class="sticky-stores">
            <div class="stores-main">
                <div wire:ignore class="in-out-store-arrow store-show">
                    <ion-icon name="chevron-back-outline"></ion-icon>
                </div>
                <div class="stores-container">
                    @foreach ($stores as $store)
                        @php
                            $store_img = route('front_end.dynamic_image', [
                                'url' => getMediaImageUrl($store->image, 'STORE_IMG_PATH'),
                                'width' => 50,
                                'quality' => 90,
                            ]);
                            $store_name = getDynamicTranslation('stores', 'name', $store->id, $language_code);
                        @endphp
                        <div class="store-box select-store {{ session('store_id') == $store->id ? 'store-active' : '' }}"
                            title="{{ $store_name }}" data-store-id="{{ $store->id }}"
                            data-store-name="{{ $store_name }}" data-store-slug="{{ $store->slug }}"
                            data-store-image="{{ $store_img }}">
                            <img src="{{ $store_img }}" alt="{{ $store_name }}">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <livewire:pages.model-cart />
    @if (isset($stores) &&
            count($stores) >= 2 &&
            url()->full() == customUrl(url()->full()) &&
            session()->get('show_store_popup'))
        <div class="newsletter-modal modal fade" id="store_select_modal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0">
                    <div class="modal-body p-0">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                        <div class="newsletter-wrap d-flex flex-column">
                            <div class="newsltr-text text-center store-popup-box">
                                <div class="wraptext mw-100">
                                    <h6 class="title text-transform-none">
                                        {{ labels('front_messages.please_choose_store.', 'Please choose a store.') }}
                                    </h6>
                                    <p class="text">
                                        {{ labels('front_messages.pick_store_that_suits_your_requirements', 'Pick a Store that Suits Your Requirements.') }}
                                    </p>
                                    <div class="collection-style1 row row-cols-2 row-cols-lg-3 mt-0">
                                        @if (count($stores) >= 1)
                                            @foreach ($stores as $store)
                                                @php
                                                    $store_img = route('front_end.dynamic_image', [
                                                        'url' => getMediaImageUrl($store->image, 'STORE_IMG_PATH'),
                                                        'width' => 400,
                                                        'quality' => 90,
                                                    ]);
                                                    $store_name = getDynamicTranslation(
                                                        'stores',
                                                        'name',
                                                        $store->id,
                                                        $language_code,
                                                    );
                                                @endphp
                                                <div class="category-item zoomscal-hov">
                                                    <a data-store-id="{{ $store->id }}"
                                                        data-store-name="{{ $store_name }}"
                                                        data-store-slug="{{ $store->slug }}"
                                                        data-store-image="{{ $store_img }}"
                                                        class="category-link select-store clr-none cursor-pointer">
                                                        <div class="zoom-scal zoom-scal-nopb brands-image">
                                                            <img class="blur-up lazyload w-100"
                                                                data-src="{{ $store_img }}"
                                                                src="{{ $store_img }}"
                                                                alt="{{ $store_name }}"
                                                                title="{{ $store_name }}" />
                                                        </div>
                                                        <div
                                                            class="details mt-3 d-flex justify-content-center align-items-center">
                                                            <h4 class="category-title mb-0">
                                                                {{ $store_name }}
                                                            </h4>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                    {{-- <div class="customCheckbox checkboxlink clearfix justify-content-center">
                                <input id="dontshow" name="newsPopup" type="checkbox" />
                                <label for="dontshow" class="mb-0">Don't show this popup again</label>
                            </div> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    <style>
        #nprogress .bar {
            background: <?=$store_details[0]->secondary_color ?? '#f4a51c' ?> !important;
            z-index: 1111;
        }

        .swiper-pagination-bullet-active-main {
            background: <?=$store_details[0]->secondary_color ?? '#f4a51c' ?> !important;
        }
    </style>
    @if (isset($stores) &&
            count($stores) >= 2 &&
            url()->full() == customUrl(url()->full()) &&
            session()->get('show_store_popup'))
        @php
            session()->put('show_store_popup', false);
        @endphp
        <script>
            setTimeout(function() {
                $('#store_select_modal').modal("show");
            }, 2000);
        </script>
    @endif
</div>
