@extends('admin/layout')
@section('title')
    {{ labels('admin_labels.home', 'Home') }}
@endsection
@section('content')
    @include('Chatify::layouts.headLinks')
    <div class="d-flex row align-items-center">
        <div class="col-md-6 col-xl-6 page-info-title">
            <h3>{{ labels('admin_labels.dashboard', 'Dashboard') }}
            </h3>
            <p class="sub_title">
                {{ labels('admin_labels.drive_success_for_you_and_your_sellers', 'Drive Success For You and Your Sellers') }}
            </p>
        </div>
    </div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-md-12 col-xxl-4">
                <div class="col-md-12 col-xxl-12">
                    <a href="{{ route('sellers.index') }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="mb-3 home_page_statitics">
                                            {{ labels('admin_labels.sellers', 'Sellers') }}
                                        </h5>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end">
                                        <div class="store_total total_box">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                viewBox="0 0 36 36" fill="none">
                                                <g clip-path="url(#clip0_2699_38715)">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M12.1044 0H16.8005V9.3697C16.8005 10.3281 16.4234 11.239 15.7458 11.9166C15.0682 12.5942 14.1573 12.9714 13.1989 12.9714C12.2404 12.9714 11.3296 12.5942 10.6519 11.9166C10.0047 11.2694 9.63577 10.4177 9.59963 9.50393L12.1044 0ZM27.5176 21.815C27.9307 21.815 28.2676 22.152 28.2676 22.565V36H32.1876C32.5091 36 32.8138 35.8739 33.0408 35.647C33.2667 35.4213 33.3894 35.1178 33.3894 34.7989V15.2858C33.0547 15.3361 32.7362 15.3669 32.3966 15.3669C30.8006 15.3669 29.2845 14.7386 28.1561 13.6102C27.9515 13.4058 27.7743 13.195 27.5972 12.9676C27.4201 13.1951 27.2428 13.4058 27.0383 13.6102C25.9099 14.7387 24.3937 15.3669 22.7977 15.3669C21.2017 15.3669 19.6855 14.7386 18.5572 13.6102C18.3526 13.4058 18.1754 13.195 17.9982 12.9676C17.8211 13.1951 17.6439 13.4058 17.4393 13.6102C16.311 14.7387 14.7948 15.3669 13.1988 15.3669C11.6027 15.3669 10.0866 14.7387 8.95824 13.6102C8.7537 13.4058 8.57644 13.195 8.39933 12.9676C8.22221 13.1951 8.04495 13.4058 7.84041 13.6102C6.71204 14.7387 5.19589 15.3669 3.59987 15.3669C3.2604 15.3669 2.94181 15.3361 2.60712 15.2857V34.798C2.60712 35.1176 2.73474 35.421 2.96037 35.6467C3.18608 35.8724 3.48948 35.9999 3.80905 35.9999H20.3907V22.565C20.3907 22.152 20.7276 21.815 21.1407 21.815H27.5176ZM28.7527 9.06567L26.3635 0H30.9656L35.9888 9.63365C35.9198 10.5045 35.5623 11.2978 34.9435 11.9166C34.2658 12.5942 33.355 12.9714 32.3966 12.9714C31.4381 12.9714 30.5273 12.5942 29.8496 11.9166C29.1721 11.239 28.7922 10.3285 28.7921 9.3697C28.7922 9.26712 28.7788 9.16488 28.7527 9.06567ZM25.3447 11.9166C24.667 12.5942 23.7562 12.9714 22.7978 12.9714C21.8393 12.9714 20.9285 12.5942 20.2508 11.9166C19.5732 11.239 19.1961 10.3281 19.1961 9.3697V0H23.8923L26.3969 9.50386C26.3608 10.4177 25.9919 11.2694 25.3447 11.9166ZM7.24395 9.06567L9.63317 0H5.03101L0.0078125 9.63365C0.0767891 10.5045 0.434258 11.2978 1.05308 11.9166C1.73075 12.5942 2.64158 12.9714 3.60001 12.9714C4.55844 12.9714 5.46926 12.5942 6.14694 11.9166C6.82454 11.239 7.20162 10.3281 7.20169 9.3697C7.20169 9.26712 7.21787 9.16474 7.24395 9.06567ZM7.84084 22.565C7.84084 22.152 8.17777 21.815 8.59079 21.815H12.9182C13.3313 21.815 13.6682 22.152 13.6682 22.565V26.8925C13.6682 27.3056 13.3313 27.6424 12.9182 27.6424H8.59079C8.1777 27.6424 7.84084 27.3056 7.84084 26.8925V22.565Z"
                                                        fill="white" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_2699_38715">
                                                        <rect width="36" height="36" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <h5>
                                    {{ isset($total_seller) && !empty($total_seller) ? $total_seller : 0 }}
                                </h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-12 col-xxl-12 mt-md-2 mt-xxl-2">
                    <a href="{{ route('admin.orders.index') }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="mb-3 home_page_statitics">
                                            {{ labels('admin_labels.orders', 'Orders') }}
                                        </h5>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end">
                                        <div class="order_total total_box">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="36"
                                                viewBox="0 0 28 36" fill="none">
                                                <path
                                                    d="M24.3673 0H21.1993V2.4V3.6C21.1993 5.57995 19.5793 7.2 17.5993 7.2H10.3993C8.41924 7.2 6.79927 5.57995 6.79927 3.6V2.4V0H3.63125C1.65129 0 0.03125 1.61997 0.03125 3.6V32.4C0.03125 34.38 1.65129 36 3.63125 36H24.3673C26.3472 36 27.9673 34.38 27.9673 32.4V3.6C27.9673 1.61997 26.3472 0 24.3673 0ZM14.1553 11.4959H21.7393C22.3993 11.4959 22.9393 12.036 22.9393 12.6959C22.9393 13.356 22.3993 13.896 21.7393 13.896H14.1553C13.4953 13.896 12.9553 13.356 12.9553 12.6959C12.9553 12.036 13.4953 11.4959 14.1553 11.4959ZM7.96323 26.4C8.89926 26.4 9.66728 27.168 9.66728 28.104C9.66728 29.04 8.89926 29.808 7.96323 29.808C7.01526 29.808 6.25925 29.04 6.25925 28.104C6.25926 27.168 7.01526 26.4 7.96323 26.4ZM6.25926 20.4C6.25926 19.464 7.01527 18.6959 7.96324 18.6959C8.89927 18.6959 9.66729 19.464 9.66729 20.4C9.66729 21.336 8.89927 22.104 7.96324 22.104C7.01526 22.104 6.25926 21.336 6.25926 20.4ZM14.1553 19.2H21.7393C22.3993 19.2 22.9393 19.74 22.9393 20.4C22.9393 21.06 22.3993 21.6 21.7393 21.6H14.1553C13.4953 21.6 12.9553 21.06 12.9553 20.4C12.9553 19.74 13.4953 19.2 14.1553 19.2ZM14.1553 26.904H21.7393C22.3993 26.904 22.9393 27.444 22.9393 28.104C22.9393 28.764 22.3993 29.304 21.7393 29.304H14.1553C13.4953 29.304 12.9553 28.764 12.9553 28.104C12.9553 27.444 13.4953 26.904 14.1553 26.904ZM7.96323 10.992C8.89926 10.992 9.66728 11.76 9.66728 12.6959C9.66728 13.632 8.89926 14.4 7.96323 14.4C7.01526 14.4 6.25925 13.632 6.25925 12.6959C6.25926 11.76 7.01526 10.992 7.96323 10.992Z"
                                                    fill="white" />
                                                <path
                                                    d="M17.5992 4.8C18.2592 4.8 18.7992 4.25999 18.7992 3.6V2.4V0H9.19922V2.4V3.6C9.19922 4.25998 9.73923 4.8 10.3992 4.8H17.5992Z"
                                                    fill="white" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <h5>
                                    {{ isset($order_counter) && !empty($order_counter['total_orders']) ? $order_counter['total_orders'] : 0 }}
                                </h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-12 col-xxl-12 mt-md-2 mt-xxl-2">
                    <a href="{{ route('admin.products.manage_product') }}">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5 class="mb-3 home_page_statitics">
                                            {{ labels('admin_labels.products', 'Products') }}
                                        </h5>
                                    </div>
                                    <div class="col-md-6 d-flex justify-content-end">
                                        <div class="products_total total_box">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36"
                                                viewBox="0 0 36 36" fill="none">
                                                <path
                                                    d="M13.0366 23.8588C13.0349 24.4476 12.8002 25.0118 12.3838 25.4282C11.9675 25.8446 11.4033 26.0793 10.8144 26.081H6.44424C5.85503 26.0805 5.29009 25.8463 4.87345 25.4296C4.45682 25.013 4.22255 24.448 4.2221 23.8588V18.7403H2.22215C1.63294 18.7407 1.06799 18.975 0.651358 19.3916C0.234723 19.8083 0.000458281 20.3733 2.00743e-06 20.9625V33.7773C-0.000390633 34.0692 0.056819 34.3584 0.168353 34.6282C0.279888 34.8979 0.443555 35.1431 0.649979 35.3495C0.856403 35.5559 1.10153 35.7196 1.37131 35.8311C1.64109 35.9427 1.93022 35.9999 2.22215 35.9995H15.0365C15.6257 35.999 16.1907 35.7648 16.6073 35.3481C17.024 34.9315 17.2582 34.3665 17.2587 33.7773V18.7402H13.0366V23.8588Z"
                                                    fill="white" />
                                                <path
                                                    d="M6.44384 24.5995H10.814C11.0103 24.5989 11.1984 24.5207 11.3372 24.3819C11.4759 24.2431 11.5542 24.055 11.5547 23.8587V18.7402H5.70313V23.8587C5.70279 23.9561 5.72172 24.0526 5.75882 24.1426C5.79593 24.2326 5.85048 24.3144 5.91933 24.3833C5.98818 24.4521 6.06996 24.5067 6.15998 24.5438C6.25001 24.5809 6.34648 24.5998 6.44384 24.5995Z"
                                                    fill="white" />
                                                <path
                                                    d="M33.7768 18.7402H31.7768V23.8587C31.7764 24.448 31.5421 25.0129 31.1255 25.4296C30.7088 25.8462 30.1439 26.0805 29.5547 26.081H25.1845C24.5957 26.0792 24.0315 25.8446 23.6151 25.4282C23.1987 25.0118 22.9641 24.4476 22.9623 23.8587V18.7402H18.7402V33.7773C18.7407 34.3665 18.975 34.9315 19.3916 35.3481C19.8082 35.7647 20.3732 35.999 20.9624 35.9995H33.7768C34.0687 35.9999 34.3578 35.9427 34.6276 35.8311C34.8974 35.7196 35.1425 35.5559 35.3489 35.3495C35.5554 35.143 35.719 34.8979 35.8306 34.6281C35.9421 34.3583 35.9993 34.0692 35.9989 33.7773V20.9625C35.9985 20.3732 35.7642 19.8083 35.3476 19.3916C34.9309 18.975 34.366 18.7407 33.7768 18.7402Z"
                                                    fill="white" />
                                                <path
                                                    d="M24.4414 23.8587C24.442 24.055 24.5202 24.2431 24.659 24.3819C24.7978 24.5207 24.9859 24.5989 25.1821 24.5995H29.5523C29.6497 24.5998 29.7462 24.5809 29.8362 24.5438C29.9262 24.5067 30.008 24.4521 30.0769 24.3833C30.1457 24.3144 30.2003 24.2326 30.2374 24.1426C30.2745 24.0526 30.2934 23.9561 30.2931 23.8587V18.7402H24.4414V23.8587Z"
                                                    fill="white" />
                                                <path
                                                    d="M26.6298 2.22222C26.6302 1.93028 26.573 1.64114 26.4615 1.37135C26.3499 1.10156 26.1863 0.85643 25.9798 0.649999C25.7734 0.443569 25.5283 0.279897 25.2585 0.168359C24.9887 0.0568208 24.6996 -0.000390646 24.4077 2.00749e-06H22.4077V5.11108C22.4073 5.70031 22.173 6.26527 21.7564 6.68191C21.3397 7.09856 20.7748 7.33283 20.1856 7.3333H15.8153C15.2261 7.33284 14.6612 7.09856 14.2445 6.68192C13.8279 6.26527 13.5936 5.70031 13.5932 5.11108V2.00749e-06H11.5932C11.3013 -0.000390646 11.0122 0.0568208 10.7424 0.168359C10.4726 0.279897 10.2275 0.443569 10.0211 0.649999C9.81465 0.85643 9.65098 1.10156 9.53945 1.37135C9.42791 1.64114 9.3707 1.93028 9.3711 2.22222V17.2593H26.6298V2.22222Z"
                                                    fill="white" />
                                                <path
                                                    d="M15.8149 5.85182H20.1852C20.3815 5.85124 20.5695 5.77301 20.7083 5.63422C20.8471 5.49543 20.9253 5.30736 20.9259 5.11108V0H15.0742V5.11108C15.0748 5.30736 15.153 5.49543 15.2918 5.63422C15.4306 5.77301 15.6187 5.85124 15.8149 5.85182Z"
                                                    fill="white" />
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                                <h5>

                                    {{ isset($total_products) && !empty($total_products) ? $total_products : 0 }}
                                </h5>
                            </div>
                        </div>
                    </a>
                </div>
                <div class="col-md-12 col-xxl-12 mt-md-2 mt-xxl-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3 home_page_statitics">
                                        {{ labels('admin_labels.total_earnings', 'Total Earnings') }}
                                    </h5>
                                </div>
                                <div class="col-md-6 d-flex justify-content-end">
                                    <div class="total_earnings total_box">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="31" height="36"
                                            viewBox="0 0 31 36" fill="none">
                                            <path
                                                d="M19.8125 8.89719H23.3568L23.2959 6.73803H20.2998L23.1156 0.91854L19.93 0.651756L17.1323 2.7964L18.0653 0.371148L14.7989 0L13.7276 3.0307L11.968 0.68424L7.12646 0.51629L10.3299 6.73942L7.43817 6.6468V8.8965H10.2249C4.28168 12.3751 0 21.4672 0 26.3792C0 32.5366 6.72421 36 15.018 36C23.3118 36 30.0361 32.5366 30.0361 26.3792C30.0367 21.4672 25.7558 12.3758 19.8125 8.89719ZM16.2738 29.3332V31.4647H14.2246V29.4797C12.8243 29.4189 11.4648 29.0394 10.6707 28.5812L11.2976 26.1373C12.1753 26.619 13.4097 27.0565 14.7678 27.0565C15.9587 27.0565 16.7749 26.5969 16.7749 25.762C16.7749 24.9271 16.1066 24.4626 14.5598 23.9422C12.3218 23.1902 10.7972 22.1438 10.7972 20.118C10.7972 18.2782 12.0931 16.8351 14.3303 16.3976V14.4112H16.3796V16.251C17.7792 16.3146 18.7212 16.6049 19.4103 16.9401L18.8042 19.3025C18.2609 19.0737 17.2988 18.5913 15.7942 18.5913C14.4354 18.5913 13.9958 19.176 13.9958 19.7607C13.9958 20.4519 14.7277 20.89 16.5047 21.5591C18.9908 22.4396 19.9957 23.5862 19.9957 25.4696C19.9964 27.3274 18.6791 28.9164 16.2752 29.3338L16.2738 29.3332Z"
                                                fill="white" />
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <h5>
                                {{ isset($total_earnings) && !empty($total_earnings) ? $currency . ' ' . $total_earnings : '' }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xxl-8 mt-md-2 mt-xxl-0">
                <!-- ============================================ Statistic Overview  ======================================== -->
                <div class="col-xxl-12 overview-statistic">
                    <div class="row">
                        <div class="col-xxl-12">
                            <div class="card revenue_card">
                                <div class="card-body">

                                    <div class="chart-card-header d-flex justify-content-between mb-8">
                                        <h4>{{ labels('admin_labels.revenue_analytics', 'Revenue Analytics') }}</h4>
                                        <ul class="nav nav-pills nav-pills-rounded chart-action float-right btn-group sale-tabs"
                                            role="group">
                                            <li class="nav-item"><a class="nav-link active" data-toggle="tab"
                                                    href="#Daily">{{ labels('admin_labels.today', 'Today') }}</a></li>
                                            <li class="nav-item"><a class="nav-link" data-toggle="tab"
                                                    href="#Weekly">{{ labels('admin_labels.weekly', 'Weekly') }}</a>
                                            </li>
                                            <li class="nav-item"><a class="nav-link" data-toggle="tab"
                                                    href="#Monthly">{{ labels('admin_labels.monthly', 'Monthly') }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div id="chart" class="admin_statistic_chart">
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12 col-xxl-12">
        <div class="row">
            <div class="col-md-12 col-xxl-4">

                {{-- chat and ticket --}}

                <div class="col-xxl-12 mt-4 overview-statistic">
                    <div class="row">
                        <div class="col col-xxl-12">
                            <div class="chart-card contact-list">
                                <div class="chart-card-header d-flex justify-content-between align-items-center">
                                    <h4>{{ labels('admin_labels.new_messages', 'New Messages') }}</h4>
                                    <div class="d-flex">
                                        <a class="view_all"
                                            href="{{ route('admin.chat.index') }}">{{ labels('admin_labels.view_all', 'View All') }}</a>
                                    </div>
                                </div>
                                <div class="listOfContacts mt-5"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xxl-4">
                {{-- orders , categories and sellers --}}
                <div class="col-12 p-0">
                    <div class="row g-3">
                        <div class="col">
                            <div class="card card mt-md-4 mt-xxl-4">
                                <div class="card-body">
                                    <h4>{{ labels('admin_labels.orders_overview', 'Orders Overview') }}</h4>
                                    <div class="d-flex mt-4">
                                        <div class="progress-primary-icon col-md-3 me-3 mx-3">
                                            <svg width="22" height="24" viewBox="0 0 22 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" class="order-overview-icon">
                                                <g id="Dashboard_Overview" clip-path="url(#clip0_2198_6507)">
                                                    <path id="Icon" fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M8.61924 5.66662L10.5052 7.81188C10.741 8.08045 11.1543 8.11045 11.4278 7.87938C11.4527 7.85875 11.4757 7.83579 11.4967 7.81188L13.3827 5.66662C13.617 5.39665 13.5831 4.99169 13.3076 4.76203C13.035 4.5347 12.6265 4.5647 12.3907 4.82858L11.6555 5.66662V1.96292C11.6584 1.60858 11.3681 1.31892 11.0065 1.31611C10.6449 1.3133 10.3493 1.5978 10.3464 1.95214C10.3464 1.95589 10.3464 1.95964 10.3464 1.96292V5.66616L9.61076 4.82905C9.37639 4.55908 8.96314 4.52674 8.68811 4.7564C8.41261 4.98607 8.3796 5.39103 8.61397 5.66053C8.61589 5.66241 8.61732 5.66475 8.61924 5.66662ZM12.9446 20.8484C12.6174 20.9989 12.4763 21.3804 12.6293 21.7015C12.7829 22.0221 13.1722 22.1603 13.4999 22.0103C13.5032 22.0089 13.5061 22.0075 13.5094 22.0057L16.1061 20.7884C16.4324 20.6356 16.5701 20.2527 16.4142 19.9335C16.2583 19.6143 15.8675 19.4789 15.5417 19.6317L12.9441 20.8489L12.9446 20.8484ZM9.78487 12.8987L8.65846 14.2528L0.5 10.5233L1.62115 9.16691L9.78534 12.8987H9.78487ZM5.55665 7.33943C7.05757 10.2857 10.7118 11.4814 13.7184 10.0106C14.9109 9.42704 15.8751 8.47604 16.4625 7.30381L16.5577 7.11351L19.0257 8.26981L10.8931 11.9876L3.01589 8.29652L5.46146 7.15101L5.55712 7.33943H5.55665ZM7.62532 1.37001C9.48975 -0.456996 12.5126 -0.456996 14.3766 1.37001C16.241 3.19702 16.241 6.15923 14.3766 7.98577C12.5122 9.81278 9.48927 9.81278 7.62532 7.98577C5.76088 6.16954 5.75179 3.21624 7.60523 1.38923C7.61192 1.38267 7.61862 1.37611 7.62532 1.36954V1.37001ZM21.5 10.424L20.3999 9.10223L12.2993 12.8969L13.3435 14.153L21.5 10.4244V10.424ZM19.9455 20.1379L11.7043 23.9995V14.2106L12.6509 15.3496C12.8403 15.5774 13.1636 15.6496 13.4353 15.5258L19.946 12.5495V20.1379H19.9455ZM10.3947 24V14.1966L9.352 15.4508C9.16211 15.6781 8.83926 15.7508 8.56758 15.6266L2.15255 12.6944V20.1383L10.3947 24Z"
                                                        fill="white" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_2198_6507">
                                                        <rect width="21" height="24" fill="#4285F4"
                                                            transform="translate(0.5)" />
                                                    </clipPath>
                                                </defs>
                                            </svg>

                                        </div>
                                        <div class="col">
                                            <div class="d-flex justify-content-between">
                                                <p class="m-0">{{ labels('admin_labels.received', 'Received') }}</p>
                                                <p class="total-order m-0 mt-1">
                                                    {{ ordersCount('received', '', '', $store_id) }}
                                                </p>
                                            </div>
                                            @php
                                                $currentRecivedOrder = ordersCount('received', '', '', $store_id);
                                                $maxValue = ordersCount('', '', '', $store_id);
                                                if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                    $recivedOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                                } else {
                                                    $recivedOrderWidth = 0;
                                                }
                                            @endphp

                                            <div class="progress" role="progressbar"
                                                aria-label="Animated striped example"
                                                aria-valuenow="{{ ordersCount('received', '', '', $store_id) }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar-primary progress-bar-striped progress-bar-animated"
                                                    style="width: <?= $recivedOrderWidth ?>%"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex mt-5">
                                        <div class="progress-info-icon me-3 mx-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" class="order-overview-icon">
                                                <g id="Processed" clip-path="url(#clip0_2198_6509)">
                                                    <g id="Icon">
                                                        <g id="Group">
                                                            <path id="Vector"
                                                                d="M12.2119 1.57266C15.1495 1.57266 18.2072 3.00094 20.2306 5.23078H19.62V6.76922H21.8142C22.1749 6.76922 22.4893 6.53906 22.5788 6.19125L23.1159 4.11328L21.5869 3.72609L21.456 4.23797C19.1365 1.65609 15.6052 0 12.2119 0C8.58506 0 5.10511 1.71281 2.88965 4.57031L4.13861 5.53172C6.08949 3.01547 9.03229 1.57266 12.2119 1.57266Z"
                                                                fill="white" />
                                                            <path id="Vector_2"
                                                                d="M24 11.7825C24 10.5314 23.8046 9.29951 23.4195 8.12061L21.9187 8.60764C22.2525 9.62904 22.422 10.6969 22.422 11.7825C22.422 15.5592 19.9443 19.1962 16.3452 20.9512L16.5241 20.43L15.0304 19.9219L14.2983 22.058C14.167 22.4414 14.3496 22.8614 14.7196 23.0283L16.8753 24L17.5259 22.5675L17.0537 22.3547C21.0548 20.3897 24 16.3115 24 11.7825Z"
                                                                fill="white" />
                                                            <path id="Vector_3"
                                                                d="M12.2123 21.9919C6.58282 21.9919 2.00267 17.4117 2.00267 11.782C2.00267 11.055 2.23617 10.17 2.52052 9.32484L2.74649 9.75843L4.14657 9.03281L3.09721 7.0214C2.91314 6.6689 2.49415 6.50859 2.12083 6.64781L0 7.43812L0.552687 8.91093L1.05877 8.7225C0.717458 9.72093 0.424166 10.8127 0.424166 11.782C0.424166 18.1861 5.76697 23.5641 12.2123 23.5641C12.3738 23.5641 12.5376 23.5608 12.6996 23.5542L12.6351 21.983C12.4948 21.9886 12.3526 21.9914 12.2123 21.9914V21.9919Z"
                                                                fill="white" />
                                                        </g>
                                                        <g id="Group_2">
                                                            <path id="Vector_4"
                                                                d="M5.61084 15.8199L11.5336 18.66L11.5346 12.6216L5.61084 9.37689V15.8199Z"
                                                                fill="white" />
                                                            <path id="Vector_5"
                                                                d="M13.5337 12.6211L13.537 18.66L19.465 15.8199V9.37689L13.5337 12.6216V12.6211Z"
                                                                fill="white" />
                                                            <path id="Vector_6"
                                                                d="M6.77295 7.78446L12.5168 10.9307L18.2805 7.78446L12.5168 5.03149L6.77295 7.78446Z"
                                                                fill="white" />
                                                        </g>
                                                    </g>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_2198_6509">
                                                        <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div class="col">
                                            <div class="d-flex justify-content-between">
                                                <p class="m-0">{{ labels('admin_labels.processed', 'Processed') }}</p>
                                                <p class="total-order m-0 mt-1">
                                                    {{ ordersCount('processed', '', '', $store_id) }}
                                                </p>
                                            </div>

                                            @php
                                                $currentProcessedOrder = ordersCount('processed', '', '', $store_id);
                                                $maxValue = ordersCount('', '', '', $store_id);

                                                if ($currentProcessedOrder > 0 && $maxValue > 0) {
                                                    $processedOrderWidth = ($currentProcessedOrder / $maxValue) * 100;
                                                } else {
                                                    $processedOrderWidth = 0;
                                                }
                                            @endphp

                                            <div class="progress" role="progressbar"
                                                aria-label="Animated striped example"
                                                aria-valuenow="{{ ordersCount('processed', '', '', $store_id) }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar-info progress-bar-striped progress-bar-animated"
                                                    style="width: <?= $processedOrderWidth ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-5">
                                        <div class="progress-warning-icon me-3 mx-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" class="order-overview-icon">
                                                <g id="Shipped">
                                                    <g id="Iocn">
                                                        <path id="Vector"
                                                            d="M18.375 13.2417C18.375 12.8531 18.69 12.5386 19.0781 12.5386C19.4662 12.5386 19.7812 12.8531 19.7812 13.2417V14.4183H23.6883L20.6133 9.45421C20.4853 9.24749 20.2589 9.1214 20.0156 9.1214H16.9688V14.4183H18.375V13.2417Z"
                                                            fill="white" />
                                                        <path id="Vector_2"
                                                            d="M0 15.8245V19.5745C0 19.9626 0.315 20.2776 0.703125 20.2776H1.01297C1.24219 18.5841 2.69766 17.2744 4.45312 17.2744C6.20859 17.2744 7.66406 18.5841 7.89328 20.2776H15.8723C16.1016 18.5841 17.557 17.2744 19.3125 17.2744C21.068 17.2744 22.5234 18.5841 22.7527 20.2776H23.2969C23.685 20.2776 24 19.9626 24 19.5745V15.8245H0Z"
                                                            fill="white" />
                                                        <path id="Vector_3"
                                                            d="M15.5625 6.63702C15.5625 6.2489 15.2475 5.9339 14.8594 5.9339H13.9786C14.0128 9.37124 11.2186 12.1908 7.78125 12.1889C6.12234 12.1889 4.55156 11.5355 3.38063 10.3523C2.22188 9.18327 1.58344 7.63452 1.58344 5.99109C1.58344 5.97187 1.58344 5.95312 1.58344 5.9339H0.703125C0.315 5.9339 0 6.2489 0 6.63702V14.4183H15.5625V6.63702Z"
                                                            fill="white" />
                                                        <path id="Vector_4"
                                                            d="M7.7349 10.7817C10.3932 10.8112 12.5743 8.64843 12.5729 5.99109C12.5747 3.33328 10.3932 1.17093 7.7349 1.2C5.11412 1.22484 2.98975 3.36422 2.98975 5.99109C2.98975 8.61797 5.11412 10.7578 7.7349 10.7817ZM5.63865 5.3189C5.91334 5.04422 6.35865 5.04422 6.63334 5.3189L7.34959 6.03562L8.92975 4.45547C9.20443 4.18078 9.64975 4.18078 9.92443 4.45547C10.1987 4.72968 10.1987 5.175 9.92443 5.44968L7.84693 7.52718C7.71615 7.65797 7.53615 7.73343 7.34959 7.73297C7.16303 7.73297 6.98443 7.6589 6.85271 7.52718L5.63865 6.31312C5.36396 6.0389 5.36396 5.59359 5.63865 5.3189Z"
                                                            fill="white" />
                                                        <path id="Vector_5"
                                                            d="M4.45299 22.8122C5.59389 22.8122 6.51877 21.8873 6.51877 20.7464C6.51877 19.6055 5.59389 18.6806 4.45299 18.6806C3.31209 18.6806 2.38721 19.6055 2.38721 20.7464C2.38721 21.8873 3.31209 22.8122 4.45299 22.8122Z"
                                                            fill="white" />
                                                        <path id="Vector_6"
                                                            d="M19.3124 22.8122C20.4533 22.8122 21.3781 21.8873 21.3781 20.7464C21.3781 19.6055 20.4533 18.6806 19.3124 18.6806C18.1715 18.6806 17.2466 19.6055 17.2466 20.7464C17.2466 21.8873 18.1715 22.8122 19.3124 22.8122Z"
                                                            fill="white" />
                                                    </g>
                                                </g>
                                            </svg>
                                        </div>
                                        <div class="col">
                                            <div class="d-flex justify-content-between">
                                                <p class="m-0">{{ labels('admin_labels.shipped', 'Shipped') }}</p>
                                                <p class="total-order m-0 mt-1">
                                                    {{ ordersCount('shipped', '', '', $store_id) }}
                                                </p>
                                            </div>
                                            @php
                                                $currentShippedOrder = ordersCount('shipped', '', '', $store_id);
                                                $maxValue = ordersCount('', '', '', $store_id);

                                                if ($currentShippedOrder > 0 && $maxValue > 0) {
                                                    $shippedOrderWidth = ($currentShippedOrder / $maxValue) * 100;
                                                } else {
                                                    $shippedOrderWidth = 0;
                                                }
                                            @endphp

                                            <div class="progress" role="progressbar"
                                                aria-label="Animated striped example"
                                                aria-valuenow="{{ ordersCount('shipped', '', '', $store_id) }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar-warning progress-bar-striped progress-bar-animated"
                                                    style="width: <?= $shippedOrderWidth ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-5">
                                        <div class="progress-success-icon me-3 mx-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" class="order-overview-icon">
                                                <g id="Delivered" clip-path="url(#clip0_2198_6563)">
                                                    <path id="Vector"
                                                        d="M23.4178 10.5864L21.7807 8.94973C21.3922 8.56122 21.0741 7.79358 21.0741 7.24272V4.92807C21.0741 3.82729 20.1746 2.9278 19.0738 2.92686H16.7582C16.2083 2.92686 15.4397 2.60779 15.0512 2.21975L13.4146 0.58312C12.6371 -0.194373 11.3636 -0.194373 10.5861 0.58312L8.9495 2.22069C8.56052 2.6092 7.791 2.92733 7.24249 2.92733H4.92784C3.82846 2.92733 2.92756 3.82682 2.92756 4.9276V7.24225C2.92756 7.79077 2.60944 8.56075 2.22092 8.94927L0.583824 10.5859C-0.194608 11.3634 -0.194608 12.6368 0.583824 13.4157L2.22092 15.0524C2.6099 15.4409 2.92756 16.2109 2.92756 16.7594V19.074C2.92756 20.1739 3.82799 21.0743 4.92784 21.0743H7.24249C7.79241 21.0743 8.56099 21.3924 8.9495 21.781L10.5861 23.4181C11.3636 24.1951 12.6371 24.1951 13.4146 23.4181L15.0512 21.781C15.4402 21.3924 16.2083 21.0743 16.7582 21.0743H19.0738C20.1746 21.0743 21.0741 20.1739 21.0741 19.074V16.7594C21.0741 16.2085 21.3927 15.4409 21.7807 15.0524L23.4178 13.4157C24.1948 12.6368 24.1948 11.3634 23.4178 10.5859V10.5864ZM10.413 16.5009L6 12.0874L7.41422 10.6736L10.413 13.6724L16.5865 7.50079L18.0002 8.91454L10.4125 16.5009H10.413Z"
                                                        fill="white" />
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_2198_6563">
                                                        <rect width="24" height="24.0005" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div class="col">
                                            <div class="d-flex justify-content-between">
                                                <p class="m-0">{{ labels('admin_labels.delivered', 'Delivered') }}</p>
                                                <p class="total-order m-0 mt-1">
                                                    {{ ordersCount('delivered', '', '', $store_id) }}
                                                </p>
                                            </div>
                                            @php
                                                $currentDeliveredOrder = ordersCount('delivered', '', '', $store_id);
                                                $maxValue = ordersCount('', '', '', $store_id);

                                                if ($currentDeliveredOrder > 0 && $maxValue > 0) {
                                                    $deliveredOrderWidth = ($currentDeliveredOrder / $maxValue) * 100;
                                                } else {
                                                    $deliveredOrderWidth = 0;
                                                }
                                            @endphp

                                            <div class="progress" role="progressbar"
                                                aria-label="Animated striped example"
                                                aria-valuenow="{{ ordersCount('delivered', '', '', $store_id) }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar-success progress-bar-striped progress-bar-animated"
                                                    style="width: <?= $deliveredOrderWidth ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-5">
                                        <div class="progress-danger-icon me-3 mx-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" class="order-overview-icon">
                                                <g id="Cancelled" clip-path="url(#clip0_2198_6566)">
                                                    <g id="Icon">
                                                        <g id="Group">
                                                            <path id="Vector"
                                                                d="M17.7173 0.316406C17.4427 0.0417187 16.9973 0.0417187 16.7231 0.316406L16.26 0.779062C14.9358 0.276563 13.5005 0 12 0C5.37281 0 0 5.37234 0 12C0 13.5005 0.276562 14.9358 0.779531 16.26L0.316406 16.7231C0.0417187 16.9978 0.0417187 17.4431 0.316406 17.7173C0.591094 17.9916 1.03594 17.992 1.31062 17.7173L17.7173 1.31109C17.992 1.03641 17.992 0.591562 17.7173 0.316875V0.316406ZM3.28125 12C3.28125 7.1925 7.1925 3.28125 12 3.28125C12.5494 3.28125 13.087 3.33281 13.6088 3.43078L12.3436 4.69594C12.2297 4.69078 12.1153 4.6875 12 4.6875C7.96781 4.6875 4.6875 7.96781 4.6875 12C4.6875 12.1153 4.69031 12.2297 4.69594 12.3436L3.43078 13.6087C3.33281 13.087 3.28125 12.5494 3.28125 12Z"
                                                                fill="white" />
                                                            <path id="Vector_2"
                                                                d="M23.2205 7.74002L23.6836 7.27689C23.9583 7.00221 23.9583 6.55736 23.6836 6.28268C23.4089 6.00799 22.9636 6.00799 22.6894 6.28268L6.28268 22.6889C6.00799 22.9636 6.00799 23.4089 6.28268 23.6831C6.55736 23.9574 7.00221 23.9578 7.27689 23.6831L7.74002 23.22C9.06377 23.723 10.4996 23.9995 12 23.9995C18.6272 23.9995 24 18.6272 24 11.9996C24 10.4991 23.7235 9.06377 23.2205 7.73955V7.74002ZM12 20.7188C11.4506 20.7188 10.913 20.6672 10.3913 20.5692L11.6564 19.3041C11.7703 19.3092 11.8847 19.3125 12 19.3125C16.0322 19.3125 19.3125 16.0322 19.3125 12C19.3125 11.8847 19.3097 11.7703 19.3041 11.6564L20.5692 10.3913C20.6672 10.913 20.7188 11.4506 20.7188 12C20.7188 16.8075 16.8075 20.7188 12 20.7188Z"
                                                                fill="white" />
                                                        </g>
                                                        <g id="Group_2">
                                                            <path id="Vector_3"
                                                                d="M5.65273 19.3935C5.45914 19.4104 5.31617 19.581 5.33305 19.7746C5.33633 19.8126 5.3368 19.8506 5.33492 19.8876C5.32414 20.077 5.24117 20.2504 5.08883 20.4023C4.88961 20.6015 4.62477 20.7112 4.34305 20.7112C4.06133 20.7112 3.79648 20.6015 3.59727 20.4023C3.18617 19.9912 3.18617 19.3218 3.59727 18.9107C3.7468 18.7612 3.93477 18.6609 4.14102 18.621C4.3318 18.584 4.45602 18.3998 4.41945 18.209C4.38289 18.0182 4.1982 17.894 4.00742 17.9306C3.66289 17.9971 3.34883 18.164 3.09992 18.4134C2.41461 19.0987 2.41461 20.2139 3.09992 20.8992C3.4318 21.231 3.87336 21.4139 4.34305 21.4139C4.81273 21.4139 5.25383 21.231 5.58617 20.8992C5.85992 20.6254 6.01602 20.2893 6.03711 19.9279C6.04133 19.8571 6.03992 19.785 6.03383 19.7128C6.01648 19.5196 5.84633 19.3762 5.65273 19.3931V19.3935Z"
                                                                fill="white" />
                                                            <path id="Vector_4"
                                                                d="M12.7973 12.2489C12.6037 12.2658 12.4607 12.4364 12.4776 12.63C12.4809 12.668 12.4813 12.7059 12.4795 12.743C12.4687 12.9323 12.3857 13.1058 12.2334 13.2576C12.0342 13.4569 11.7693 13.5666 11.4876 13.5666C11.2059 13.5666 10.941 13.4569 10.7418 13.2576C10.3307 12.8466 10.3307 12.1772 10.7418 11.7661C10.8913 11.6166 11.0793 11.5167 11.286 11.4764C11.4768 11.4394 11.6015 11.2551 11.5645 11.0644C11.5274 10.8736 11.3437 10.7489 11.1524 10.7859C10.8079 10.8525 10.4938 11.0194 10.2449 11.2687C9.55963 11.9541 9.55963 13.0692 10.2449 13.7545C10.5768 14.0864 11.0184 14.2692 11.4881 14.2692C11.9578 14.2692 12.3988 14.0864 12.7312 13.7545C13.0049 13.4808 13.161 13.1447 13.1817 12.7833C13.1859 12.7125 13.1845 12.6403 13.1784 12.5681C13.1615 12.375 12.9904 12.2316 12.7973 12.2489Z"
                                                                fill="white" />
                                                            <path id="Vector_5"
                                                                d="M14.5239 10.9678L13.9347 11.557L13.4373 11.0597L13.9651 10.5319C14.1025 10.3945 14.1025 10.1719 13.9651 10.0345C13.8278 9.89719 13.6051 9.89719 13.4678 10.0345L12.94 10.5623L12.4426 10.065L13.0319 9.47578C13.1692 9.33844 13.1692 9.11578 13.0319 8.97844C12.8945 8.84109 12.6719 8.84109 12.5345 8.97844L11.6964 9.81656C11.6303 9.88265 11.5933 9.97172 11.5933 10.065C11.5933 10.1583 11.6303 10.2478 11.6964 10.3134L13.6853 12.3023C13.7514 12.3684 13.8404 12.4055 13.9337 12.4055C14.027 12.4055 14.1165 12.3684 14.1822 12.3023L15.0203 11.4642C15.1576 11.3269 15.1576 11.1042 15.0203 10.9669C14.8829 10.8295 14.6603 10.8295 14.5229 10.9669L14.5239 10.9678Z"
                                                                fill="white" />
                                                            <path id="Vector_6"
                                                                d="M19.33 6.16168L18.7408 6.7509L18.2435 6.25355L18.7713 5.72621C18.9086 5.58887 18.9086 5.36621 18.7713 5.22887C18.6339 5.09152 18.4113 5.09152 18.2739 5.22887L17.7461 5.75668L17.2488 5.25933L17.838 4.67011C17.9754 4.53277 17.9754 4.31011 17.838 4.17277C17.7007 4.03543 17.478 4.03543 17.3407 4.17277L16.5025 5.01043C16.4364 5.07652 16.3994 5.16558 16.3994 5.25886C16.3994 5.35215 16.4364 5.44168 16.5025 5.5073L17.4968 6.50152C17.4968 6.50152 17.4968 6.50152 17.4968 6.50199L18.491 7.49621C18.5594 7.56465 18.6499 7.59933 18.7394 7.59933C18.8289 7.59933 18.9194 7.56511 18.9879 7.49621L19.826 6.65808C19.9633 6.52074 19.9633 6.29808 19.826 6.16074C19.6886 6.0234 19.466 6.0234 19.3286 6.16074L19.33 6.16168Z"
                                                                fill="white" />
                                                            <path id="Vector_7"
                                                                d="M8.48868 17.325L5.78774 16.1039C5.78446 16.1025 5.78118 16.1011 5.77837 16.0997C5.62227 16.0345 5.44415 16.0697 5.32462 16.1897C5.20509 16.3097 5.1704 16.4878 5.23556 16.6439C5.23649 16.6467 5.2379 16.649 5.23884 16.6519L6.4529 19.3594C6.51149 19.4897 6.63946 19.567 6.77399 19.567C6.82227 19.567 6.87103 19.5572 6.91743 19.5361C7.09462 19.4569 7.17384 19.2487 7.09415 19.0715L6.89071 18.6183L7.74759 17.7619L8.19852 17.9658C8.37524 18.0459 8.58384 17.9672 8.66353 17.7905C8.74368 17.6133 8.66493 17.4051 8.48821 17.3255L8.48868 17.325ZM6.58321 17.9316L6.19134 17.0578L7.06274 17.4515L6.58321 17.9311V17.9316Z"
                                                                fill="white" />
                                                            <path id="Vector_8"
                                                                d="M8.84791 13.1592C8.70916 13.0233 8.4865 13.0256 8.35056 13.1639C8.21463 13.3027 8.21697 13.5253 8.35525 13.6613L9.57869 14.8613L7.29306 14.4431C7.14166 14.4159 6.98978 14.4895 6.91853 14.6255C6.84728 14.7619 6.8726 14.9288 6.98135 15.0375L8.9665 17.0227C9.03494 17.0911 9.12494 17.1258 9.21494 17.1258C9.30494 17.1258 9.39494 17.0916 9.46338 17.0227C9.60072 16.8853 9.60072 16.6627 9.46338 16.5253L8.27556 15.338L10.452 15.7364C10.631 15.7688 10.7974 15.6919 10.8767 15.5405C10.9578 15.3858 10.9235 15.1992 10.7871 15.0628L8.84697 13.1597L8.84791 13.1592Z"
                                                                fill="white" />
                                                            <path id="Vector_9"
                                                                d="M16.1075 9.38109C15.9715 9.51843 15.8 9.6914 15.6626 9.82921L13.9226 8.08921C13.7853 7.95187 13.5626 7.95187 13.4253 8.08921C13.2879 8.22656 13.2879 8.44921 13.4253 8.58656L15.4123 10.5736C15.4803 10.6416 15.5703 10.6767 15.6612 10.6767C15.732 10.6767 15.8028 10.6556 15.8637 10.6125C15.8946 10.5909 15.9148 10.5764 16.6071 9.87609C16.7436 9.73781 16.7426 9.51562 16.6043 9.37874C16.4661 9.24187 16.2434 9.24328 16.107 9.38156L16.1075 9.38109Z"
                                                                fill="white" />
                                                            <path id="Vector_10"
                                                                d="M17.6319 7.85625C17.4795 8.01047 17.3159 8.175 17.187 8.30437L15.447 6.56437C15.3097 6.42703 15.087 6.42703 14.9497 6.56437C14.8123 6.70172 14.8123 6.92437 14.9497 7.06172L16.9367 9.04875C17.0047 9.11672 17.0947 9.15187 17.1856 9.15187C17.2564 9.15187 17.3272 9.13078 17.3886 9.08719C17.4191 9.06562 17.4392 9.05109 18.1316 8.35125C18.268 8.21297 18.267 7.99078 18.1288 7.8539C17.9909 7.7175 17.7683 7.71844 17.6314 7.85672L17.6319 7.85625Z"
                                                                fill="white" />
                                                            <path id="Vector_11"
                                                                d="M21.0983 2.90153C20.3549 2.1581 19.4324 2.08122 18.8038 2.71028L18.2403 3.27372C18.1742 3.33982 18.1372 3.42935 18.1372 3.52263C18.1372 3.61591 18.1747 3.70544 18.2408 3.77153L18.2417 3.77247C18.2417 3.77247 18.2417 3.77247 18.2422 3.77294L20.1964 5.72716C20.2133 5.74403 20.2311 5.75857 20.2499 5.77122C20.3136 5.827 20.3956 5.85841 20.481 5.85841H20.4824C20.5756 5.85841 20.6647 5.82044 20.7308 5.75435C20.7477 5.73747 21.148 5.33435 21.3055 5.17075C21.8924 4.56325 21.8052 3.60888 21.0978 2.90153H21.0983ZM20.8002 4.68232C20.7271 4.75778 20.5972 4.88997 20.4796 5.00903C20.2438 4.77466 19.9021 4.43482 19.731 4.26419C19.588 4.12122 19.2313 3.76544 18.9866 3.52169L19.3006 3.20763C19.7094 2.79888 20.2405 3.03841 20.601 3.39888C20.9652 3.7631 21.1681 4.30122 20.8002 4.68278V4.68232Z"
                                                                fill="white" />
                                                        </g>
                                                    </g>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_2198_6566">
                                                        <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div class="col">
                                            <div class="d-flex justify-content-between">
                                                <p class="m-0">{{ labels('admin_labels.cancelled', 'Cancelled') }}</p>
                                                <p class="total-order m-0 mt-1">
                                                    {{ ordersCount('cancelled', '', '', $store_id) }}
                                                </p>
                                            </div>
                                            @php
                                                $currentRecivedOrder = ordersCount('cancelled', '', '', $store_id);
                                                $maxValue = ordersCount('', '', '', $store_id);

                                                if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                    $cancelledOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                                } else {
                                                    $cancelledOrderWidth = 0;
                                                }
                                            @endphp

                                            <div class="progress" role="progressbar"
                                                aria-label="Animated striped example"
                                                aria-valuenow="{{ ordersCount('received', '', '', $store_id) }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar-danger progress-bar-striped progress-bar-animated"
                                                    style="width: <?= $cancelledOrderWidth ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex mt-5">
                                        <div class="progress-pink-icon me-3 mx-3">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                xmlns="http://www.w3.org/2000/svg" class="order-overview-icon">
                                                <g id="Returned" clip-path="url(#clip0_2198_6613)">
                                                    <g id="Icon">
                                                        <path id="Vector" fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M8.50378 19.8625H2.52669C1.85647 19.8625 1.21394 19.6012 0.740393 19.1349C0.26637 18.67 0 18.0385 0 17.3793V2.48276C0 1.82407 0.265892 1.19259 0.740393 0.727188C1.21394 0.261318 1.85647 0 2.52669 0H6.73705V4.63617C6.73705 5.17007 6.99912 5.67159 7.44116 5.98264C7.8832 6.29322 8.45079 6.37485 8.96539 6.20268L10.0524 5.83767L11.2682 6.2266C11.7809 6.39127 12.3427 6.30354 12.7791 5.99249C13.2154 5.68145 13.4736 5.18321 13.4736 4.65353V0H17.684C18.3542 0 18.9967 0.261318 19.4703 0.727657C19.9443 1.19259 20.2107 1.82407 20.2107 2.48323V8.93033C19.1715 8.50903 18.0348 8.27633 16.8424 8.27633C11.9623 8.27633 8.00064 12.1703 8.00064 16.966C8.00064 17.9812 8.17822 18.9566 8.50426 19.8625H8.50378ZM8.42119 0H11.7895V4.65353L10.3068 4.17828C10.134 4.12292 9.94733 4.12433 9.77452 4.1825L8.42119 4.63617V0Z"
                                                            fill="white" />
                                                        <path id="Vector_2" fill-rule="evenodd" clip-rule="evenodd"
                                                            d="M16.8422 9.93103C12.892 9.93103 9.68457 13.0833 9.68457 16.9655C9.68457 20.8478 12.892 24 16.8422 24C20.7924 24 23.9998 20.8478 23.9998 16.9655C23.9998 13.0833 20.7924 9.93103 16.8422 9.93103ZM15.5065 16.5522H17.6843C18.7335 16.5522 19.3135 17.1349 19.3111 17.7903C19.3092 18.448 18.7249 19.0349 17.6843 19.0349H16.0001C15.5352 19.0349 15.158 19.4056 15.158 19.8625C15.158 20.3195 15.5352 20.6901 16.0001 20.6901H17.6843C19.9217 20.6901 20.9905 19.2095 20.9953 17.7969C21.001 16.3824 19.9441 14.897 17.6843 14.897H15.5065L15.7533 14.6545C16.0817 14.3317 16.0817 13.8072 15.7533 13.4844C15.4249 13.1616 14.8912 13.1616 14.5628 13.4844L12.8786 15.1396C12.5492 15.4623 12.5492 15.9869 12.8786 16.3096L14.5628 17.9648C14.8912 18.2876 15.4249 18.2876 15.7533 17.9648C16.0817 17.642 16.0817 17.1175 15.7533 16.7947L15.5065 16.5522Z"
                                                            fill="white" />
                                                    </g>
                                                </g>
                                                <defs>
                                                    <clipPath id="clip0_2198_6613">
                                                        <rect width="24" height="24" fill="white" />
                                                    </clipPath>
                                                </defs>
                                            </svg>
                                        </div>
                                        <div class="col">
                                            <div class="d-flex justify-content-between">
                                                <p class="m-0">{{ labels('admin_labels.returned', 'Returned') }}</p>
                                                <p class="total-order m-0 mt-1">
                                                    {{ ordersCount('returned', '', '', $store_id) }}
                                                </p>
                                            </div>
                                            @php
                                                $currentRecivedOrder = ordersCount('returned', '', '', $store_id);
                                                $maxValue = ordersCount('', '', '', $store_id);

                                                if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                    $returnedOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                                } else {
                                                    $returnedOrderWidth = 0;
                                                }
                                            @endphp

                                            <div class="progress" role="progressbar"
                                                aria-label="Animated striped example"
                                                aria-valuenow="{{ ordersCount('received', '', '', $store_id) }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                <div class="progress-bar-pink progress-bar-striped progress-bar-animated"
                                                    style="width: <?= $returnedOrderWidth ?>%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 col-xxl-4 mt-xxl-4">
                <div class="col-xxl-12">
                    <div class="card">
                        <div class="card-body customer_statistics_card">
                            <div class="chart-card-header d-flex justify-content-between">
                                <h4>{{ labels('admin_labels.customer_statistics', 'Customer Statistics') }}
                                </h4>
                                <div class="d-flex">
                                    <a class="view_all"
                                        href="{{ route('admin.customers') }}">{{ labels('admin_labels.view_all', 'View All') }}</a>
                                </div>
                            </div>
                            <div class="d-flex justify-content-center">
                                <div id="chart" class="customer_statistics">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="row">
            <div class="col-xxl-8 mb-6 mt-4">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <h4>{{ labels('admin_labels.recent_tickets', 'Recent Tickets') }}</h4>
                            <a class="view_all"
                                href="{{ route('admin.tickets.viewTickets') }}">{{ labels('admin_labels.view_all', 'View All') }}</a>
                        </div>
                        <div class="table-responsive">
                            <table class='table' id="admin_ticket_table" data-toggle="table"
                                data-loading-template="loadingTemplate"
                                data-url="{{ route('admin.tickets.getTicketList') }}" data-click-to-select="true"
                                data-side-pagination="server" data-pagination="true"
                                data-page-list="[5, 10, 20, 50, 100, 200]" data-search="false" data-show-columns="false"
                                data-show-refresh="false" data-trim-on-search="false" data-sort-name="id"
                                data-sort-order="desc" data-mobile-responsive="true" data-toolbar=""
                                data-show-export="false" data-maintain-selected="true"
                                data-export-types='["txt","excel"]' data-query-params="queryParams">
                                <thead>
                                    <tr>
                                        <th data-field="ticket_type" data-sortable="false">
                                            {{ labels('admin_labels.ticket_type', 'Ticket Type') }}
                                        </th>

                                        <th data-field="subject" data-sortable="false">
                                            {{ labels('admin_labels.subject', 'Subject') }}
                                        </th>
                                        <th data-field="status" data-sortable="false">
                                            {{ labels('admin_labels.status', 'Status') }}
                                        </th>
                                        <th data-field="date_created" data-sortable="false">
                                            {{ labels('admin_labels.date_created', 'Date Created') }}
                                        </th>

                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xxl-4 col-md-12 mt-4">
                <div class="col-md-12 col-xxl-12 mt-md-2 mt-xxl-0">
                    <div class="card">
                        <div class="card-body">
                            <h4>{{ labels('admin_labels.top_sellers', 'Top Sellers') }}</h4>

                            @forelse ($top_sellers as $item)
                                <div class="d-flex mt-4">
                                    <div class="col-md-2 col-4">
                                        <div class="d-flex">
                                            <img src="{{ route('admin.dynamic_image', [
                                                'url' => getMediaImageUrl($item->logo, 'SELLER_IMG_PATH'),
                                                'width' => 60,
                                                'quality' => 90,
                                            ]) }} "
                                                alt="{{ $item->store_name }}" class="img-fluid">
                                        </div>
                                    </div>
                                    <div class="col-md-2 col-4 d-flex justify-content-start">
                                        <div class="d-flex flex-column">
                                            <p class="lead mb-0">{{ $item->seller_name }}</p>
                                            <p class="data_total_font">{{ $item->store_name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-8 col-4 d-flex justify-content-end">
                                        <div>
                                            <p class="data_sales_font mb-2">{{ $item->total_sales }}</p>
                                            <div class="d-flex align-items-center">
                                                <p class="data_total_font m-0">{{ $item->total_commission }} Sales
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr />
                            @empty
                                <div class="d-flex justify-content-center">
                                    <p>No data found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- recent orders --}}
    <div class="col-xxl-12 p-0 mt-4">
        <div class="row cols d-flex">
            <div class="col col-xxl-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center d-flex heading mb-5">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4>{{ labels('admin_labels.recent_orders', 'Recent Orders') }} </h4>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="pt-0">
                                            <div id="order_items_table">
                                                <div class="table-responsive">
                                                    <table id="seller_order_item_table" data-toggle="table"
                                                        data-loading-template="loadingTemplate"
                                                        data-url="{{ route('admin.orders.item_list') }}"
                                                        data-click-to-select="true" data-side-pagination="server"
                                                        data-pagination="true" data-page-list="[5, 10, 20, 50, 100, 200]"
                                                        data-search="false" data-show-columns="false"
                                                        data-show-refresh="false" data-trim-on-search="false"
                                                        data-sort-name="o.id" data-sort-order="desc"
                                                        data-mobile-responsive="true" data-toolbar=""
                                                        data-show-export="false" data-maintain-selected="true"
                                                        data-query-params="queryParams">
                                                        <thead>
                                                            <tr>
                                                                <th data-field="id" data-sortable='true'
                                                                    data-footer-formatter="totalFormatter">
                                                                    {{ labels('admin_labels.order_id', 'Order ID') }}
                                                                </th>
                                                                <th data-field="username" data-sortable='false'>
                                                                    {{ labels('admin_labels.user_name', 'User Name') }}
                                                                </th>
                                                                <th data-field="payment_method" data-visible="true">
                                                                    {{ labels('admin_labels.payment_method', 'Payment Method') }}
                                                                </th>
                                                                <th data-field="active_status" data-sortable='false'
                                                                    data-visible='true'>
                                                                    {{ labels('admin_labels.active_status', 'Active Status') }}
                                                                </th>
                                                                <th data-field="date_added">
                                                                    {{ labels('admin_labels.order_date', 'Order Date') }}
                                                                </th>
                                                                <th data-field="sub_total" data-sortable='false'
                                                                    data-visible="true">
                                                                    {{ labels('admin_labels.total', 'Total') }}(<?= $currency ?>)
                                                                </th>
                                                                <th data-field="operate" data-sortable='false'>
                                                                    {{ labels('admin_labels.action', 'Action') }}
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- modal for order tracking -->
    <div class="modal fade" id="edit_order_tracking_modal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog  modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="user_name">
                        {{ labels('admin_labels.order_tracking', 'Order Tracking') }}
                    </h5>
                    <div class="d-flex justify-content-end"><button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button></div>
                </div>
                <form class="form-horizontal " id="order_tracking_form"
                    action="{{ route('admin.orders.update_order_tracking') }}" method="POST"
                    enctype="multipart/form-data">
                    @method('POST')
                    @csrf
                    <input type="hidden" name="order_id" id="order_id">
                    <input type="hidden" name="order_item_id" id="order_item_id">
                    <input type="hidden" name="seller_id" id="seller_id">
                    <input type="hidden" id="edit_zipcode_id" name="edit_zipcode_id">
                    <div class="modal-body">
                        <div class="form-group ">
                            <label class="mb-2 mt-2"
                                for="courier_agency">{{ labels('admin_labels.courier_agency', 'Courier Agency') }}</label>
                            <input type="text" class="form-control" name="courier_agency" id="courier_agency"
                                placeholder="Courier Agency" />
                        </div>
                        <div class="form-group">
                            <label class="mb-2 mt-2"
                                for="tracking_id">{{ labels('admin_labels.tracking_id', 'Tracking ID') }}</label>
                            <input type="text" class="form-control" name="tracking_id" id="tracking_id"
                                placeholder="Tracking Id" />
                        </div>
                        <div class="form-group ">
                            <label class="mb-2 mt-2" for="url">{{ labels('admin_labels.url', 'URL') }}</label>
                            <input type="text" class="form-control" name="url" id="url"
                                placeholder="URL" />
                        </div>
                    </div>
                    <div class="modal-footer">
                        <div class="d-flex justify-content-end">
                            <button type="reset"
                                class="btn mx-2 reset_button">{{ labels('admin_labels.reset', 'Reset') }}</button>
                            <button type="submit" class="btn btn-primary"
                                id="submit_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                // Admin Statistic Chart
                var adminSalesData = {{ json_encode($sales[2]['total_sales']) }};

                const data = {
                    Monthly: {
                        series: [{
                            name: 'Sales',
                            data: <?php echo json_encode($sales[0]['total_sales']); ?>
                        }, {
                            name: 'Revenue',
                            data: <?php echo json_encode($sales[0]['total_revenue']); ?>
                        }, {
                            name: 'Comission',
                            data: <?php echo json_encode($sales[0]['total_commission']); ?>
                        }],
                        categories: <?php echo json_encode($sales[0]['month_name']); ?>
                    },
                    Weekly: {
                        series: [{
                            name: 'Sales',
                            data: <?php echo json_encode($sales[1]['total_sales']); ?>
                        }, {
                            name: 'Revenue',
                            data: <?php echo json_encode($sales[1]['total_revenue']); ?>
                        }, {
                            name: 'Comission',
                            data: <?php echo json_encode($sales[1]['total_commission']); ?>
                        }],
                        categories: <?php echo json_encode($sales[1]['day']); ?>
                    },

                    Daily: {
                        series: [{
                            name: 'Sales',
                            data: <?php echo json_encode($sales[2]['total_sales']); ?>
                        }, {
                            name: 'Revenue',
                            data: <?php echo json_encode($sales[2]['total_revenue']); ?>
                        }, {
                            name: 'Comission',
                            data: <?php echo json_encode($sales[2]['total_commission']); ?>
                        }],
                        categories: <?php echo json_encode($sales[2]['day']); ?>
                    }
                };
                let chartData = data['Daily'];
                const adminChartData = {
                    series: adminSalesData.series,
                    categories: adminSalesData.categories,
                };

                var adminOptions = {
                    series: chartData.series,
                    colors: ['#F9AC38', '#0077E5', '#12B77C'],
                    chart: {
                        height: 350,
                        type: 'area',
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        curve: 'smooth',
                    },
                    xaxis: {
                        categories: chartData.categories,
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return (value * 20) + 'k';
                            },
                        },
                    },
                    fill: {
                        opacity: 1,
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                return "<?= $currency ?> " + val;
                            },
                        },
                    },
                };

                var adminChart = new ApexCharts(document.querySelector(".admin_statistic_chart"),
                    adminOptions);
                adminChart.render();

                $(".overview-statistic .chart-action li a").on("click", function() {
                    $(".chart-action li a").removeClass('active');
                    $(this).addClass('active');

                    var chartType = $(this).attr("href").replace('#', '');
                    chartData = data[$(this).attr("href").replace('#', '')];
                    adminChart.updateOptions({
                        series: chartData.series,
                        xaxis: {
                            categories: chartData.categories,
                        },
                    });
                    return false;
                });

                // Customer Statistics Chart
                var user_data = <?php echo json_encode($user_counter); ?>;
                var customerOptions = {
                    series: [user_data.current_month_users, user_data.active_user, user_data
                        .inactive_user
                    ],
                    labels: ['New', 'Active', 'Deactive'],
                    chart: {
                        width: 380,
                        height: 500,
                        type: 'donut',
                    },
                    fill: {
                        type: 'gradient',
                    },
                    legend: {
                        position: "bottom",
                    },
                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200,
                            },
                            legend: {
                                position: 'bottom',
                            },
                        },
                    }],
                    colors: ['#0077E5', '#12B77C', '#F9AC38'],
                    dataLabels: {
                        enabled: false,
                    },
                    plotOptions: {
                        pie: {
                            expandOnClick: false,
                            donut: {
                                labels: {
                                    show: true,
                                    name: {
                                        show: true,
                                        fontSize: '22px',
                                        offsetY: -5,
                                    },
                                    value: {
                                        show: true,
                                        fontSize: '16px',
                                        color: undefined,
                                        offsetY: +5,
                                        formatter: function(val) {
                                            return val;
                                        },
                                    },
                                    total: {
                                        show: true,
                                        label: 'Total',
                                        color: '#ffa500',
                                        formatter: function(w) {
                                            return w.globals.seriesTotals.reduce((a, b) => {
                                                return user_data.total_users;
                                            }, 0);
                                        },
                                    },
                                },
                            },
                        },
                    },
                };

                var customerChart = new ApexCharts(document.querySelector(".customer_statistics"),
                    customerOptions);
                customerChart.render();
            }, 200);
        });
    </script>

    @include('Chatify::layouts.modals')
    @include('Chatify::layouts.footerLinks')
@endsection
