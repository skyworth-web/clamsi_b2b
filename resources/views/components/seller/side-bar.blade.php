 <!-- Sidebar -->

 <nav class="navbar-vertical navbar bg-white" {{ session()->get('is_rtl') == 1 ? 'dir=rtl' : '' }}>
     <div class="nav-scroller bg-white">
         @php

             use Chatify\ChatifyMessenger;

             $setting = getSettings('system_settings', true);
             $setting = json_decode($setting, true);

             $messenger = new ChatifyMessenger();
             $unread = $messenger->totalUnseenMessages();

             $logo = file_exists(public_path(config('constants.MEDIA_PATH') . $setting['logo']))
                 ? asset(config('constants.MEDIA_PATH') . $setting['logo'])
                 : asset(config('constants.DEFAULT_LOGO'));
         @endphp
         <div class="sidenav-header">
             <a class="navbar-brand m-0" href="{{ route('seller.home') }}" target="">
                 <img src="{{ $logo }}" class="navbar-brand-img" alt="main_logo">
             </a>
         </div>

         <!-- code for menu search -->

         <div class="ps-2 pe-2 mt-4">
             <!-- Search Bar -->
             <input type="text" class="form-control menuSearch" placeholder="Search Menu">
         </div>

         <ul class="navbar-nav" id="menuList">
             <li class="sidebar-title"><i class='bx bx-tachometer'></i>
                 {{ labels('admin_labels.dashboard', 'Dashboard') }}
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/home') || Request::is('seller/home/*') ? 'active' : '' }}"
                     href="{{ route('seller.home') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.home', 'Home') }}</span>
                 </a>
             </li>
             <li class="sidebar-title "><i class='bx bx-card'></i> {{ labels('admin_labels.manage', 'Manage') }}</li>
             <li class="nav-item ">
                 <a data-bs-toggle="collapse" href="#order_dropdown"
                     class="nav-link {{ Request::is('seller/orders') || Request::is('seller/orders*') ? 'active' : '' }}  {{ Request::is('seller/orders') || Request::is('seller/orders*') ? '' : 'collapsed' }}"
                     aria-controls="order_dropdown" role="button" aria-expanded="false">
                     <span class="nav-link-text ">{{ labels('admin_labels.orders_manage', 'Orders Manage') }}</span><i
                         class="fas fa-angle-down"></i>
                 </a>
                 <div class="collapse {{ Request::is('seller/orders') || Request::is('seller/orders*') ? 'show' : '' }}"
                     id="order_dropdown">
                     <ul class="nav">
                         <li
                             class="nav-item {{ Request::is('seller/orders') || Request::is('seller/orders*') ? 'active' : '' }}">
                             <a class="nav-link " href="{{ route('seller.orders.index') }}">
                                 <span class="nav-link-text ">{{ labels('admin_labels.orders', 'Orders') }}</span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/categories') || Request::is('seller/categories/*') ? 'active' : '' }}"
                     href="{{ route('seller_categories.index') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.categories', 'Categories') }}</span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/point_of_sale') || Request::is('seller/point_of_sale/*') ? 'active' : '' }}"
                     href="{{ route('seller.point_of_sale.index') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.point_of_sale', 'Point Of Sale') }}</span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/manage_stock') || Request::is('seller/manage_stock/*') ? 'active' : '' }}"
                     href="{{ route('seller.manage_stock.index') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.stock_manage', 'Stock Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item">
                 <a class="nav-link {{ Request::is('seller/manage_combo_stock') || Request::is('seller/manage_combo_stock/*') ? 'active' : '' }}"
                     href="{{ route('seller.manage_combo_stock.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.combo_stock_manage', 'Combo Stock Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link manage_product_deliverability {{ Request::is('seller/manage_product_deliverability') ? 'active' : '' }}"
                     href="{{ route('seller.manage_product_deliverability.index') }}">
                     <span
                         class="nav-link-text ">{{ labels('admin_labels.manage_product_deliverability', 'Product Deliverability Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link manage_product_deliverability {{ Request::is('seller/manage_combo_product_deliverability') ? 'active' : '' }}"
                     href="{{ route('seller.manage_combo_product_deliverability.index') }}">
                     <span
                         class="nav-link-text ">{{ labels('admin_labels.manage_combo_product_deliverability', 'Combo Product Deliverability Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('seller/language/bulk_translation_upload') || Request::is('seller/language/bulk_translation_upload/*') ? 'active' : '' }}"
                     href="{{ route('seller.translation_bulk_upload.index') }}">
                     {!! labels('admin_labels.bulk_upload', 'Multi Language Bulk<br>Import') !!}
                 </a>
             </li>
             <li class="sidebar-title "><i class='bx bx-cart-alt'></i>
                 {{ labels('admin_labels.products', ' Products') }}
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/tax') || Request::is('seller/tax/*') ? 'active' : '' }}"
                     href="{{ route('tax.index') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.tax', 'Tax') }}</span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/products/attributes') || Request::is('seller/products/attributes') ? 'active' : '' }}"
                     href="{{ route('attributes.index') }}">
                     <span class="sidenav-normal"> {{ labels('admin_labels.attributes', 'Attributes') }} </span>
                 </a>
             </li>

             <li class="nav-item ">
                 <a data-bs-toggle="collapse" href="#products_dropdown"
                     class="nav-link {{ Request::is('seller/products') || Request::is('seller/products/manage_product') || Request::is('seller/product_faqs') || Request::is('seller/product/product_bulk_upload') ? 'active' : '' }} {{ Request::is('seller/products') || Request::is('seller/products/*') || Request::is('seller/product_faqs') ? '' : 'collapsed' }}"
                     aria-controls="products_dropdown" role="button" aria-expanded="false">
                     <span
                         class="nav-link-text ">{{ labels('admin_labels.products_manage', 'Products Manage') }}</span><i
                         class="fas fa-angle-down"></i>
                 </a>
                 <div class="collapse {{ Request::is('seller/products') || Request::is('seller/products/*') || Request::is('seller/product_faqs') ? 'show' : '' }}"
                     id="products_dropdown">
                     <ul class="nav ">
                         <li class="nav-item {{ Request::is('seller/products') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('seller.products.index') }}">
                                 <span class="nav-link-text">{{ labels('admin_labels.add_products', 'Add Products') }}
                                 </span>
                             </a>
                         </li>
                         <li class="nav-item {{ Request::is('seller/products/manage_product') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('seller.products.manage_product') }}">
                                 <span
                                     class="nav-link-text">{{ labels('admin_labels.manage_products', 'Manage Products') }}
                                 </span>
                             </a>
                         </li>
                         <li class="nav-item {{ Request::is('seller/product_faqs') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('seller.product_faqs.index') }}">
                                 <span
                                     class="nav-link-text">{{ labels('admin_labels.product_faqs', 'Product FAQs') }}</span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('seller/product/product_bulk_upload') || Request::is('seller/product/product_bulk_upload') ? 'active' : '' }}"
                                 href="{{ route('seller.product_bulk_upload') }}">
                                 <span class="nav-link-text">{{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}
                                 </span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>

             <li class="sidebar-title"><i class='bx bx-package'></i>
                 {{ labels('admin_labels.combo_products_manage', 'Combo Products Manage') }}
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/combo_product_attributes') ? 'active' : '' }}"
                     href="{{ route('seller.combo_product_attributes.index') }}">
                     <span class="sidenav-normal"> {{ labels('admin_labels.attributes', 'Attributes') }} </span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/combo_products') ? 'active' : '' }}"
                     href="{{ route('seller.combo_products.index') }}">
                     <span class="sidenav-normal">
                         {{ labels('admin_labels.add_combo_products', 'Add Combo Products') }} </span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/combo_products/manage_product') ? 'active' : '' }}"
                     href="{{ route('seller.combo_products.manage_product') }}">
                     <span class="sidenav-normal"> {{ labels('admin_labels.manage_products', 'Manage Products') }}
                     </span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/combo_product_faqs') || Request::is('seller/combo_product_faqs') ? 'active' : '' }}"
                     href="{{ route('seller.combo_product_faqs.index') }}">
                     <span class="sidenav-normal">{{ labels('admin_labels.product_faqs', 'Product FAQs') }} </span>
                 </a>
             </li>
             <li class="sidebar-title"><i class='bx bx-image-add'></i>
                 {{ labels('admin_labels.media_management', 'Media Management') }}
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/media') || Request::is('seller/media/*') ? 'active' : '' }}"
                     href="{{ route('seller.media') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.add_media', 'Add Media') }}</span>
                 </a>
             </li>
             <li class="sidebar-title "><i class='bx bx-chat'></i>
                 {{ labels('admin_labels.chat_manage', 'Chat Management') }}
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/chat') || Request::is('seller/chat/*') ? 'active' : '' }}"
                     href="{{ route('seller.chat.index') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.chats', 'Chats') }}</span>
                     @if ($unread > 0)
                         <span
                             class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20  rounded-pill">{{ $unread }}</span>
                     @endif
                 </a>
             </li>
             <li class="sidebar-title "><i class='bx bx-wallet-alt'></i>
                 {{ labels('admin_labels.wallet_management', 'Wallet Management') }}
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/transaction/wallet_transactions') ? 'active' : '' }}"
                     href="{{ route('seller.transaction.wallet_transactions') }}">
                     <span
                         class="nav-link-text ">{{ labels('admin_labels.wallet_transaction', 'Wallet Transaction') }}</span>
                 </a>
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/payment_request/withdrawal_requests') ? 'active' : '' }}"
                     href="{{ route('seller.payment_request.withdrawal_requests') }}">
                     <span class="nav-link-text ">
                         {{ labels('admin_labels.withdrawal_requests', 'Withdrawal Requests') }}</span>
                 </a>
             </li>
             <li class="sidebar-title "><i
                     class='bx bx-tachometer'></i></i>{{ labels('admin_labels.location_management', 'Location Management') }}
             </li>
             <li class="nav-item ">
                 <a data-bs-toggle="collapse" href="#location_dropdown"
                     class="nav-link {{ Request::is('seller/area') || Request::is('seller/area/*') ? 'active' : '' }}  {{ Request::is('seller/area') || Request::is('seller/area/*') ? '' : 'collapsed' }}"
                     aria-controls="location_dropdown" role="button" aria-expanded="false">
                     <span class="nav-link-text ">{{ labels('admin_labels.location', 'Locations') }}</span><i
                         class="fas fa-angle-down"></i>
                 </a>
                 <div class="collapse {{ Request::is('seller/area') || Request::is('seller/area/*') ? 'show' : '' }}"
                     id="location_dropdown">
                     <ul class="nav">
                         <li class="nav-item {{ Request::is('seller/area/zipcodes') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('seller.zipcodes') }}">
                                 <span class="nav-link-text">{{ labels('admin_labels.zipcodes', 'Zipcodes') }}</span>
                             </a>
                         </li>
                         <li class="nav-item {{ Request::is('seller/area/city') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('seller.city') }}">
                                 <span class="nav-link-text">{{ labels('admin_labels.city', 'City') }}</span>
                             </a>
                         </li>
                         <li class="nav-item {{ Request::is('seller/area/zones') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('seller.zones') }}">
                                 <span class="nav-link-text">{{ labels('admin_labels.zones', 'Zones') }}</span>
                             </a>
                         </li>

                     </ul>
                 </div>
             </li>
             {{-- <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/pickup_locations') || Request::is('seller/pickup_locations/*') ? 'active' : '' }}"
                     href="{{ route('pickup_locations.index') }}">
                     <span
                         class="nav-link-text ">{{ labels('admin_labels.pickup_locations', 'Pickup Locations') }}</span>
                 </a>
             </li> --}}
             <li class="sidebar-title "><i class='bx bx-tachometer'></i>
                 {{ labels('admin_labels.reports_and_sales_management', 'Reports & Sales Mangement') }}
             </li>
             <li class="nav-item ">
                 <a class="nav-link {{ Request::is('seller/reports/sales_report') ? 'active' : '' }}"
                     href="{{ route('seller.reports.sales_report') }}">
                     <span class="nav-link-text ">{{ labels('admin_labels.sales_report', 'Sales Report') }}</span>
                 </a>
             </li>
         </ul>
     </div>
 </nav>
