@props(['user_info', 'cities'])
@php
    $img =
        !empty($user_info->image) && file_exists(public_path(config('constants.MEDIA_PATH') . $user_info->image))
            ? getMediaImageUrl($user_info->image, 'MEDIA_PATH')
            : getImageUrl('no-user-img.jpeg', '', '', 'image', 'NO_USER_IMAGE');
    $img = dynamic_image($img, 130);
@endphp
<div class="col-12 col-sm-12 col-md-12 col-lg-3 mb-4 mb-lg-0">
    <!-- Dashboard sidebar -->
    <div class="dashboard-sidebar bg-block">
        <div class="profile-top text-center mb-4 px-3">
            <div class="mb-3">
                <img class="profile-image rounded-circle blur-up lazyload" data-src="{{ $img }}"
                    src="{{ $img }}" alt="user" width="130" />
            </div>
            <div class="profile-detail">
                <h3 class="mb-1">{{ $user_info->username }}</h3>
                <p class="text-muted">{{ $user_info->email }}</p>
            </div>
        </div>
        <div class="dashboard-tab">
            <h1></h1>
            <ul class="nav nav-tabs flex-lg-column border-bottom-0" role="tablist">
                <li class="nav-item">
                    <a href="{{ customUrl('my-account') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account') ? 'active' : '' }}">{{ labels('front_messages.account_information', 'Account Info') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account.addresses') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/addresses') ? 'active' : '' }}">{{ labels('front_messages.addresses', 'Addresses') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('orders') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/orders') ? 'active' : '' }}">{{ labels('front_messages.my_orders', 'My Orders') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account/favorites') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/favorites') ? 'active' : '' }}">{{ labels('front_messages.my_wishlist', 'My Wishlist') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account/wallet') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/wallet') ? 'active' : '' }}">{{ labels('front_messages.wallet', 'Wallet') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account/profile') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/profile') ? 'active' : '' }}">{{ labels('front_messages.profile', 'Profile') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account/live-customer-support') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/live-customer-support') ? 'active' : '' }}">{{ labels('front_messages.live_customer_support', 'Live Customer Support') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account/support') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/support') ? 'active' : '' }}">{{ labels('front_messages.support', 'Raise Support Tickets') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account/transactions') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/transactions') ? 'active' : '' }}">{{ labels('front_messages.transactions', 'Transactions') }}</a>
                </li>
                <li class="nav-item"><a href="{{ customUrl('my-account/notifications') }}" wire:navigate
                        class="nav-link {{ url()->full() == customUrl('/my-account/notifications') ? 'active' : '' }}">{{ labels('front_messages.notifications', 'Notifications') }}</a>
                </li>
                <li class="nav-item"><a href="{{ route('logout') }}" wire:navigate
                        class="nav-link">{{ labels('front_messages.log_out', 'Log Out') }}</a> </li>
            </ul>
        </div>
    </div>
    <!-- End Dashboard sidebar -->
</div>
