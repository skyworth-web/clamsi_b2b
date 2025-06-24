<div id="page-content">
    @php
        $store_settings = getStoreSettings();
        $language_code = get_language_code();
    @endphp
    <!--Page Header-->
    <x-utility.breadcrumbs.breadcrumbOne :$breadcrumb />
    @php
        $component = getBrandDisplayComponent($store_settings);
    @endphp
    {{-- @dd($language_code); --}}
    <x-dynamic-component :component="$component" :brands="$brands" :language_code="$language_code" />
    {{-- <x-utility.brands.cards.listCardThree :$brands /> --}}
    <!--End Page Header-->
</div>
