<?php

use Chatify\ChatifyMessenger;

$setting = getSettings('system_settings', true);
$setting = json_decode($setting, true);


$messenger = new ChatifyMessenger();
$unread = $messenger->totalUnseenMessages();


?>
<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 fixed-start  ps ps--active-y" id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="/seller/home" target="">
            <img src="{{ config('app.url') }}storage/{{ $setting['logo'] }}" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold">{{ $setting['app_name'] }}</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto h-auto ps" id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Dashboard</li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/home') || Request::is('seller/home/*') ? 'active' : '' }}" href="/seller/home">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Home</span>
                </a>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Manage</li>
            <li class="nav-item ps-2">
                <a data-bs-toggle="collapse" href="#order_dropdown" class="nav-link collapsed {{ Request::is('seller/orders') || Request::is('seller/orders*') ? 'active' : '' }}" aria-controls="order_dropdown" role="button" aria-expanded="false">
                    <div class="ms-4 text-center d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Orders Manage</span>
                </a>
                <div class="collapse" id="order_dropdown">
                    <ul class="nav">
                        <li class="nav-item ms-7">
                            <a class="nav-link {{ Request::is('seller/orders') || Request::is('seller/orders*') ? 'active' : '' }}" href="{{ route('seller.orders.index') }}">
                                <span class="nav-link-text ms-1">Orders</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/categories') || Request::is('seller/categories/*') ? 'active' : '' }}" href="/seller/categories">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Categories</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/point_of_sale') || Request::is('seller/point_of_sale/*') ? 'active' : '' }}" href="/seller/point_of_sale">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Point Of Sale</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/manage_stock') || Request::is('seller/manage_stock/*') ? 'active' : '' }}" href="/seller/manage_stock">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    </div>
                    <span class="nav-link-text ms-1">Stock Manage </span>
                </a>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Products</li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/tax') || Request::is('seller/tax/*') ? 'active' : '' }}" href="/seller/tax">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Tax</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/products/attributes/') || Request::is('seller/products/attributes/*') ? 'active' : '' }}" href="{{ route('attributes.index') }}">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                    </div>
                    <span class="sidenav-normal"> Attributes </span>
                </a>
            </li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#products_dropdown" class="nav-link collapsed {{ Request::is('seller/products') || Request::is('seller/products/*') ? 'active' : '' }}" aria-controls="products_dropdown" role="button" aria-expanded="false">
                    <div class="ms-4 text-center d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Products Manage</span>
                </a>
                <div class="collapse" id="products_dropdown">
                    <ul class="nav ">

                        <li class="nav-item ms-7">
                            <a class="nav-link {{ Request::is('seller/products/') || Request::is('seller/products/') ? 'active' : '' }}" href="{{ route('seller.products.index') }}">
                                <span class="sidenav-normal"> Add Products </span>
                            </a>
                        </li>
                        <li class="nav-item ms-7">
                            <a class="nav-link {{ Request::is('seller/products/') || Request::is('seller/products/') ? 'active' : '' }}" href="{{ route('seller.products.manage_product') }}">
                                <span class="sidenav-normal"> Manage Products </span>
                            </a>
                        </li>
                        <li class="nav-item ms-7">
                            <a class="nav-link {{ Request::is('seller/product_faqs') || Request::is('seller/product_faqs') ? 'active' : '' }}" href="{{ route('seller.product_faqs.index') }}">
                                <span class="sidenav-normal">Product FAQs </span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Combo Products Manage</li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/combo_product_attributes/') || Request::is('seller/combo_product_attributes/') ? 'active' : '' }}" href="{{ route('seller.combo_product_attributes.index') }}">
                    <div class="ms-4 text-center d-flex align-items-center justify-content-center">
                    </div>
                    <span class="sidenav-normal"> Attributes </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/combo_products/') || Request::is('seller/combo_products/') ? 'active' : '' }}" href="{{ route('seller.combo_products.index') }}">
                    <div class="ms-4 text-center d-flex align-items-center justify-content-center">
                    </div>
                    <span class="sidenav-normal"> Add Combo Products </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/combo_products/manage-products') || Request::is('seller/combo_products/manage-products') ? 'active' : '' }}" href="{{ route('seller.combo_products.manage_product') }}">
                    <div class="ms-4 text-center d-flex align-items-center justify-content-center">
                    </div>
                    <span class="sidenav-normal"> Manage Products </span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/combo_product_faqs') || Request::is('seller/combo_product_faqs') ? 'active' : '' }}" href="{{ route('seller.combo_product_faqs.index') }}">
                    <div class="ms-4 text-center d-flex align-items-center justify-content-center">
                    </div>
                    <span class="sidenav-normal">Product FAQs </span>
                </a>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Media Management</li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/media') || Request::is('seller/media/*') ? 'active' : '' }}" href="/seller/media">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Add Media</span>
                </a>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Chat Management</li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/chat') || Request::is('seller/chat/*') ? 'active' : '' }}" href="{{ route('seller.chat.index') }}">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Chats</span>
                    @if($unread > 0)
                    <span class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20 ms-1 rounded-pill">{{$unread}}</span>
                    @endif
                </a>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Wallet Management</li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/transaction') || Request::is('seller/transaction/*') ? 'active' : '' }}" href="{{ route('seller.transaction.wallet_transactions') }}">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Wallet Transaction</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/transaction') || Request::is('seller/transaction/*') ? 'active' : '' }}" href="{{ route('seller.payment_request.withdrawal_requests') }}">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1"> Withdrawal Requests</span>
                </a>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Location Management</li>
            <li class="nav-item">
                <a data-bs-toggle="collapse" href="#location_dropdown" class="nav-link collapsed {{ Request::is('seller/area') || Request::is('seller/area/*') ? 'active' : '' }}" aria-controls="location_dropdown" role="button" aria-expanded="false">
                    <div class="ms-4 text-center d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Locations</span>
                </a>
                <div class="collapse" id="location_dropdown">
                    <ul class="nav">
                        <li class="nav-item ms-7">
                            <a class="nav-link {{ Request::is('seller/area/zipcodes') || Request::is('seller/area/zipcodes') ? 'active' : '' }}" href="/seller/area/zipcodes">
                                <span class="sidenav-normal">Zipcodes</span>
                            </a>
                        </li>
                        <li class="nav-item ms-7">
                            <a class="nav-link {{ Request::is('seller/area/city') || Request::is('seller/area/city') ? 'active' : '' }}" href="/seller/area/city">
                                <span class="sidenav-normal">City</span>
                            </a>
                        </li>
                        <li class="nav-item ms-7">
                            <a class="nav-link {{ Request::is('seller/area/') || Request::is('seller/area/') ? 'active' : '' }}" href="/seller/area/">
                                <span class="sidenav-normal">Area</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ Request::is('seller/pickup_locations') || Request::is('seller/pickup_locations/*') ? 'active' : '' }}" href="/seller/pickup_locations">
                    <div class="ms-4 border-radius-md text-center me-2 d-flex align-items-center justify-content-center">

                    </div>
                    <span class="nav-link-text ms-1">Pickup Locations</span>
                </a>
            </li>
            <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i> Reports & sales Management</li>





        </ul>
        <!-- <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
            <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
        </div>
        <div class="ps__rail-y" style="top: 0px; right: 0px;">
            <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 0px;"></div>
        </div>
    </div>
    <div class="ps__rail-x" style="left: 0px; bottom: 0px;">
        <div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div>
    </div>
    <div class="ps__rail-y" style="top: 0px; height: 486px; right: 0px;">
        <div class="ps__thumb-y" tabindex="0" style="top: 0px; height: 155px;"></div>
    </div> -->
</aside>