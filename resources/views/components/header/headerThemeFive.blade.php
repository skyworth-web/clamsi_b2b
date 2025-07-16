<div>
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

    <div class="top-header top-info-bar">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-6 col-sm-6 col-md-3 col-lg-4 text-left d-flex">
                    @if (
                        $settings->twitter_link !== null ||
                            $settings->facebook_link !== null ||
                            $settings->instagram_link !== null ||
                            $settings->youtube_link !== null)
                        <div
                            class="col-6 col-sm-6 col-md-3 col-lg-4 text-right d-flex align-items-center justify-content-end">
                            <div class="social-email left-brd d-inline-flex">
                                <ul class="list-inline social-icons d-inline-flex align-items-center">
                                    @if ($settings->facebook_link !== null)
                                        <li class="list-inline-item"><a href="{{ $settings->twitter_link }}"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Facebook"><i
                                                    class="anm anm-facebook hdr-icon icon text-white"></i></a></li>
                                    @endif
                                    @if ($settings->twitter_link !== null)
                                        <li class="list-inline-item"><a {{ $settings->twitter_link }}
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Twitter"><i
                                                    class="anm anm-twitter hdr-icon icon text-white"></i></a></li>
                                    @endif
                                    @if ($settings->instagram_link !== null)
                                        <li class="list-inline-item"><a href="{{ $settings->instagram_link }}"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Instagram"><i
                                                    class="anm anm-instagram hdr-icon icon text-white"></i></a></li>
                                    @endif
                                    @if ($settings->youtube_link !== null)
                                        <li class="list-inline-item"><a href="{{ $settings->youtube_link }}"
                                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Youtube"><i
                                                    class="anm anm-youtube hdr-icon icon text-white"></i></a></li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-12 col-sm-12 col-md-6 col-lg-4 text-center d-none d-md-block">
                    {{ $settings->return_title }} | {{ $settings->support_title }}
                </div>

                <div class="col-6 col-sm-6 col-md-3 col-lg-4 text-right d-flex align-items-center justify-content-end">
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
                            <li>
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
            </div>
        </div>
    </div>
    <!--End Topbar info-->

    <!--Header-->
    <header wire:ignore.self class="header d-flex align-items-center header-1 header-fixed">
        <div id="app_url" data-app-url="{{ route('home') }}"></div>
        <div class="container-fluid">
            <div class="row">
                {{-- @dd($store_details) --}}
                @php
                    $img =
                        !empty($store_details[0]->half_store_logo) &&
                        file_exists(
                            public_path(config('constants.STORE_IMG_PATH') . $store_details[0]->half_store_logo),
                        )
                            ? getImageUrl($store_details[0]->half_store_logo, '', '', 'image', 'STORE_IMG_PATH')
                            : getImageUrl($settings->logo);
                    $img = dynamic_image($img, 150);
                @endphp
                <!--Logo-->
                <div class="logo col-5 col-sm-3 col-md-3 col-lg-2 align-self-center">
                    <a wire:navigate class="logoImg" href="{{ customUrl('home') }}"><img src="{{ $img }}"
                            alt="{{ $settings->site_title }}" title="{{ $settings->site_title }}" /></a>
                </div>
                <!--End Logo-->
                <!--Menu-->
                <div class="col-1 col-sm-1 col-md-1 col-lg-8 align-self-center d-menu-col">
                    <nav class="navigation" id="AccessibleNav">
                        <ul id="siteNav" class="site-nav medium center">
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
                                    href="{{ customUrl('blogs') }}">{{ labels('front_messages.blogs', 'Blogs') }} </a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <!--End Menu-->
                <!--Right Icon-->
                <div class="col-7 col-sm-9 col-md-9 col-lg-2 align-self-center icons-col text-right">
                    <!--Search-->
                    <div class="search-parent iconset">
                        <div class="site-search" title="Search">
                            <a href="#;" class="search-icon clr-none" data-bs-toggle="offcanvas"
                                data-bs-target="#search-drawer"><i class="hdr-icon icon anm anm-search-l"></i></a>
                        </div>
                    </div>
                    <!--End Search-->

                    <!--Account-->
                    <div class="account-parent iconset">
                        <div class="account-link" title="Account"><i class="hdr-icon icon anm anm-user-al"></i></div>
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
                                        <li><a href="{{ route('logout') }}"><i
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
                    <!--End Account-->
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
                        <!--Wishlist-->
                        <div class="wishlist-link iconset" title="Wishlist"><a wire:navigate
                                href="{{ customUrl('my-account.favorites') }}" class="text-black"><i
                                    class="anm anm-heart anm-heart-l hdr-icon icon"></i><span
                                    class="wishlist-count">{{ $favorites['favorites_count'] }}</span></a>
                        </div>
                        <!--End Wishlist-->
                    @endauth
                    <!--Minicart-->
                    <div class="header-cart iconset" title="Cart">
                        <a href="#;" class="header-cart btn-minicart clr-none" data-bs-toggle="offcanvas"
                            data-bs-target="#minicart-drawer"><i class="hdr-icon icon anm anm-cart-l"></i><span
                                class="cart-count">{{ $cart_count }}</span></a>
                    </div>
                    <!--End Minicart-->
                    <!--Mobile Toggle-->
                    <button type="button"
                        class="iconset pe-0 menu-icon js-mobile-nav-toggle mobile-nav--open d-lg-none"
                        title="Menu"><ion-icon wire:ignore class="fs-5" name="menu"></ion-icon></button>
                    <!--End Mobile Toggle-->
                </div>
                <!--End Right Icon-->
            </div>
        </div>
    </header>
    <!--End Header-->
    <!--Mobile Menu-->
    <div class="mobile-nav-wrapper" role="navigation">
        <div class="closemobileMenu">{{ labels('front_messages.close_menu', 'Close Menu') }}<ion-icon
                name="close-outline" class="icon"></ion-icon></div>
        <ul id="MobileNav" class="mobile-nav">
            <li class="lvl1 parent dropdown"><a wire:navigate
                    href="{{ customUrl('home') }}">{{ labels('front_messages.home', 'Home') }}</a>
            </li>
            <li class="lvl1 parent megamenu"><a wire:navigate
                    href="{{ customUrl('products') }}">{{ labels('front_messages.products', 'Products') }} </a>
            </li>
            <li class="lvl1 parent megamenu"><a wire:navigate
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
            <li class="lvl1 parent dropdown"><a wire:navigate
                    href="{{ customUrl('contact_us') }}">{{ labels('front_messages.contact_us', 'Contact Us') }}
                </a>
            </li>
            <li class="lvl1 parent dropdown"><a wire:navigate
                    href="{{ customUrl('faqs') }}">{{ labels('front_messages.faqs', 'FAQs') }} </a>
            </li>
            <li class="lvl1 parent dropdown"><a wire:navigate
                    href="{{ customUrl('blogs') }}">{{ labels('front_messages.blogs', 'Blogs') }} </a>
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
                                    name="mail-outline" class="me-1"></ion-icon> {{ $settings->support_email }}</a>
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
                                                                src="{{ $store_img }}" alt="{{ $store_name }}"
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
