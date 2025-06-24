@php
    // dd(count($products_listing) < 1);
    // dd(count($Attributes) >= 1 && count($products_listing) < 1);
    $showFilter = true;
    if (($routeType == 'category' || $routeType == 'section') && count($products_listing) < 1) {
        $showFilter = false;
    }
@endphp
<div id="page-content">
    <div class="template-product">
        <div class="page-header text-center">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-12 col-sm-12 col-md-12 col-lg-12">
                        <!--Breadcrumbs-->
                        <div class="breadcrumbs"><a wire:navigate href="{{ customUrl('/') }}"
                                title="Back to the home page">{{ labels('front_messages.home', 'Home') }}</a>
                            @if (isset($bread_crumb['right_breadcrumb']) && !empty($bread_crumb['right_breadcrumb']))
                                @foreach ($bread_crumb['right_breadcrumb'] as $right_breadcrumb)
                                    <span class="main-title fw-bold"><ion-icon class="align-text-top icon"
                                            name="chevron-forward-outline"></ion-icon>{!! $right_breadcrumb !!}</span>
                                @endforeach
                            @endif
                            <span class="main-title fw-bold"><ion-icon class="align-text-top icon"
                                    name="chevron-forward-outline"></ion-icon>{!! $bread_crumb['page_main_bread_crumb'] ?? 'Products' !!}</span>
                        </div>
                        <!--End Breadcrumbs-->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="container-fluid">
        @if ($bySearch != null)
            <div class="search-results-form mb-4 pb-4 mb-lg-5 pb-lg-5 border-bottom">
                <div class="row">
                    <div class="col-12 col-sm-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">
                        <div class="page-title text-center mb-3">
                            <h2 class="mb-2">{{ labels('front_messages.results_for', 'Results for') }}
                                "{{ $bySearch }}"</h2>
                            <p>{{ count($products_listing) }}
                                {{ labels('front_messages.results_found_for', 'results found for') }}
                                "{{ $bySearch }}"</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <div class="row">
            <!--Sidebar-->
            @if ($showFilter == true)
                <div class="col-12 col-sm-12 col-md-12 col-lg-3 sidebar sidebar-bg filterbar">
                    <div class="closeFilter d-block d-lg-none"><ion-icon class="icon" name="close-outline"></ion-icon>
                    </div>
                    <div class="sidebar-tags sidebar-sticky clearfix">
                        <!--Filter By-->
                        @if (count($products_listing) >= 1)
                            <div class="sidebar-widget filterBox filter-widget border-0 p-0">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <h2>{{ labels('front_messages.filter_by', 'Filter By') }}</h2>
                                    <p class="cursor-pointer toggle-filter-tab show_tabs m-0">
                                        {{ labels('front_messages.close_all_tabs', 'Close All Tabs') }}</p>
                                    <p class="cursor-pointer toggle-filter-tab close_tabs d-none m-0">
                                        {{ labels('front_messages.show_all_tabs', 'Show All Tabs') }}</p>
                                </div>
                            </div>
                        @endif
                        <!--End Filter By-->
                        <!--Price Filter-->
                        @if ($min_max_price['max_price'] >= 1)
                            <div class="sidebar-widget filterBox filter-widget">
                                <div class="widget-title">
                                    <h2>{{ labels('front_messages.price', 'Price') }}</h2>
                                </div>
                                <div class="widget-content price-filter filterDD">
                                    <div id="slider-range" class="mt-2"></div>
                                    <div class="row">
                                        <div class="col-6"><input id="amount" type="text" disabled /></div>
                                        <div class="col-6 text-right"><button
                                                class="btn btn-sm price-filter-btn">{{ labels('front_messages.filter', 'filter') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        {{-- @dd($Attributes) --}}
                        <!--End Price Filter-->
                        @if (count($products_listing) >= 1 && count($Attributes) >= 1)
                            @foreach ($Attributes as $Attribute)
                                <div class="sidebar-widget filterBox filter-widget brand-filter">
                                    <div class="widget-title">
                                        <h2>{{ $Attribute['attribute_name'] }}</h2>
                                    </div>
                                    <div class="widget-content filterDD">
                                        <ul class="clearfix">
                                            <ul class="sidebar-categories scrollspy morelist clearfix">
                                                @foreach ($Attribute['attribute_values'] as $key => $type)
                                                    {{-- @dd($type == 1) --}}
                                                    <li class="lvl1 more-item"><input type="checkbox"
                                                            value="{{ $Attribute['attribute_values'][$key] }}"
                                                            id="{{ $Attribute['attribute_values'][$key] }}"
                                                            class="product-filter"
                                                            data-attribute="{{ $Attribute['attribute_name'] }}"
                                                            {{ $Attribute['is_checked'][$key] == true ? 'checked' : '' }}><label
                                                            for="{{ $Attribute['attribute_values'][$key] }}"><span></span>{{ $Attribute['attribute_values'][$key] }}</label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                        {{-- @dd(count($products_listing) < 1); --}}
                        @if ($products_type == 'regular')
                            @if (count($brands) >= 1 && count($products_listing) < 1)
                                <div class="sidebar-widget filterBox filter-widget brand-filter">
                                    <div class="widget-title">
                                        <h2>Brands</h2>
                                    </div>
                                    <div class="widget-content filterDD">
                                        <ul class="clearfix">
                                            <ul class="sidebar-categories scrollspy morelist clearfix">
                                                @foreach ($brands as $brand)
                                                    <li class="lvl1 more-item">
                                                        <input type="checkbox" value="{{ $brand->slug }}"
                                                            id="{{ $brand->slug }}" class="brand"
                                                            {{ $brand->is_checked == true ? 'checked' : '' }}>
                                                        <label for="{{ $brand->slug }}"
                                                            class="d-flex align-items-center"><span></span>
                                                            <div class="filter-brand-img"><img
                                                                    src="{{ dynamic_image(getMediaImageUrl($brand->image), 40) }}"
                                                                    alt="{{ getDynamicTranslation('brands', 'name', $brand->id, $language_code) }}"
                                                                    srcset=""></div>
                                                            {{ getDynamicTranslation('brands', 'name', $brand->id, $language_code) }}
                                                        </label>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </ul>
                                    </div>
                                </div>
                            @endif
                        @endif
                        @if (count($products_listing) >= 1)
                            <div class="text-right">
                                <a wire:navigate href="{{ customUrl(url()->current()) }}"
                                    class="btn btn-sm btn-secondary">{{ labels('front_messages.clear', 'Clear') }}</a>
                                <button
                                    class="btn btn-sm product-filter-btn">{{ labels('front_messages.filter', 'filter') }}</button>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!--End Sidebar-->

            <!--Products-->
            <div class="col-12 col-sm-12 col-md-12 {{ $showFilter == true ? 'col-lg-9' : '' }}  main-col">

                @if (count($sub_categories) >= 1)
                    <x-utility.categories.subCategories.subCategoriesSection :$sub_categories :language_code="$language_code" />
                @endif
                @if (count($products_listing) < 1)
                    @php
                        $title = labels('front_messages.no_product_found', 'No Product Found!');
                    @endphp
                    <x-utility.others.not-found :$title />
                @else
                    <!--Toolbar-->
                    <div class="toolbar toolbar-wrapper shop-toolbar">
                        <div class="row align-items-center">
                            <div
                                class="col-4 col-sm-2 col-md-4 col-lg-4 text-left filters-toolbar-item d-flex order-1 order-sm-0">
                                <button type="button"
                                    class="p-0 px-2 btn icon anm anm-sliders-hr d-inline-flex d-lg-none me-2"><ion-icon
                                        class="btn-filter icon fs-5" name="options-outline"></ion-icon><span
                                        class="d-none">{{ labels('front_messages.filter', 'filter') }}</span></button>
                                <div class="filters-item d-flex align-items-center">
                                    <label
                                        class="mb-0 me-2 d-none d-lg-inline-block">{{ labels('front_messages.view_as', 'View as') }}:</label>
                                    <div class="grid-options view-mode d-flex">
                                        <a class="list_view icon-mode mode-list d-block {{ $view_mode == 'list' ? 'active' : '' }}"
                                            data-col="1" data-value="list"></a>
                                        <a class="icon-mode mode-grid grid-2 d-block" data-col="2"></a>
                                        <a class="icon-mode mode-grid grid-3 d-md-block" data-col="3"></a>
                                        <a class="icon-mode mode-grid grid-4 d-lg-block {{ $view_mode == 'list' ? '' : 'active' }}"
                                            data-col="4"></a>
                                    </div>
                                </div>
                            </div>
                            <div
                                class="col-12 col-sm-4 col-md-4 col-lg-4 text-center product-count order-0 order-md-1 mb-3 mb-sm-0">
                                @if (count($products_listing) >= 1)
                                    <span
                                        class="toolbar-product-count">{{ labels('front_messages.showing', 'Showing') }}:
                                        {{ count($products_listing) }} {{ labels('front_messages.out_of', 'Out of') }}
                                        {{ $total_products }}
                                        {{ labels('front_messages.products', 'products') }}</span>
                                @endif
                            </div>
                            <div
                                class="col-8 col-sm-6 col-md-4 col-lg-4 text-right filters-toolbar-item d-flex justify-content-end order-2 order-sm-2">
                                <div class="filters-item d-flex align-items-center ms-2 ms-lg-3">
                                    <label for="perPage"
                                        class="mb-0 me-2 text-nowrap d-none">{{ labels('front_messages.per_page', 'Per Page') }}:</label>
                                    <select name="perPage" id="perPage" class="filters-toolbar-perPage me-2">
                                        <option value="12" {{ $perPage == '12' ? 'selected' : '' }}>12
                                        </option>
                                        <option value="16" {{ $perPage == '16' ? 'selected' : '' }}>16
                                        </option>
                                        <option value="20" {{ $perPage == '20' ? 'selected' : '' }}>20
                                        </option>
                                        <option value="24" {{ $perPage == '24' ? 'selected' : '' }}>24
                                        </option>
                                    </select>
                                    <label for="SortBy"
                                        class="mb-0 me-2 text-nowrap d-none">{{ labels('front_messages.sort_by', 'Sort by') }}:</label>
                                    <select name="SortBy" id="SortBy" class="filters-toolbar-sort">
                                        <option value="" {{ $sorted_by == '' ? 'selected' : '' }}>
                                            {{ labels('front_messages.featured', 'Featured') }}
                                        </option>
                                        <option value="top-rated" {{ $sorted_by == 'top-rated' ? 'selected' : '' }}>
                                            {{ labels('front_messages.top_rated', 'Top Rated') }}</option>
                                        <option value="price-asc" {{ $sorted_by == 'price-asc' ? 'selected' : '' }}>
                                            {{ labels('front_messages.price_low_to_high', 'Price, low to high') }}
                                        </option>
                                        <option value="price-desc" {{ $sorted_by == 'price-desc' ? 'selected' : '' }}>
                                            {{ labels('front_messages.price_high_to_low', 'Price, high to low') }}
                                        </option>
                                        <option value="oldest-first"
                                            {{ $sorted_by == 'oldest-first' ? 'selected' : '' }}>
                                            {{ labels('front_messages.old_to_new', 'Old to New') }}</option>
                                        <option value="latest-products"
                                            {{ $sorted_by == 'latest-products' ? 'selected' : '' }}>
                                            {{ labels('front_messages.new_to_old', 'New to Old') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--End Toolbar-->
                    <!--Product Grid-->
                    @php
                        $store_settings = getStoreSettings();
                    @endphp
                    <div class="grid-products grid-view-items mb-4">
                        <div
                            class="row col-row product-options {{ ($store_settings['products_display_style_for_web'] ?? '') == 'products_display_style_for_web_3' ? 'pro-hover3' : '' }} {{ $view_mode == 'list' ? 'list-style' : 'row-cols-lg-4 row-cols-md-3 row-cols-sm-3 row-cols-2' }} ">
                            @foreach ($products_listing as $details)
                                @php
                                    $store_settings = getStoreSettings();
                                    $component = getProductDisplayComponent($store_settings);
                                @endphp

                                <x-dynamic-component :component="$component" :details="$details" />
                            @endforeach
                        </div>
                    </div>
                @endif

                <!--End Product Grid-->
                <div class="d-flex justify-content-between align-content-center">
                    {{-- @dd($links) --}}
                    {!! $links !!}
                </div>
            </div>
            <!--End Products-->
        </div>
    </div>
    <input type="hidden" name="min-price" id='min-price'
        value="{{ currentCurrencyPrice($min_max_price['min_price']) }}">
    <input type="hidden" name="max-price" id='max-price'
        value="{{ currentCurrencyPrice($min_max_price['max_price']) }}">
    <input type="hidden" name="selected_max_price" id='selected_max_price'
        value="{{ currentCurrencyPrice($min_max_price['selected_max_price']) }}">
    <input type="hidden" name="selected_min_price" id='selected_min_price'
        value="{{ currentCurrencyPrice($min_max_price['selected_min_price']) }}">
</div>
