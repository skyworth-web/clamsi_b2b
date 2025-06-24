 <!-- Sidebar -->
 <nav class="navbar-vertical navbar bg-white" {{ session()->get('is_rtl') == 1 ? 'dir=rtl' : '' }}>
     <div class="nav-scroller bg-white">
         @php
             $user = auth()->user();
             use Chatify\ChatifyMessenger;

             $setting = getSettings('system_settings', true);
             $setting = json_decode($setting, true);

             $sms_gateway_settings = getSettings('sms_gateway_settings');
             $messenger = new ChatifyMessenger();
             $unread = $messenger->totalUnseenMessages();
         @endphp
         <input type="hidden" id="sms_gateway_data" value='{{ $sms_gateway_settings }}' />
         <div class="sidenav-header">
             <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
                 aria-hidden="true" id="iconSidenav"></i>
             <a class="navbar-brand m-0" href="{{ route('admin.home') }}" target="">
                 @php
                     $store_logo =
                         !empty($setting['logo']) &&
                         file_exists(public_path(config('constants.MEDIA_PATH') . $setting['logo']))
                             ? getMediaImageUrl($setting['logo'])
                             : asset('assets/img/default_full_logo.png');
                 @endphp
                 <img src="{{ $store_logo }}" class="navbar-brand-img" alt="main_logo">
             </a>
         </div>
         <hr class="horizontal dark mt-0">

         <!-- code for menu search -->

         <div class="ps-2 pe-2">
             <!-- Search Bar -->
             <input type="text" class="form-control menuSearch" placeholder="Search Menu...">
         </div>


         <ul class="navbar-nav" id="menuList">
             <li class="sidebar-title ms-3"><i class='bx bx-tachometer'></i>
                 {{ labels('admin_labels.dashboard', 'Dashboard') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/home') || Request::is('admin/home/*') ? 'active' : '' }}"
                     href="{{ route('admin.home') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.dashboard', 'Dashboard') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-store-alt'></i>
                 {{ labels('admin_labels.stores', 'Stores') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/store') || Request::is('admin/store') ? 'active' : '' }}"
                     href="{{ route('admin.stores.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_store', 'Add Store') }}</span>
                 </a>
             </li>
             @if ($user_role == 'super_admin' || $user->hasPermissionTo('view store'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/stores/manage_store') || Request::is('admin/stores/manage_store*') ? 'active' : '' }}"
                         href="{{ route('admin.stores.manage_store') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.manage_stores', 'Manage Stores') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $user->hasPermissionTo('view orders'))
                 <li class="sidebar-title ms-3"><i class='bx bx-card'></i>
                     {{ labels('admin_labels.manage', 'Manage') }}
                 </li>
                 <li class="nav-item ms-3">
                     <a data-bs-toggle="collapse" href="#order_dropdown"
                         class="nav-link collapsed {{ Request::is('admin/orders') || Request::is('admin/orders*') || Request::is('admin/order_items') ? 'active' : '' }} {{ Request::is('admin/orders') || Request::is('admin/orders*') || Request::is('admin/order_items') ? '' : 'collapsed' }}"
                         aria-controls="order_dropdown" role="button" aria-expanded="false">

                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.orders_manage', 'Orders Manage') }}</span>
                         <i class="fas fa-angle-down"></i>
                     </a>
                     <div class="collapse {{ Request::is('admin/orders') || Request::is('admin/orders*') || Request::is('admin/order_items') ? 'show' : '' }}"
                         id="order_dropdown">
                         <ul class="nav">
                             <li class="nav-item {{ Request::is('admin/orders') ? 'active' : '' }}">
                                 <a class="nav-link " href="{{ route('admin.orders.index') }}">
                                     <span
                                         class="nav-link-text ms-1">{{ labels('admin_labels.orders', 'Orders') }}</span>
                                 </a>
                             </li>
                             <li
                                 class="nav-item {{ Request::is('admin/order_items') || Request::is('admin/order_items*') ? 'active' : '' }}">
                                 <a class="nav-link " href="{{ route('admin.order_items.index') }}">
                                     <span
                                         class="nav-link-text ms-1">{{ labels('admin_labels.order_items', 'Order Items') }}</span>
                                 </a>
                             </li>
                             <li class="nav-item {{ Request::is('admin/orders/order_tracking') ? 'active' : '' }}">
                                 <a class="nav-link " href="{{ route('admin.orders.order_tracking') }}">
                                     <span
                                         class="nav-link-text ms-1">{{ labels('admin_labels.order_tracking', 'Order Tracking') }}</span>
                                 </a>
                             </li>
                         </ul>
                     </div>
                 </li>
             @endif
             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#category_dropdown"
                     class="nav-link {{ Request::is('admin/categories') || Request::is('admin/categories/*') ? '' : 'collapsed' }} {{ Request::is('admin/categories') || Request::is('admin/categories/*') ? 'active' : '' }}"
                     aria-controls="category_dropdown" role="button" aria-expanded="false">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.categories', 'Categories') }}</span>
                     <i class="fas fa-angle-down"></i>
                 </a>
                 <div class="collapse {{ Request::is('admin/categories') || Request::is('admin/categories/*') ? 'show' : '' }}"
                     id="category_dropdown">
                     <ul class="nav">
                         <li
                             class="nav-item {{ Request::is('admin/categories') || Request::is('admin/categories') ? 'active' : '' }}">
                             <a class="nav-link" href="{{ route('categories.index') }}">
                                 <span
                                     class="nav-link-text ms-1">{{ labels('admin_labels.categories', 'Categories') }}</span>
                             </a>
                         </li>
                         @if ($user_role == 'super_admin' || $user->hasPermissionTo('view category_order'))
                             <li
                                 class="nav-item {{ Request::is('admin/categories/category_order') || Request::is('admin/categories/category_order/*') ? 'active' : '' }}">
                                 <a class="nav-link " href="{{ route('category_order.index') }}">
                                     <span
                                         class="nav-link-text ms-1">{{ labels('admin_labels.categories_order', 'Categories Order') }}</span>
                                 </a>
                             </li>
                         @endif
                         <li
                             class="nav-item {{ Request::is('admin/categories/category_slider') || Request::is('admin/categories/category_slider/*') ? 'active' : '' }}">
                             <a class="nav-link " href="{{ route('category_slider.index') }}">
                                 <span
                                     class="nav-link-text ms-1">{{ labels('admin_labels.categories_sliders', 'Categories Sliders') }}</span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>
             @if ($user_role == 'super_admin' || $user->hasPermissionTo('view stock'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/manage_stock') || Request::is('admin/manage_stock/*') ? 'active' : '' }}"
                         href="{{ route('admin.manage_stock.index') }}">

                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.stock_manage', 'Stock Manage') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $user->hasPermissionTo('view combo_stock'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/manage_combo_stock') || Request::is('admin/manage_combo_stock/*') ? 'active' : '' }}"
                         href="{{ route('admin.manage_combo_stock.index') }}">

                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.combo_stock_manage', 'Combo Stock Manage') }}</span>
                     </a>
                 </li>
             @endif
             <li class="sidebar-title ms-3"><i class='bx bx-card'></i></i>
                 {{ labels('admin_labels.brand', 'Brand') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/brands') || Request::is('admin/brands') ? 'active' : '' }}"
                     href="{{ route('brands.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.brand', 'Brand') }}</span>
                 </a>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/brands/bulk_upload') || Request::is('admin/brands/bulk_upload/*') ? 'active' : '' }}"
                     href="{{ route('brands.bulk_upload') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-card'></i>
                 {{ labels('admin_labels.sellers', 'Sellers') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/seller/*') || Request::is('admin/seller/create') ? 'active' : '' }}"
                     href="{{ route('admin.sellers.create') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_sellers', 'Add Sellers') }}</span>
                 </a>
                 </a>
             </li>
             @if ($user_role == 'super_admin' || $user->hasPermissionTo('view seller'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/sellers') || Request::is('admin/sellers') ? 'active' : '' }}"
                         href="{{ route('sellers.index') }}">
                         <span class="nav-link-text ms-1">{{ labels('admin_labels.sellers', 'Sellers') }}</span>
                     </a>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $user->hasPermissionTo('view seller_wallet_transaction'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/sellers/seller_wallet_transaction') || Request::is('admin/sellers/seller_wallet_transaction/*') ? 'active' : '' }}"
                         href="{{ route('admin.sellers.sellerWallet') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.wallet_transactions', 'Wallet Transaction') }}</span>
                     </a>
                 </li>
             @endif
             <li class="sidebar-title ms-3"><i class='bx bx-cart-alt'></i>
                 {{ labels('admin_labels.products', 'Products') }}
             </li>
             @if ($user_role == 'super_admin' || $user->hasPermissionTo('view tax'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/taxes') || Request::is('admin/taxes/*') ? 'active' : '' }}"
                         href="{{ route('taxes.index') }}">
                         <span class="nav-link-text ms-1">{{ labels('admin_labels.tax', 'Tax') }}</span>
                     </a>
                 </li>
             @endif
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/attributes') || Request::is('admin/attributes*') ? 'active' : '' }}"
                     href="{{ route('admin.attributes.index') }}">
                     <span class="sidenav-normal">
                         {{ labels('admin_labels.attributes_manage', 'Attributes Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#products_dropdown"
                     class="nav-link  {{ Request::is('admin/products') || Request::is('admin/products/*') || Request::is('admin/product_faqs') ? '' : 'collapsed' }} {{ Request::is('admin/products') || Request::is('admin/products/*') || Request::is('admin/product_faqs') ? 'active' : '' }}"
                     aria-controls="products_dropdown" role="button" aria-expanded="false">
                     <span
                         class="nav-link-text">{{ labels('admin_labels.products_manage', 'Products Manage') }}</span>
                     <i class="fas fa-angle-down"></i>
                 </a>
                 <div class="collapse {{ Request::is('admin/products') || Request::is('admin/products/*') || Request::is('admin/product_faqs') ? 'show' : '' }}"
                     id="products_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/products') ? 'active' : '' }}"
                                 href="{{ route('admin.products.index') }}">
                                 <span class="sidenav-normal">
                                     {{ labels('admin_labels.add_products', 'Add Products') }}</span>
                             </a>
                         </li>
                         @if ($user_role == 'super_admin' || $user->hasPermissionTo('view product'))
                             <li class="nav-item">
                                 <a class="nav-link {{ Request::is('admin/products/manage_product') || Request::is('admin/products/manage_product') ? 'active' : '' }}"
                                     href="{{ route('admin.products.manage_product') }}">
                                     <span class="sidenav-normal">
                                         {{ labels('admin_labels.manage_products', 'Manage Products') }}
                                     </span>
                                 </a>
                             </li>
                         @endif
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/product_faqs') || Request::is('admin/product_faqs') ? 'active' : '' }}"
                                 href="{{ route('admin.product_faqs.index') }}">
                                 <span
                                     class="sidenav-normal">{{ labels('admin_labels.product_faqs', 'Product FAQs') }}
                                 </span>
                             </a>
                         </li>
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/product/product_bulk_upload') || Request::is('admin/product/product_bulk_upload') ? 'active' : '' }}"
                                 href="{{ route('admin.product_bulk_upload') }}">
                                 <span class="sidenav-normal">{{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}
                                 </span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>
             <li class="sidebar-title ms-3"><i class='bx bx-package'></i>
                 {{ labels('admin_labels.combo_products_manage', 'Combo Products Manage') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/combo_product_attributes') || Request::is('admin/combo_product_attributes/*') ? 'active' : '' }}"
                     href="{{ route('admin.combo_product_attributes.index') }}">
                     <span class="sidenav-normal">
                         {{ labels('admin_labels.attributes_manage', 'Attributes Manage') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#combo_products_dropdown"
                     class="nav-link {{ Request::is('admin/combo_products') || Request::is('admin/combo_products*') || Request::is('admin/combo_product_faqs') ? '' : 'collapsed' }} {{ Request::is('admin/combo_products') || Request::is('admin/combo_products*') || Request::is('admin/combo_product_faqs') ? 'active' : '' }}"
                     aria-controls="combo_products_dropdown" role="button" aria-expanded="false">

                     <span
                         class="nav-link-text">{{ labels('admin_labels.products_manage', 'Products Manage') }}</span>
                     <i class="fas fa-angle-down"></i>
                 </a>
                 <div class="collapse {{ Request::is('admin/combo_products') || Request::is('admin/combo_products*') || Request::is('admin/combo_product_faqs') ? 'show' : '' }}"
                     id="combo_products_dropdown">
                     <ul class="nav">

                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/combo_products') ? 'active' : '' }}"
                                 href="{{ route('admin.combo_products.index') }}">
                                 <span class="sidenav-normal">
                                     {{ labels('admin_labels.add_combo_products', 'Add Products') }}
                                 </span>
                             </a>
                         </li>
                         @if ($user_role == 'super_admin' || $user->hasPermissionTo('view combo_product'))
                             <li class="nav-item">
                                 <a class="nav-link {{ Request::is('admin/combo_products/manage_product') ? 'active' : '' }}"
                                     href="{{ route('admin.combo_products.manage_product') }}">
                                     <span class="sidenav-normal">
                                         {{ labels('admin_labels.manage_products', 'Manage Products') }}
                                     </span>
                                 </a>
                             </li>
                         @endif
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/combo_product_faqs') || Request::is('admin/combo_product_faqs') ? 'active' : '' }}"
                                 href="{{ route('admin.combo_product_faqs.index') }}">
                                 <span
                                     class="sidenav-normal">{{ labels('admin_labels.product_faqs', 'Product FAQs') }}
                                 </span>
                             </a>
                         </li>
                     </ul>
                 </div>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-card'></i>
                 {{ labels('admin_labels.blogs', 'Blogs') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/blogs') ? 'active' : '' }}"
                     href="{{ route('admin.blogs.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.blog_categories', 'Blog Categories') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/manage_blogs') || Request::is('admin/manage_blogs/*') ? 'active' : '' }}"
                     href="{{ route('manage_blogs.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.create_blog', 'Create Blog') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-image-add'></i>
                 {{ labels('admin_labels.media_manage', 'Media Manage') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/media') || Request::is('admin/media/*') ? 'active' : '' }}"
                     href="{{ route('admin.media') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_media', 'Add Media') }}</span>
                 </a>
                 <a class="nav-link {{ Request::is('admin/storage_type') || Request::is('admin/storage_type/*') ? 'active' : '' }}"
                     href="{{ route('admin.storage_type') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.storage_type', 'Storage Type') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-carousel'></i>
                 {{ labels('admin_labels.slider', 'Slider') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/sliders') || Request::is('admin/sliders/*') ? 'active' : '' }}"
                     href="{{ route('sliders.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.add_slider', 'Add Slider') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-gift'></i>
                 {{ labels('admin_labels.offers', 'Offers') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/offers') ? 'active' : '' }}"
                     href="{{ route('offers.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.offers', 'Offers') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/offer_sliders') || Request::is('admin/offer_sliders/*') ? 'active' : '' }}"
                     href="{{ route('offer_sliders.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.offer_sliders', 'Offer Sliders') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/promo_codes') || Request::is('admin/promo_codes/*') ? 'active' : '' }}"
                     href="{{ route('promo_codes.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.promo_codes', 'Promo Codes') }}</span>
                 </a>
             </li>


             <li class="sidebar-title ms-3"><i class='bx bx-support'></i>
                 {{ labels('admin_labels.support_tickets', 'Support Tickets') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/tickets/ticket_types') || Request::is('admin/tickets/ticket_types*') ? 'active' : '' }}"
                     href="{{ route('ticket_types.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.ticket_types', 'Ticket Types') }}</span>
                 </a>
             </li>
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view tickets'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/tickets') ? 'active' : '' }}"
                         href="{{ route('admin.tickets.viewTickets') }}">
                         <span class="nav-link-text ms-1">{{ labels('admin_labels.tickets', 'Tickets') }}</span>
                     </a>
                 </li>
             @endif

             <li class="sidebar-title ms-3"><i class='bx bx-chat'></i>
                 {{ labels('admin_labels.chat_manage', 'Chat Manage') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/chat') || Request::is('admin/chat*') ? 'active' : '' }}"
                     href="{{ route('admin.chat.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.chats', 'Chats') }}</span>
                     @if ($unread > 0)
                         <span
                             class="flex-shrink-0 badge badge-center bg-danger w-px-20 h-px-20 ms-1 rounded-pill">{{ $unread }}</span>
                     @endif
                 </a>
             </li>
             <li class="sidebar-title ms-3"><i class='bx bx-chat'></i>
                 {{ labels('admin_labels.featured_section', 'Featured Section') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/feature_section') ? 'active' : '' }}"
                     href="{{ route('feature_section.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.featured', 'Featured') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/feature_section/section_order') ? 'active' : '' }}"
                     href="{{ route('feature_section.section_order') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.sections_order', 'Sections Order') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-user'></i>
                 {{ labels('admin_labels.customers', 'Customers') }}
             </li>
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view customers'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/customers') ? 'active' : '' }}"
                         href="{{ route('admin.customers') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.view_customers', 'View Customers') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view address'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/customers/customers_addresses') ? 'active' : '' }}"
                         href="{{ route('admin.customers.getCustomersAddresses') }}">
                         <span class="nav-link-text ms-1">{{ labels('admin_labels.addresses', 'Addresses') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view customer_transaction'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/customers/view_transactions') ? 'active' : '' }}"
                         href="{{ route('admin.customers.viewTransactions') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.transactions', 'Transactions') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view customer_wallet_transaction'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/customers/wallet_transaction') ? 'active' : '' }}"
                         href="{{ route('admin.customers.walletTransaction') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.wallet_transactions', 'Wallet Transactions') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view return_request'))
                 <li class="sidebar-title ms-3"><i
                         class='bx bx-revision'></i>{{ labels('admin_labels.return_requests', 'Return Requests') }}
                 </li>
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/return_request') || Request::is('admin/return_request/*') ? 'active' : '' }}"
                         href="{{ route('admin.return_request.index') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.return_requests', 'Return Requests') }}</span>
                     </a>
                 </li>
             @endif
             <li class="sidebar-title ms-3"><i class='bx bx-cycling'></i>
                 {{ labels('admin_labels.delivery_boys', 'Delivery Boys') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/delivery_boys') ? 'active' : '' }}"
                     href="{{ route('delivery_boys.index') }}">

                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.delivery_boys', 'Delivery Boys') }}</span>
                 </a>
             </li>
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view delivery_boy_cash_collection'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/delivery_boys/manage_cash') || Request::is('admin/delivery_boys/manage_cash*') ? 'active' : '' }}"
                         href="{{ route('admin.get_cash_collection.index') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.cash_collection', 'Cash Collection') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view fund_transfer'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/delivery_boys/fund_transfers') || Request::is('admin/delivery_boys/fund_transfers*') ? 'active' : '' }}"
                         href="{{ route('admin.delivery_boys.fund_transfers.index') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.fund_transfer', 'Fund Transfer') }}</span>
                     </a>
                 </li>
             @endif
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view payment_request'))
                 <li class="sidebar-title ms-3"><i class='bx bx-wallet-alt'></i>
                     {{ labels('admin_labels.payment_request', 'Payment Request') }}
                 </li>
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/payment_request') || Request::is('admin/payment_request/*') ? 'active' : '' }}"
                         href="{{ route('admin.payment_request.index') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.payment_request', 'Payment Request') }}</span>
                     </a>
                 </li>
             @endif
             <li class="sidebar-title ms-3"><i class='bx bx-chat'></i>{{ labels('admin_labels.faqs', 'FAQs') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/faq') || Request::is('admin/faq/*') ? 'active' : '' }}"
                     href="{{ route('faqs.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.faqs', 'FAQs') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-send'></i>
                 {{ labels('admin_labels.send_notification', 'Send Notification') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/send_notification') || Request::is('admin/send_notification/*') ? 'active' : '' }}"
                     href="{{ route('notifications.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.notification', 'Notification') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/send_seller_notification') || Request::is('admin/send_seller_notification/*') ? 'active' : '' }}"
                     href="{{ route('seller_notifications.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.seller_notification', 'Seller Notification') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/seller_email_notification') || Request::is('admin/seller_email_notification/*') ? 'active' : '' }}"
                     href="{{ route('seller_email_notifications.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.seller_email_notification', 'Seller Email Notification') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-message-dots'></i>
                 {{ labels('admin_labels.custom_message', 'Custom Message') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/custom_message') || Request::is('admin/custom_message/*') ? 'active' : '' }}"
                     href="{{ route('admin.custom_message.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.add_custom_message', 'Add Custom Message') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i
                     class='bx bx-map'></i>{{ labels('admin_labels.location_management', 'Location Management') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/area/zipcodes') || Request::is('admin/area/zipcodes') ? 'active' : '' }}"
                     href="{{ route('admin.display_zipcodes') }}">
                     <span class="sidenav-normal">{{ labels('admin_labels.zipcodes', 'Zipcodes') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/area/city') || Request::is('admin/area/city') ? 'active' : '' }}"
                     href="{{ route('admin.display_city') }}">
                     <span class="sidenav-normal">{{ labels('admin_labels.city', 'City') }}</span>
                 </a>
             </li>
             {{-- <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/pickup_location') || Request::is('admin/pickup_location') ? 'active' : '' }}"
                     href="{{ route('admin.pickup_location.index') }}">
                     <span
                         class="sidenav-normal">{{ labels('admin_labels.pickup_locations', 'Pickup Locations') }}</span>
                 </a>
             </li> --}}
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/zones') ? 'active' : '' }}"
                     href="{{ route('admin.zones.index') }}">
                     <span class="sidenav-normal">{{ labels('admin_labels.zones', 'Zones') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/area/location_bulk_upload') || Request::is('admin/area/location_bulk_upload/*') ? 'active' : '' }}"
                     href="{{ route('admin.location_bulk_upload.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.bulk_upload', 'Bulk Upload') }}</span>
                 </a>
             </li>
             <li class="sidebar-title ms-3"><i class='bx bx-cog'></i>
                 {{ labels('admin_labels.system_settings', 'System Settings') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/settings') ? 'active' : '' }}"
                     href="{{ route('settings.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.settings', 'Settings') }}</span>
                 </a>
             </li>

             <li class="sidebar-title ms-3"><i class='bx bx-globe'></i>
                 {{ labels('admin_labels.web_settings', 'Web Settings') }}
             </li>

             <li class="nav-item ms-3">
                 <a data-bs-toggle="collapse" href="#web_setting_dropdown"
                     class="nav-link {{ Request::is('admin/web_settings*') ? '' : 'collapsed' }}  {{ Request::is('admin/web_settings*') ? 'active' : '' }}"
                     aria-controls="web_setting_dropdown" role="button" aria-expanded="false">

                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.web_settings', 'Web Settings') }}</span>
                     <i class="fas fa-angle-down"></i>
                 </a>
                 <div class="collapse {{ Request::is('admin/web_settings*') ? 'show' : '' }}"
                     id="web_setting_dropdown">
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/general_settings') || Request::is('admin/web_settings/general_settings*') ? 'active' : '' }}"
                                 href="{{ route('general_settings') }}">
                                 <span
                                     class="nav-link-text ms-1">{{ labels('admin_labels.general_settings', 'General Settings') }}</span>
                             </a>
                         </li>
                     </ul>
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/pwa_settings') || Request::is('admin/web_settings/pwa_settings*') ? 'active' : '' }}"
                                 href="{{ route('pwa_settings') }}">
                                 <span
                                     class="nav-link-text ms-1">{{ labels('admin_labels.general_settings', 'PWA Settings') }}</span>
                             </a>
                         </li>
                     </ul>
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/firebase') || Request::is('admin/web_settings/firebase*') ? 'active' : '' }}"
                                 href="{{ route('firebase') }}">
                                 <span
                                     class="nav-link-text ms-1">{{ labels('admin_labels.firebase', 'Firebase') }}</span>
                             </a>
                         </li>
                     </ul>
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/theme') || Request::is('admin/web_settings/theme*') ? 'active' : '' }}"
                                 href="{{ route('theme') }}">
                                 <span
                                     class="nav-link-text ms-1">{{ labels('admin_labels.themes', 'Themes') }}</span>
                             </a>
                         </li>
                     </ul>
                     <ul class="nav">
                         <li class="nav-item">
                             <a class="nav-link {{ Request::is('admin/web_settings/language') || Request::is('admin/web_settings/language/*') ? 'active' : '' }}"
                                 href="{{ route('web_language') }}">
                                 <span
                                     class="nav-link-text ms-1">{{ labels('admin_labels.languages', 'Languages') }}</span>
                             </a>
                         </li>
                     </ul>

                 </div>
             </li>

             <li class="sidebar-title ms-3"><i
                     class='bx bx-group'></i>{{ labels('admin_labels.system_users', 'System Users') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/system_users') || Request::is('admin/system_users/*') ? 'active' : '' }}"
                     href="{{ route('admin.system_users.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.system_users', 'System Users') }}</span>
                 </a>
             </li>
             @if ($user_role == 'super_admin' || $logged_in_user->hasPermissionTo('view system_user'))
                 <li class="nav-item ms-3">
                     <a class="nav-link {{ Request::is('admin/manage_system_users') || Request::is('admin/manage_system_users/*') ? 'active' : '' }}"
                         href="{{ route('admin.manage_system_users') }}">
                         <span
                             class="nav-link-text ms-1">{{ labels('admin_labels.manage_system_users', 'Manage System Users') }}</span>
                     </a>
                 </li>
             @endif

             <li class="sidebar-title ms-3"><i class='bx bx-text'></i>
                 {{ labels('admin_labels.language_settings', 'Language Settings') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/settings/language') || Request::is('admin/settings/language/*') ? 'active' : '' }}"
                     href="{{ route('language.index') }}">
                     <span class="nav-link-text ms-1">{{ labels('admin_labels.language', 'Language') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/settings/manage_language') || Request::is('admin/settings/manage_language/*') ? 'active' : '' }}"
                     href="{{ route('manage_language.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.manage_language', 'Manage Language') }}</span>
                 </a>
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/language/bulk_translation_upload') || Request::is('admin/language/bulk_translation_upload/*') ? 'active' : '' }}"
                     href="{{ route('translation_bulk_upload.index') }}">
                     <span class="nav-link-text ms-1">
                         {!! labels('admin_labels.bulk_upload', 'Multi Language Bulk<br>Import') !!}
                     </span>

                 </a>
             </li>
             <li class="sidebar-title ms-3"><i class='bx bx-text'></i>
                 {{ labels('admin_labels.reports', 'Reports') }}
             </li>
             <li class="nav-item ms-3">
                 <a class="nav-link {{ Request::is('admin/reports/') || Request::is('admin/settings/sales_reports/*') ? 'active' : '' }}"
                     href="{{ route('admin.sales_reports.index') }}">
                     <span
                         class="nav-link-text ms-1">{{ labels('admin_labels.sales_reports', 'Sales Reports') }}</span>
                 </a>
             </li>
         </ul>
     </div>
 </nav>
