 <!-- Sidebar -->
 <nav class="navbar-vertical navbar bg-white">
     <div class="nav-scroller bg-white">
         @php

            use Chatify\ChatifyMessenger;

            $setting = getSettings('system_settings', true);
            $setting = json_decode($setting, true);

            $messenger = new ChatifyMessenger();
            $unread = $messenger->totalUnseenMessages();

            @endphp
         <div class="sidenav-header">
             <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
             <a class="navbar-brand m-0" href="{{ route('admin.home') }}" target="">
                 <img src="{{ getMediaImageUrl($setting['logo']) }}" class="navbar-brand-img" alt="main_logo">

             </a>
         </div>
         <hr class="horizontal dark mt-0">
         <ul class="navbar-nav">
             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.dashboard', 'Dashboard') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/home') || Request::is('admin/home/*') ? 'active' : '' }}" href="{{ route('admin.home') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.dashboard', 'Dashboard') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.stores', 'Stores') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/store') || Request::is('admin/store*') ? 'active' : '' }}" href="{{ route('admin.stores.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_store', 'Add Store') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/stores/manage_store') || Request::is('admin/stores/manage_store*') ? 'active' : '' }}" href="{{ route('admin.stores.manage_store') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.manage_stores', 'Manage Stores') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.manage', 'Manage') }}
             </li>
             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#order_dropdown" class="nav-link collapsed {{ Request::is('admin/orders') || Request::is('admin/orders*') || Request::is('admin/order_items') ? 'active' : '' }}" aria-controls="order_dropdown" role="button" aria-expanded="false">

                     <span class="nav-link-text ms-1">{{ labels('admin_labels.orders_manage', 'Orders Manage') }}</span>
                 </a>
                 <div class="collapse" id="order_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/orders') || Request::is('admin/orders*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.orders', 'Orders') }}</span>
                             </a>
                         </li>
                     </ul>
                 </div>
                 <div class="collapse" id="order_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/order_items') || Request::is('admin/order_items*') ? 'active' : '' }}" href="{{ route('admin.order_items.index') }}">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.order_items', 'Order Items') }}</span>
                             </a>
                         </li>
                     </ul>
                 </div>
                 <div class="collapse" id="order_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/orders') || Request::is('admin/orders*') ? 'active' : '' }}" href="{{ route('admin.orders.order_tracking') }}">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.order_tracking', 'Order Tracking') }}</span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>

             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#category_dropdown" class="nav-link collapsed {{ Request::is('admin/categories') || Request::is('admin/categories/*') ? 'active' : '' }}" aria-controls="category_dropdown" role="button" aria-expanded="false">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.categories', 'Categories') }}</span>
                 </a>
                 <div class="collapse" id="category_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/categories') || Request::is('admin/categories/*') ? 'active' : '' }}" href="/admin/categories">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.categories', 'Categories') }}</span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/categories/category_order') || Request::is('admin/categories/category_order/*') ? 'active' : '' }}" href="/admin/categories/category_order">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.categories_order', 'Categories Order') }}</span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/categories/category_slider') || Request::is('admin/categories/category_slider/*') ? 'active' : '' }}" href="/admin/categories/category_slider">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.categories_sliders', 'Categories Sliders') }}</span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/manage_stock') || Request::is('admin/manage_stock/*') ? 'active' : '' }}" href="{{ route('admin.manage_stock.index') }}">

                     <span class="nav-link-text ms-1">{{ labels('admin_labels.stock_manage', 'Stock Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/manage_combo_stock') || Request::is('admin/manage_combo_stock/*') ? 'active' : '' }}" href="{{ route('admin.manage_combo_stock.index') }}">

                     <span class="nav-link-text ms-1">{{ labels('admin_labels.combo_stock_manage', 'Combo Stock Manage') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.brand', 'Brand') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/brands') || Request::is('admin/brands') ? 'active' : '' }}" href="/admin/brands">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.brand', 'Brand') }}</span>
                 </a>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/brands/bulk_upload') || Request::is('admin/brands/bulk_upload/*') ? 'active' : '' }}" href="/admin/brands/bulk_upload">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.sellers', 'Sellers') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/sellers') || Request::is('admin/sellers') ? 'active' : '' }}" href="{{ route('admin.sellers.create') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_sellers', 'Add Sellers') }}</span>
                 </a>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/sellers') || Request::is('admin/sellers') ? 'active' : '' }}" href="{{ route('sellers.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.sellers', 'Sellers') }}</span>
                 </a>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/sellers/sellerWallet') || Request::is('admin/sellers/sellerWallet/*') ? 'active' : '' }}" href="{{ route('admin.sellers.sellerWallet') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.wallet_transactions', 'Wallet Transaction') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.products', 'Products') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/taxes') || Request::is('admin/taxes/*') ? 'active' : '' }}" href="/admin/taxes">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.tax', 'Tax') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/attributes/') || Request::is('admin/attributes/') ? 'active' : '' }}" href="{{ route('admin.attributes.index') }}">
                     <span class="sidenav-normal">
                         {{ labels('admin_labels.attributes_manage', 'Attributes Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#products_dropdown" class="nav-link collapsed {{ Request::is('admin/products') || Request::is('admin/products/*') ? 'active' : '' }}" aria-controls="products_dropdown" role="button" aria-expanded="false">
                     <span class="nav-link-text">{{ labels('admin_labels.products_manage', 'Products Manage') }}</span>
                 </a>
                 <div class="collapse" id="products_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/products/') || Request::is('admin/products/') ? 'active' : '' }}" href="{{ route('admin.products.index') }}">
                                 <span class="sidenav-normal">
                                     {{ labels('admin_labels.add_products', 'Add Products') }}</span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/products/manage-products') || Request::is('admin/products/manage-products') ? 'active' : '' }}" href="{{ route('admin.products.manage_product') }}">
                                 <span class="sidenav-normal">
                                     {{ labels('admin_labels.manage_products', 'Manage Products') }}
                                 </span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/product_faqs') || Request::is('admin/product_faqs') ? 'active' : '' }}" href="{{ route('admin.product_faqs.index') }}">
                                 <span class="sidenav-normal">{{ labels('admin_labels.product_faqs', 'Product FAQs') }}
                                 </span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>
             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.combo_products_manage', 'Combo Products Manage') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/combo_product_attributes/') || Request::is('admin/combo_product_attributes/') ? 'active' : '' }}" href="{{ route('admin.combo_product_attributes.index') }}">
                     <span class="sidenav-normal">
                         {{ labels('admin_labels.attributes_manage', 'Attributes Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#combo_products_dropdown" class="nav-link collapsed {{ Request::is('admin/combo_products') || Request::is('admin/combo_product_attributes/*') ? 'active' : '' }}" aria-controls="combo_products_dropdown" role="button" aria-expanded="false">

                     <span class="nav-link-text">{{ labels('admin_labels.products_manage', 'Products Manage') }}</span>
                 </a>
                 <div class="collapse" id="combo_products_dropdown">
                     <ul class="nav">

                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/combo_products/') || Request::is('admin/combo_products/') ? 'active' : '' }}" href="{{ route('admin.combo_products.index') }}">
                                 <span class="sidenav-normal">
                                     {{ !trans()->has('admin_labels.add_combo_products') ? 'Products Manage' : trans('admin_labels.add_combo_products') }}
                                 </span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/combo_products/manage-products') || Request::is('admin/combo_products/manage-products') ? 'active' : '' }}" href="{{ route('admin.combo_products.manage_product') }}">
                                 <span class="sidenav-normal">
                                     {{ labels('admin_labels.manage_products', 'Manage Products') }}
                                 </span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/combo_product_faqs') || Request::is('admin/combo_product_faqs') ? 'active' : '' }}" href="{{ route('admin.combo_product_faqs.index') }}">
                                 <span class="sidenav-normal">{{ labels('admin_labels.product_faqs', 'Product FAQs') }}
                                 </span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.blogs', 'Blogs') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/blogs') ? 'active' : '' }}" href="{{ route('admin.blogs.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.blog_categories', 'Blog Categories') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/manage_blogs') || Request::is('admin/manage_blogs/*') ? 'active' : '' }}" href="/admin/manage_blogs">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.create_blog', 'Create Blog') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.media_manage', 'Media Manage') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/media') || Request::is('admin/media/*') ? 'active' : '' }}" href="/admin/media">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_media', 'Add Media') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.slider', 'Slider') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/sliders') || Request::is('admin/sliders/*') ? 'active' : '' }}" href="/admin/sliders">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_slider', 'Add Slider') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.offers', 'Offers') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/offers') ? 'active' : '' }}" href="/admin/offers">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.offers', 'Offers') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/offer_sliders') || Request::is('admin/offer_sliders/*') ? 'active' : '' }}" href="/admin/offer_sliders">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.offer_sliders', 'Offer Sliders') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/promo_codes') || Request::is('admin/promo_codes/*') ? 'active' : '' }}" href="/admin/promo_codes">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.promo_codes', 'Promo Codes') }}</span>
                 </a>
             </li>


             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.support_tickets', 'Support Tickets') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/tickets/ticket_types') || Request::is('admin/tickets/ticket_types/*') ? 'active' : '' }}" href="/admin/tickets/ticket_types">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.ticket_types', 'Ticket Types') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/tickets/ticket_types') || Request::is('admin/tickets/ticket_types/*') ? 'active' : '' }}" href="{{ route('admin.tickets.viewTickets') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.tickets', 'Tickets') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.chat_manage', 'Chat Manage') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/chat') || Request::is('admin/chat*') ? 'active' : '' }}" href="{{ route('admin.chat.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.chats', 'Chats') }}</span>
                     @if ($unread > 0)
                     <span class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20 ms-1 rounded-pill">{{ $unread }}</span>
                     @endif
                 </a>
             </li>
             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.featured_section', 'Featured Section') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/feature_section') || Request::is('admin/feature_section*') ? 'active' : '' }}" href="{{ route('feature_section.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.featured', 'Featured') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.customers', 'Customers') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/customers') || Request::is('admin/customers*') ? 'active' : '' }}" href="{{ route('admin.customers') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.view_customers', 'View Customers') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/customers') || Request::is('admin/customers*') ? 'active' : '' }}" href="{{ route('admin.customers.getCustomersAddresses') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.addresses', 'Addresses') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/customers') || Request::is('admin/customers*') ? 'active' : '' }}" href="{{ route('admin.customers.viewTransactions') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.transactions', 'Transactions') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/customers') || Request::is('admin/customers*') ? 'active' : '' }}" href="{{ route('admin.customers.walletTransaction') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.wallet_transactions', 'Wallet Transactions') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>{{ labels('admin_labels.return_requests', 'Return Requests') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/return_request') || Request::is('admin/return_request/*') ? 'active' : '' }}" href="{{ route('admin.return_request.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.return_requests', 'Return Requests') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.delivery_boys', 'Delivery Boys') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/delivery_boys') ? 'active' : '' }}" href="/admin/delivery_boys">

                     <span class="nav-link-text ms-1">{{ labels('admin_labels.delivery_boys', 'Delivery Boys') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/delivery_boys/manage_cash') || Request::is('admin/delivery_boys/manage_cash*') ? 'active' : '' }}" href="/admin/delivery_boys/manage_cash">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.cash_collection', 'Cash Collection') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/delivery_boys/fund_transfers') || Request::is('admin/delivery_boys/fund_transfers*') ? 'active' : '' }}" href="/admin/delivery_boys/fund_transfers">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.fund_transfer', 'Fund Transfer') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.payment_request', 'Payment Request') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/payment_request') || Request::is('admin/payment_request/*') ? 'active' : '' }}" href="/admin/payment_request">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.payment_request', 'Payment Request') }}</span>
                 </a>
             </li>
             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>{{ labels('admin_labels.faqs', 'FAQs') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/faq') || Request::is('admin/faq/*') ? 'active' : '' }}" href="/admin/faq">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.faqs', 'FAQs') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.send_notification', 'Send Notification') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/send_notification') || Request::is('admin/send_notification/*') ? 'active' : '' }}" href="/admin/send_notification">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.notification', 'Notification') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.custom_message', 'Custom Message') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/custom_message') || Request::is('admin/custom_message/*') ? 'active' : '' }}" href="{{ route('admin.custom_message.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_custom_message', 'Add Custom Message') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>{{ labels('admin_labels.location_management', 'Location Management') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/area/zipcodes') || Request::is('admin/area/zipcodes') ? 'active' : '' }}" href="{{ route('admin.display_zipcodes') }}">
                     <span class="sidenav-normal">{{ labels('admin_labels.zipcodes', 'Zipcodes') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/area/city') || Request::is('admin/area/city') ? 'active' : '' }}" href="{{ route('admin.display_city') }}">
                     <span class="sidenav-normal">{{ labels('admin_labels.city', 'City') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/location/bulk_upload') || Request::is('admin/location/bulk_upload/*') ? 'active' : '' }}" href="/admin/area/location_bulk_upload">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.system_settings', 'System Settings') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/settings') || Request::is('admin/settings/*') ? 'active' : '' }}" href="/admin/settings">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.settings', 'Settings') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.web_settings', 'Web Settings') }}
             </li>

             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#web_setting_dropdown" class="nav-link collapsed {{ Request::is('admin/web_settings') || Request::is('admin/web_settings*') ? 'active' : '' }}" aria-controls="web_setting_dropdown" role="button" aria-expanded="false">

                     <span class="nav-link-text ms-1">{{ labels('admin_labels.web_settings', 'Web Settings') }}</span>
                 </a>
                 <div class="collapse" id="web_setting_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/general_settings') || Request::is('admin/web_settings/general_settings*') ? 'active' : '' }}" href="{{ route('general_settings') }}">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.general_settings', 'General Settings') }}</span>
                             </a>
                         </li>
                     </ul>
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/firebase') || Request::is('admin/web_settings/firebase*') ? 'active' : '' }}" href="{{ route('firebase') }}">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.firebase', 'Firebase') }}</span>
                             </a>
                         </li>
                     </ul>
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/firebase') || Request::is('admin/web_settings/firebase*') ? 'active' : '' }}" href="{{ route('firebase') }}">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.themes', 'Themes') }}</span>
                             </a>
                         </li>
                     </ul>
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/language') || Request::is('admin/web_settings/language/*') ? 'active' : '' }}" href="{{ route('web_language') }}">
                                 <span class="nav-link-text ms-1">{{ labels('admin_labels.languages', 'Languages') }}</span>
                             </a>
                         </li>
                     </ul>

                 </div>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>{{ labels('admin_labels.system_users', 'System Users') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/system_users') || Request::is('admin/system_users/*') ? 'active' : '' }}" href="/admin/system_users">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.system_users', 'System Users') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/manage_system_users') || Request::is('admin/manage_system_users/*') ? 'active' : '' }}" href="/admin/manage_system_users">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.manage_system_users', 'Manage System Users') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class="fas fa-tachometer-alt"></i>
                 {{ labels('admin_labels.language_settings', 'Language Settings') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/settings/language') || Request::is('admin/settings/language/*') ? 'active' : '' }}" href="/admin/settings/language">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.language', 'Language') }}</span>
                 </a>
             </li>

         </ul>
     </div>
 </nav>
