@extends('seller/layout')
@section('title')
    {{ labels('admin_labels.home', 'Home') }}
@endsection
@section('content')
    @include('Chatify::layouts.headLinks')
    <section class="main-content">
        <x-seller.breadcrumb :title="labels('admin_labels.dashboard', 'Dashboard')" :subtitle="labels('admin_labels.all_information_about_your_store', 'All Information About your Store')" :breadcrumbs="[]" />
        <section class="dashboard overview-data">

            <!-- ============================================ Info cards ======================================== -->

            <div class="row">
                <div class="col-xxl-12">
                    <div class="row cols-5 d-flex">
                        <div class="col-md-6 col-xl-4 seller_statistics_card">
                            <div class="info-box align-items-center">
                                <div class="primary-icon">
                                    <img src="{{ asset('storage/dashboard_icon/total_sale.svg') }}" class="dashboard-icon"
                                        alt="">
                                </div>
                                <div class="content">
                                    <p class="body-default">{{ labels('admin_labels.total_sales', 'Total Sales') }}
                                    </p>
                                    <h5>{{ $currency . $overallSale }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 seller_statistics_card">
                            <div class="info-box align-items-center">
                                <div class="success-icon">
                                    <img src="{{ asset('storage/dashboard_icon/total_order.svg') }}" class="dashboard-icon"
                                        alt="">
                                </div>
                                <div class="content">
                                    <p class="body-default">
                                        {{ labels('admin_labels.total_orders', 'Total Orders') }}
                                    </p>
                                    <h5>{{ ordersCount('', $seller_id, '', $store_id) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 seller_statistics_card">
                            <div class="info-box align-items-center">
                                <div class="danger-icon">
                                    <img src="{{ asset('storage/dashboard_icon/total_products.svg') }}"
                                        class="dashboard-icon" alt="">
                                </div>
                                <div class="content">
                                    <p class="body-default">
                                        {{ labels('admin_labels.total_products', 'Total Products') }}
                                    </p>
                                    <h5>{{ countProducts($seller_id, $store_id) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 seller_statistics_card">
                            <div class="info-box align-items-center">
                                <div class="warning-icon">
                                    <img src="{{ asset('storage/dashboard_icon/low_stock_product.svg') }}"
                                        class="dashboard-icon" alt="">
                                </div>
                                <div class="content">
                                    <p class="body-default">
                                        {{ labels('admin_labels.low_stock_products', 'Low Stock Products') }}
                                    </p>
                                    <h5>{{ countProductsStockLowStatus($seller_id, $store_id) }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-xl-4 seller_statistics_card">
                            <div class="info-box align-items-center">
                                <div class="info-icon">
                                    <img src="{{ asset('storage/dashboard_icon/total_earning.svg') }}"
                                        class="dashboard-icon" alt="">
                                </div>
                                <div class="content">
                                    <p class="body-default">
                                        {{ labels('admin_labels.total_balance', 'Total Balance') }}
                                    </p>
                                    <h5>{{ $currency . number_format($total_balance, 2) }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ============================================ Statistic Overview  ======================================== -->
        <section class="overview-statistic">


            <div class="row">
                <div class="col-xxl-12">
                    <div class="row cols-5 d-flex">
                        <div class="col col-xxl-7">
                            <div class="chart-card">
                                <div class="align-items-center chart-card-header d-flex justify-content-between">
                                    <h4>{{ labels('admin_labels.overview_statistics', 'Overview Statistic') }}</h4>
                                    <ul class="nav nav-pills nav-pills-rounded chart-action float-right btn-group sale-tabs"
                                        role="group">
                                        <li class="nav-item"><a class="nav-link active" data-toggle="tab"
                                                href="#Daily">{{ labels('admin_labels.today', 'Daily') }}</a></li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="tab"
                                                href="#Weekly">{{ labels('admin_labels.weekly', 'Weekly') }}</a>
                                        </li>
                                        <li class="nav-item"><a class="nav-link" data-toggle="tab"
                                                href="#Monthly">{{ labels('admin_labels.monthly', 'Monthly') }}</a>
                                        </li>
                                    </ul>
                                </div>
                                <div id="chart" class="seller_statistic_chart">
                                </div>
                            </div>
                        </div>
                        <div class="col col-xxl-5">
                            <div class="chart-card">
                                <div class="align-items-center chart-card-header d-flex justify-content-between">
                                    <h4>{{ labels('admin_labels.most_selling_category', 'Most Selling Category') }}
                                    </h4>
                                    <div class="d-flex">
                                        <select class="form-select " id="most_selling_category_filter">
                                            <option value="weekly">Weekly</option>
                                            <option value="monthly">Monthly</option>
                                            <option value="yearly">Yearly</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="chart" class="seller_most_selling_category_chart">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </section>

        <section class="customer-review-statistic">


            <div class="row">
                <div class="col-xxl-12">
                    <div class="row cols-5 d-flex">
                        <div class="col col-xxl-3">
                            <div class="chart-card p-0">
                                <div class="chart-card-header d-flex justify-content-between p-5">
                                    <h4>{{ labels('admin_labels.customer_rating', 'Customer Rating') }}</h4>
                                </div>

                                <div class="mt-5 p-5">
                                    <p class="font-display-4 text-center">
                                        {{ isset($seller_rating[0]->rating) ? $seller_rating[0]->rating : '' }}
                                    </p>
                                    <div class="d-flex justify-content-around justify-content-lg-center">
                                        <div id=""
                                            data-rating="{{ isset($seller_rating[0]->rating) ? $seller_rating[0]->rating : '' }}"
                                            data-rateyo-read-only="true" class="rateYo px-23"></div>
                                    </div>
                                </div>



                                <div id="chart" class="customer_rating_chart">
                                </div>

                            </div>
                        </div>

                        <div class="col col-xxl-6 recent-review">
                            <div class="chart-card">
                                <div class="chart-card-header d-flex justify-content-between">
                                    <h4>{{ labels('admin_labels.recent_reviews', 'Recent Reviews') }}</h4>
                                </div>
                                <!-- Carousel -->
                                <div id="customer_review" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        @php $i = 0; @endphp
                                        @foreach ($latestRatings as $row)
                                            {{-- @dd($row); --}}
                                            <div class="carousel-item {{ $i == 0 ? 'active' : '' }}">
                                                <div class="d-flex mt-5">
                                                    <div class=" col-md-2">
                                                        <img src="
                                                        {{ route('seller.dynamic_image', [
                                                            'url' => getMediaImageUrl($row->product_image),
                                                            'width' => 60,
                                                            'quality' => 90,
                                                        ]) }}
                                                        "
                                                            class="" alt="">
                                                    </div>
                                                    <div class="col-md-10 ms-3 pt-2">
                                                        <div class="justify-content-between">
                                                            <p class="m-0 lead">
                                                                {{ getDynamicTranslation('products', 'name', $row->product_id, $language_code) }}
                                                            </p>
                                                            <h6 class="product-price m-0 mt-1">
                                                                {{ $row->special_price > 0 ? formateCurrency($currency, formatePriceDecimal($row->special_price)) : formateCurrency($currency, formatePriceDecimal($row->price)) }}
                                                            </h6>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="customer-review mt-5">
                                                    <div class="d-flex">
                                                        <div class=" col-md-1">
                                                            <img src="{{ getMediaImageUrl($row->user_image, 'USER_IMG_PATH') }}"
                                                                class="customer-img-box" alt="">
                                                        </div>
                                                        <div class="col-md-9 ms-3">
                                                            <div class="justify-content-between">
                                                                <h6 class="m-0 customer-name">{{ $row->username }}
                                                                </h6>
                                                                <h6 class="m-0 mt-1">
                                                                    <div id="" data-rating="{{ $row->rating }}"
                                                                        data-rateyo-read-only="true"
                                                                        class="rateYo bookrating"></div>
                                                                </h6>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <hr class="border-1 border-dashed border-light">
                                                    <div class="customer-review-text">
                                                        <p>{{ $row->comment }}</p>
                                                    </div>

                                                </div>
                                            </div>
                                            @php $i++; @endphp
                                        @endforeach
                                    </div>

                                    <!-- Left and right controls/icons -->

                                </div>
                            </div>
                        </div>
                        <div class="col col-xxl-3">
                            <div class="chart-card contact-list">
                                <div class="chart-card-header d-flex justify-content-between align-items-center">
                                    <h4>{{ labels('admin_labels.new_messages', 'New Messages') }}</h4>
                                    <div class="d-flex">
                                        <a
                                            href="{{ route('seller.chat.index') }}">{{ labels('admin_labels.view_all', 'View All') }}</a>
                                    </div>
                                </div>
                                <div class="listOfContacts mt-5"></div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

        </section>

        <section class="order-overview-statistic">

            <div class="row">
                <div class="col-xxl-12">
                    <div class="row cols-5 d-flex">
                        <div class="col col-xxl-3">
                            <div class="chart-card">
                                <div class="chart-card-header d-flex justify-content-between">
                                    <h4>{{ labels('admin_labels.orders_overview', 'Orders Overview') }}</h4>
                                </div>
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
                                            <p class="m-0">{{ labels('admin_labels.received', 'Received') }}
                                            </p>
                                            <p class="total-order m-0 mt-1">
                                                {{ ordersCount('received', $seller_id, '', $store_id) }}
                                            </p>
                                        </div>
                                        @php
                                            $currentRecivedOrder = ordersCount('received', $seller_id, '', $store_id);
                                            $maxValue = ordersCount('', $seller_id, '', $store_id);
                                            if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                $recivedOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                            } else {
                                                $recivedOrderWidth = 0;
                                            }
                                        @endphp

                                        <div class="progress" role="progressbar" aria-label="Animated striped example"
                                            aria-valuenow="{{ ordersCount('received', $seller_id, '', $store_id) }}"
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
                                            <p class="m-0">{{ labels('admin_labels.processed', 'Processed') }}
                                            </p>
                                            <p class="total-order m-0 mt-1">
                                                {{ ordersCount('processed', $seller_id, '', $store_id) }}
                                            </p>
                                        </div>
                                        @php
                                            $currentRecivedOrder = ordersCount('processed', $seller_id, '', $store_id);
                                            $maxValue = ordersCount('', $seller_id, '', $store_id);
                                            if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                $processedOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                            } else {
                                                $processedOrderWidth = 0;
                                            }
                                        @endphp

                                        <div class="progress" role="progressbar" aria-label="Animated striped example"
                                            aria-valuenow="{{ ordersCount('processed', $seller_id, '', $store_id) }}"
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
                                                {{ ordersCount('shipped', $seller_id, '', $store_id) }}
                                            </p>
                                        </div>
                                        @php
                                            $currentRecivedOrder = ordersCount('shipped', $seller_id, '', $store_id);
                                            $maxValue = ordersCount('', $seller_id, '', $store_id);
                                            if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                $shippedOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                            } else {
                                                $shippedOrderWidth = 0;
                                            }
                                        @endphp

                                        <div class="progress" role="progressbar" aria-label="Animated striped example"
                                            aria-valuenow="{{ ordersCount('shipped', $seller_id, '', $store_id) }}"
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
                                            <p class="m-0">{{ labels('admin_labels.delivered', 'Delivered') }}
                                            </p>
                                            <p class="total-order m-0 mt-1">
                                                {{ ordersCount('delivered', $seller_id, '', $store_id) }}
                                            </p>
                                        </div>
                                        @php
                                            $currentRecivedOrder = ordersCount('delivered', $seller_id, '', $store_id);
                                            $maxValue = ordersCount('', $seller_id, '', $store_id);
                                            if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                $deliveredOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                            } else {
                                                $deliveredOrderWidth = 0;
                                            }
                                        @endphp

                                        <div class="progress" role="progressbar" aria-label="Animated striped example"
                                            aria-valuenow="{{ ordersCount('delivered', $seller_id, '', $store_id) }}"
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
                                            <p class="m-0">{{ labels('admin_labels.cancelled', 'Cancelled') }}
                                            </p>
                                            <p class="total-order m-0 mt-1">
                                                {{ ordersCount('cancelled', $seller_id, '', $store_id) }}
                                            </p>
                                        </div>
                                        @php
                                            $currentRecivedOrder = ordersCount('cancelled', $seller_id, '', $store_id);
                                            $maxValue = ordersCount('', $seller_id, '', $store_id);
                                            if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                $cancelledOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                            } else {
                                                $cancelledOrderWidth = 0;
                                            }
                                        @endphp

                                        <div class="progress" role="progressbar" aria-label="Animated striped example"
                                            aria-valuenow="{{ ordersCount('received', $seller_id, '', $store_id) }}"
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
                                            <p class="m-0">{{ labels('admin_labels.returned', 'Returned') }}
                                            </p>
                                            <p class="total-order m-0 mt-1">
                                                {{ ordersCount('returned', $seller_id, '', $store_id) }}
                                            </p>
                                        </div>
                                        @php
                                            $currentRecivedOrder = ordersCount('returned', $seller_id, '', $store_id);
                                            $maxValue = ordersCount('', $seller_id, '', $store_id);
                                            if ($currentRecivedOrder > 0 && $maxValue > 0) {
                                                $returnedOrderWidth = ($currentRecivedOrder / $maxValue) * 100;
                                            } else {
                                                $returnedOrderWidth = 0;
                                            }
                                        @endphp

                                        <div class="progress" role="progressbar" aria-label="Animated striped example"
                                            aria-valuenow="{{ ordersCount('received', $seller_id, '', $store_id) }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar-pink progress-bar-striped progress-bar-animated"
                                                style="width: <?= $returnedOrderWidth ?>%"></div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <div class="col col-xxl-9">
                            <div class="chart-card">
                                <div class="chart-card-header d-flex justify-content-between">
                                    <h4>{{ labels('admin_labels.stock_report', 'Stock Report') }}</h4>
                                </div>
                                <div class="table-responsive">
                                    <table id='products_table' data-toggle="table"
                                        data-loading-template="loadingTemplate" data-sticky-header="true"
                                        data-page-size="4" data-url="{{ route('stock_list') }}"
                                        data-click-to-select="true" data-side-pagination="server" data-pagination="true"
                                        data-search="false" data-show-columns="false" data-show-refresh="false"
                                        data-trim-on-search="false" data-sort-name="id" data-sort-order="desc"
                                        data-mobile-responsive="true" data-toolbar="" data-show-export="false"
                                        data-maintain-selected="true" data-export-types='["txt","excel","csv"]'
                                        data-export-options='{"fileName": "products-list","ignoreColumn": ["state"] }'
                                        data-query-params="stock_query_params">
                                        <thead>
                                            <tr>
                                                <th data-field="id" data-sortable="true">
                                                    {{ labels('admin_labels.product_variant_id', 'Product Variant ID') }}
                                                </th>
                                                <th data-field="name" data-disabled="1" data-sortable="false">
                                                    {{ labels('admin_labels.product', 'Product') }}
                                                </th>
                                                <th data-field="price" data-sortable="false">
                                                    {{ labels('admin_labels.price', 'Price') }}
                                                </th>
                                                <th data-field="stock_count" data-sortable="false">
                                                    {{ labels('admin_labels.stock_count', 'Stock Count') }}
                                                </th>
                                                <th data-field="stock_status" data-sortable="false">
                                                    {{ labels('admin_labels.stock_status', 'Stock Status') }}
                                                </th>
                                                <th data-field="category_name" data-sortable="false"
                                                    data-visible="false">
                                                    {{ labels('admin_labels.category', 'Category') }}
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

        </section>
        <section class="order-item-section">

            <div class="row">
                <div class="col-xxl-12">
                    <div class="row cols d-flex">
                        <div class="col col-xxl-12">
                            <div class="chart-card">
                                <div class="row align-items-center d-flex heading mb-5">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h4>{{ labels('admin_labels.recent_orders', 'Recent Orders') }}
                                                </h4>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="pt-0">
                                                    <div id="order_items_table">
                                                        <div class="table-responsive">
                                                            <table id="seller_order_item_table" data-toggle="table"
                                                                data-loading-template="loadingTemplate"
                                                                data-url="{{ route('seller.orders.item_list') }}"
                                                                data-click-to-select="true" data-side-pagination="server"
                                                                data-pagination="true"
                                                                data-page-list="[5, 10, 20, 50, 100, 200]"
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
                                                                            {{ labels('admin_labels.id', 'ID') }}
                                                                        </th>
                                                                        <th data-field="order_item_id"
                                                                            data-sortable='true'>
                                                                            {{ labels('admin_labels.order_item_id', 'Order Item ID') }}
                                                                        </th>
                                                                        <th data-field="order_id" data-sortable='true'>
                                                                            {{ labels('admin_labels.order_id', 'Order ID') }}
                                                                        </th>
                                                                        <th data-field="user_id" data-sortable='true'
                                                                            data-visible="false">
                                                                            {{ labels('admin_labels.user_id', 'User ID') }}
                                                                        </th>
                                                                        <th data-field="seller_id" data-sortable='true'
                                                                            data-visible="false">
                                                                            {{ labels('admin_labels.seller_id', 'Seller ID') }}
                                                                        </th>
                                                                        <th data-field="is_credited" data-sortable='false'
                                                                            data-visible="false">
                                                                            {{ labels('admin_labels.comission', 'Commission') }}
                                                                        </th>
                                                                        <th data-field="quantity" data-sortable='false'
                                                                            data-visible="false">
                                                                            {{ labels('admin_labels.quantity', 'Quantity') }}
                                                                        </th>
                                                                        <th data-field="username" data-sortable='false'>
                                                                            {{ labels('admin_labels.user_name', 'User Name') }}
                                                                        </th>
                                                                        <th data-field="product_name"
                                                                            data-sortable='true'>
                                                                            {{ labels('admin_labels.product_name', 'Product Name') }}
                                                                        </th>
                                                                        <th data-field="mobile" data-sortable='false'
                                                                            data-visible='false'>
                                                                            {{ labels('admin_labels.mobile', 'Mobile') }}
                                                                        </th>
                                                                        <th data-field="sub_total" data-sortable='false'
                                                                            data-visible="true">
                                                                            {{ labels('admin_labels.total', 'Total') }}(<?= $currency ?>)
                                                                        </th>
                                                                        <th data-field="delivery_boy"
                                                                            data-sortable='false' data-visible='false'>
                                                                            {{ labels('admin_labels.deliver_by', 'Deliver By') }}
                                                                        </th>
                                                                        <th data-field="delivery_boy_id"
                                                                            data-sortable='true' data-visible='false'>
                                                                            {{ labels('admin_labels.delivery_boy_id', 'Delivery Boy ID') }}
                                                                        </th>
                                                                        <th data-field="product_variant_id"
                                                                            data-sortable='true' data-visible='false'>
                                                                            {{ labels('admin_labels.product_variant_id', 'Product Variant ID') }}
                                                                        </th>
                                                                        <th data-field="delivery_date"
                                                                            data-sortable='true' data-visible='false'>
                                                                            {{ labels('admin_labels.delivery_date', 'Delivery Date') }}
                                                                        </th>
                                                                        <th data-field="delivery_time"
                                                                            data-sortable='true' data-visible='false'>
                                                                            {{ labels('admin_labels.delivery_time', 'Delivery Time') }}
                                                                        </th>
                                                                        <th data-field="updated_by" data-sortable='false'
                                                                            data-visible="false">
                                                                            {{ labels('admin_labels.updated_by', 'Updated By') }}
                                                                        </th>
                                                                        <th data-field="active_status"
                                                                            data-sortable='true' data-visible='true'>
                                                                            {{ labels('admin_labels.active_status', 'Active Status') }}
                                                                        </th>
                                                                        <th data-field="transaction_status"
                                                                            data-sortable='true' data-visible='false'>
                                                                            {{ labels('admin_labels.transaction_status', 'Transaction Status') }}
                                                                        </th>
                                                                        <th data-field="date_added" data-sortable='true'>
                                                                            {{ labels('admin_labels.order_date', 'Order Date') }}
                                                                        </th>
                                                                        <th data-field="mail_status" data-sortable='false'
                                                                            data-visible='false'>
                                                                            {{ labels('admin_labels.mail_status', 'Mail Status') }}
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
        </section>

        <section class="product-section">

            <div class="row">
                <div class="col-xxl-12">
                    <div class="row cols d-flex">
                        <div class="col col-xxl-6">
                            <div class="chart-card products_chart_card">
                                <div class="align-items-center chart-card-header d-flex justify-content-between">
                                    <h4>{{ labels('admin_labels.top_selling_products', 'Top Selling Products') }}
                                    </h4>
                                    <div class="d-flex">
                                        <select class="form-select" id="top_selling_product_filter">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">
                                                    {{ getDynamicTranslation('categories', 'name', $category->id, $language_code) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="top-selling-products">

                                </div>


                            </div>
                        </div>
                        <div class="col col-xxl-6">
                            <div class="chart-card products_chart_card">
                                <div class="align-items-center chart-card-header d-flex justify-content-between">
                                    <h4>{{ labels('admin_labels.most_popular_products', 'Most Popular Products') }}
                                    </h4>
                                    <div class="d-flex">
                                        <select class="form-select" id="most_popular_product_filter">
                                            <option value="">Select Category</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="most-popular-products">

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        {{-- order tracking modal --}}

        <div class="modal fade" id="edit_order_tracking_modal" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form class="form-horizontal " id="order_tracking_form"
                        action="{{ route('seller.orders.update_order_tracking') }}" method="POST"
                        enctype="multipart/form-data">
                        @method('POST')
                        @csrf
                        <input type="hidden" name="order_id" id="order_id">
                        <input type="hidden" name="order_item_id" id="order_item_id">
                        <input type="hidden" name="seller_id" id="seller_id">
                        <div class="modal-header">
                            <h5 class="modal-title" id="user_name">
                                {{ labels('admin_labels.order_tracking', 'Order Tracking') }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="form-group ">
                                    <label
                                        for="courier_agency">{{ labels('admin_labels.courier_agency', 'Courier Agency') }}</label>
                                    <input type="text" class="form-control" name="courier_agency" id="courier_agency"
                                        placeholder="Courier Agency" />
                                </div>
                                <div class="form-group ">
                                    <label
                                        for="tracking_id">{{ labels('admin_labels.tracking_id', 'Tracking ID') }}</label>
                                    <input type="text" class="form-control" name="tracking_id" id="tracking_id"
                                        placeholder="Tracking Id" />
                                </div>
                                <div class="form-group ">
                                    <label for="url">{{ labels('admin_labels.url', 'URL') }}</label>
                                    <input type="text" class="form-control" name="url" id="url"
                                        placeholder="URL" />
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-label="Close">
                                {{ labels('admin_labels.close', 'Close') }}
                            </button>
                            <button type="submit" class="btn btn-primary"
                                id="save_changes_btn">{{ labels('admin_labels.save', 'Save') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </section>

    <script>
        $(document).ready(function() {
            setTimeout(function() {

                const data = {
                    Monthly: {
                        series: [{
                            name: 'Sales',
                            data: <?php echo json_encode($sales[0]['total_sale']); ?>
                        }, {
                            name: 'Orders',
                            data: <?php echo json_encode($sales[0]['total_orders']); ?>
                        }, {
                            name: 'Revenue',
                            data: <?php echo json_encode($sales[0]['total_revenue']); ?>
                        }],

                        categories: <?php echo json_encode($sales[0]['month_name']); ?>

                    },
                    Weekly: {
                        series: [{
                            name: 'Sales',
                            data: <?php echo json_encode($sales[1]['total_sale']); ?>
                        }, {
                            name: 'Orders',
                            data: <?php echo json_encode($sales[1]['total_orders']); ?>
                        }, {
                            name: 'Revenue',
                            data: <?php echo json_encode($sales[1]['total_revenue']); ?>
                        }],

                        categories: <?php echo json_encode($sales[1]['day']); ?>
                    },
                    Daily: {
                        series: [{
                            name: 'Sales',
                            data: <?php echo json_encode($sales[2]['total_sale']); ?>
                        }, {
                            name: 'Orders',
                            data: <?php echo json_encode($sales[2]['total_orders']); ?>
                        }, {
                            name: 'Revenue',
                            data: <?php echo json_encode($sales[2]['total_revenue']); ?>
                        }],

                        categories: <?php echo json_encode($sales[2]['day']); ?>

                    }
                };

                let chartData = data['Daily'];


                const options = {
                    chart: {
                        type: 'bar',
                        height: 350
                    },
                    plotOptions: {
                        bar: {
                            horizontal: false,
                            columnWidth: '55%',
                            endingShape: 'rounded'
                        },
                    },
                    series: chartData.series,
                    dataLabels: {
                        enabled: false
                    },
                    stroke: {
                        show: true,
                        width: 2,
                        colors: ['transparent']
                    },
                    xaxis: {
                        categories: chartData.categories
                    },
                    yaxis: {
                        labels: {
                            formatter: function(value) {
                                return (value / 1000) +
                                    '00k'; // Divide by 1000 to convert to thousands and then add '00k'
                            }
                        }
                    },
                    fill: {
                        opacity: 1,
                    },
                    tooltip: {
                        y: {
                            formatter: function(val) {
                                var currencySymbol = "<?php echo $currency_symbol; ?>";
                                return currencySymbol + val;
                            }
                        }
                    }
                };


                const chart = new ApexCharts(document.querySelector(".seller_statistic_chart"), options);
                chart.render();

                $(".overview-statistic .chart-action li a").on("click", function() {
                    $(".chart-action li a").removeClass('active');
                    $(this).addClass('active');

                    chartData = data[$(this).attr("href").replace('#', '')];

                    chart.updateOptions({
                        series: chartData.series,
                        xaxis: {
                            categories: chartData.categories
                        }
                    });
                });


                // ====================================== Most selling category chart =======================================


                // monthly data
                var monthlyTotalSold = <?php echo json_encode($topSellingCategories[0]['totalSold']); ?>;
                var monthlyTotalSoldsum = monthlyTotalSold.reduce(
                    (total, value) => total + value,
                    0
                );
                var monthlyTotalSoldpercentages = monthlyTotalSold.map(
                    (value) => (value / monthlyTotalSoldsum) * 100
                );

                // yearly data
                var yearlyTotalSold = <?php echo json_encode($topSellingCategories[1]['totalSold']); ?>;
                var yearlyTotalSoldsum = yearlyTotalSold.reduce(
                    (total, value) => total + value,
                    0
                );
                var yearlyTotalSoldpercentages = yearlyTotalSold.map(
                    (value) => (value / yearlyTotalSoldsum) * 100
                );


                // weekly data

                var weeklyTotalSold = <?php echo json_encode($topSellingCategories[2]['totalSold']); ?>;

                var weeklyTotalSoldsum = weeklyTotalSold.reduce(
                    (total, value) => total + value,
                    0
                );
                var weeklyTotalSoldpercentages = weeklyTotalSold.map(
                    (value) => (value / weeklyTotalSoldsum) * 100
                );

                const data1 = {
                    yearly: {
                        series: yearlyTotalSoldpercentages,
                        categories: <?php echo !$topSellingCategories[1]['categoryNames']->isEmpty() ? json_encode($topSellingCategories[1]['categoryNames']) : '{}'; ?>,
                        originalValues: yearlyTotalSold
                    },
                    monthly: {
                        series: monthlyTotalSoldpercentages,
                        categories: <?php echo !$topSellingCategories[0]['categoryNames']->isEmpty() ? json_encode($topSellingCategories[0]['categoryNames']) : '{}'; ?>,
                        originalValues: monthlyTotalSold
                    },
                    weekly: {
                        series: weeklyTotalSoldpercentages,
                        categories: <?php echo !$topSellingCategories[2]['categoryNames']->isEmpty() ? json_encode($topSellingCategories[2]['categoryNames']) : '{}'; ?>,
                        originalValues: weeklyTotalSold

                    }
                };

                let catChartData = data1['weekly'];


                const catOptions = {
                    series: catChartData.series,
                    chart: {
                        // width: 100,
                        height: 408,
                        type: "donut",
                    },
                    plotOptions: {
                        pie: {
                            startAngle: -90,
                            endAngle: 270,
                        },
                    },

                    dataLabels: {
                        enabled: false,
                    },
                    fill: {
                        type: "gradient",
                    },
                    legend: {
                        position: "bottom",
                        formatter: function(val, opts) {
                            // Use categoryNames instead of opts.w.globals.series
                            return catChartData.categories[
                                opts.seriesIndex
                            ];
                        },
                    },
                    tooltip: {
                        y: {
                            formatter: function(val, opts) {
                                return (
                                    catChartData.categories[
                                        opts.seriesIndex
                                    ] +
                                    " - " +
                                    catChartData.originalValues[opts.seriesIndex]
                                );
                            },
                        },
                    },

                    responsive: [{
                        breakpoint: 480,
                        options: {
                            chart: {
                                width: 200,
                            },
                            legend: {
                                position: "bottom",
                            },
                        },
                    }, ],
                };

                const catChart = new ApexCharts(document.querySelector(
                    ".seller_most_selling_category_chart"), catOptions);
                catChart.render();

                $("#most_selling_category_filter").on("change", function() {

                    catChartData = data1[$(this).val()];
                    console.log('bd')
                    console.log(catChart);
                    catChart.updateOptions({
                        series: catChartData.series,
                        legend: {
                            position: "bottom",
                            formatter: function(val, opts) {
                                // Use categoryNames instead of opts.w.globals.series
                                return catChartData.categories[
                                    opts.seriesIndex
                                ];
                            },
                        },
                        tooltip: {
                            y: {
                                formatter: function(val, opts) {
                                    return (
                                        catChartData.categories[
                                            opts.seriesIndex
                                        ] +
                                        " - " +
                                        catChartData.originalValues[opts
                                            .seriesIndex]
                                    );
                                },
                            },
                        },
                    });
                });

                // ====================================== Customer Rating chrt ===========================================


                var ratingOptions = {
                    series: [{
                        name: 'Rating',
                        data: <?php echo json_encode($store_rating['total_ratings']); ?>,
                    }, ],
                    colors: ["#FEB019"],
                    chart: {
                        height: 208,
                        type: "area",
                        zoom: {
                            enabled: false,
                        },
                        toolbar: {
                            show: true,
                            tools: {
                                download: false,
                            },
                        },
                    },
                    dataLabels: {
                        enabled: false,
                    },
                    stroke: {
                        curve: "smooth",
                    },
                    grid: {
                        show: false,
                    },
                    yaxis: {
                        labels: {
                            show: false,
                        },
                        lines: {
                            show: false,
                        },
                    },
                    xaxis: {
                        labels: {
                            show: false,
                        },
                        axisBorder: {
                            show: false,
                        },
                        axisTicks: {
                            show: false,
                        },
                        categories: <?php echo json_encode($store_rating['month_name']); ?>,
                    },

                    tooltip: {
                        custom: function({
                            series,
                            seriesIndex,
                            dataPointIndex,
                            w
                        }) {
                            var monthAbbreviation = <?php echo json_encode($store_rating['month_name']); ?>[dataPointIndex];
                            var ratingValue = w.globals.series[seriesIndex][dataPointIndex];
                            return '<div><h6 class="text-center p-1"> ' + monthAbbreviation +
                                ' </h6> <hr><span class="p-2"> Rating: ' + ratingValue +
                                ' </span> </div>'

                        },
                    },
                };

                var ratingChart = new ApexCharts(
                    document.querySelector(".customer_rating_chart"),
                    ratingOptions
                );
                ratingChart.render();

            }, 200);
        });
    </script>
    @include('Chatify::layouts.modals')
    @include('Chatify::layouts.footerLinks')
@endsection
